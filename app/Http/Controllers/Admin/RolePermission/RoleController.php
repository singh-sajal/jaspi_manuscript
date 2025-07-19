<?php

namespace App\Http\Controllers\Admin\RolePermission;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Admin\RolePermission\RoleRequest;
use App\Http\Controllers\Admin\RolePermission\RoleRepository;

class RoleController extends Controller
{
    private $repository;

    public function __construct(RoleRepository $repository)
    {
        $this->repository = $repository;
    }
    public function index(Request $request)
    {
        return $this->repository->index($request);
    }
    public function create()
    {
        return $this->repository->create();
    }

    public function store(RoleRequest $request)
    {
        return $this->repository->store($request);
    }

    public function edit($id)
    {
        return $this->repository->edit($id);
    }
    public function update(RoleRequest $request, $id)
    {
        return $this->repository->update($request, $id);
    }

    public function show($id)
    {
        return $this->repository->show($id);
    }

    public function assignPermission(Request $request, $roleId)
    {
        return $this->repository->assignPermission($request, $roleId);
    }
    public function destroy($id)
    {
        return $this->repository->destroy($id);
    }


    // Permission Manager-----------------
    public function permissionManager(Request $request)
    {
        return $this->repository->permissionManager($request);
    }


    public function destroyPermission($id)
    {
        return $this->repository->destroyPermission($id);
    }
}
