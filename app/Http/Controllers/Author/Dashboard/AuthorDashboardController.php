<?php

namespace App\Http\Controllers\Author\Dashboard;

use Illuminate\Http\Request;
use App\Http\Controllers\Author\Dashboard\AuthorDashboardRepository;
use App\Http\Controllers\Controller;

class AuthorDashboardController extends Controller
{
    private $repository;

    public function __construct(AuthorDashboardRepository $repository)
    {
        $this->repository = $repository;
    }


    public function dashboard(Request $request)
    {
        return $this->repository->dashboard($request);
    }
}
