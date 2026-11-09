<?php

namespace App\Http\Controllers\Admin\Integration;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;


class IntegrationController extends Controller
{
    private $repository;

    public function __construct(IntegrationRepository $repository)
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

    public function store(IntegrationRequest $request)
    {
        return $this->repository->store($request);
    }

    public function edit($id)
    {
        return $this->repository->edit($id);
    }

    public function update(IntegrationRequest $request, $id)
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
}