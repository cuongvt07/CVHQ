<div x-data="{ 
        notifications: [],
        add(e) {
            this.notifications.push({
                id: e.timeStamp,
                type: e.detail.type || 'info',
                message: e.detail.message,
                show: false
            });
            
            this.$nextTick(() => {
                let n = this.notifications.find(n => n.id === e.timeStamp);
                if (n) n.show = true;
            });

            // Reduced timeout to 2 seconds for faster hiding
            setTimeout(() => {
                this.remove(e.timeStamp);
            }, 2000);
        },
        remove(id) {
            let n = this.notifications.find(n => n.id === id);
            if (n) {
                n.show = false;
                setTimeout(() => {
                    this.notifications = this.notifications.filter(n => n.id !== id);
                }, 300);
            }
        }
     }" 
     @notify.window="add($event)"
     class="fixed top-6 right-6 z-[9999] flex flex-col gap-2 pointer-events-none w-64">
    
    <template x-for="n in notifications" :key="n.id">
        <div x-show="n.show"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 translate-x-8 scale-95"
             x-transition:enter-end="opacity-100 translate-x-0 scale-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100 translate-x-0 scale-100"
             x-transition:leave-end="opacity-0 translate-x-8 scale-95"
             class="pointer-events-auto relative group">
            
            <div class="glass-card p-2.5 border border-white/20 shadow-xl overflow-hidden rounded-xl flex items-center gap-2 bg-white/90 backdrop-blur-md">
                <!-- Status Icon (Smaller) -->
                <div class="shrink-0">
                    <template x-if="n.type === 'success'">
                        <div class="w-6 h-6 rounded-full bg-emerald-500/10 flex items-center justify-center text-emerald-500">
                            <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="4" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                        </div>
                    </template>
                    <template x-if="n.type === 'error'">
                        <div class="w-6 h-6 rounded-full bg-rose-500/10 flex items-center justify-center text-rose-500">
                            <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="4" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" x2="12" y1="8" y2="12"/><line x1="12" x2="12.01" y1="16" y2="16"/></svg>
                        </div>
                    </template>
                    <template x-if="n.type === 'info' || n.type === 'warning'">
                        <div class="w-6 h-6 rounded-full bg-electric-blue/10 flex items-center justify-center text-electric-blue">
                            <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="4" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" x2="12" y1="12" y2="16"/><line x1="12" x2="12.01" y1="8" y2="8"/></svg>
                        </div>
                    </template>
                </div>

                <!-- Content (Smaller Text) -->
                <div class="flex-1 min-w-0">
                    <p class="text-[11px] font-bold text-slate-800 leading-tight truncate" x-text="n.message"></p>
                </div>

                <!-- Close Button (Smaller) -->
                <button @click="remove(n.id)" class="text-slate-300 hover:text-slate-900 transition-colors shrink-0">
                    <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
                </button>

                <!-- Progress Timer Bar (Faster) -->
                <div class="absolute bottom-0 left-0 h-0.5 bg-electric-blue/20" 
                     x-init="setTimeout(() => $el.style.width = '0%', 50); $el.style.width = '100%'"
                     style="transition: width 2000ms linear;"></div>
            </div>
        </div>
    </template>
</div>
