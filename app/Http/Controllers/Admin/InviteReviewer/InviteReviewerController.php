<?php

namespace App\Http\Controllers\Admin\InviteReviewer;

use App\Http\Controllers\Admin\InviteReviewer\InviteReviewerRequest;
use App\Http\Controllers\Admin\InviteReviewer\InviteReviewerRepository;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;


class InviteReviewerController extends Controller
{

    private $repository;
    public function __construct(InviteReviewerRepository $repository)
    {
        $this->repository = $repository;
    }
    public function index(Request $request)
    {
        return $this->repository->index($request);
    }

    public function store(InviteReviewerRequest $request)
    {
        return $this->repository->store($request);
    }

    public function resend(Request $request, $id)
    {
        return $this->repository->resend($request, $id);
    }
    public function destroy(Request $request, $id)
    {
        return $this->repository->destroy($request, $id);
    }

    public function sendBulk(Request $request)
    {
        return $this->repository->sendBulk($request);
    }

    public function import(Request $request)
    {
        return $this->repository->import($request);
    }
    public function bulkInvite(Request $request)
    {
        return $this->repository->bulkInvite($request);
    }

    public function export()
    {
        return $this->repository->export($request);
    }
}
