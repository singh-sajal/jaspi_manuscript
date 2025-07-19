<?php

namespace App\Http\Controllers\Author\Dashboard;

use Carbon\Carbon;
use App\Models\Application;

class AuthorDashboardRepository
{

    public function dashboard($request)
    {
        $counts = $this->getCounts();

        $recentApplications = $this->getRecentApplications();
        $charts = $this->getCharts();

        return view('author.cms.dashboard', compact('counts', 'recentApplications', 'charts'));
    }



    public function getCounts($request = null)
    {
        $counts = [];
        $today = Carbon::today();

        $applicationQuery = Application::query()->where('author_id', authUser()->id);

        $counts['Total Applications'] = $applicationQuery->count();
        $statusCounts = (clone $applicationQuery)->selectRaw('status, count(*) as count')->groupBy('status')->pluck('count', 'status');

        $counts['Incomplete/Revised Applications'] = ($statusCounts['incomplete'] ?? 0) + ( $statusCounts['revised_submission'] ?? 0);
        // $counts['Revised Applications'] = $statusCounts['revised_submission'] ?? 0;
        $counts['Submitted Applications'] = $statusCounts['submitted'] ?? 0;
        $counts['Under Review Applications'] = $statusCounts['under review'] ?? 0;
        $counts['Rejected Applications'] = $statusCounts['rejected'] ?? 0;
        $counts['Published Applications'] = $statusCounts['published'] ?? 0;

        return $counts;
    }

    private function getRecentApplications()
    {
        return Application::where('author_id', authUser()->id)->orderBy('created_at', 'desc')->limit(5)->get();
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
        $statusCounts = Application::where('author_id', authUser()->id)
            ->selectRaw('LOWER(status) as status, COUNT(*) as count')
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
        $monthly = Application::where('author_id', authUser()->id)
            ->selectRaw("DATE_FORMAT(created_at, '%M') as month, COUNT(*) as count")
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

        $daily = Application::where('author_id', authUser()->id)
            ->whereBetween('created_at', [$from, $to])
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
        $counts = Application::where('author_id', authUser()->id)
            ->whereIn('status', ['Approved', 'Rejected'])
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
