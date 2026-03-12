<?php

namespace App\Livewire\Admin;

use App\Models\Announcement;
use App\Models\Fee;
use App\Models\Payment;
use Livewire\Attributes\Layout;
use Livewire\Component;

class AdminDashboard extends Component
{
    #[Layout('layouts.app')]
    public function render()
    {
        $recentAnnouncements = Announcement::orderByDesc('created_at')
            ->limit(5)
            ->get();

        $pendingReviewCount = Payment::where('status', 'pending_review')->count();

        $lastPeriod = Fee::query()
            ->whereNotNull('issued_at')
            ->orderByDesc('issued_at')
            ->value('period');

        $lastPeriodStats = null;
        $lastPeriodByGroup = collect();

        if ($lastPeriod) {
            $fees = Fee::where('period', $lastPeriod)->with('group')->get();
            $total = $fees->count();
            $paid = $fees->where('status', 'paid')->count();
            $lastPeriodStats = (object) [
                'period' => $lastPeriod,
                'total' => $total,
                'paid' => $paid,
            ];

            $lastPeriodByGroup = $fees
                ->groupBy('group_id')
                ->map(function ($groupFees, $groupId) {
                    $group = $groupFees->first()->group;
                    return (object) [
                        'group_name' => $group ? $group->name : 'Sin grupo',
                        'total' => $groupFees->count(),
                        'paid' => $groupFees->where('status', 'paid')->count(),
                    ];
                })
                ->values();
        }

        return view('livewire.admin.admin-dashboard', [
            'recentAnnouncements' => $recentAnnouncements,
            'pendingReviewCount' => $pendingReviewCount,
            'lastPeriodStats' => $lastPeriodStats,
            'lastPeriodByGroup' => $lastPeriodByGroup,
        ]);
    }
}
