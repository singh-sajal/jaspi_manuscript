<?php
namespace App\Http\Controllers\Staff;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Staff\StaffRequest;
use App\Http\Controllers\Staff\StaffRepository;


class StaffController extends Controller
{
    private $repository;

    public function __construct(StaffRepository $repository)
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

    public function store(StaffRequest $request)
    {
        return $this->repository->store($request);
    }

    public  function edit($id)
    {
        return $this->repository->edit($id);
    }

    public function update(StaffRequest $request, $id)
    {
        return $this->repository->update($request, $id);
    }

    public function destroy($id)
    {
        return $this->repository->destroy($id);
    }

    
    public function generateCredentials(Request $request, $uuid)
    {

        return $this->repository->generateCredentials($request, $uuid);
    }

    public function show($id)
    {
        return $this->repository->show($id);
    }

    public function toggleStatus(Request $request, $uuid)
    {
        return $this->repository->toggleStatus($request, $uuid);
    }
}
