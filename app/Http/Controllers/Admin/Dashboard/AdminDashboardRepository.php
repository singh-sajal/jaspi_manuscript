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

        return view('admin.cms.dashboard', compact('counts', 'recentApplications', 'charts', 'userCounts'));
    }



    public function getCounts($request = null)
    {
        $counts = [];
        $today = Carbon::today();

        $applicationQuery = Application::query()->where('status','!=' ,'incomplete');

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
    private function getRecentApplications()
    {
        return Application::where('status', '!=', 'incomplete')->orderBy('created_at', 'desc')->limit(5)->get();
    }
    private function getCharts()
    {
        $charts['applicationStatusChart'] = $this->applicationStatusChartBuilder();
        $charts['monthlyApplicationChart'] = $this->monthlyApplicationChartBuilder();
        $charts['lastSevenDaysApplicationChart'] = $this->lastSevenDaysApplicationChartBuilder();
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

    public function monthlyApplicationChartBuilder()
    {
        // Saare months ka fixed array (January to December)
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

        // Tere query se month wise count nikal rahe hain
        $monthly = Application::selectRaw("DATE_FORMAT(created_at, '%M') as month, COUNT(*) as count")
            ->groupBy('month')
            ->orderByRaw("STR_TO_DATE(month, '%M')")
            ->pluck('count', 'month')
            ->toArray();

        // Ab allMonths ko loop kar ke agar count na ho to 0 set kar dete hain
        $counts = [];
        foreach ($allMonths as $month) {
            $counts[$month] = $monthly[$month] ?? 0;
        }

        return [
            'labels' => array_keys($counts),
            'data' => array_values($counts)
        ];
    }


    public function lastSevenDaysApplicationChartBuilder()
    {
        $from = now()->subDays(6)->startOfDay();
        $to = now()->endOfDay();

        $daily = Application::whereBetween('created_at', [$from, $to])
            ->selectRaw("DATE(created_at) as date, COUNT(*) as count")
            ->groupBy('date')
            ->orderBy('date')
            ->pluck('count', 'date');

        // Ensure all 7 days are present
        $labels = collect();
        $data = collect();

        for ($i = 0; $i < 7; $i++) {
            $day = now()->subDays(6 - $i)->format('Y-m-d');
            $labels->push($day);
            $data->push($daily[$day] ?? 0);
        }

        return [
            'labels' => $labels,
            'data' => $data
        ];
    }

    public function approvedAndRejectedChartBuilder()
    {
        $counts = Application::whereIn('status', ['Approved', 'Rejected'])
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
