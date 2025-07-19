<?php

namespace App\Http\Controllers\Admin\Checklist;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Admin\Checklist\ChecklistRequest;


class ChecklistController extends Controller
{
    private $repository;

    public function __construct(ChecklistRepository $repository)
    {
        $this->repository = $repository;
    }

    public function index(Request $request)
    {
        return $this->repository->index($request);
    }


    public function create(Request $request)
    {
        return $this->repository->create($request);
    }

    public function store(ChecklistRequest $request)
    {
        return $this->repository->store($request);
    }

    public  function edit($id)
    {
        return $this->repository->edit($id);
    }

    public function update(ChecklistRequest $request, $id)
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
