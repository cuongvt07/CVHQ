<?php

namespace App\Livewire\Dashboard;

use App\Models\Invoice;
use App\Models\Product;
use App\Models\Customer;
use Carbon\Carbon;
use Livewire\Component;

class DashboardIndex extends Component
{
    public function getStats()
    {
        $today = Carbon::today();
        
        return [
            'revenue_today' => Invoice::whereDate('created_at', $today)->sum('final_amount'),
            'orders_today' => Invoice::whereDate('created_at', $today)->count(),
            'total_customers' => Customer::count(),
            'low_stock_count' => Product::whereRaw('stock_quantity < min_stock')->count(),
        ];
    }

    public function getRecentActivity()
    {
        return Invoice::with('customer')
            ->latest()
            ->take(5)
            ->get();
    }

    public function getChartData()
    {
        // Simple mock for a 7-day trend (in real apps, aggregate from DB)
        return [
            ['day' => 'Mon', 'val' => 45],
            ['day' => 'Tue', 'val' => 52],
            ['day' => 'Wed', 'val' => 48],
            ['day' => 'Thu', 'val' => 70],
            ['day' => 'Fri', 'val' => 65],
            ['day' => 'Sat', 'val' => 85],
            ['day' => 'Sun', 'val' => 95],
        ];
    }

    public function render()
    {
        return view('livewire.dashboard.dashboard-index', [
            'stats' => $this->getStats(),
            'activities' => $this->getRecentActivity(),
            'chartData' => $this->getChartData()
        ])->layout('layouts.app');
    }
}
