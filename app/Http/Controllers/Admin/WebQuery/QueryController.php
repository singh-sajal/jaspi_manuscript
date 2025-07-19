<?php
namespace App\Http\Controllers\Admin\WebQuery;

use App\Http\Controllers\Admin\WebQuery\QueryRepository;
use App\Http\Controllers\Admin\WebQuery\QueryRequest;
use App\Http\Controllers\Controller;
use GuzzleHttp\Psr7\Query;
use Illuminate\Http\Request;

class QueryController extends Controller
{
    private $repository;

    public function __construct(QueryRepository $repository)
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

    public function store(QueryRequest $request)
    {
        return $this->repository->store($request);
    }

    public  function edit($id)
    {
        return $this->repository->edit($id);
    }

    public function update(QueryRequest $request, $id)
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
