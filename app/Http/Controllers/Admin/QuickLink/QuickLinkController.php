<?php

namespace App\Http\Controllers\Admin\QuickLink;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;


class QuickLinkController extends Controller
{
    private $repository;

    public function __construct(QuickLinkRepository $repository)
    {
        $this->repository = $repository;
    }

    public function index(Request $request)
    {
        return $this->repository->index($request);
    }


    public function create(QuickLinkRequest $request)
    {
        return $this->repository->create($request);
    }

    public function store(QuickLinkRequest $request)
    {
        return $this->repository->store($request);
    }

    public  function edit($id)
    {
        return $this->repository->edit($id);
    }

    public function update(QuickLinkRequest $request, $id)
    {
        return $this->repository->update($request, $id);
    }

    public function destroy($id)
    {
        return $this->repository->destroy($id);
    }

    public function show($id)
    {
        return $this->repository->show($id);
    }

    public function toggleStatus(Request $request, $id)
    {
        return $this->repository->toggleStatus($request, $id);
    }
}
