{{-- POS-specific JS: localStorage restore + keyboard shortcuts --}}
<script>
    document.addEventListener('livewire:init', function () {
        // Restore saved POS tabs from localStorage on full page load
        try {
            const saved = localStorage.getItem('cvha_pos_tabs');
            if (saved) {
                const parsed = JSON.parse(saved);
                if (parsed) Livewire.dispatch('restoreTabs', { payload: parsed });
            }
        } catch (e) {
            console.error('Restore POS tabs failed', e);
        }

        // Persist tabs when backend dispatches the event
        Livewire.on('posTabsUpdate', function (detail) {
            try {
                const data = Array.isArray(detail) ? detail[0] : detail;
                localStorage.setItem('cvha_pos_tabs', JSON.stringify(data));
            } catch (err) { /* ignore */ }
        });

        // Restore saved branch filter
        try {
            const savedBranch = localStorage.getItem('cvha_pos_branch');
            if (savedBranch && ['all', 'sg', 'hn'].includes(savedBranch)) {
                Livewire.dispatch('restoreBranch', { branch: savedBranch });
            }
        } catch (e) { /* ignore */ }

        // Persist branch when it changes
        Livewire.on('posBranchUpdate', function (detail) {
            try {
                const data = Array.isArray(detail) ? detail[0] : detail;
                localStorage.setItem('cvha_pos_branch', data.branch);
            } catch (err) { /* ignore */ }
        });
    });

    // Keyboard shortcuts for quick tab operations
    window.addEventListener('keydown', function (e) {
        const target = e.target;
        if (target && (target.tagName === 'INPUT' || target.tagName === 'TEXTAREA' || target.isContentEditable)) return;

        if (e.altKey && !e.shiftKey && e.key.toLowerCase() === 'n') { e.preventDefault(); Livewire.dispatch('addTab'); return; }
        if (e.altKey && !e.shiftKey && e.key.toLowerCase() === 'w') { e.preventDefault(); Livewire.dispatch('closeActiveTab'); return; }
        if (e.altKey && e.key === 'ArrowRight') { e.preventDefault(); Livewire.dispatch('nextTab'); return; }
        if (e.altKey && e.key === 'ArrowLeft')  { e.preventDefault(); Livewire.dispatch('prevTab'); return; }
    });
</script>
