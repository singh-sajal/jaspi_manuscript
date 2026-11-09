<?php

namespace App\Http\Controllers\Admin\Application;


use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Admin\Application\PublishRequest;
use App\Http\Controllers\Admin\Application\ApplicationRequest;
use App\Http\Controllers\Admin\Application\ApplicationRepository;
use App\Http\Controllers\Admin\Application\InactiveApplicationRepository;
use App\Http\Controllers\Admin\Application\ReviewDeadlineRepository;


class ApplicationController extends Controller
{
    private $repository;
    private $inactiveRepository;
    private $reviewDeadlineRepository;

    public function __construct(
        ApplicationRepository $repository,
        InactiveApplicationRepository $inactiveRepository,
        ReviewDeadlineRepository $reviewDeadlineRepository
    ) {
        $this->repository = $repository;
        $this->inactiveRepository = $inactiveRepository;
        $this->reviewDeadlineRepository = $reviewDeadlineRepository;
    }

    public function index(Request $request)
    {
        if ($request->type === 'inactive') {
            return $this->inactiveRepository->index($request);
        }

        if ($request->type === 'review-deadline') {
            return $this->reviewDeadlineRepository->index($request);
        }

        return $this->repository->index($request);
    }

    /**
     * One-click manual reminder to a single reviewer from the Review Deadlines tab.
     */
    public function sendReviewerReminder(Request $request, $id)
    {
        return $this->reviewDeadlineRepository->sendReminder($request, $id);
    }

    /**
     * Mail every manuscript owner in the Inactive section (one email per manuscript).
     */
    public function inactiveBulkMail(Request $request)
    {
        return $this->inactiveRepository->bulkMail($request);
    }

    /**
     * Mail a single manuscript owner from the Inactive list's row action.
     */
    public function inactiveSendMailForm(Request $request, $uuid)
    {
        return $this->inactiveRepository->sendMailForm($request, $uuid);
    }

    public function inactiveSendMail(Request $request, $uuid)
    {
        return $this->inactiveRepository->sendMail($request, $uuid);
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

    public function file_upload(Request $request, $id = null, $type = null, $app_id = null, $legend = null)
    {
        return $this->repository->fileUpload($request, $id, $type, $app_id, $legend);
    }


    public function fileDestroy($id, $app_id)
    {
        return $this->repository->fileDestroy($id, $app_id);
    }



    // public function editor_accepts($uuid)
    // {
    //     return $this->repository->editor_accepts($uuid);
    // }
    // public function editor_rejects($uuid)
    // {
    //     return $this->repository->editor_rejects($uuid);
    // }
    // public function editor_revise($uuid)
    // {
    //     return $this->repository->editor_revise($uuid);
    // }
    public function editor_final_decision(Request $request, $uuid, $action)
    {
        return $this->repository->editor_final_decision($request, $uuid, $action);
    }

    public function re_review(Request $request, $uuid) {
        return $this->repository->re_review($request, $uuid);
    }

    public function remindReviewers(Request $request, $uuid) {
        return $this->repository->remindReviewers($request, $uuid);
    }
    // public function acceptance($uuid)
    // {
    //     return $this->repository->acceptance($uuid);
    // }

    // public function rejection($uuid)
    // {
    //     return $this->repository->rejection($uuid);
    // }
    // public function revise($uuid)
    // {
    //     return $this->repository->revise($uuid);
    // }
    public function assistantToChiefForApproval(Request $request, $uuid)
    {
        return $this->repository->assistantToChiefForApproval($request, $uuid);
    }
    public function chiefToAssistanResponse(Request $request, $uuid)
    {
        return $this->repository->chiefToAssistanResponse($request, $uuid);
    }
    public function seeChiefDecision(Request $request, $uuid)
    {
        return $this->repository->seeChiefDecision($request, $uuid);
    }
    public function chiefReadMore($uuid, $timeline_id = null)
    {
        return $this->repository->chiefReadMore($uuid, $timeline_id);
    }
    public function assistant_editor_decision(Request $request, $uuid, $action)
    {
        return $this->repository->assistant_editor_decision($request, $uuid, $action);
    }


    public function assignApplication(request $request, $uuid)
    {
        return $this->repository->assignApplication($request, $uuid);
    }

    public function handlingEditorDecision(Request $request, $uuid)
    {
        return $this->repository->handlingEditorDecision($request, $uuid);
    }

    public function handlingEditorRejection($uuid)
    {
        return $this->repository->handlingEditorRejection($uuid);
    }

    public function viewAbstract($uuid)
    {
        return $this->repository->viewAbstract($uuid);
    }

    public function assignReviewer(request $request, $uuid)
    {
        return $this->repository->assignReviewer($request, $uuid);
    }

    public function reviewed(Request $request, $uuid)
    {
        return $this->repository->reviewed($request, $uuid);
    }

    public function readMistake($uuid, $timeline_id = null)
    {
        return $this->repository->readMistake($uuid, $timeline_id);
    }
    public function reviewerReadMore($uuid, $timeline_id = null)
    {
        return $this->repository->reviewerReadMore($uuid, $timeline_id);
    }

    public function techEditorReadMore($uuid, $timeline_id)
    {
        return $this->repository->techEditorReadMore($uuid, $timeline_id);
    }

    public function techEditorDecision(Request $request, $uuid)
    {
        return $this->repository->techEditorDecision($request, $uuid);
    }

    public function authorUpdate($uuid, $timeline_id = null)
    {
        return $this->repository->authorUpdate($uuid, $timeline_id);
    }

    public function assignPublisher(Request $request, $uuid)
    {
        return $this->repository->assignPublisher($request, $uuid);
    }

    public function formatted_document(PublishRequest $request, $app_id)
    {
        return $this->repository->formatted_document($request, $app_id);
    }

    public function send_for_proofreading(Request $request, $uuid)
    {
        return $this->repository->send_for_proofreading($request, $uuid);
    }

    public function acceptForPublication(Request $request, $uuid)
    {
        return $this->repository->acceptForPublication($request, $uuid);
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

    public function editEditorDecision(Request $request, $uuid)
    {
        return $this->repository->editEditorDecision($request, $uuid);
    }

    public function rev_decision(Request $request, $uuid)
    {
        return $this->repository->rev_decision($request, $uuid);
    }

    public function reviewerRejection($uuid, $type = 'internal')
    {
        return $this->repository->reviewerRejection($uuid, $type);
    }

    public function reviewer(Request $request, $uuid)
    {
        return $this->repository->reviewer($request, $uuid);
    }

    public function reviewer_update(Request $request, $uuid, $reviewer_id)
    {
        return $this->repository->reviewer_update($request, $uuid, $reviewer_id);
    }

    public function reviewer_delete(Request $request, $uuid, $reviewer_id)
    {
        return $this->repository->reviewer_delete($request, $uuid, $reviewer_id);
    }

    public function documents(Request $request)
    {
        return $this->repository->documents($request);
    }
    public function articleInformation(Request $request)
    {
        return $this->repository->articleInformation($request);
    }
    public function coAuthors(Request $request)
    {
        return $this->repository->coAuthors($request);
    }

    // public function preview(Request $request)
    // {
    //     return $this->repository->preview($request);
    // }

    public function showCheckList(Request $request, $uuid)
    {
        return $this->repository->showCheckList($request, $uuid);
    }

    public function publish(Request $request, $uuid)
    {
        return $this->repository->publish($request, $uuid);
    }

    public function direct_publish(Request $request, $uuid)
    {
        return $this->repository->direct_publish($request, $uuid);
    }

    public function change_status(Request $request)
    {
        // return 1;
        return $this->repository->change_status($request);
    }

    public function add_doi(Request $request, $uuid)
    {
        return $this->repository->add_doi($request, $uuid);
    }
}
