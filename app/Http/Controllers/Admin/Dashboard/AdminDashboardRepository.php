<?php

namespace App\Http\Controllers\Admin\Dashboard;

use App\Models\Admin;
use Carbon\Carbon;
use App\Models\Application;
use App\Models\Author;

class AdminDashboardRepository
{

    public function dashboard($request)
    {
        $counts = $this->getCounts();
        $userCounts = $this->getUserCounts();
        $recentApplications = $this->getRecentApplications();
        $charts = $this->getCharts();
        $years = $this->getAvailableYears();

        return view('admin.cms.dashboard', compact('counts', 'recentApplications', 'charts', 'userCounts', 'years'));
    }



    public function getCounts($request = null)
    {
        $counts = [];

        $applicationQuery = Application::query()->where('status', '!=', 'incomplete');
        if (!isSuperAdmin()) {
            $applicationQuery = $applicationQuery->where('assigned_to_id', authUser('admin')->id)
                // ->where('status','!=','incomplete')
                ->orWhereHas('timelines', function ($query) {
                    $query->where('assigned_to_id', authUser('admin')->id);
                });
        }


        $counts['Total Applications'] = $applicationQuery->count();
        $statusCounts = (clone $applicationQuery)->selectRaw('status, count(*) as count')
            ->groupBy('status')->pluck('count', 'status');

        $counts['Submitted Applications'] = $statusCounts['submitted'] ?? 0;
        $counts['Under Review Applications'] = $statusCounts['under review'] ?? 0;
        $counts['Rejected Applications'] = $statusCounts['rejected'] ?? 0;
        $counts['Published Applications'] = $statusCounts['published'] ?? 0;
        $counts['Revised Applications'] = $statusCounts['revised_submission'] ?? 0;


        return $counts;
    }

    private function getUserCounts($request = null)
    {

        $adminCounts = Admin::selectRaw("COUNT(*) as total, SUM(CASE WHEN status = 1 THEN 1 ELSE 0 END) as active")->first();
        $counts['Total Users'] = $adminCounts->total;
        $counts['Active Users'] = $adminCounts->active;

        $authorCounts = Author::selectRaw("COUNT(*) as total, SUM(CASE WHEN status = 1 THEN 1 ELSE 0 END) as active")->first();
        $counts['Total Authors'] = $authorCounts->total;
        $counts['Active Authors'] = $authorCounts->active;

        return $counts;
    }

    public function getAvailableYears()
    {
        return Application::where('status', '!=', 'incomplete')
            ->selectRaw('YEAR(created_at) as year')
            ->distinct()
            ->orderByDesc('year')
            ->pluck('year');
    }

    private function getRecentApplications()
    {
        if (authUser('admin')->is_admin != 1) {
            return Application::where('status', '!=', 'incomplete')->where('assigned_to_id', authUser('admin')->id)
                ->orWhereHas('timelines', function ($query) {
                    $query->where('assigned_to_id', authUser('admin')->id);
                })->orderBy('created_at', 'desc')->limit(5)->get();
        }
        return Application::where('status', '!=', 'incomplete')->orderBy('created_at', 'desc')->limit(5)->get();
    }
    private function getCharts($year = null, $weekOffset = 0)
    {
        $charts['applicationStatusChart'] = $this->applicationStatusChartBuilder();
        $charts['monthlyApplicationChart'] = $this->monthlyApplicationChartBuilder($year);
        $charts['lastSevenDaysApplicationChart'] = $this->lastSevenDaysApplicationChartBuilder($weekOffset);
        $charts['approvedAndRejectedChart'] = $this->approvedAndRejectedChartBuilder();
        return $charts;
    }

    // ----Chart Helpers--------------------
    private function applicationStatusChartBuilder()
    {
        $statusCounts = Application::where('status', '!=', 'incomplete')->selectRaw('LOWER(status) as status, COUNT(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status');

        return [
            'labels' => $statusCounts->keys(),
            'data' => $statusCounts->values()
        ];
    }

    public function monthlyApplicationChartBuilder($year = null)
    {
        $allMonths = [
            'January',
            'February',
            'March',
            'April',
            'May',
            'June',
            'July',
            'August',
            'September',
            'October',
            'November',
            'December'
        ];

        if (!$year) {
            $year = $this->getAvailableYears()->first() ?? now()->year;
        }

        $monthly = Application::where('status', '!=', 'incomplete')
            ->whereYear('created_at', $year)
            ->selectRaw("DATE_FORMAT(created_at, '%M') as month, COUNT(*) as count")
            ->groupBy('month')
            ->orderByRaw("STR_TO_DATE(month, '%M')")
            ->pluck('count', 'month')
            ->toArray();

        $counts = [];
        foreach ($allMonths as $month) {
            $counts[$month] = $monthly[$month] ?? 0;
        }

        return [
            'labels' => array_keys($counts),
            'data' => array_values($counts),
            'year' => (int) $year,
        ];
    }



    public function lastSevenDaysApplicationChartBuilder($weekOffset = 0)
    {
        $weekOffset = (int) $weekOffset;

        $from = now()->startOfWeek()->addWeeks($weekOffset);
        $to = $from->copy()->endOfWeek();

        $daily = Application::where('status', '!=', 'incomplete')
            ->whereBetween('created_at', [$from->copy()->startOfDay(), $to->copy()->endOfDay()])
            ->selectRaw("DATE(created_at) as date, COUNT(*) as count")
            ->groupBy('date')
            ->pluck('count', 'date');

        $labels = collect();
        $data = collect();

        $cursor = $from->copy();
        for ($i = 0; $i < 7; $i++) {
            $day = $cursor->format('Y-m-d');
            $labels->push($cursor->format('D, d M'));
            $data->push($daily[$day] ?? 0);
            $cursor->addDay();
        }

        return [
            'labels' => $labels,
            'data' => $data,
            'weekOffset' => $weekOffset,
            'rangeLabel' => $from->format('d M Y') . ' - ' . $to->format('d M Y'),
        ];
    }

    public function approvedAndRejectedChartBuilder()
    {
        $counts = Application::whereIn('status', ['approved', 'rejected'])
            ->selectRaw("status, COUNT(*) as count")
            ->groupBy('status')
            ->pluck('count', 'status');

        [
            'labels' => ['Approved', 'Rejected'],
            'data' => [
                $counts['Approved'] ?? 0,
                $counts['Rejected'] ?? 0
            ]
        ];
    }
}
