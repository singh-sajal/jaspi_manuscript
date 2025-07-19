<?php

namespace App\Http\Controllers\Admin\Application;


use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Admin\Application\PublishRequest;
use App\Http\Controllers\Admin\Application\ApplicationRequest;
use App\Http\Controllers\Admin\Application\ApplicationRepository;


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


    // public function create(Request $request)
    // {
    //     return $this->repository->create($request);
    // }

    // public function store(ApplicationRequest $request)
    // {
    //     return $this->repository->store($request);
    // }

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


    public function show($id)
    {
        return $this->repository->show($id);
    }




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

    public function acceptance($uuid)
    {
        return $this->repository->acceptance($uuid);
    }

    public function rejection($uuid)
    {
        return $this->repository->rejection($uuid);
    }
    public function revise($uuid)
    {
        return $this->repository->revise($uuid);
    }


    public function assignApplication(request $request, $uuid)
    {
        return $this->repository->assignApplication($request, $uuid);
    }

    public function assignReviewer(request $request, $uuid)
    {
        return $this->repository->assignReviewer($request, $uuid);
    }

    public function reviewed(Request $request, $uuid)
    {
        return $this->repository->reviewed($request, $uuid);
    }

    public function readMistake($uuid)
    {
        return $this->repository->readMistake($uuid);
    }
    public function reviewerReadMore($uuid)
    {
        return $this->repository->reviewerReadMore($uuid);
    }

    public function authorUpdate($uuid)
    {
        return $this->repository->authorUpdate($uuid);
    }

    public function assignPublisher(Request $request, $uuid)
    {
        return $this->repository->assignPublisher($request, $uuid);
    }

    public function publish(PublishRequest $request, $uuid)
    {
        return $this->repository->publish($request, $uuid);
    }

    // public function toggleStatus(Request $request, $uuid)
    // {
    //     return $this->repository->toggleStatus($request, $uuid);
    // }

    public function reviewScore(Request $request, $uuid)
    {
        return $this->repository->reviewScore($request, $uuid);
    }

    public function editorDecision(Request $request, $uuid)
    {
        return $this->repository->editorDecision($request, $uuid);
    }
}
