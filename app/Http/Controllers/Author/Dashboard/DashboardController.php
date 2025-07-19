<?php

namespace App\Http\Controllers\Author\Dashboard;

use App\Models\Application;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;



class DashboardController extends Controller
{

    public function dashboard()
    {

        $appCount = Application::count();
        $submittedCount = Application::where('status', 'submitted')->count();
        $inprogressCount = Application::where('status', 'incomplete')->count();
        $approvedCount = Application::where('status', 'approved')->count();
        $rejectedCount = Application::where('status', 'rejected')->count();

        return view('author.cms.dashboard', compact(
            'appCount',
            'submittedCount',
            'inprogressCount',
            'approvedCount',
            'rejectedCount'
        ));
    }
}
