<?php

namespace App\Http\Controllers\Author\Application;




use Illuminate\Http\Request;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Author\Application\ApplicationRequest;
use App\Http\Controllers\Author\Application\ApplicationRepository;



class ApplicationController extends Controller
{
    private $repository;

    public function __construct(ApplicationRepository $repository)
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

    public function store(ApplicationRequest $request)
    {
        return $this->repository->store($request);
    }

    public  function edit($id)
    {
        return $this->repository->edit($id);
    }

    public function update(ApplicationRequest $request, $id)
    {
        return $this->repository->update($request, $id);
    }

    public function destroy($id)
    {
        return $this->repository->destroy($id);
    }


    // public function generateCredentials(Request $request, $uuid)
    // {

    //     return $this->repository->generateCredentials($request, $uuid);
    // }

    public function show($id)
    {
        return $this->repository->show($id);
    }

    // public function toggleStatus(Request $request, $uuid)
    // {
    //     return $this->repository->toggleStatus($request, $uuid);
    // }

    public function co_author(Request $request, $id)
    {
        return $this->repository->coAuthor($request, $id);
    }

    public function co_author_update(Request $request, $uuid, $author_id)
    {
        return $this->repository->coAuthorUpdate($request, $uuid, $author_id);
    }

    public function co_author_delete(Request $request, $uuid, $author_id)
    {
        return $this->repository->coAuthorDelete($request, $uuid, $author_id);
    }

    public function file_upload(Request $request, $id = null, $type, $app_id)
    {
        return $this->repository->fileUpload($request, $id, $type, $app_id);
    }

    public function fileDestroy($id, $app_id)
    {
        return $this->repository->fileDestroy($id, $app_id);
    }

    public function readMistake($uuid)
    {
        return $this->repository->readMistake($uuid);
    }
    
    public function reviewerReadMore($uuid)
    {
        return $this->repository->reviewerReadMore($uuid);
    }

    public function reSubmission(Request $request, $uuid)
    {
        return $this->repository->reSubmission($request, $uuid);
    }

    public function authorUpdate($uuid){
        return $this->repository->authorUpdate($uuid);
    }


    public function toggleStatus(Request $request, $uuid)
    {
        return $this->repository->toggleStatus($request, $uuid);
    }
}
