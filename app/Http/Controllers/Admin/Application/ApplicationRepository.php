<?php

namespace App\Http\Controllers\Admin\Application;


use App\Mail\AssistantEditorAssignmentMail;
use App\Mail\AuthorFinalDecisionMail;
use App\Mail\AuthorReviseMail;
use App\Mail\ChiefApprovalRequestMail;
use App\Mail\ChiefApprovalResponseMail;
use App\Mail\EditorAssignmentMail;
use App\Mail\EditorDeclinedAssignmentMail;
use App\Mail\EditorFinalDecisionMail;
use App\Mail\FormattedProofReadyMail;
use App\Mail\FormattedProofSubmittedMail;
use App\Mail\ManuscriptPublishedMail;
use App\Mail\ProofreadingMail;
use App\Mail\PublicationAcceptedMail;
use App\Mail\PublisherAssignmentMail;
use App\Mail\TechnicalEditorNotificationMail;
use App\Mail\ReviewerAssigned;
use App\Mail\ReviewerAcceptedMail;
use App\Mail\ReviewerDeclinedMail;
use App\Mail\ReviewerFinalDecisionMail;
use App\Mail\ReviewerPublishedMail;
use App\Mail\ReviewerReminder;
use App\Mail\ReviewSubmittedMail;
use App\Models\Admin;
use App\Models\Application;
use App\Models\ApplicationTimeline;
use App\Models\Attachment;
use App\Models\Author;
use App\Models\Integration;
use App\Models\ReviewAssignment;
use App\Rules\FileTypeRule;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Throwable;

class ApplicationRepository
{

    /**
     * Admin account kept selectable in the handling editor picker even though
     * admins are otherwise excluded from it.
     */
    const HANDLING_EDITOR_ADMIN_EXCEPTION = 'akanksha.itconsultant@gmail.com';

    protected $basePath = 'uploads/author/file/';

    private NotificationService $notifications;

    public function __construct(NotificationService $notifications)
    {
        $this->notifications = $notifications;
    }

    private function getApplication($uuid)
    {
        return Application::where('uuid', $uuid)->first();
    }

    private function applicationReference(Application $application): string
    {
        return $application->application_id ?: ($application->title ?: 'the manuscript');
    }

    private function notifyInApp($recipient, Application $application, array $payload, bool $forAuthor = false): void
    {
        if (!$recipient) {
            return;
        }

        $payload['url'] = $payload['url'] ?? ($forAuthor
            ? route('author.application.show', $application->uuid)
            : route('admin.application.show', $application->uuid));

        $this->notifications->send($recipient, $payload);
    }



    public function index($request)
    {

        $perPage = $request->perPage ?? 50;

        // $staffs = Admin::where('is_admin', false)->whereHas('roles', function ($q) {
        //     $q->where('name', 'Editor');
        // })->get();
        $type = $request->type ?? '';

        $applications = Application::query()->with('assignee', 'attachments');

        if ($request->type === 'completed') {
            return $this->completed_index($request);
        } elseif ($request->type === 'closed') {
            return $this->closed_index($request);
        } elseif ($request->type === 'mine') {
            return $this->my_index($request);
        } elseif ($request->type === 'published') {
            // Staff legacy links pointed at their old Completed view; admins
            // now see Published as a Closed filter.
            return authUser('admin')->is_admin == 0
                ? $this->completed_index($request)
                : $this->closed_index($request);
        } elseif (in_array($request->type, ['rejected', 'withdrawn', 'duplicate'], true)) {
            // These are filters inside Closed.
            return $this->closed_index($request);
        } elseif ($request->type === 'pending') {
            return $this->pending_index($request);
        } elseif ($request->type === 'chief') {
            return $this->chiefApproval_index($request);
        } else {
            if (authUser('admin')->is_admin == 0) {
                $applications = $applications->activeFor(authUser('admin'));
            } else {
                $applications = $applications->whereNotIn('status', ['incomplete', 'published', 'rejected', 'withdrawn', 'duplicate']);
            }
        }

        // $request->sortColumn= 23;
        // return $request->sortColumn;

        $this->search($request, $applications);
        $this->sort($request, $applications);


        $applications = $applications->paginate($perPage);
        // return $applications;

        if ($request->ajax()) {

            try {
                $datatable = view('admin.application.datatable', compact('applications'))->render();
                return response()->json([
                    'status' => '200',
                    'msg' => 'Data loaded',
                    'data' => $datatable,
                    'paginationInfo' => getPaginationInfo($applications)
                ], 200);
            } catch (Throwable $th) {
                return throwException($th);
            }
        }
        return view('admin.application.index', compact('type'));
    }

    public function completed_index($request)
    {
        $perPage = $request->perPage ?? 50;
        $type = $request->type ?? '';

        $applications = Application::query()
            ->with('assignee', 'attachments')
            ->completedFor(authUser('admin'));

        $this->search($request, $applications);
        $this->sort($request, $applications);

        $applications = $applications->paginate($perPage);

        if ($request->ajax()) {
            try {
                $datatable = view('admin.application.datatable', compact('applications'))->render();
                return response()->json([
                    'status' => '200',
                    'msg' => 'Data loaded',
                    'data' => $datatable,
                    'paginationInfo' => getPaginationInfo($applications)
                ], 200);
            } catch (Throwable $th) {
                return throwException($th);
            }
        }

        return view('admin.application.index', compact('type'));
    }

    public function closed_index($request)
    {
        $perPage = $request->perPage ?? 50;
        $type = $request->type ?? '';

        $applications = Application::query()
            ->with('assignee', 'attachments')
            ->closedFor(authUser('admin'));

        if (in_array($type, Application::CLOSED_STATUSES, true)) {
            $applications->where('status', $type);
        }

        $this->search($request, $applications);
        $this->sort($request, $applications);

        $applications = $applications->paginate($perPage);

        if ($request->ajax()) {
            try {
                $datatable = view('admin.application.datatable', compact('applications'))->render();
                return response()->json([
                    'status' => '200',
                    'msg' => 'Data loaded',
                    'data' => $datatable,
                    'paginationInfo' => getPaginationInfo($applications)
                ], 200);
            } catch (Throwable $th) {
                return throwException($th);
            }
        }

        return view('admin.application.index', compact('type'));
    }

    public function my_index($request)
    {
        abort_unless(canViewMyManuscripts(), 403, 'Unauthorized action.');

        $perPage = $request->perPage ?? 50;
        $type = $request->type ?? '';

        $applications = Application::query()
            ->with('assignee', 'attachments')
            ->myManuscripts(authUser('admin'));

        $this->search($request, $applications);
        $this->sort($request, $applications);

        $applications = $applications->paginate($perPage);

        if ($request->ajax()) {
            try {
                $datatable = view('admin.application.datatable', compact('applications'))->render();
                return response()->json([
                    'status' => '200',
                    'msg' => 'Data loaded',
                    'data' => $datatable,
                    'paginationInfo' => getPaginationInfo($applications)
                ], 200);
            } catch (Throwable $th) {
                return throwException($th);
            }
        }

        return view('admin.application.index', compact('type'));
    }

    public function pending_index($request)
    {
        $perPage = $request->perPage ?? 50;
        $type = $request->type ?? '';

        $applications = Application::query()->with('assignee', 'attachments')->pendingFor(authUser('admin'));

        $this->search($request, $applications);
        $this->sort($request, $applications);

        $applications = $applications->paginate($perPage);

        if ($request->ajax()) {
            try {
                $datatable = view('admin.application.datatable', compact('applications'))->render();
                return response()->json([
                    'status' => '200',
                    'msg' => 'Data loaded',
                    'data' => $datatable,
                    'paginationInfo' => getPaginationInfo($applications)
                ], 200);
            } catch (Throwable $th) {
                return throwException($th);
            }
        }
        return view('admin.application.index', compact('type'));
    }

    public function chiefApproval_index($request)
    {
        abort_unless(isSuperAdmin(), 403, 'Unauthorized action.');

        $perPage = $request->perPage ?? 50;
        $type = $request->type ?? '';

        $applications = Application::query()->with('assignee', 'attachments')
            ->whereIn('status', ['chief-review', 'chief-approval', 'editor-accepts', 'editor-rejects', 'editor-revise'])
            ->where('data->status', 'sended to chief approval');

        $this->search($request, $applications);
        $this->sort($request, $applications);

        $applications = $applications->paginate($perPage);

        if ($request->ajax()) {
            try {
                $datatable = view('admin.application.datatable', compact('applications'))->render();
                return response()->json([
                    'status' => '200',
                    'msg' => 'Data loaded',
                    'data' => $datatable,
                    'paginationInfo' => getPaginationInfo($applications)
                ], 200);
            } catch (Throwable $th) {
                return throwException($th);
            }
        }
        return view('admin.application.index', compact('type'));
    }

    public function create($request)
    {
        // Added 'id' to the selection list
        $authors = Author::all('id', 'name', 'username', 'uuid');

        $app_info = Application::where('uuid', $request->applicationId)->first() ?? [];

        return view('admin.application.final_docs', compact('authors', 'app_info'));
    }
    public function store($request)
    {


        try {
            // $author_id = Author::where('uuid', $request->author_uuid)->first()->id;

            $parts = explode('/', $request->application_id);
            // $parts[0] is "JASPI"
            // $parts[1] is "23"
            // $parts[2] is "2025"
            $number = $parts[1] ?? null;

            $data = [
                // 'author_id' => $author_id,
                'application_id' => $request->application_id,
                'title' => $request->title,
                'author_affiliation' => $request->author_affiliation,
                'author_orcid_id' => $request->author_orcid_id,
                'author_saspi_id' => $request->author_saspi_id,
                'app_num' => $number,
                'author_name' => $request->author_name,
                'published_doi' => $request->doi_number,
                // 'description' => $request->description,

            ];
            // if ($request->hasFile('file')) {
            //     $file = $request->file('file');
            //     $data['file_name'] = $file->getClientOriginalName();
            //     $data['file_type'] = $file->getClientOriginalExtension();
            //     $data['file_size'] = $file->getSize();
            //     $data['file_url'] = uploadFile($file, $this->basePath);
            // }
            // return $data;

            if (!empty($request->uuid)) {
                $application = Application::where('uuid', $request->uuid)->first();
                $application->update($data);
            } else {

                $application = Application::create($data);
            }
            // return $application;
            if ($application) {
                // return successResponse('Progress saved Successfully');
                return successResponse('Progress Saved Successfully', route('admin.application.create', [
                    'applicationId' => $application->uuid,
                    'step' => "documents",
                    // 'app_info'=>$application,
                ]));
            }
            return errorResponse();
        } catch (Throwable $th) {
            return throwException($th);
        }
    }


    public function show($uuid)
    {
        $application = Application::where('uuid', $uuid)->with([
            'attachments',
            'timelines.assignee',
            'timelines.assignedBy'
        ])->firstOrFail();

        // return

        $attachments = $application->attachments ?? collect();

        $manuScriptFiles = $attachments
            ->where('attachment_type', 'Upload Manuscript')
            ->sortBy('created_at');

        $correctedFiles = $attachments
            ->where('attachment_type', 'Corrected Proof')
            ->sortByDesc('created_at');

        $otherfiles = $attachments
            ->where('attachment_type', '!=', 'Upload Manuscript')
            ->where('attachment_type', '!=', 'Corrected Proof');

        $attachment_categories = $attachments->pluck('attachment_type')->toArray();

        $techApprovedCount = ApplicationTimeline::where('application_id', $application->id)
            ->where('status', 'technical checks approved')
            ->count();

        // ==========================================
        // PERMISSION & TIMELINE LOGIC
        // ==========================================
        $user = authUser('admin');
        $rev_type = $user->rev_type; // 👈 ADD THIS LINE BACK HERE
        $isAdmin = $user->is_admin == 1;

        // Fetch assigned actors from timelines
        $editor = ApplicationTimeline::where('application_id', $application->id)
            ->where('status', 'processing')
            ->orderBy('id', 'desc')
            ->first('assigned_to_id');

        $internal_reviewer = ApplicationTimeline::where('application_id', $application->id)
            ->whereIn('remark', ['Assigned to Internal Reviewer', 'Internal Reviewer Reassigned'])
            ->orderBy('id', 'desc')
            ->value('assigned_to_id');

        $external_reviewer = ApplicationTimeline::where('application_id', $application->id)
            ->whereIn('remark', ['Assigned to External Reviewer', 'External Reviewer Reassigned'])
            ->orderBy('id', 'desc')
            ->value('assigned_to_id');

        $publisher = ApplicationTimeline::where('application_id', $application->id)
            ->where('status', 'accepted')
            ->orderBy('id', 'desc')
            ->value('assigned_to_id');

        // 1. Identify exactly what role this user plays for THIS specific manuscript
        $isAssignedInternalReviewer = $internal_reviewer == $user->id;
        $isAssignedExternalReviewer = $external_reviewer == $user->id;

        // ✨ THE OVERRIDE: Lock into Reviewer Mode only during the manuscript's ACTIVE review phase,
        //    never across stale historical cycles (e.g. an editor re-assigned to 'processing' after a revise loop)
        $isStrictReviewer = in_array($application->status, ['assigning reviewers', 'under review', 're-review'], true)
            && ($isAssignedInternalReviewer || $isAssignedExternalReviewer);

        // A reviewer keeps read access to the manuscript forever once they have submitted a
        // review - even after the review phase ends, or if they are no longer the last
        // assigned reviewer (e.g. both reviewers have scored and the status is 'reviewed').
        $hasSubmittedReview = ApplicationTimeline::where('application_id', $application->id)
            ->whereIn('status', ['internal-reviewer-reviewed', 'external-reviewer-reviewed'])
            ->where('assigned_by', $user->id)
            ->exists();

        $hasAcceptedAsInternal = $isAssignedInternalReviewer && $application->accept_in == 1;
        $hasAcceptedAsExternal = $isAssignedExternalReviewer && $application->accept_ex == 1;
        $hasAcceptedReview = $hasAcceptedAsInternal || $hasAcceptedAsExternal;

        $assignedEditorId = optional($editor)->assigned_to_id;
        $isEditorOfThisManuscript = $assignedEditorId == $user->id;
        $isUserPublisher = isPublisher();

        // Technical editors are no longer admins. They may open the manuscript
        // only while the application itself is assigned to them.
        $isAssignedTechnicalEditor = $user->is_admin == 0
            && isTechnicalEditor()
            && (int) $application->assigned_to_id === (int) $user->id;

        // Once a technical editor accepts a manuscript for publication it is
        // reassigned to the publisher, but the editor still needs read access
        // to the manuscript from their Completed list.
        $hasAcceptedForPublication = $user->is_admin == 0
            && isTechnicalEditor()
            && ApplicationTimeline::where('application_id', $application->id)
                ->where('status', Application::ACCEPTED_FOR_PUBLICATION_STATUS)
                ->where('assigned_by', $user->id)
                ->exists();

        // Handling editor must Accept the assignment before viewing content
        $handlingEditorDecision = data_get($application->data, 'handling_editor_decision');
        $handlingEditorReason = data_get($application->data, 'handling_editor_reason');

        // The editor only needs to accept a *fresh* assignment. Once the manuscript has moved
        // past the assignment stage (reviewers assigned / review done / decision pending ...)
        // the editor must never be asked to accept it again.
        $isPastAssignment = !in_array(
            $application->status,
            ['incomplete', 'submitted', 'resubmitted', 'technical checks approved', 'processing'],
            true
        );

        // 2. Evaluate View Permissions
        if ($isStrictReviewer) {
            // STRICT REVIEWER MODE: Must accept (or have already reviewed) to see content. Never sees sensitive meta.
            $canViewContent = $hasAcceptedReview || $hasSubmittedReview;
            $canViewSensitiveMeta = false;
        } elseif ($isEditorOfThisManuscript) {
            // ASSIGNED HANDLING EDITOR: Can view after accepting, after having reviewed, or once the
            // manuscript has moved past the assignment stage - so the editor is never asked to
            // accept an assignment they already worked on.
            // Editors are blinded from the author/co-author identity (double-blind review);
            // only admins/publishers can see the sensitive author meta.
            $canViewContent = (int) $handlingEditorDecision === 1 || $hasSubmittedReview || $isPastAssignment;
            $canViewSensitiveMeta = false;
        } elseif ($isAssignedTechnicalEditor || $hasAcceptedForPublication) {
            // TECHNICAL EDITOR: preserve the full view they had before the
            // account stopped being an admin, for manuscripts explicitly
            // assigned to them or accepted by them for publication.
            $canViewContent = true;
            $canViewSensitiveMeta = true;
        } elseif ($hasSubmittedReview) {
            // REVIEWER (NO LONGER THE ACTIVE ASSIGNEE): Keep read access to what they reviewed.
            $canViewContent = true;
            $canViewSensitiveMeta = $isAdmin || $isUserPublisher;
        } else {
            // NORMAL MODE: Admins (who are NOT the assigned editor) and Publishers can view
            $canViewContent = $isAdmin || $isUserPublisher;
            $canViewSensitiveMeta = $isAdmin || $isUserPublisher;
        }

        $isPreAccepted = ApplicationTimeline::where('application_id', $application->id)
            ->where('status', 'accepted')
            ->exists();

        // return $isPreAccepted;

        // return compact(
        //     'application',
        //     'manuScriptFiles',
        //     'attachment_categories',
        //     'techApprovedCount',
        //     'correctedFiles',
        //     'editor',
        //     'internal_reviewer',
        //     'external_reviewer',
        //     'publisher',
        //     'canViewContent',
        //     'canViewSensitiveMeta',
        //     'isStrictReviewer', // 👈 Pass the override flag to the view
        //     'user',
        //     'rev_type', // 👈 MAKE SURE THIS IS HERE
        //     'isPreAccepted',
        // );


        return view('admin.application.show', compact(
            'application',
            'manuScriptFiles',
            'attachment_categories',
            'techApprovedCount',
            'correctedFiles',
            'editor',
            'internal_reviewer',
            'external_reviewer',
            'publisher',
            'canViewContent',
            'canViewSensitiveMeta',
            'isStrictReviewer', // 👈 Pass the override flag to the view
            'user',
            'rev_type', // 👈 MAKE SURE THIS IS HERE
            'isPreAccepted',
            'isEditorOfThisManuscript', // 👈 Handling editor identity flag
            'handlingEditorDecision', // 👈 1 = accepted, 0 = rejected, null = pending
            'handlingEditorReason',
        ));
    }

    public function edit($uuid)
    {

        $application = Application::where('uuid', $uuid)->with('attachments')->first();
        // return $application;

        return view('admin.application.editForm', compact('application'));
    }

    public function update($request, $uuid)
    {
        try {
            // Find the existing application
            $application = $this->getApplication($uuid);

            // Prepare the updated application data
            $data = [
                'submission_type' => $request->submission_type,
                'article_type' => $request->article_type ?? [],
                'title' => $request->title,
                'author_affiliation' => $request->author_affiliation,
                'author_orcid_id' => $request->author_orcid_id,
                'author_saspi_id' => $request->author_saspi_id,
                'jaspi_id' => $request->submission_type === 'revised_submission' ? $request->jaspi_id : $application->jaspi_id,
                'description' => $request->description,
                'status' => $request->status ?? $application->status,
            ];

            // Update the application data
            $application->update($data);
            return successResponse('Manuscript Updated Successfully', route('admin.application.show', $uuid));
        } catch (Throwable $th) {
            return throwException($th);
        }
    }

    public function destroy($uuid)
    {
        try {
            // Fetch the application
            $application =  $this->getApplication($uuid);

            // Remove the associated file if it exists
            if ($application->file_url) {
                deleteFile($application->file_url);
            }

            // Delete the application record
            if ($application->delete()) {
                return successResponse('Manuscript deleted successfully');
            }

            return errorResponse('Failed to delete the application');
        } catch (Throwable $th) {
            return throwException($th);
        }
    }



    private function search($request, $query)
    {
        // if (!empty($request->search)) {
        //     $statusMap = [
        //         'active' => true,
        //         'inactive' => false,
        //     ];
        //     $statusValue = $statusMap[strtolower($request->search)] ?? null;
        //     $query->where(function ($subQuery) use ($request, $statusValue) {
        //         $subQuery->where('title', 'LIKE', "%{$request->search}%")
        //             ->orWhere('type', 'LIKE', "%{$request->search}%")
        //             ->orWhereHas('service', function ($q) use ($request) {
        //                 $q->where('name', 'LIKE', "%{$request->search}%");
        //             })
        //             ->orWhereHas('creator', function ($q) use ($request) {
        //                 $q->where('name', 'LIKE', "%{$request->search}%");
        //             })
        //             ->orWhere('created_at', 'LIKE', "%{$request->search}%");

        //         if (!is_null($statusValue)) {
        //             $subQuery->orWhere('status', $statusValue);
        //         }
        //     });
        // }

        if ($request->filled('search')) {
            $searchTerm = $request->search;

            $query->where(function ($q) use ($searchTerm) {
                $q->where('application_id', 'like', "%{$searchTerm}%")
                    ->orWhere('submission_type', 'like', "%{$searchTerm}%")
                    ->orWhere('title', 'like', "%{$searchTerm}%");
                // ->orWhere('phone', 'like', "%{$searchTerm}%");
            });
        }
    }

    private function sort($request, $query, $validColumns = [])
    {

        // if (!empty($request->sortColumn) && in_array($request->sortColumn, $validColumns)) {
        if (!empty($request->sortColumn)) {
            $query->orderBy($request->sortColumn, "{$request->sortDirection}");
        } else {
            $query->orderBy('updated_at', 'desc');
        }
    }



    public function coAuthor($request, $uuid)
    {
        // $author = authUser()->id;
        $application = Application::where('uuid', $uuid)->first();

        if ($request->method() == 'GET') {
            return view('admin.application.co_author_form', compact('application'));
        } elseif ($request->method() == 'POST') {
            $newCoauthor = [
                'coauthor_id' =>    $request->coauthor_id ?? uniqid(),
                'coauthor_name' =>  $request->coauthor_name,
                'coauthor_email' => $request->coauthor_email,
                'coauthor_phone' => $request->coauthor_phone,
                'coauthor_affiliation' =>  $request->author_affiliation,
                'coauthor_orcid_id' => $request->author_orcid_id,
                'coauthor_saspi_id' => $request->author_saspi_id ?? null,
            ];

            // Decode current JSON to array or create a new array if empty
            $existingData = $application->co_author_data ?? [];

            // Append new co-author
            $existingData[] = $newCoauthor;

            // Save updated JSON
            $application->co_author_data = $existingData;
            $update = $application->save();
            if ($update) {
                return successResponse('Co-author added successfully', route('admin.application.create', ['applicationId' => $uuid, 'app_info' => $application, 'step' => "coAuthors"]));
            } else {
                return errorResponse('Something went wrong');
            }
        } else {
            return errorResponse('Invalid request method');
        }
    }

    public function coAuthorUpdate($request, $uuid, $author_id)
    {
        // $author = authUser()->id;
        $application = Application::where('uuid', $uuid)->first();

        if (!$application) {
            return errorResponse('Manuscript not found');
        }

        $all_author_data = $application->co_author_data ?? [];

        // GET Request: Show Edit Form
        if ($request->method() == 'GET') {
            $author_data = collect($all_author_data)->firstWhere('coauthor_id', $author_id);

            if (!$author_data) {
                return errorResponse('Co-author not found');
            }

            return view('admin.application.co_author_edit_form', compact('uuid', 'author_data'));

            // POST Request: Update Co-author
        } elseif ($request->method() == 'POST') {
            foreach ($all_author_data as &$coauthor) {
                if ($coauthor['coauthor_id'] == $author_id) {
                    $coauthor['coauthor_name']  = $request->coauthor_name;
                    $coauthor['coauthor_email'] = $request->coauthor_email;
                    $coauthor['coauthor_phone'] = $request->coauthor_phone;
                    $coauthor['coauthor_affiliation']  = $request->author_affiliation;
                    $coauthor['coauthor_orcid_id'] = $request->author_orcid_id;
                    $coauthor['coauthor_saspi_id'] = $request->author_saspi_id ?? null;
                    break;
                }
            }

            $application->co_author_data = $all_author_data;
            $update = $application->save();

            if ($update) {
                return successResponse('Co-author updated successfully', route('admin.application.create', ['applicationId' => $uuid, 'app_info' => $application, 'step' => "coAuthors"]));
            } else {
                return errorResponse('Something went wrong while updating');
            }
        } else {
            return errorResponse('Invalid request method');
        }
    }

    public function coAuthorDelete($request, $uuid, $author_id)
    {
        try {
            // $author = authUser()->id;

            $application = Application::where('uuid', $uuid)->first();

            if (!$application) {
                return errorResponse('Manuscript not found');
            }

            $coAuthors = $application->co_author_data ?? [];

            // Filter out the co-author to be deleted
            $updatedCoAuthors = array_filter($coAuthors, function ($coauthor) use ($author_id) {
                return $coauthor['coauthor_id'] !== $author_id;
            });

            // Re-index the array
            $updatedCoAuthors = array_values($updatedCoAuthors);

            $application->co_author_data = $updatedCoAuthors;
            $application->save();

            return successResponse('Co-author deleted successfully', route('admin.application.create', ['applicationId' => $uuid, 'app_info' => $application, 'step' => "coAuthors"]));
        } catch (\Exception $e) {
            return errorResponse('Error deleting co-author: ' . $e->getMessage());
        }
    }

    public function fileUpload($request, $id, $type, $app_id, $legend)
    {
        // $author = authUser()->id;

        // Check if the application exists and belongs to the author
        $application = Application::where('id', $app_id)->first();

        if (!$application) {
            return errorResponse('Manuscript not found');
        }

        $attachment_id = Attachment::where('application_id', $id)->first()->id ?? null;

        if ($request->isMethod('GET')) {

            return view('admin.application.file_upload', compact('id', 'type', 'app_id', 'legend', 'attachment_id'));
        }

        if ($request->isMethod('POST')) {
            if (!$request->hasFile('file_upload')) {
                return errorResponse('File not found');
            }

            // $validator = Validator::make($request->all(), [
            //     'file_upload' => 'required|mimes:pdf|max:10240', // max size in KB
            // ], [
            //     'file_upload.required' => 'File is required.',
            //     'file_upload.mimes'    => 'Only PDF files are allowed.',
            //     'file_upload.max'      => 'The file size must be less than 10 MB.',
            // ]);

            // if ($validator->fails()) {
            //     return errorResponse($validator->errors()->first()); // or return all errors using ->all()
            // }
            $validator = Validator::make($request->all(), [
                'file_upload' => ['required', 'file', new FileTypeRule([
                    'application/zip',
                    'application/pdf',
                    'application/msword',
                    'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                    'application/octet-stream',

                ]), 'max:10240'],
            ], [
                'file_upload.required' => 'File is required.',
                'file_upload.mimes'    => 'Only PDF, DOCX or DOC files are allowed.',
                'file_upload.max'      => 'The file size must be less than 10 MB.',
            ]);

            if ($validator->fails()) {
                return errorResponse($validator->errors()->first()); // or return all errors using ->all()
            }

            $file = $request->file('file_upload');

            // Extract metadata before moving the file
            $originalName = $file->getClientOriginalName();
            $extension = $file->getClientOriginalExtension();
            $mimeType = $file->getClientOriginalExtension();
            $size = $file->getSize();

            $fileName = time() . '_' . Str::slug(pathinfo($originalName, PATHINFO_FILENAME)) . '.' . $extension;
            $relativePath = 'uploads/applications/';
            $fullPath = public_path($relativePath);

            // Create directory if it doesn't exist
            if (!File::exists($fullPath)) {
                File::makeDirectory($fullPath, 0775, true);
            }

            // Move the file
            $file->move($fullPath, $fileName);

            $data = [
                'application_id'   => $app_id,
                'file_name'        => $fileName,
                'file_type'        => $mimeType,
                'file_size'        => $size,
                'file_url'         => $relativePath . $fileName,
                'attachment_type'  => $type,
                'legend' => $request->legend,
            ];

            // If id == 'na', create new; otherwise update existing
            if ($id === 'na') {
                $attachment = new Attachment();
                $attachment->uuid = (string) Str::uuid();
                $attachment->fill($data)->save();
            } else {
                $attachment = Attachment::find($id);

                if (!$attachment) {
                    return errorResponse('Attachment not found');
                }

                // Delete old file if exists
                if (!empty($attachment->file_url) && File::exists(public_path($attachment->file_url))) {
                    File::delete(public_path($attachment->file_url));
                }

                $attachment->fill($data)->save();
            }
            if ($attachment_id) {
                $attachment = Attachment::find($attachment_id);
                $attachment->legend = $legend;
                $attachment->save();
            }

            return successResponse(
                'File uploaded successfully',
                route(
                    'admin.application.create',
                    ['applicationId' => $application->uuid, 'step' => "documents"]
                )
            );
        }

        return errorResponse('Invalid request method');
    }

    public function fileDestroy($app_id, $id)
    {
        // $author = authUser()->id;

        // Retrieve the attachment by ID and ensure it belongs to the application
        $attachment = Attachment::find($id);
        // return $id;
        if (!$attachment) {
            return errorResponse('Attachment not found');
        }

        // Delete the file from the server
        if (!empty($attachment->file_url) && File::exists(public_path($attachment->file_url))) {
            File::delete(public_path($attachment->file_url));
        }

        // Remove the attachment record from the database
        if ($attachment->delete()) {

            return successResponse('File deleted successfully', route(
                'admin.application.create',
                ['applicationId' => $app_id, 'step' => "documents"]
            ));
        }

        return errorResponse('Error deleting file');
    }


    public function assignApplication($request, $uuid)
    {
        if ($request->isMethod('GET')) {

            // Admins are deliberately excluded from this picker so a handling
            // editor must be a real Editor, with one named admin excepted.
            $staffs = Admin::where(function ($q) {
                $q->whereHas('roles', function ($roleQuery) {
                    $roleQuery->where('name', 'Editor');
                })
                    ->orWhere('email', self::HANDLING_EDITOR_ADMIN_EXCEPTION);
            })
                ->get();

            return view('admin.application.assignForm', compact('uuid', 'staffs'));
        }

        if ($request->isMethod('POST')) {
            try {

                /* ========== EARLY SECURITY CHECK (EDITOR ONLY) ========== */

                $assignedStaff = Admin::with('roles')->findOrFail($request->staff_id);

                // $isEditor = $assignedStaff
                //     ->roles
                //     ->contains('name', 'Editor');

                // if (!$isEditor) {
                //     return errorResponse('Only an Editor can be assigned to this application.');
                // }

                /* ========== SAFE TO PROCEED ========== */

                $application = $this->getApplication($uuid);

                $data = [
                    'application_id' => $application->id,
                    'assigned_to_id' => $assignedStaff->id,
                    'assigned_by' => authUser('admin')->id,
                    'remark' => 'With Editor',
                    'status' => 'processing'
                ];

                $timelineUpdated = ApplicationTimeline::create($data);

                $appData = $application->data ?? [];
                unset($appData['handling_editor_decision']);
                unset($appData['handling_editor_reason']);

                $applicationUpdated = $application->update([
                    'assigned_to_id' => $assignedStaff->id,
                    'status' => $data['status'],
                    'is_visible_to_author' => 1,
                    'data' => $appData,
                ]);

                /* ========== SEND MAIL TO EDITOR ========== */

                Mail::to($assignedStaff->email)
                    ->cc('jaspi@saspi.in') // Add CC here  uncomment it
                    ->send(
                        new EditorAssignmentMail(
                            editor: $assignedStaff,
                            application: $application,
                            assignedBy: auth()->user(),
                            url: route('admin.application.show', $application->uuid),
                        )
                    );

                $this->notifyInApp($assignedStaff, $application, [
                    'category' => 'application',
                    'type' => 'application.assigned',
                    'title' => 'New manuscript assigned',
                    'message' => 'Manuscript ' . $this->applicationReference($application) . ' has been assigned to you.',
                    'icon' => 'ri-file-paper-2-line',
                    'severity' => 'info',
                ]);

                if ($timelineUpdated && $applicationUpdated) {
                    return successResponse('Manuscript assigned successfully');
                }

                return errorResponse();
            } catch (\Throwable $th) {
                return throwException($th);
            }
        }
    }

    public function handlingEditorDecision($request, $uuid)
    {
        $application = Application::where('uuid', $uuid)->firstOrFail();
        $currentAdmin = authUser('admin');

        if ($request->isMethod('GET')) {
            return view('admin.application.handlingEditorDecisionForm', compact('application'));
        }

        if ($request->isMethod('POST')) {
            try {
                /* ========== AUTHORIZATION ========== */
                // Only the assigned handling editor can accept/reject the assignment
                if ($application->status !== 'processing' || (int) $application->assigned_to_id !== (int) $currentAdmin->id) {
                    return errorResponse('You are not authorized to accept or reject this assignment.');
                }

                /* ========== VALIDATION ========== */
                $validator = Validator::make($request->all(), [
                    'decision' => 'required|in:0,1',
                    'reason'   => 'required_if:decision,0|nullable|string|max:1000',
                ], [
                    'decision.required'   => 'Please select Accept or Reject.',
                    'decision.in'         => 'Invalid decision value.',
                    'reason.required_if'  => 'Please provide a reason for rejecting the assignment.',
                ]);

                if ($validator->fails()) {
                    return failedValidation($validator);
                }

                $decision = (int) $request->decision;
                $reason   = $decision === 1 ? null : trim($request->reason ?? '');

                /* ========== CAPTURE ASSIGNER BEFORE TIMELINE AUDIT ========== */
                $assignerTimeline = ApplicationTimeline::where('application_id', $application->id)
                    ->where('status', 'processing')
                    ->orderBy('id', 'desc')
                    ->first();

                /* ========== STORE DECISION IN DATA COLUMN (merge, never wipe) ========== */
                $data = $application->data ?? [];
                $data['handling_editor_decision'] = $decision;
                $data['handling_editor_reason']   = $reason;

                $applicationUpdated = $application->update(['data' => $data]);

                if ($applicationUpdated) {
                    if ($decision === 0) {
                        $this->notifyAssignerOfEditorDecline($application, $currentAdmin, $reason, $assignerTimeline);
                    }

                    return successResponse(
                        $decision === 1 ? 'You accepted to work on this application.' : 'You declined this assignment.',
                        route('admin.application.show', $application->uuid)
                    );
                }

                return errorResponse('Something went wrong.');
            } catch (\Throwable $th) {
                return throwException($th);
            }
        }

        return errorResponse('Invalid request method.');
    }

    public function handlingEditorRejection($uuid)
    {
        $application = Application::where('uuid', $uuid)->with('assignee')->firstOrFail();

        return view('admin.application.handlingEditorRejection', compact('application'));
    }

    public function viewAbstract($uuid)
    {
        $application = Application::where('uuid', $uuid)->firstOrFail();

        return view('admin.application.viewAbstract', compact('application'));
    }

    public function assignReviewer($request, $uuid)
    {
        if ($request->isMethod('GET')) {
            $staffs = Admin::where('is_admin', false)->whereHas('roles', function ($q) {
                $q->where('name', 'Reviewer');
            })->get();
            // return $staffs;
            return view('admin.application.assignReviewerForm', compact('uuid', 'staffs'));
        }

        if ($request->isMethod('POST')) {
            try {
                $application = $this->getApplication($uuid);

                $data = [
                    'application_id' => $application->id,
                    'assigned_to_id' => $request->staff_id,
                    'assigned_by' => authUser('admin')->id,
                    'remark' => 'Assigned to Reviewer',
                    'status' => 'under review',
                ];

                // Create timeline entry
                $timelineCreated = ApplicationTimeline::create($data);

                // Update application (status and assigned staff)
                $applicationUpdated = $application->update([
                    'assigned_to_id' => $request->staff_id,
                    'status' => $data['status'],
                ]);

                if ($timelineCreated && $applicationUpdated) {
                    return successResponse(
                        'Manuscript assigned to reviewer successfully',
                        route('admin.application.show', $application->uuid)
                    );
                } else {
                    return errorResponse();
                }
            } catch (Throwable $th) {
                return throwException($th);
            }
        }
    }


    public function editor_accepts($description, $uuid)
    {
        $application = Application::where('uuid', $uuid)->firstOrFail();
        $editor = ApplicationTimeline::where('status', 'processing')
            ->where('application_id', $application->id)
            ->orderBy('id', 'desc')
            ->first(); // fetch the latest single record
        // $reason = $request->review_status == 'significant_rework_required' ? 'The submission is not acceptable in its current form but may be resubmitted after significant rework.' : 'The submission does not meet the requirements or standards and will not be considered further.';

        // The handling editor's job is done: hand the manuscript to the
        // assistant editor for the next step.
        $assistantEditor = $this->assistantEditor();
        $assignee        = $assistantEditor?->id ?? $editor->assigned_to_id;

        $timeline = ApplicationTimeline::create([
            'application_id' => $application->id,
            'assigned_to_id' => $assignee,
            'status' => 'editor-accepts',
            'is_visible_to_author' => 0,
            'data' => [
                'comment' => $description,
            ],
            'remark' => 'Your application have been processed.',
        ]);

        $applicationUpdate = $application->update([
            'status' => 'editor-accepts',
            'assigned_to_id' => $assignee,
        ]);

        if ($timeline && $applicationUpdate) {
            $this->notifyEditorFinalDecision($application, $editor, 'Accepted', $description);
            return successResponse('Manuscript processed successfully', route('admin.application.index'));
        }
        return errorResponse('Their was an while processing please try again');
        // return errorResponse('Invalid request method');
    }


    public function editor_revise($description, $uuid)
    {
        $application = Application::where('uuid', $uuid)->firstOrFail();
        $editor = ApplicationTimeline::where('status', 'processing')
            ->where('application_id', $application->id)
            ->orderBy('id', 'desc')
            ->first(); // fetch the latest single record
        // $reason = $request->review_status == 'significant_rework_required' ? 'The submission is not acceptable in its current form but may be resubmitted after significant rework.' : 'The submission does not meet the requirements or standards and will not be considered further.';

        // The handling editor's job is done: hand the manuscript to the
        // assistant editor for the next step.
        $assistantEditor = $this->assistantEditor();
        $assignee        = $assistantEditor?->id ?? $editor->assigned_to_id;

        $timeline = ApplicationTimeline::create([
            'application_id' => $application->id,
            'assigned_to_id' => $assignee,
            'status' => 'editor-revise',
            'is_visible_to_author' => 0,
            'data' => [
                'comment' => $description,
            ],
            'remark' => 'Requested a revision of your manuscript',

        ]);

        $applicationUpdate = $application->update([
            'status' => 'editor-revise',
            'assigned_to_id' => $assignee,
        ]);

        if ($timeline && $applicationUpdate) {
            $this->notifyEditorFinalDecision($application, $editor, 'Revision Requested', $description);
            return successResponse('Manuscript processed successfully', route('admin.application.index'));
        }
        return errorResponse('Their was an while processing please try again');
        // return errorResponse('Invalid request method');
    }
    public function editor_rejects($description, $uuid)
    {
        $application = Application::where('uuid', $uuid)->firstOrFail();
        $editor = ApplicationTimeline::where('status', 'processing')
            ->where('application_id', $application->id)
            ->orderBy('id', 'desc')
            ->first(); // fetch the latest single record
        // $reason = $request->review_status == 'significant_rework_required' ? 'The submission is not acceptable in its current form but may be resubmitted after significant rework.' : 'The submission does not meet the requirements or standards and will not be considered further.';

        // The handling editor's job is done: hand the manuscript to the
        // assistant editor for the next step.
        $assistantEditor = $this->assistantEditor();
        $assignee        = $assistantEditor?->id ?? $editor->assigned_to_id;

        $timeline = ApplicationTimeline::create([
            'application_id' => $application->id,
            'assigned_to_id' => $assignee,
            'status' => 'editor-rejects',
            'is_visible_to_author' => 0,
            'data' => [
                'comment' => $description,
            ],
            'remark' => 'Your application have been processed.',
        ]);

        $applicationUpdate = $application->update([
            'status' => 'editor-rejects',
            'assigned_to_id' => $assignee,
        ]);

if ($timeline && $applicationUpdate) {
            $this->notifyEditorFinalDecision($application, $editor, 'Rejected', $description);
            return successResponse('Manuscript processed successfully', route('admin.application.index'));
        }
        return errorResponse('Their was an while processing please try again');
        // return errorResponse('Invalid request method');
    }


    public function editor_final_decision($request, $uuid, $action)
    {
        $application = Application::where('uuid', $uuid)->firstOrFail();

        $currentAdmin = authUser('admin');
        $editor = ApplicationTimeline::where('application_id', $application->id)
            ->where('status', 'processing')
            ->orderBy('id', 'desc')
            ->first('assigned_to_id');
        $isAssignedEditor = optional($editor)->assigned_to_id == $currentAdmin->id;
        $hasPerm = $currentAdmin->can('application.acceptance') ||
            $currentAdmin->can('application.revise') ||
            $currentAdmin->can('application.rejection');
        if ($currentAdmin->is_admin != 1 && (!$isAssignedEditor || !$hasPerm)) {
            return errorResponse('You are not authorized to make this decision.');
        }
        if ($application->status !== 'reviewed') {
            return errorResponse('This manuscript has not been reviewed yet.');
        }

        if ($request->isMethod('GET')) {

            return view('admin.application.editorFinalFeedback', compact('uuid', 'action'));
        }

        if ($request->isMethod('POST')) {
            // $description = preg_replace('/<\/?p[^>]*>/', '', $request->description);
            $description = $request->description;
            // return $description;
            switch ($action) {
                case 'accept':
                    return $this->editor_accepts($description, $uuid);
                    break;
                case 'reject':
                    return  $this->editor_rejects($description, $uuid);
                    break;
                case 'revise':
                    return $this->editor_revise($description, $uuid);
                    break;
                default:
                    return errorResponse('Invalid request method');
            }
        }
    }



    public function acceptance($description, $uuid)
    {
        $application = Application::where('uuid', $uuid)->first();
        $editor = ApplicationTimeline::where('status', 'processing')
            ->where('application_id', $application->id)
            ->orderBy('id', 'desc')
            ->first(); // fetch the latest single record

        $timeline = ApplicationTimeline::create([
            'application_id' => $application->id,
            'status' => 'pre-accepted',
            'assigned_by' => authUser('admin')->id,
            'is_visible_to_author' => 1,
            'data' => [
                'comment' => $description
            ],
            'assigned_to_id' => $editor->assigned_to_id,
            'remark' => 'Your application has been pre-accepted by the Administration',
        ]);
        $updated = $application->update([
            'status' => 'pre-accepted',
            'assigned_to_id' => $editor->assigned_to_id,
            'accept_in' => null,
            'accept_ex' => null,
            'status_ex' => null,
        ]);

        if ($timeline && $updated) {
            $this->notifyAuthorFinalDecision($application, 'Accepted', $description);
            $this->notifyTechnicalEditor($application, $description);
            $this->notifyReviewersFinalDecision($application, 'Accepted', $description);
            return successResponse('Manuscript pre-accepted successfully', route('admin.application.show', $application->uuid));
        }
        return errorResponse('Their was an error accepting your application');
    }


    public function rejection($description, $uuid)
    {

        $application = Application::where('uuid', $uuid)->firstOrFail();
        $editor = ApplicationTimeline::where('status', 'processing')
            ->where('application_id', $application->id)
            ->orderBy('id', 'desc')
            ->first(); // fetch the latest single record
        // $reason = $request->review_status == 'significant_rework_required' ? 'The submission is not acceptable in its current form but may be resubmitted after significant rework.' : 'The submission does not meet the requirements or standards and will not be considered further.';

        $timeline = ApplicationTimeline::create([
            'application_id' => $application->id,
            'assigned_to_id' => $editor->assigned_to_id,
            'assigned_by' => authUser('admin')->id,
            'status' => 'rejected',
            'data' => [
                'comment' => $description
            ],
            'is_visible_to_author' => 1,
            'remark' => 'Your application have been rejected',

        ]);

        $applicationUpdate = $application->update([
            'status' => 'rejected',
            'assigned_to_id' => $editor->assigned_to_id,
            'accept_in' => null,
            'accept_ex' => null,
            'status_ex' => null,
        ]);

        if ($timeline && $applicationUpdate) {
            $this->notifyAuthorFinalDecision($application, 'Rejected', $description);
            $this->notifyReviewersFinalDecision($application, 'Rejected', $description);
            return successResponse('Manuscript rejected successfully', route('admin.application.show', $uuid));
        }
        return errorResponse('Their was an while processing please try again');
        // return errorResponse('Invalid request method');
    }
    public function revise($description, $uuid)
    {
        $application = Application::where('uuid', $uuid)->firstOrFail();

        $timeline = ApplicationTimeline::create([
            'application_id' => $application->id,
            'assigned_to_id' => null,
            'assigned_by' => authUser('admin')->id,
            'status' => 'revise',
            'is_visible_to_author' => 1,
            'data' => [
                'comment' => $description
            ],
            'remark' => 'Manuscript revision is requested',
        ]);
        $applicationUpdate = $application->update([
            'status' => 'revise',
            'assigned_to_id' => null,
            'accept_in' => null,
            'accept_ex' => null,
            'status_ex' => null,
        ]);

        if ($timeline && $applicationUpdate) {
            $this->notifyAuthorFinalDecision($application, 'Revision Required', $description);
            return successResponse(
                'Manuscript send to revise successfully',
                route('admin.application.show', $application->uuid)
            );
        }
        return errorResponse('Their was an while processing please try again');

        // return errorResponse('Invalid request method');
    }

    public function re_review($request, $uuid)
    {
        if ($request->isMethod('GET')) {
            $application = Application::where('uuid', $uuid)->firstOrFail();

            $staffs = Admin::whereHas('roles', function ($q) {
                $q->where('name', 'Reviewer');
                // ->orWhere('is_admin', 1);
            })->get();

            return view('admin.application.re_review', compact('application', 'staffs'));
        }

        if ($request->isMethod('PUT')) {

            $application  = Application::where('uuid', $uuid)->firstOrFail();
            $currentAdmin = authUser('admin');

            $newInternal = $request->internal_reviewer_id ?? $currentAdmin->id;
            $newExternal = ($request->external_reviewer_id === 'not')
                ? null
                : $request->external_reviewer_id;

            $internalChanged = (int)$application->assigned_to_id !== (int)$newInternal;
            $externalChanged = (int)$application->assigned_to_ex_id !== (int)$newExternal;

            // Force reset conditions as requested
            $statusToSet = 'assigning reviewers';

            $updated = $application->update([
                'assigned_to_id'    => $newInternal,
                'assigned_to_ex_id' => $newExternal,
                // 'status'            => 're-review',
                'status'            => 'assigning reviewers',
                'status_ex'         => null, // Forced to null
                'accept_in'         => null, // Forced to null
                'accept_ex'         => null, // Forced to null
                'data'              => [
                    'comment' => $request->comment ?? '',
                ],
            ]);

            // if ($internalChanged) {
            ApplicationTimeline::create([
                'uuid'                 => Str::uuid(),
                'application_id'       => $application->id,
                'status'               => $statusToSet,
                'assigned_to_id'       => $newInternal,
                'is_visible_to_author' => 0,
                'remark'               => 'Internal Reviewer Reassigned',
                'data'                 => [
                    'type'    => 'internal',
                    'comment' => $request->comment ?? '',
                ],
            ]);
            $this->notifyReviewer($application, $newInternal, 'internal', 15, $currentAdmin);
            // }

            if ($newExternal !== null) {
                ApplicationTimeline::create([
                    'uuid'                 => Str::uuid(),
                    'application_id'       => $application->id,
                    'status'               => $statusToSet,
                    'assigned_to_id'       => $newExternal,
                    'is_visible_to_author' => 0,
                    'remark'               => 'External Reviewer Reassigned',
                    'data'                 => [
                        'type'    => 'external',
                        'comment' => $request->comment ?? '',
                    ],
                ]);

                $this->notifyReviewer($application, $newExternal, 'external', 0, $currentAdmin);
            }

            if ($updated) {
                return successResponse('Sended for re-review & reviewers notified', route('admin.application.show', $application->uuid));
            }

            return errorResponse('Something went wrong!');
        }
        return errorResponse('Invalid request method');
    }




    public function remindReviewers($request, $uuid)
    {
        // Handle GET request: Show the view/modal
        if ($request->isMethod('GET')) {
            $application = Application::where('uuid', $uuid)->firstOrFail();

            // Return the view containing your reminder form/modal
            return view('admin.application.remindReviewers', compact('application'));
        }

        // Handle POST request: Process the form submission and send emails
        if ($request->isMethod('POST')) {


            $validated = Validator::make(
                $request->all(),
                [
                    'reviewer_ids'   => 'required|array',
                    'reviewer_ids.*' => 'exists:admins,id' // Adjust 'admins' if your table name is different
                ],
                [
                    'reviewer_ids.required' => 'Please select at least one reviewer.',
                    'reviewer_ids.*.exists' => 'Selected reviewer does not exist.'
                ]
            );

            if ($validated->fails()) {
                return response()->json([
                    'status' => "400",
                    'errors' => $validated->errors()
                ], 400);
            }

            $application = Application::where('uuid', $uuid)->firstOrFail();
            $currentAdmin = authUser('admin');

            // Loop through the selected IDs and send the email
            foreach ($request->reviewer_ids as $reviewerId) {
                $reviewer = Admin::find($reviewerId);

                if ($reviewer && $reviewer->email) {
                    // Determine if they are internal or external
                    $type = ($application->assigned_to_id == $reviewerId) ? 'internal' : 'external';

                    // Generate the URL for the reviewer portal
                    $url = route('admin.application.show', $application->uuid);

                    // Send the queued email
                    Mail::to($reviewer->email)
                    ->cc('akanksha.itconsultant@gmail.com') // Add CC here  uncomment it
                    ->send(
                        new ReviewerReminder($application, $reviewer, $type, $currentAdmin, $url)
                    );

                    $this->notifyInApp($reviewer, $application, [
                        'category' => 'review',
                        'type' => 'review.reminder',
                        'title' => 'Review reminder',
                        'message' => 'Please review manuscript ' . $this->applicationReference($application) . '.',
                        'icon' => 'ri-time-line',
                        'severity' => 'warning',
                    ]);
                }
            }

            // Redirect back with a success message (adjust the route name as needed)
            return successResponse('Reminders have been sent successfully.', route('admin.application.show', $application->uuid));
        }
    }

    public function assistantToChiefForApproval($request, $uuid)
    {
        $application = Application::where('uuid', $uuid)->firstOrFail();

        $currentAdmin = authUser('admin');
        if ($currentAdmin->is_admin != 1 && !$currentAdmin->can('application.assistantToChiefForApproval')) {
            return errorResponse('You are not authorized to send this manuscript for chief approval.');
        }
        if (!in_array($application->status, ['editor-accepts', 'editor-rejects', 'chief-response'], true)) {
            return errorResponse('This manuscript is not ready for chief approval.');
        }
        // The send → Chief response cycle can repeat any number of times.
        // Only block a new request while one is still awaiting the Chief
        // Editor's response. Once the chief has responded the ledger status
        // becomes 'redirected to assistant editor' and a fresh round may start.
        $ledgerStatus = data_get($application->data, 'status');
        if ($ledgerStatus === 'sended to chief approval') {
            return errorResponse('This manuscript is already awaiting the Chief Editor\'s response.');
        }

        // if ($request->isMethod('GET')) {

        //     return view('admin.application.assistantToChiefFeedback', compact('uuid'));
        // }

        // if ($request->isMethod('POST')) {
        //     $description = preg_replace('/<\/?p[^>]*>/', '', $request->description);
        //     $description = $request->description;
        //     return $description;

        // }

        $chief = $this->chiefAdmin();

        // Record the handling editor's decision for reference, then move the
        // application into the chief-review stage.
        $previousStatus = $application->status;

        $application->data = array_merge($application->data ?? [], [
            'status' => 'sended to chief approval',
            'chief_requested_by' => authUser('admin')->id,
            'chief_previous_status' => $previousStatus,
        ]);
        $application->status = 'chief-review';
        // While awaiting the Chief Editor's decision the manuscript is
        // assigned to the chief.
        if ($chief) {
            $application->assigned_to_id = $chief->id;
        }
        $application->save();

        $this->notifyChief($application);

        // Record this step in the timeline (internal only, hidden from author).
        // The entry is assigned to the chief as well.
        $application->timelines()->create([
            'status'               => 'chief-review',
            'remark'               => 'Manuscript sent to Chief Editor for approval',
            'assigned_to_id'       => $chief->id ?? $currentAdmin->id,
            'assigned_by'          => $currentAdmin->id,
            'is_visible_to_author' => 0,
            'data'                 => [
                'action'  => 'sent_to_chief',
                'by'      => $currentAdmin->id,
                'by_name' => $currentAdmin->name,
            ],
        ]);

        return successResponse('Sended to Chief for approval', route('admin.application.show', $application->uuid));
    }
    public function chiefToAssistanResponse($request, $uuid)
    {
        $application = Application::where('uuid', $uuid)->firstOrFail();

        $currentAdmin = authUser('admin');
        if ($currentAdmin->is_admin != 1 && !$currentAdmin->can('application.chiefToAssistanResponse')) {
            return errorResponse('You are not authorized to respond on behalf of the Editor in Chief.');
        }
        // Accept the chief-review status, plus the legacy chief-approval and the
        // original decision statuses for in-flight rows.
        $pendingChief = in_array($application->status, ['chief-review', 'chief-approval', 'editor-accepts', 'editor-rejects'], true)
            && data_get($application->data, 'status') === 'sended to chief approval';
        if (!$pendingChief) {
            return errorResponse('This manuscript is not awaiting a chief response.');
        }

        if ($request->isMethod('GET')) {

            return view('admin.application.chiefToAssistantResponse', compact('uuid'));
        }

        if ($request->isMethod('POST')) {
            // return 'hii';
            $requesterId = data_get($application->data, 'chief_requested_by');

            // Once the chief has responded the manuscript goes back to the
            // assistant editor for the next step, regardless of which admin
            // forwarded it for approval.
            $assistantEditor             = $this->assistantEditor();
            $assignee                    = $assistantEditor?->id ?? $requesterId ?? $currentAdmin->id;
            $application->assigned_to_id = $assignee;

            // The manuscript is now in the chief-response stage; the assistant
            // editor can take the final decision or forward it again.
            $application->status = 'chief-response';

            $application->data = array_merge($application->data ?? [], [
                'status' => 'redirected to assistant editor',
                'chief_response' => $request->description,
                'decision' => $request->decision,
            ]);
            $application->save();

            // Record the Chief Editor's response in the timeline (internal only,
            // hidden from the author). The manuscript still returns to the
            // assistant editor exactly as before.
            $decision = $request->decision; // 'accept' | 'reject'
            $remark = $decision === 'accept'
                ? 'Chief Editor approved the manuscript'
                : 'Chief Editor rejected the manuscript';

            $application->timelines()->create([
                'status'               => 'chief-response',
                'remark'               => $remark,
                'assigned_to_id'       => $assignee,
                'assigned_by'          => $currentAdmin->id,
                'is_visible_to_author' => 0,
                'data'                 => [
                    'action'   => 'chief_response',
                    'decision' => $decision,
                    'by'       => $currentAdmin->id,
                    'by_name'  => $currentAdmin->name,
                    'comment'  => $request->description,
                ],
            ]);

            $this->notifyRequester($application, $request->decision, $request->description);

            return successResponse('Permission granted', route('admin.application.show', $application->uuid));
        }
    }


    public function seeChiefDecision($request, $uuid)
    {
        $application = Application::where('uuid', $uuid)->firstOrFail();

        if ($request->isMethod('GET')) {

            return view('admin.application.super_admin_readMore', compact('application'));
        }

        // if ($request->isMethod('POST')) {
        //     // return 'hii';
        //     $application->data = [
        //         'status' => 'redirected to assistant editor',
        //         'chief_response' => $request->description,
        //     ];
        //     $application->save();

        //     return successResponse('Permission granted', route('admin.application.show', $application->uuid));
        // }
    }

    /**
     * Show the Chief Editor's decision for a specific chief-response timeline
     * entry. Because the send → chief response cycle can repeat, the decision
     * is read from the timeline entry itself rather than the application's
     * (latest-only) `data`.
     */
    public function chiefReadMore($uuid, $timeline_id = null)
    {
        $application = Application::where('uuid', $uuid)->firstOrFail();

        $timeline = $application->timelines()
            ->where('status', 'chief-response')
            ->when(!empty($timeline_id), function ($query) use ($timeline_id) {
                $query->where('id', $timeline_id);
            })
            ->orderByDesc('id')
            ->firstOrFail();

        return view('admin.application.chief_readMore', compact('timeline'));
    }

    public function assistant_editor_decision($request, $uuid, $action)
    {
        $application = Application::where('uuid', $uuid)->firstOrFail();

        $currentAdmin = authUser('admin');
        if ($currentAdmin->is_admin != 1 && !$currentAdmin->can('application.assistantEditorFinalDecision')) {
            return errorResponse('You are not authorized to make the final decision for this manuscript.');
        }
        if (!in_array($application->status, ['chief-response', 'editor-accepts', 'editor-rejects', 'editor-revise'], true)) {
            return errorResponse('This manuscript is not in a state that allows a final decision.');
        }
        if ($application->status !== 'editor-revise' &&
            $application->status !== 'chief-response' &&
            data_get($application->data, 'status') !== 'redirected to assistant editor') {
            return errorResponse('Chief approval is still pending for this manuscript.');
        }

        if ($request->isMethod('GET')) {

            return view('admin.application.assistantEditorFinalFeedback', compact('uuid', 'action'));
        }

        if ($request->isMethod('POST')) {
            // $description = preg_replace('/<\/?p[^>]*>/', '', $request->description);
            $description = $request->description;
            // return $description;
            switch ($action) {
                case 'accept':
                    return $this->acceptance($description, $uuid);
                    break;
                case 'reject':
                    return  $this->rejection($description, $uuid);
                    break;
                case 'revise':
                    return $this->revise($description, $uuid);
                    break;
                default:
                    return errorResponse('Invalid request method');
            }
        }
    }

    public function reviewed($request, $uuid)
    {
        if ($request->isMethod('GET')) {

            return view('admin.application.reviewForm', compact('uuid'));
        }

        if ($request->isMethod('POST')) {

            $application = Application::where('uuid', $uuid)->first();
            $currentAdmin = authUser('admin');
            $editor = ApplicationTimeline::where('application_id', $application->id)->where('status', 'processing')->orderBy('id', 'desc')->first();
            $isAssignedEditor = optional($editor)->assigned_to_id == $currentAdmin->id;
            if ($currentAdmin->is_admin != 1 && !$isAssignedEditor) {
                return errorResponse('You are not authorized to complete this review.');
            }
            if (!in_array($application->status, ['under review', 'assigning reviewers', 're-review'], true)) {
                return errorResponse('This manuscript cannot be marked as reviewed in its current state.');
            }
            if (($application->status_ex ?? 0) < 1) {
                return errorResponse('At least one reviewer must have submitted a score before the manuscript can be marked as reviewed.');
            }
            $statusTexts = [
                'Accept' => 'The submission is approved as-is, with no changes required.',
                'Accept with Minor Changes' => 'Approved with only small revisions needed (e.g., typos, formatting, minor clarifications).',
                'Request Revisions with Major Changes' => 'Substantial changes are required before the work can be accepted (e.g., missing content, reorganization, factual updates).',
                'Reject with Suggestions' => 'The submission is not acceptable in its current form but may be resubmitted after significant rework.',
                'Reject' => 'The submission does not meet the requirements or standards and will not be considered further.',
            ];

            $timelineData = [
                'application_id' => $application->id,
                'assigned_to_id' => $editor->assigned_to_id,
                'status' => 'reviewed',
                'remark' => 'Manuscript has been reviewed',
                'data' => [
                    'review_status' => $statusTexts[$request->review_status] ?? 'Unknown decision',
                    'comment' =>  $request->remark,

                ],
            ];

            $updated = $application->update([
                'status' => 'reviewed',
                'assigned_to_id' => $editor->assigned_to_id,
            ]);

            if (ApplicationTimeline::create($timelineData) && $updated) {
                return successResponse('Manuscript reviewed successfully', route('admin.application.show', $application->uuid));
            }
            return errorResponse('Their was an while processing please try again');
        }
    }

    public function readMistake($uuid, $timeline_id = null)
    {
        $application = Application::uuid($uuid)->firstOrFail();

        if (!empty($timeline_id)) {
            $timeline = $application->timelines()
                ->where('id', $timeline_id)
                ->whereIn('status', ['editor-accepts', 'editor-revise', 'editor-rejects', 'assigning reviewers'])
                ->firstOrFail();

            return view('admin.application.readMistake', compact('timeline'));
        }

        $timeline = $application->timelines()
            ->whereIn('status', ['editor-accepts', 'editor-revise', 'editor-rejects', 'assigning reviewers'])
            ->orderByDesc('created_at')
            ->first();
        return view('admin.application.readMistake', compact('timeline'));
    }

    public function reviewerReadMore($uuid, $timeline_id = null)
    {
        $application = Application::where('uuid', $uuid)->firstOrFail();
        $internalStatuses = [
            'internal-reviewer-accepts',
            'internal-reviewer-revise',
            'internal-reviewer-rejects',
            'internal-reviewer-reviewed',
        ];
        $externalStatuses = [
            'external-reviewer-accepts',
            'external-reviewer-revise',
            'external-reviewer-rejects',
            'external-reviewer-reviewed',
        ];

        // The reviewer read-more modal is shared by every role, and `rev_type` is a
        // static property of the *user*, not of their relationship to this manuscript.
        // An editor who is also typed as a reviewer therefore needs to be identified by
        // the assignment instead, otherwise they would be blinded to the other slot's
        // review on their own manuscript. Same rule as show().
        $user = authUser('admin');

        $assignedEditorId = ApplicationTimeline::where('application_id', $application->id)
            ->where('status', 'processing')
            ->orderBy('id', 'desc')
            ->value('assigned_to_id');

        $isEditorOfThisManuscript = $assignedEditorId == optional($user)->id;

        if (!empty($timeline_id)) {
            $timeline = $application->timelines()
                ->where('id', $timeline_id)
                ->whereIn('status', array_merge($internalStatuses, $externalStatuses))
                ->firstOrFail();

            $timeline1 = in_array($timeline->status, $internalStatuses, true)
                ? $timeline
                : new ApplicationTimeline(['data' => []]);
            $timeline2 = in_array($timeline->status, $externalStatuses, true)
                ? $timeline
                : new ApplicationTimeline(['data' => []]);

            return view('admin.application.reviewer_readmore', compact('timeline1', 'timeline2', 'application', 'isEditorOfThisManuscript'));
        }

        $timeline1 = $application->timelines()
            ->whereIn('status', $internalStatuses)
            ->orderByDesc('id')
            ->first();
        $timeline2 = $application->timelines()
            ->whereIn('status', $externalStatuses)
            ->orderByDesc('id')
            ->first();

        return view('admin.application.reviewer_readmore', compact('timeline1', 'timeline2', 'application', 'isEditorOfThisManuscript'));
    }


    // Change $status to $timeline_id
    public function techEditorReadmore($uuid, $timeline_id)
    {
        // Find the application
        $application = Application::where('uuid', $uuid)->firstOrFail();

        // Find the EXACT timeline entry by its ID, ensuring it belongs to this application
        $timeline = clone $application->timelines()
            ->where('id', $timeline_id)
            ->firstOrFail();

        // Assistant editor's final decisions (revise/rejected/pre-accepted) don't carry "checks" data,
        // whereas technical editor rows always do. Use that to pick the right modal heading.
        $isAssistantEditor = in_array($timeline->status, ['revise', 'rejected', 'pre-accepted'], true)
            && !isset(($timeline->data ?? [])['checks']);

        return view('admin.application.techEditor_readMore', compact('timeline', 'isAssistantEditor'));
    }



    public function techEditorDecision($request, $uuid)
    {


        if ($request->isMethod('GET')) {
            return view('admin.application.techEditorDecision', compact('uuid'));
        }

        if ($request->isMethod("POST")) {
            $application = Application::where('uuid', $uuid)->with('timelines')->first();

            // Capture checks
            $checks = [
                'plagiarism'   => $request->has('plagiarism'),
                'completeness' => $request->has('completeness'),
                'crossref'     => $request->has('crossref'),
            ];

            // Enforce rule: Accept only if all checks are true
            if ($request->decision === 'accept' && in_array(false, $checks, true)) {
                return errorResponse('All checks must be completed before accepting.');
            }

            if ($request->decision === 'accept') {
                $timeline = $application->timelines
                    ->where('application_id', $application->id)
                    ->where('status', 'submitted')
                    ->first();

                $updated = $application->update([
                    'status' => 'technical checks approved',
                    'data' => [
                        'comment'   => $request->remark,
                        'decision'  => $request->decision,
                        'checks'    => $checks,
                    ],
                ]);

                $timelineUpdated = ApplicationTimeline::create([
                    'application_id' => $application->id,
                    'status' => 'technical checks approved',
                    'assigned_by' => authUser('admin')->id,
                    'is_visible_to_author' => 1,

                    'remark' => 'Your manuscript checks the technical requirements.',
                    'data' => [
                        'comment'   => $request->remark,
                        'decision'  => $request->decision,
                        'checks'    => $checks,
                    ],
                ]);

                if ($timelineUpdated) {
                    $this->notifyAssistantEditor($application, $request->remark);
                }
            }

            if ($request->decision === 'revise') {
                $updated = $application->update([
                    'status' => 'revise',
                    'data' => [
                        'comment'   => $request->remark,
                        'decision'  => $request->decision,
                        'checks'    => $checks,
                    ],
                ]);

                $timelineUpdated = ApplicationTimeline::create([
                    'application_id' => $application->id,
                    'status' => 'revise',
                    'assigned_by' => authUser('admin')->id,
                    'is_visible_to_author' => 1,
                    'remark' => 'Please revise your manuscript.',
                    'data' => [
                        'comment'   => $request->remark,
                        'decision'  => $request->decision,
                        'checks'    => $checks,
                    ],
                ]);

                if ($timelineUpdated && $application->author) {
                    Mail::to($application->author->email)
                        ->send(new AuthorReviseMail(
                            $application,
                            $request->remark,
                            route('author.application.show', $application->uuid),
                        ));

                    $this->notifyInApp($application->author, $application, [
                        'category' => 'decision',
                        'type' => 'application.revise',
                        'title' => 'Revision requested',
                        'message' => 'Please revise your manuscript ' . $this->applicationReference($application) . '.',
                        'icon' => 'ri-edit-2-line',
                        'severity' => 'warning',
                    ], true);
                }
            }
            if ($request->decision === 'out-of-scope') {
                $updated = $application->update([
                    'status' => 'out of scope',
                    'data' => [
                        'comment'   => $request->remark,
                        'decision'  => $request->decision,
                        'checks'    => $checks,
                    ],
                ]);

                $timelineUpdated = ApplicationTimeline::create([
                    'application_id' => $application->id,
                    'status' => 'out of scope',
                    'assigned_by' => authUser('admin')->id,
                    'is_visible_to_author' => 1,
                    'remark' => 'Your manuscript is out of scope.',
                    'data' => [
                        'comment'   => $request->remark,
                        'decision'  => $request->decision,
                        'checks'    => $checks,
                    ],
                ]);

                if ($timelineUpdated) {
                    $this->notifyAssistantEditor($application, $request->remark);
                }
            }




            if (!empty($updated) && !empty($timelineUpdated)) {
                return successResponse('Successfull', route('admin.application.show', $uuid));
            }

            return errorResponse('Something went wrong');
        }
    }

    private function assistantEditor()
    {
        return Admin::whereHas('roles', function ($query) {
            $query->where('name', 'assitantEditor');
        })->orderBy('id')->first();
    }

    private function notifyAssistantEditor(Application $application, ?string $remark = null)
    {
        $assistantEditor = $this->assistantEditor();

        if (!$assistantEditor) {
            return;
        }

        Mail::to($assistantEditor->email)
            ->send(new AssistantEditorAssignmentMail(
                $assistantEditor,
                $application,
                $remark,
                route('admin.application.index'),
            ));

        $this->notifyInApp($assistantEditor, $application, [
            'category' => 'application',
            'type' => 'application.assistant-editor',
            'title' => 'Manuscript needs your attention',
            'message' => 'Manuscript ' . $this->applicationReference($application) . ' has been forwarded to you.',
            'icon' => 'ri-file-paper-2-line',
            'severity' => 'info',
        ]);
    }

    private function notifyEditorFinalDecision(Application $application, ?ApplicationTimeline $processingTimeline, string $decision, ?string $comment = null)
    {
        if (!$processingTimeline || empty($processingTimeline->assigned_by)) {
            return;
        }

        $assigner = Admin::find($processingTimeline->assigned_by);

        if (!$assigner) {
            return;
        }

        Mail::to($assigner->email)
            ->send(new EditorFinalDecisionMail(
                $assigner,
                $application,
                $decision,
                $comment,
                route('admin.application.show', $application->uuid),
            ));

        $this->notifyInApp($assigner, $application, [
            'category' => 'decision',
            'type' => 'decision.editor-final',
            'title' => 'Handling editor decision: ' . $decision,
            'message' => 'The handling editor recorded a decision for manuscript ' . $this->applicationReference($application) . '.',
            'icon' => 'ri-gavel-line',
            'severity' => 'info',
        ]);
    }

    /**
     * Resolve the Editor in Chief account. Mirrors the resolution used by
     * notifyChief() (the configured Chief email), falling back to the
     * superadmin role account.
     */
    private function chiefAdmin()
    {
        $chief = Admin::where('email', 'editor@jaspi.saspi.in')->first();

        if (!$chief) {
            $chief = Admin::whereHas('roles', function ($query) {
                $query->where('name', 'superadmin');
            })->first();
        }

        return $chief;
    }

    private function notifyChief(Application $application)
    {
        // $chief = Admin::whereHas('roles', function ($query) {
        //     $query->where('name', 'superadmin');
        // })->first();

        // if (!$chief) {
        //     $chief = Admin::where('is_admin', true)->first();
        // }

        $chief = Admin::where('email','editor@jaspi.saspi.in')->first();
        if (!$chief) {
            return;
        }

        Mail::to($chief->email)
            ->send(new ChiefApprovalRequestMail(
                $chief,
                $application,
                authUser('admin'),
                route('admin.application.show', $application->uuid),
            ));

        $this->notifyInApp($chief, $application, [
            'category' => 'decision',
            'type' => 'decision.chief-request',
            'title' => 'Chief approval requested',
            'message' => 'A manuscript is waiting for your approval: ' . $this->applicationReference($application) . '.',
            'icon' => 'ri-shield-check-line',
            'severity' => 'warning',
        ]);
    }

    private function notifyRequester(Application $application, string $decision, ?string $response = null)
    {
        $requesterId = data_get($application->data, 'chief_requested_by');

        if (!$requesterId) {
            return;
        }

        $requester = Admin::find($requesterId);

        if (!$requester) {
            return;
        }

        Mail::to($requester->email)
            ->send(new ChiefApprovalResponseMail(
                $requester,
                $application,
                $decision === 'accept' ? 'Accepted' : 'Rejected',
                $response,
                route('admin.application.show', $application->uuid),
            ));

        $this->notifyInApp($requester, $application, [
            'category' => 'decision',
            'type' => 'decision.chief-response',
            'title' => 'Chief editor responded',
            'message' => 'The Chief Editor ' . ($decision === 'accept' ? 'accepted' : 'rejected') . ' the request for manuscript ' . $this->applicationReference($application) . '.',
            'icon' => 'ri-shield-check-line',
            'severity' => $decision === 'accept' ? 'success' : 'danger',
        ]);
    }

    private function notifyAuthorFinalDecision(Application $application, string $decision, ?string $comment = null)
    {
        if (!$application->author || empty($application->author->email)) {
            return;
        }

        Mail::to($application->author->email)
            ->send(new AuthorFinalDecisionMail(
                $application,
                $decision,
                $comment,
                route('author.application.show', $application->uuid),
            ));

        $this->notifyInApp($application->author, $application, [
            'category' => 'decision',
            'type' => 'decision.author-final',
            'title' => 'Final decision: ' . $decision,
            'message' => 'A final decision has been recorded for your manuscript ' . $this->applicationReference($application) . '.',
            'icon' => 'ri-gavel-line',
            'severity' => $decision === 'Accepted' ? 'success' : 'warning',
        ], true);
    }

    /**
     * Reviewers who actually submitted a review for this manuscript.
     *
     * Decliners, replacements and reviewers who never submitted are excluded:
     * the list is built from the completion timeline rows, whose `assigned_by`
     * is the reviewer.
     */
    private function reviewersWhoSubmitted(Application $application)
    {
        $reviewerIds = ApplicationTimeline::where('application_id', $application->id)
            ->whereIn('status', ['internal-reviewer-reviewed', 'external-reviewer-reviewed'])
            ->pluck('assigned_by')
            ->filter()
            ->unique();

        if ($reviewerIds->isEmpty()) {
            return collect();
        }

        return Admin::whereIn('id', $reviewerIds)->get();
    }

    /**
     * Thank every reviewer who actually submitted a review once the final
     * decision (accepted / rejected / withdrawn) has been recorded.
     *
     * Each send is isolated so one bad address cannot break the decision.
     */
    private function notifyReviewersFinalDecision(Application $application, string $decision, ?string $comment = null)
    {
        foreach ($this->reviewersWhoSubmitted($application) as $reviewer) {
            if (empty($reviewer->email)) {
                continue;
            }

            try {
                Mail::to($reviewer->email)->send(new ReviewerFinalDecisionMail(
                    $reviewer,
                    $application,
                    $decision,
                    $comment,
                    route('admin.application.show', $application->uuid),
                ));
            } catch (Throwable $e) {
                Log::error('Reviewer final decision mail failed for reviewer ' . $reviewer->id . ': ' . $e->getMessage());
            }

            $this->notifyInApp($reviewer, $application, [
                'category' => 'decision',
                'type' => 'decision.reviewer-final',
                'title' => 'Final decision: ' . $decision,
                'message' => 'A final decision has been recorded for manuscript ' . $this->applicationReference($application) . '.',
                'icon' => 'ri-gavel-line',
                'severity' => $decision === 'Accepted' ? 'success' : 'warning',
            ]);
        }
    }

    /**
     * Tell the reviewers who submitted a review that the manuscript is now
     * published, including the DOI.
     */
    private function notifyReviewersPublished(Application $application, ?string $doi = null)
    {
        foreach ($this->reviewersWhoSubmitted($application) as $reviewer) {
            if (empty($reviewer->email)) {
                continue;
            }

            try {
                Mail::to($reviewer->email)->send(new ReviewerPublishedMail(
                    $reviewer,
                    $application,
                    $doi,
                    route('admin.application.show', $application->uuid),
                ));
            } catch (Throwable $e) {
                Log::error('Reviewer published mail failed for reviewer ' . $reviewer->id . ': ' . $e->getMessage());
            }

            $this->notifyInApp($reviewer, $application, [
                'category' => 'publication',
                'type' => 'publication.reviewer-published',
                'title' => 'Manuscript published',
                'message' => 'Manuscript ' . $this->applicationReference($application) . ' has been published.',
                'icon' => 'ri-book-open-line',
                'severity' => 'success',
            ]);
        }
    }

    private function notifyTechnicalEditor(Application $application, ?string $comment = null)
    {
        $technicalEditors = Admin::whereHas('roles', function ($query) {
            $query->where('name', 'technicalEditor');
        })->get();

        if ($technicalEditors->isEmpty()) {
            return;
        }

        foreach ($technicalEditors as $technicalEditor) {
            Mail::to($technicalEditor->email)->send(new TechnicalEditorNotificationMail(
                $technicalEditor,
                $application,
                $comment,
                route('admin.application.show', $application->uuid),
            ));

            $this->notifyInApp($technicalEditor, $application, [
                'category' => 'application',
                'type' => 'application.technical-editor',
                'title' => 'Manuscript ready for technical checks',
                'message' => 'Manuscript ' . $this->applicationReference($application) . ' is ready for technical checks.',
                'icon' => 'ri-file-check-line',
                'severity' => 'info',
            ]);
        }
    }

    private function notifyPublisher(Application $application, $staffId)
    {
        $publisher = Admin::find($staffId);

        if (!$publisher || empty($publisher->email)) {
            return;
        }

        Mail::to($publisher->email)
            ->send(new PublisherAssignmentMail(
                $publisher,
                $application,
                route('admin.application.show', $application->uuid),
            ));

        $this->notifyInApp($publisher, $application, [
            'category' => 'publication',
            'type' => 'publication.publisher-assigned',
            'title' => 'Manuscript assigned for publishing',
            'message' => 'Manuscript ' . $this->applicationReference($application) . ' has been assigned to you for publishing.',
            'icon' => 'ri-book-open-line',
            'severity' => 'info',
        ]);
    }

    private function notifyPublisherAcceptedForPublication(
        Application $application,
        Admin $publisher,
        ?string $comment = null
    ): void {
        if (empty($publisher->email)) {
            return;
        }

        try {
            Mail::to($publisher->email)
                ->send(new PublicationAcceptedMail(
                    $publisher,
                    $application,
                    $comment,
                    route('admin.application.show', $application->uuid),
                ));
        } catch (Throwable $e) {
            Log::error('Publication accepted mail failed for publisher ' . $publisher->id . ': ' . $e->getMessage());
        }

        $this->notifyInApp($publisher, $application, [
            'category' => 'publication',
            'type' => 'publication.accepted-for-publication',
            'title' => 'Manuscript accepted for publication',
            'message' => 'Manuscript ' . $this->applicationReference($application) . ' is ready for its DOI and publication.',
            'icon' => 'ri-book-open-line',
            'severity' => 'success',
        ]);
    }

    private function notifyTechnicalEditorFormattedProof(Application $application, Admin $technicalEditor, ?string $comment = null)
    {
        if (empty($technicalEditor->email)) {
            return;
        }

        Mail::to($technicalEditor->email)
            ->send(new FormattedProofReadyMail(
                $technicalEditor,
                $application,
                $comment,
                route('admin.application.show', $application->uuid),
            ));

        $this->notifyInApp($technicalEditor, $application, [
            'category' => 'publication',
            'type' => 'publication.formatted-proof-ready',
            'title' => 'Formatted proof ready',
            'message' => 'The formatted proof for manuscript ' . $this->applicationReference($application) . ' is ready.',
            'icon' => 'ri-file-text-line',
            'severity' => 'info',
        ]);
    }

    private function notifyAuthorFormattedProof(Application $application, ?string $comment = null)
    {
        if (!$application->author || empty($application->author->email)) {
            return;
        }

        Mail::to($application->author->email)
            ->send(new FormattedProofSubmittedMail(
                $application,
                $comment,
                route('author.application.show', $application->uuid),
            ));

        $this->notifyInApp($application->author, $application, [
            'category' => 'publication',
            'type' => 'publication.formatted-proof-submitted',
            'title' => 'Formatted proof submitted',
            'message' => 'The formatted proof for your manuscript ' . $this->applicationReference($application) . ' has been submitted.',
            'icon' => 'ri-file-text-line',
            'severity' => 'info',
        ], true);
    }

    private function notifyReviewSubmitted(Application $application)
    {
        $processingTimeline = ApplicationTimeline::where('application_id', $application->id)
            ->where('status', 'processing')
            ->orderBy('id', 'desc')
            ->first();

        if (!$processingTimeline || empty($processingTimeline->assigned_to_id)) {
            return;
        }

        $editor = Admin::find($processingTimeline->assigned_to_id);

        if (!$editor || empty($editor->email)) {
            return;
        }

        Mail::to($editor->email)
            ->send(new ReviewSubmittedMail(
                $editor,
                $application,
                authUser('admin'),
                route('admin.application.show', $application->uuid),
            ));

        $this->notifyInApp($editor, $application, [
            'category' => 'review',
            'type' => 'review.submitted',
            'title' => 'Review submitted',
            'message' => 'A review has been submitted for manuscript ' . $this->applicationReference($application) . '.',
            'icon' => 'ri-file-check-line',
            'severity' => 'success',
        ]);
    }

    /**
     * The handling editor on record for a manuscript - same rule as show():
     * the assignee of the most recent `processing` timeline entry.
     */
    private function handlingEditorFor(Application $application): ?Admin
    {
        $processingTimeline = ApplicationTimeline::where('application_id', $application->id)
            ->where('status', 'processing')
            ->orderBy('id', 'desc')
            ->first();

        if (!$processingTimeline || empty($processingTimeline->assigned_to_id)) {
            return null;
        }

        return Admin::find($processingTimeline->assigned_to_id);
    }

    /**
     * A reviewer accepting or declining is a decision the handling editor has to
     * act on (start the review, or line up a replacement), so both outcomes are
     * pushed to them by email.
     */
    private function notifyReviewerAccepted(Application $application, bool $isInternal)
    {
        $editor = $this->handlingEditorFor($application);

        if (!$editor || empty($editor->email)) {
            return;
        }

        Mail::to($editor->email)
            ->send(new ReviewerAcceptedMail(
                $editor,
                $application,
                authUser('admin'),
                $isInternal,
                route('admin.application.show', $application->uuid),
            ));

        $this->notifyInApp($editor, $application, [
            'category' => 'review',
            'type' => 'review.accepted',
            'title' => 'Reviewer accepted',
            'message' => 'A reviewer accepted the invitation for manuscript ' . $this->applicationReference($application) . '.',
            'icon' => 'ri-user-follow-line',
            'severity' => 'success',
        ]);
    }

    private function notifyReviewerDeclined(Application $application, ?string $reason = null)
    {
        $editor = $this->handlingEditorFor($application);

        if (!$editor || empty($editor->email)) {
            return;
        }

        Mail::to($editor->email)
            ->send(new ReviewerDeclinedMail(
                $editor,
                $application,
                authUser('admin'),
                $reason,
                route('admin.application.show', $application->uuid),
            ));

        $this->notifyInApp($editor, $application, [
            'category' => 'review',
            'type' => 'review.declined',
            'title' => 'Reviewer declined',
            'message' => 'A reviewer declined the invitation for manuscript ' . $this->applicationReference($application) . '.',
            'icon' => 'ri-user-unfollow-line',
            'severity' => 'danger',
        ]);
    }

    private function notifyAssignerOfEditorDecline(Application $application, Admin $editor, ?string $reason, ?ApplicationTimeline $assignerTimeline = null)
    {
        if (!$assignerTimeline || empty($assignerTimeline->assigned_by)) {
            return;
        }

        $assigner = Admin::find($assignerTimeline->assigned_by);

        if (!$assigner || empty($assigner->email)) {
            return;
        }

        Mail::to($assigner->email)
            ->send(new EditorDeclinedAssignmentMail(
                $assigner,
                $application,
                $editor,
                $reason,
                route('admin.application.show', $application->uuid),
            ));

        $this->notifyInApp($assigner, $application, [
            'category' => 'application',
            'type' => 'application.editor-declined',
            'title' => 'Handling editor declined',
            'message' => ($editor->name ?: 'The handling editor') . ' declined the assignment for manuscript ' . $this->applicationReference($application) . '.',
            'icon' => 'ri-user-unfollow-line',
            'severity' => 'danger',
        ]);
    }

    private function notifyAuthorPublished(Application $application, ?string $doi = null)
    {
        if (!$application->author || empty($application->author->email)) {
            return;
        }

        Mail::to($application->author->email)
            ->send(new ManuscriptPublishedMail(
                $application,
                $doi,
                route('author.application.show', $application->uuid),
            ));

        $this->notifyInApp($application->author, $application, [
            'category' => 'publication',
            'type' => 'publication.published',
            'title' => 'Your manuscript is published',
            'message' => 'Manuscript ' . $this->applicationReference($application) . ' has been published.',
            'icon' => 'ri-book-open-line',
            'severity' => 'success',
        ], true);
    }

    public function authorUpdate($uuid, $timeline_id = null)
    {
        $application = Application::uuid($uuid)->firstOrFail();

        if (!empty($timeline_id)) {
            $timeline = $application->timelines()
                ->where('id', $timeline_id)
                ->where('status', 'processing')
                ->firstOrFail();

            return view('author.cms.authorUpdate', compact('timeline'));
        }

        $timeline = $application->timelines()
            ->where('status', 'processing')
            ->orderByDesc('created_at')
            ->first();
        return view('author.cms.authorUpdate', compact('timeline'));
    }

    public function assignPublisher($request, $uuid)
    {
        if ($request->isMethod('GET')) {
            // $staffs = Admin::whereHas('roles', function ($q) {
            //     $q->whereIn('name', ['Publisher', 'superadmin']);
            // })->get();

            $staffs = Admin::where('is_admin', false)
                ->whereHas('roles', function ($q) {
                    $q->where('name', 'Publisher');
                })
                ->get();

            return view('admin.application.assignPublisherForm', compact('uuid', 'staffs'));
        }

        if ($request->isMethod('POST')) {
            $application = Application::where('uuid', $uuid)->first();

            $timeline = ApplicationTimeline::create([
                'application_id' => $application->id,
                'status' => 'accepted',
                'assigned_to_id' => $request->staff_id,
                'assigned_by' => authUser('admin')->id,
                'remark' => 'Your application has been submitted for formatting.',
                'is_visible_to_author' => 1,
            ]);
            $updated = $application->update(['status' => 'accepted', 'assigned_to_id' => $request->staff_id]);

            if ($timeline && $updated) {
                $this->notifyPublisher($application, $request->staff_id);
                return successResponse('First Cycle Completed Successfully', route('admin.application.show', $application->uuid));
            }
            return errorResponse('Their was an error in the process.');
        }
        return errorResponse('Invalid request method');
    }

    public function formatted_document($request, $app_id)
    {
        $application = Application::where('id', $app_id)->first();
        // return $app_id;

        if (!$application) {
            return errorResponse('Application Not Found.');
        }

        $user = authUser('admin');
        $isAssignedPublisher = isPublisher()
            && (int) $application->assigned_to_id === (int) $user->id;

        if (!$isAssignedPublisher && $user->is_admin != 1) {
            return errorResponse('Only the assigned publisher can upload the formatted proof.', 403);
        }

        if ($application->status !== 'accepted') {
            return errorResponse('The manuscript is not ready for typesetting.', 400);
        }

        if ($request->isMethod('GET')) {
            return view('admin.application.formatted_document', [
                // 'id' => $id,
                'type' => 'typesetting review',
                'app_id' => $app_id
            ]);
        }


        if ($request->isMethod('POST')) {

            if (!$request->hasFile('file_upload')) {
                return errorResponse('File not found');
            }

            $technicalEditor = Admin::where('status', 1)
                ->whereHas('roles', function ($q) {
                    $q->where('name', 'technicalEditor');
                })
                ->orderBy('id')
                ->first();

            if (!$technicalEditor) {
                return errorResponse('No active technical editor is available.');
            }

            // Store file ONLY in attachments
            $attachment = $this->storeApplicationFile(
                $request,
                $application->id,
                'Formatted Proof',
                'Formatted Proof',
            );

            if (!$attachment) {
                return errorResponse('File upload failed');
            }

            // Timeline entry (NO file_url here)
            $timeline = ApplicationTimeline::create([
                'application_id' => $application->id,
                'status' => 'typesetting review',
                'is_visible_to_author' => 0,
                'assigned_to_id' => $user->id,
                'remark' => 'Typesetting review completed.',
                'data' => [
                    'comment' => $request->comment,
                ],
            ]);

            // Update application status (NO file_url here)
            $updated = $application->update([
                'status' => 'typesetting review',
                'assigned_to_id' => $technicalEditor->id,
                // 'published_doi' => $request->doi,
            ]);

            if ($timeline && $updated) {
                $this->notifyTechnicalEditorFormattedProof($application, $technicalEditor, $request->comment);
                $this->notifyAuthorFormattedProof($application, $request->comment);

                return successResponse(
                    'Formatted proof uploaded successfully',
                    route('admin.application.show', $application->uuid)
                );
            }

            return errorResponse('There was an error uploading the formatted proof');
        }
    }

    public function send_for_proofreading($request, $uuid)
    {
        $application = Application::where('uuid', $uuid)->with('timelines', 'author')->first();
        if (!$application) {
            return errorResponse('Application not found');
        }

        $user = authUser('admin');
        $isAssignedTechnicalEditor = isTechnicalEditor()
            && (int) $application->assigned_to_id === (int) $user->id;

        if (!$isAssignedTechnicalEditor && $user->is_admin != 1) {
            return errorResponse('Only the assigned technical editor can send the manuscript for proofreading.', 403);
        }

        if (!in_array($application->status, ['typesetting review', 'author corrections submitted'], true)) {
            return errorResponse('The manuscript is not ready for proofreading.', 400);
        }

        if ($request->isMethod('GET')) {
            return view('admin.application.send_for_proofreading', [
                'uuid' => $uuid
            ]);
        }

        if ($request->isMethod('POST')) {

            $validated = Validator::make(
                $request->all(),
                [
                    'description' => 'required',
                ],
                [
                    'description.required' => 'Comment is required',
                ]
            );

            if ($validated->fails()) {
                return response()->json([
                    'status' => "400",
                    'errors' => $validated->errors(),
                ], 400);
            }

            // Keep the technical editor as the assignee while the author
            // holds the manuscript. The list renders the author's name for
            // this status without changing the actual owner.
            $technicalEditorId = $application->assigned_to_id ?: $user->id;

            $timeline = ApplicationTimeline::create([
                'application_id' => $application->id,
                'status' => 'proofreading',
                'is_visible_to_author' => 1,
                'assigned_to_id' => $technicalEditorId,
                'remark' => 'Author(Owner of Manuscript) required to proofread the manuscript.',
                'data' => [
                    'comment' => $request->description,
                ],
            ]);

            $updated = $application->update([
                'status' => 'proofreading',
                'assigned_to_id' => $technicalEditorId,
            ]);

            // --- NEW EMAIL LOGIC ---

            // Adjust '$application->user->email' to whatever relationship points to your author
            $authorEmail = $application->author->email ?? "";

            if ($authorEmail) {
                // Send the email. (Tip: Use ->queue() instead of ->send() if you have background workers setup)
                Mail::to($authorEmail)->send(new ProofreadingMail($application, $request->description, route('author.application.show', $application->uuid)));

                $this->notifyInApp($application->author, $application, [
                    'category' => 'publication',
                    'type' => 'publication.proofreading',
                    'title' => 'Proofreading required',
                    'message' => 'Please proofread your manuscript ' . $this->applicationReference($application) . '.',
                    'icon' => 'ri-file-text-line',
                    'severity' => 'warning',
                ], true);
            }

            // Return a success response so the frontend knows it's done
            // return response()->json([
            //     'status' => "200",
            //     'message' => 'Manuscript sent for proofreading successfully.',
            // ], 200);
            return successResponse('Manuscript sent for proofreading successfully.', route('admin.application.show', $application->uuid));
            // -----------------------

        }

        return errorResponse('Invalid request method');
    }

    public function acceptForPublication($request, $uuid)
    {
        $application = Application::where('uuid', $uuid)->with('author')->first();

        if (!$application) {
            return errorResponse('Application not found');
        }

        $user = authUser('admin');
        $isAssignedTechnicalEditor = isTechnicalEditor()
            && (int) $application->assigned_to_id === (int) $user->id;

        if (!$isAssignedTechnicalEditor) {
            return errorResponse('Only the assigned technical editor can accept this manuscript for publication.', 403);
        }

        if ($application->status !== 'author corrections submitted') {
            return errorResponse('This manuscript is not awaiting technical editor approval.', 400);
        }

        if ($request->isMethod('GET')) {
            return view('admin.application.accept_for_publication', [
                'uuid' => $uuid,
            ]);
        }

        if ($request->isMethod('POST')) {
            $validated = Validator::make($request->all(), [
                'comment' => 'nullable|string|max:2000',
            ]);

            if ($validated->fails()) {
                return errorResponse($validated->errors()->first(), 422);
            }

            // The publisher is the person recorded when the manuscript was
            // first handed to the publication team.
            $publisherId = ApplicationTimeline::where('application_id', $application->id)
                ->where('status', 'accepted')
                ->orderByDesc('id')
                ->value('assigned_to_id');

            $publisher = $publisherId ? Admin::find($publisherId) : null;

            if (!$publisher) {
                return errorResponse('No publisher is assigned to this manuscript.', 422);
            }

            $comment = $validated->validated()['comment'] ?? null;
            $status = Application::ACCEPTED_FOR_PUBLICATION_STATUS;

            $result = DB::transaction(function () use ($application, $publisher, $user, $status, $comment) {
                $timeline = ApplicationTimeline::create([
                    'application_id' => $application->id,
                    'status' => $status,
                    'is_visible_to_author' => 1,
                    'assigned_to_id' => $publisher->id,
                    'assigned_by' => $user->id,
                    'remark' => 'Technical editor accepted the manuscript for publication. Assigned to the publisher for DOI and publication.',
                    'data' => [
                        'comment' => $comment,
                    ],
                ]);

                $updated = $application->update([
                    'status' => $status,
                    'assigned_to_id' => $publisher->id,
                ]);

                return $timeline && $updated;
            });

            if (!$result) {
                return errorResponse('There was an error accepting the manuscript for publication.');
            }

            $this->notifyPublisherAcceptedForPublication($application, $publisher, $comment);

            return successResponse(
                'Manuscript accepted for publication and assigned to the publisher.',
                route('admin.application.index')
            );
        }

        return errorResponse('Invalid request method');
    }

    private function storeApplicationFile($request, $app_id, $type, $legend = null)
    {
        if (!$request->hasFile('file_upload')) {
            return null;
        }

        $file = $request->file('file_upload');

        $originalName = $file->getClientOriginalName();
        $extension = $file->getClientOriginalExtension();
        $mimeType = $file->getClientOriginalExtension();
        $size = $file->getSize();

        $fileName = time() . '_' . Str::slug(pathinfo($originalName, PATHINFO_FILENAME)) . '.' . $extension;
        $relativePath = 'uploads/applications/';
        $fullPath = public_path($relativePath);

        if (!File::exists($fullPath)) {
            File::makeDirectory($fullPath, 0775, true);
        }

        $file->move($fullPath, $fileName);

        $attachment = Attachment::create([
            'uuid'            => (string) Str::uuid(),
            'application_id'  => $app_id,
            'file_name'       => $fileName,
            'file_type'       => $mimeType,
            'file_size'       => $size,
            'file_url'        => $relativePath . $fileName,
            'attachment_type' => $type,
            'legend'          => $legend,
        ]);

        return $attachment;
    }

    /**
     * Decide which recommendation question (Q8/Q17/Q18) should be used based on article type.
     *
     * Group A  : Original Articles, Systematic Review and Meta-Analysis, Brief Communications  -> Q17
     * Group B  : Infectious Diseases Cases/Vignettes, Narrative Reviews                        -> Q18
     * Fallback : Anything else                                                                -> Q8
     */
    private function resolveArticleTypeRecommendation(Application $application): array
    {
        $articleTypes = $application->article_type ?? [];

        if (is_string($articleTypes)) {
            $decoded = json_decode($articleTypes, true);
            $articleTypes = json_last_error() === JSON_ERROR_NONE ? $decoded : [$articleTypes];
        }

        if (!is_array($articleTypes)) {
            $articleTypes = [$articleTypes];
        }

        $primaryType = trim($articleTypes[0] ?? '');

        $groupA = [
            'Original Articles',
            'Original Article',
            'Systematic Review and Meta-Analysis',
            'Brief Communications',
            'Brief Communication',
        ];

        $groupB = [
            'Infectious Diseases Cases/Vignettes',
            'Infectious Diseases Cases/Vignette',
            'Narrative Reviews',
            'Narrative Review',
        ];

        $recommendationField = 'q8';
        if (in_array($primaryType, $groupA, true)) {
            $recommendationField = 'q17';
        } elseif (in_array($primaryType, $groupB, true)) {
            $recommendationField = 'q18';
        }

        return [$primaryType, $recommendationField];
    }


    public function reviewScore($request, $uuid)
    {
        if ($request->isMethod('GET')) {
            $application = Application::where('uuid', $uuid)->firstOrFail();
            [$primaryArticleType, $recommendationField] = $this->resolveArticleTypeRecommendation($application);

            return view('admin.application.reviewScoreForm', compact('uuid', 'primaryArticleType', 'recommendationField'));
        }

        if ($request->isMethod('POST')) {
            $admin = Admin::where('is_admin', true)->first();
            $application = Application::where('uuid', $uuid)->first();
            [$primaryArticleType, $recommendationField] = $this->resolveArticleTypeRecommendation($application);
            // return $request;

            $editor = ApplicationTimeline::where('status', 'processing')
                ->where('application_id', $application->id)
                ->orderBy('id', 'desc')
                ->first(); // fetch the latest single record

            // $editor_id = Admin::Find($editor->assigned_to_id)->id;

            // return $rev_type;


            $list = [
                1 => [
                    'heading' => 'Q.1 Originality',
                    'question' => 'How original is the concept presented in this article?',
                    'answer' => $request->q1,
                ],
                2 => [
                    'heading' => 'Q.2 Significance',
                    'question' => 'How significant are the article’s conclusions?',
                    'answer' => $request->q2,
                ],
                3 => [
                    'heading' => 'Q.3 Timeliness',
                    'question' => 'How relevant is the article for stewardship or infectious diseases in the contemporary landscape?',
                    'answer' => $request->q3,
                ],
                4 => [
                    'heading' => 'Q.4 Logic',
                    'question' => 'How well-reasoned is the article?',
                    'answer' => $request->q4,
                ],
                5 => [
                    'heading' => 'Q.5 Quality',
                    'question' => 'What is the quality and clarity of writing in the article?',
                    'answer' => $request->q5,
                ],
                6 => [
                    'heading' => 'Q.6 Interest',
                    'question' => 'How interesting is the article?',
                    'answer' => $request->q6,
                ],
                7 => [
                    'heading' => 'Q.7 Methodology Validity',
                    'question' => 'Does the manuscript provide RRB/IEC approval? If applicable, how valid is the research design for the stated objectives, and how appropriate are any statistical techniques applied? (for Original articles, Brief communications, Systematic Review and Meta-Analysis ONLY)',
                    'answer' => !empty($request->q20_hidden) ?  $request->q20_hidden : 'no',
                ],
                8 => [
                    'heading' => 'Q.8 Recommendation',
                    'question' => 'What is your recommendation for the article?',
                    'answer' => null,
                ],
                9 => [
                    'heading' => 'Q.9 Need for Editorial Commentary',
                    'question' => 'Do you think this article, if accepted, requires commentary or editorial?',
                    'answer' => $request->q9,
                ],
                10 => [
                    'heading' => 'Q.10 Willingness to Write Commentary',
                    'question' => 'Would you like to write a commentary/editorial when the article is accepted?',
                    'answer' => $request->q10,
                ],
                11 => [
                    'heading' => 'Q.11 New Message in Manuscript',
                    'question' => 'Is there a new message from the manuscript?',
                    'answer' => $request->q11,
                ],
                12 => [
                    'heading' => 'Q.12 Priority for Publication',
                    'question' => 'Please rate this paper’s priority for publication.',
                    'answer' => $request->q12,
                ],
                13 => [
                    'heading' => 'Q.13 Need for Reassessment',
                    'question' => 'Do you want to reassess this article?',
                    'answer' => $request->q13,
                ],
                14 => [
                    'heading' => 'Q.14 Competing Interests',
                    'question' => 'Do you have any competing interests?',
                    'answer' => $request->q14 . ' (' . $request->q14_text . ')',
                ],
                15 => [
                    'heading' => 'Q.15 Comments to the Author',
                    'question' => 'Comments to the Author – Manuscript Section Wise (if possible)',
                    'answer' => $request->q15,
                ],
                16 => [
                    'heading' => 'Q.16 Confidential Comments to the Editor',
                    'question' => 'Confidential Comments to the Editor (will not be shared with authors)',
                    'answer' => $request->q16,
                ],
                17 => [
                    'heading' => 'Q.17 Recommendation (For Original Articles, Systematic Review and Meta-Analysis, Brief Communications)',
                    'question' => 'To Decide, Please Add All Scores (In Parentheses) Of Marked Responses For Questions 1 To 7',
                    'answer' => null,
                ],
                18 => [
                    'heading' => 'Q.18 Recommendation (For Infectious Diseases Cases/Vignettes, Narrative Reviews)',
                    'question' => 'To Decide, Please Add All Scores (In Parentheses) Of Marked Responses For Questions 1 To 7',
                    'answer' => null,
                ],
                19 => [
                    'heading' => 'Q.19 Confidential Comments to the Admin',
                    'question' => 'Confidential Comments to the Admin (will not be shared with authors)',
                    'answer' => $request->q19,
                ],
            ];

            $selectedRecommendation = match ($recommendationField) {
                'q17' => $request->q17,
                'q18' => $request->q18,
                default => $request->q8,
            };

            if (empty($selectedRecommendation)) {
                return errorResponse('Please provide a recommendation for this article.');
            }

            if ($recommendationField === 'q17') {
                $list[17]['answer'] = $selectedRecommendation;
            } elseif ($recommendationField === 'q18') {
                $list[18]['answer'] = $selectedRecommendation;
            } else {
                $list[8]['answer'] = $selectedRecommendation;
            }

            // Make sure all values are treated as integers
            $sumQ1toQ7 = (int) $request->q1 +
                (int) $request->q2 +
                (int) $request->q3 +
                (int) $request->q4 +
                (int) $request->q5 +
                (int) $request->q6 +
                (int) (!empty($request->q20_hidden) ? $request->q20_hidden : 0);

            $rev_type = authUser('admin')->rev_type;

            if ($rev_type == 'internal' || authUser('admin')->is_admin == 1) {
                if ($sumQ1toQ7 >= 28) {
                    $recommendation = 'Accept';
                    // $status = 'internal-reviewer-accepts';
                } elseif ($sumQ1toQ7 >= 21) {
                    $recommendation = 'Minor Revision';
                    // $status = 'internal-reviewer-revise';
                } elseif ($sumQ1toQ7 >= 14) {
                    $recommendation = 'Major Revision';
                    // $status = 'internal-reviewer-revise';
                } else {
                    $recommendation = 'Reject';
                    // $status = 'internal-reviewer-rejects';
                }
                $status = 'internal-reviewer-reviewed';
                $status_ex = 1;
            } else {
                if ($sumQ1toQ7 >= 28) {
                    $recommendation = 'Accept';
                    // $status = 'external-reviewer-accepts';
                } elseif ($sumQ1toQ7 >= 21) {
                    $recommendation = 'Minor Revision';
                    // $status = 'external-reviewer-revise';
                } elseif ($sumQ1toQ7 >= 14) {
                    $recommendation = 'Major Revision';
                    // $status = 'external-reviewer-revise';
                } else {
                    $recommendation = 'Reject';
                    // $status = 'external-reviewer-rejects';
                }
                $status = 'external-reviewer-reviewed';
                $status_ex = 2;
            }

            $data = [
                'application_id' => $application->id,
                'status' => $status,
                'assigned_to_id' => $editor->assigned_to_id,
                'assigned_by' => authUser('admin')->id,
                'remark' => 'Manuscript has been reviewed successfully',
                'data' => [
                    'comment' =>  $request->remark,
                    'score' => $sumQ1toQ7,
                    'recommendation' => $recommendation,
                    'review_score' => $list,
                    'selected_recommendation' => [
                        'question' => (int) str_replace('q', '', $recommendationField),
                        'value' => $selectedRecommendation,
                        'article_type' => $primaryArticleType,
                    ],
                ],
            ];

            if ($request->hasFile('file_input')) {

                $data['data']['file_input'] = uploadFile($request->file_input, 'uploads/reviewer_uploads/');
            }


            $timeline = ApplicationTimeline::create($data);
            // return [$data, $timeline];


            // return $timeline;
            if ($timeline) {
                $status_ex = $status_ex + ($application->status_ex ?? 0);
                if ($status_ex == 3 || $application->assigned_to_ex_id == null) {
                    $data = [
                        'status_ex' => $status_ex,
                        'status' => 'reviewed',
                        'assigned_to_id' => $editor->assigned_to_id,
                        'assigned_by' => authUser('admin')->id,
                    ];
                } else {
                    $data = [
                        'status_ex' => $status_ex,
                    ];
                }
                $updated = $application->update($data);
                if ($updated) {
                    $this->notifyReviewSubmitted($application);

                    return successResponse(
                        'Manuscript reviewed successfully',
                        route(
                            'admin.application.show',
                            $application->uuid
                        )
                    );
                }
            }

            // return $list;

            return errorResponse('Something went wrong!');
        }
    }

    public function editorDecision($request, $uuid)
    {
        if ($request->isMethod('GET')) {
            $application = Application::where('uuid', $uuid)->firstOrFail();
            $staffs = Admin::with('integrations')->whereHas('roles', function ($q) {
                $q->where('name', 'Reviewer');
                // ->orWhere('is_admin', 1);
            })->get();
            $integrations = Integration::orderBy('name')->get();

            return view('admin.application.editor_decision', compact('application', 'staffs', 'integrations'));
        }

        if ($request->isMethod('POST')) {
            $application  = Application::where('uuid', $uuid)->firstOrFail();
            $currentAdmin = authUser('admin');
            $decision     = $request->decision;
            $remark       = $request->comment;

            /* ----------------------------------
        | CASE 3 & 4: REVISE OR REJECT
        -----------------------------------*/
            if (in_array($decision, [3, 4])) {
                $status = ($decision == 3) ? 'editor-revise' : 'editor-rejects';

                $assistantEditor = $this->assistantEditor();
                $assignee        = $assistantEditor?->id ?? $application->assigned_to_id;

                $existingData = $application->data ?? [];
                $updated = $application->update([
                    'status'         => $status,
                    'assigned_to_id' => $assignee,
                    'data'           => array_merge($existingData, [
                        'comment'  => $remark ?? '',
                        'decision' => $decision,
                    ]),
                ]);

                ApplicationTimeline::create([
                    'uuid'                 => Str::uuid(),
                    'application_id'       => $application->id,
                    'status'               => $status,
                    'assigned_to_id'       => $assignee,
                    'assigned_by'          => $currentAdmin->id,
                    'is_visible_to_author' => 0,
                    'remark'               => 'Editor Decision: ' . ($decision == 3 ? 'Requested Revision' : 'Rejected'),
                    'data'                 => [
                        'comment'            => $remark ?? '',
                        'decision'           => $decision,
                        'assistant_editor_id' => $assistantEditor?->id,
                    ],
                ]);

                if ($updated) {
                    $processingTimeline = ApplicationTimeline::where('application_id', $application->id)
                        ->where('status', 'processing')
                        ->orderBy('id', 'desc')
                        ->first();

                    $this->notifyEditorFinalDecision(
                        $application,
                        $processingTimeline,
                        $decision == 3 ? 'Revision Requested' : 'Rejected',
                        $remark
                    );

                    return successResponse('Decision recorded successfully', route('admin.application.show', $application->uuid));
                }
                return errorResponse('Something went wrong!');
            }

            /* ----------------------------------
            | CASE 5: ACCEPT WITHOUT FURTHER REVISION
            -----------------------------------*/
            if ($decision == 5) {
                $status = 'editor-accepts';

                $assistantEditor = $this->assistantEditor();
                $assignee        = $assistantEditor?->id ?? $application->assigned_to_id;

                $existingData = $application->data ?? [];
                $updated = $application->update([
                    'status'            => $status,
                    'assigned_to_id'    => $assignee,
                    'assigned_to_ex_id' => null,
                    'accept_ex'         => null,
                    'status_ex'         => null,
                    'data'              => array_merge($existingData, [
                        'comment'  => $remark ?? '',
                        'decision' => $decision,
                    ]),
                ]);

                ApplicationTimeline::create([
                    'uuid'                 => Str::uuid(),
                    'application_id'       => $application->id,
                    'status'               => $status,
                    'assigned_to_id'       => $assignee,
                    'assigned_by'          => $currentAdmin->id,
                    'is_visible_to_author' => 0,
                    'remark'               => 'Editor Decision: Accepted without further revision',
                    'data'                 => [
                        'comment'            => $remark ?? '',
                        'decision'           => $decision,
                        'assistant_editor_id' => $assistantEditor?->id,
                    ],
                ]);

                if ($updated) {
                    $processingTimeline = ApplicationTimeline::where('application_id', $application->id)
                        ->where('status', 'processing')
                        ->orderBy('id', 'desc')
                        ->first();

                    $this->notifyEditorFinalDecision(
                        $application,
                        $processingTimeline,
                        'Accepted',
                        $remark
                    );

                    return successResponse('Decision recorded successfully', route('admin.application.show', $application->uuid));
                }
                return errorResponse('Something went wrong!');
            }

            /* ----------------------------------
        | CASE 1 & 2: ASSIGNING REVIEWERS
        -----------------------------------*/
            $internalId = $request->internal_reviewer_id ?? $currentAdmin->id;
            $externalId = ($request->external_reviewer_id === 'not') ? null : $request->external_reviewer_id;

            $isSelfAssign = (int)$internalId === (int)$currentAdmin->id && ($externalId === null || (int)$externalId === (int)$currentAdmin->id);

            $status    = 'assigning reviewers';
            $accept_in = null;
            $accept_ex = null;

            // Admin choosing self as internal counts as accepted internally
            if ((int)$internalId === (int)$currentAdmin->id) {
                $accept_in = 1;
            }

            if ($isSelfAssign) {
                $status    = 'under review';
                $accept_in = 1;
                $accept_ex = 1;
            }

            $existingData = $application->data ?? [];
            $updated = $application->update([
                'assigned_to_id'    => $internalId,
                'assigned_to_ex_id' => $externalId,
                'status'            => $status,
                'accept_in'         => $accept_in,
                'accept_ex'         => $accept_ex,
                'data'              => array_merge($existingData, [
                    'comment'  => $remark ?? '',
                    'decision' => $decision,
                ]),
            ]);

            if ($internalId) {
                ApplicationTimeline::create([
                    'uuid'                 => Str::uuid(),
                    'application_id'       => $application->id,
                    'status'               => $status,
                    'assigned_to_id'       => $internalId,
                    'assigned_by'          => $currentAdmin->id,
                    'is_visible_to_author' => 0,
                    'remark'               => 'Assigned to Internal Reviewer',
                    'data'                 => [
                        'type'     => 'internal',
                        'comment'  => $remark ?? '',
                        'decision' => $decision,
                    ],
                ]);
            }

            if ($externalId) {
                ApplicationTimeline::create([
                    'uuid'                 => Str::uuid(),
                    'application_id'       => $application->id,
                    'status'               => $status,
                    'assigned_to_id'       => $externalId,
                    'assigned_by'          => $currentAdmin->id,
                    'is_visible_to_author' => 0,
                    'remark'               => 'Assigned to External Reviewer',
                    'data'                 => [
                        'type'     => 'external',
                        'comment'  => $remark ?? '',
                        'decision' => $decision,
                    ],
                ]);
            }

            $this->updateReviewerAssignmentTimelineComment($application->id, $internalId, $status, $remark, $decision);
            $this->updateReviewerAssignmentTimelineComment($application->id, $externalId, $status, $remark, $decision);

            $this->notifyReviewer($application, $internalId, 'internal', 15, $currentAdmin);
            $this->notifyReviewer($application, $externalId, 'external', 0, $currentAdmin);

            if ($updated) {
                return successResponse('Manuscript assigned to reviewers', route('admin.application.show', $application->uuid));
            }

            return errorResponse('Something went wrong!');
        }
    }


    public function editEditorDecision($request, $uuid)
    {
        if ($request->isMethod('GET')) {
            $application = Application::where('uuid', $uuid)->firstOrFail();
            $staffs = Admin::where('status', 1)->with('integrations')->whereHas('roles', function ($q) {
                $q->where('name', 'Reviewer');
                // ->orWhere('is_admin', 1);
            })->get();
            $integrations = Integration::orderBy('name')->get();

            return view('admin.application.edit_editor_decision', compact('application', 'staffs', 'integrations'));
        }

        if ($request->isMethod('PUT')) {
            $application  = Application::where('uuid', $uuid)->firstOrFail();
            $currentAdmin = authUser('admin');
            $decision     = $request->decision;
            $remark       = $request->comment;

            /* ----------------------------------
            | CASE 3 & 4: REVISE OR REJECT
            -----------------------------------*/
            if (in_array($decision, [3, 4])) {
                $status = ($decision == 3) ? 'editor-revise' : 'editor-rejects';

                $assistantEditor = $this->assistantEditor();
                $assignee        = $assistantEditor?->id ?? $application->assigned_to_id;

                $existingData = $application->data ?? [];
                $updated = $application->update([
                    'status'            => $status,
                    'assigned_to_id'    => $assignee,
                    'assigned_to_ex_id' => null,
                    'accept_ex'         => null,
                    'status_ex'         => null,
                    'data'              => array_merge($existingData, [
                        'comment'  => $remark ?? '',
                        'decision' => $decision,
                    ]),
                ]);

                ApplicationTimeline::create([
                    'uuid'                 => Str::uuid(),
                    'application_id'       => $application->id,
                    'status'               => $status,
                    'assigned_to_id'       => $assignee,
                    'assigned_by'          => $currentAdmin->id,
                    'is_visible_to_author' => 0,
                    'remark'               => 'Editor changed decision to: ' . ($decision == 3 ? 'Requested Revision' : 'Rejected'),
                    'data'                 => [
                        'comment'            => $remark ?? '',
                        'decision'           => $decision,
                        'assistant_editor_id' => $assistantEditor?->id,
                    ],
                ]);

                if ($updated) {
                    return successResponse('Decision updated successfully', route('admin.application.show', $application->uuid));
                }
                return errorResponse('Something went wrong!');
            }

            /* ----------------------------------
            | CASE 5: ACCEPT WITHOUT FURTHER REVISION
            -----------------------------------*/
            if ($decision == 5) {
                $status = 'editor-accepts';

                $assistantEditor = $this->assistantEditor();
                $assignee        = $assistantEditor?->id ?? $application->assigned_to_id;

                $existingData = $application->data ?? [];
                $updated = $application->update([
                    'status'            => $status,
                    'assigned_to_id'    => $assignee,
                    'assigned_to_ex_id' => null,
                    'accept_ex'         => null,
                    'status_ex'         => null,
                    'data'              => array_merge($existingData, [
                        'comment'  => $remark ?? '',
                        'decision' => $decision,
                    ]),
                ]);

                ApplicationTimeline::create([
                    'uuid'                 => Str::uuid(),
                    'application_id'       => $application->id,
                    'status'               => $status,
                    'assigned_to_id'       => $assignee,
                    'assigned_by'          => $currentAdmin->id,
                    'is_visible_to_author' => 0,
                    'remark'               => 'Editor changed decision to: Accepted without further revision',
                    'data'                 => [
                        'comment'            => $remark ?? '',
                        'decision'           => $decision,
                        'assistant_editor_id' => $assistantEditor?->id,
                    ],
                ]);

                if ($updated) {
                    $processingTimeline = ApplicationTimeline::where('application_id', $application->id)
                        ->where('status', 'processing')
                        ->orderBy('id', 'desc')
                        ->first();

                    $this->notifyEditorFinalDecision(
                        $application,
                        $processingTimeline,
                        'Accepted',
                        $remark
                    );

                    return successResponse('Decision updated successfully', route('admin.application.show', $application->uuid));
                }
                return errorResponse('Something went wrong!');
            }

            /* ----------------------------------
            | CASE 1 & 2: ASSIGNING REVIEWERS
            -----------------------------------*/
            $newInternal = $request->internal_reviewer_id ?? $currentAdmin->id;
            $newExternal = ($request->external_reviewer_id === 'not') ? null : $request->external_reviewer_id;

            $internalChanged = (int)$application->assigned_to_id !== (int)$newInternal;
            $externalChanged = (int)$application->assigned_to_ex_id !== (int)$newExternal;

            $isSelfAssign = (int)$newInternal === (int)$currentAdmin->id && ($newExternal === null || (int)$newExternal === (int)$currentAdmin->id);

            $status = 'assigning reviewers';

            if ($isSelfAssign) {
                $status    = 'under review';
                $accept_in = 1;
                $accept_ex = 1;
            } else {
                if ((int)$newInternal === (int)$currentAdmin->id) {
                    $accept_in = 1;
                } else {
                    $accept_in = $internalChanged ? null : $application->accept_in;
                }
                $accept_ex = $externalChanged ? null : $application->accept_ex;
            }

            /* ----------------------------------
            | STATUS_EX SUBMISSION TRACKING LOGIC
            -----------------------------------*/
            $status_ex = (int)$application->status_ex;

            // If internal changed, remove their submission score (1)
            if ($internalChanged) {
                if ($status_ex === 1 || $status_ex === 3) {
                    $status_ex -= 1;
                }
            }

            // If external changed, remove their submission score (2)
            if ($externalChanged) {
                if ($status_ex === 2 || $status_ex === 3) {
                    $status_ex -= 2;
                }
            }

            // Revert back to null if no one has a valid submission to keep database clean
            $status_ex = ($status_ex === 0) ? null : $status_ex;

            /* ----------------------------------
            | UPDATE APPLICATION
            -----------------------------------*/
            $existingData = $application->data ?? [];
            $updated = $application->update([
                'assigned_to_id'    => $newInternal,
                'assigned_to_ex_id' => $newExternal,
                'status'            => $status,
                'status_ex'         => $status_ex,
                'accept_in'         => $accept_in,
                'accept_ex'         => $accept_ex,
                'data'              => array_merge($existingData, [
                    'comment'  => $remark ?? '',
                    'decision' => $decision,
                ]),
            ]);

            /* ----------------------------------
            | TIMELINES & NOTIFICATIONS
            -----------------------------------*/
            if ($internalChanged) {
                ApplicationTimeline::create([
                    'uuid'                 => Str::uuid(),
                    'application_id'       => $application->id,
                    'status'               => $status,
                    'assigned_to_id'       => $newInternal,
                    'assigned_by'          => $currentAdmin->id,
                    'is_visible_to_author' => 0,
                    'remark'               => 'Internal Reviewer Reassigned',
                    'data'                 => [
                        'type'     => 'internal',
                        'comment'  => $remark ?? '',
                        'decision' => $decision,
                    ],
                ]);
                $this->notifyReviewer($application, $newInternal, 'internal', 15, $currentAdmin);
            }

            if ($externalChanged && $newExternal !== null) {
                ApplicationTimeline::create([
                    'uuid'                 => Str::uuid(),
                    'application_id'       => $application->id,
                    'status'               => $status,
                    'assigned_to_id'       => $newExternal,
                    'assigned_by'          => $currentAdmin->id,
                    'is_visible_to_author' => 0,
                    'remark'               => 'External Reviewer Reassigned',
                    'data'                 => [
                        'type'     => 'external',
                        'comment'  => $remark ?? '',
                        'decision' => $decision,
                    ],
                ]);
                $this->notifyReviewer($application, $newExternal, 'external', 0, $currentAdmin);
            }

            $this->updateReviewerAssignmentTimelineComment($application->id, $newInternal, $status, $remark, $decision);
            $this->updateReviewerAssignmentTimelineComment($application->id, $newExternal, $status, $remark, $decision);

            if ($updated) {
                return successResponse('Manuscript updated & reviewers notified', route('admin.application.show', $application->uuid));
            }

            return errorResponse('Something went wrong!');
        }
    }

    private function notifyReviewer($application, $reviewerId, $type, $delaySeconds, $currentAdmin)
    {
        if (empty($reviewerId)) {
            return;
        }

        $reviewer = Admin::find($reviewerId);
        if (!$reviewer || empty($reviewer->email)) {
            return;
        }

        $url = route('admin.application.show', $application->uuid);

        // Quote the same deadline the Review Deadlines tab will track: the
        // first assignment for a slot gets 14-7-3 days, every later assignment
        // on the same manuscript gets 7-3-2. The window starts at the
        // assignment (i.e. when this invitation is sent), not at acceptance.
        $remarks = $type === 'external'
            ? ReviewDeadlineRepository::EXTERNAL_REMARKS
            : ReviewDeadlineRepository::INTERNAL_REMARKS;

        $timeline = ApplicationTimeline::where('application_id', $application->id)
            ->whereIn('remark', $remarks)
            ->where('assigned_to_id', $reviewerId)
            ->orderByDesc('id')
            ->first();

        $cycle = $timeline
            ? ApplicationTimeline::where('application_id', $application->id)
                ->whereIn('remark', $remarks)
                ->where('id', '<=', $timeline->id)
                ->count()
            : 1;

        $profile = $cycle <= 1 ? ReviewAssignment::PROFILE_LONG : ReviewAssignment::PROFILE_SHORT;
        $dueAt   = ($timeline->created_at ?? now())->copy()
            ->addDays(array_sum(ReviewAssignment::PROFILE_DAYS[$profile]));

        $mailable = new ReviewerAssigned($application, $reviewer, $type, $currentAdmin, $url, $dueAt, $profile);

        $queueDriver = config('queue.default') ?? env('QUEUE_CONNECTION', 'sync');

        if ($queueDriver === 'sync') {
            Mail::to($reviewer->email)->send($mailable);
        } else {
            Mail::to($reviewer->email)
                ->queue($mailable->delay(now()->addSeconds($delaySeconds)));
        }

        $this->notifyInApp($reviewer, $application, [
            'category' => 'review',
            'type' => 'review.assigned',
            'title' => 'New review assignment',
            'message' => 'You have been assigned to review manuscript ' . $this->applicationReference($application) . '.',
            'icon' => 'ri-file-search-line',
            'severity' => 'info',
        ]);
    }

    private function updateReviewerAssignmentTimelineComment($applicationId, $assignedToId, $status, $remark, $decision)
    {
        if (empty($assignedToId)) {
            return;
        }

        $timeline = ApplicationTimeline::where('application_id', $applicationId)
            ->where('assigned_to_id', $assignedToId)
            ->where('status', $status)
            ->orderByDesc('id')
            ->first();

        if (!$timeline) {
            return;
        }

        $data = $timeline->data ?? [];
        $data['comment'] = $remark ?? '';
        $data['decision'] = $decision;
        $timeline->data = $data;
        $timeline->save();
    }

    public function rev_decision($request, $uuid)
    {
        $application = Application::where('uuid', $uuid)->first();

        if (!$application) {
            return errorResponse('Application not found');
        }

        if ($request->isMethod('GET')) {
            return view('admin.application.reviewerDecisionForm', compact('application'));
        }

        if ($request->isMethod('POST')) {
            $validator = Validator::make($request->all(), [
                'decision' => 'required|in:0,1',
                'reason'   => 'required_if:decision,0|nullable|string|max:1000',
            ], [
                'decision.required'  => 'Please select Accept or Deny.',
                'decision.in'        => 'Invalid decision value.',
                'reason.required_if' => 'Please provide a reason for denying the review assignment.',
            ]);

            if ($validator->fails()) {
                return failedValidation($validator);
            }

            $decision = (int) $request->decision;
            $reason   = $decision === 1 ? null : trim($request->reason ?? '');
            $rev_type = authUser('admin')->rev_type;

            $isInternal = ($rev_type === 'internal' || isSuperAdmin());

            /* ========== STORE DECISION & REASON (merge, never wipe) ========== */
            /* The internal and the external reviewer decline independently, so each
               one gets its own slot. `reviewer_*` is kept as the mirror of the most
               recent decision for legacy readers. */
            $storedData = $application->data ?? [];
            $storedData = array_merge($storedData, [
                'reviewer_decision' => $decision,
                'reviewer_reason'   => $reason,
            ]);

            if ($isInternal) {
                $storedData['internal_reviewer_decision'] = $decision;
                $storedData['internal_reviewer_reason']   = $reason;
            } else {
                $storedData['external_reviewer_decision'] = $decision;
                $storedData['external_reviewer_reason']   = $reason;
            }

            $updateData = ['data' => $storedData];

            if ($isInternal) {
                $updateData['accept_in'] = $decision;
            } else {
                $updateData['accept_ex'] = $decision;
            }

            $updated = $application->update($updateData);

            if ($decision === 1 && $application->accept_in == 1 && ($application->accept_ex == 1 || isSuperAdmin())) {
                $updated = $application->update([
                    'status' => 'under review'
                ]);
            }

            if ($updated) {
                if ($decision === 1) {
                    $this->notifyReviewerAccepted($application, $isInternal);
                    return successResponse("You accepted to review this application.", route('admin.application.show', $uuid));
                }

                $this->notifyReviewerDeclined($application, $reason);
                return successResponse("You denied to review this application.", route('admin.application.show', $uuid));
            }

            return errorResponse();
        }

        return errorResponse('Invalid request method');
    }

    public function reviewerRejection($uuid, $type = 'internal')
    {
        $type = $type === 'external' ? 'external' : 'internal';

        $application = Application::where('uuid', $uuid)
            ->with(['assignee', 'externalAssignee'])
            ->firstOrFail();

        return view('admin.application.reviewerRejection', compact('application', 'type'));
    }

    public function reviewer($request, $uuid)
    {
        // $author = authUser()->id;

        $application = Application::where('uuid', $uuid)
            ->first();

        if ($request->method() == 'GET') {

            return view('admin.application.reviewer_suggestion', compact('application'));
        } elseif ($request->method() == 'POST') {

            // New reviewer entry
            $newReviewer = [
                'reviewer_id'    => $request->reviewer_id ?? uniqid(),
                'title'          => $request->title,
                'first_name'     => $request->first_name,
                'last_name'      => $request->last_name,
                'email'          => $request->email,
                'institution'    => $request->institution ?? null,
                'specialization' => $request->specialization ?? null,
                'reason'         => $request->reason ?? null,
            ];

            $data = $application->data ?? [];

            // SAFETY: convert string JSON → array
            // if (is_string($data)) {
            //     $data = json_decode($data, true) ?? [];
            // }

            // Ensure reviewer_suggestions exists
            if (!isset($data['reviewer_suggestions']) || !is_array($data['reviewer_suggestions'])) {
                $data['reviewer_suggestions'] = [];
            }

            // Add new reviewer
            $data['reviewer_suggestions'][] = $newReviewer;

            // Save
            $application->data = $data;
            $application->save();

            return successResponse(
                'Reviewer suggestion added successfully',
                route('admin.application.show', $uuid)
            );
        }

        return errorResponse('Invalid request method');
    }

    public function reviewer_update($request, $uuid, $reviewer_id)
    {
        $application = Application::where('uuid', $uuid)->first();

        if (!$application) {
            return errorResponse("Application not found");
        }

        // Get data safely
        $data = $application->data ?? [];

        // SAFETY: convert JSON string → array
        if (is_string($data)) {
            $data = json_decode($data, true) ?? [];
        }

        // Ensure reviewer_suggestions exists
        if (!isset($data['reviewer_suggestions']) || !is_array($data['reviewer_suggestions'])) {
            $data['reviewer_suggestions'] = [];
        }

        // =========================
        // ⭐ GET METHOD
        // =========================
        if ($request->method() === 'GET') {

            $reviewerData = collect($data['reviewer_suggestions'])
                ->firstWhere('reviewer_id', $reviewer_id);

            if (!$reviewerData) {
                return errorResponse("Reviewer not found");
            }

            return view('admin.application.reviewer_suggestion_update', [
                'application'   => $application,
                'uuid'          => $uuid,
                'reviewer_data' => $reviewerData
            ]);
        }

        // =========================
        // ⭐ POST METHOD (UPDATE)
        // =========================
        if ($request->method() === 'POST') {

            $updatedReviewer = [
                'reviewer_id'    => $reviewer_id,
                'title'          => $request->title,
                'first_name'     => $request->first_name,
                'last_name'      => $request->last_name,
                'email'          => $request->email,
                'institution'    => $request->institution,
                'specialization' => $request->specialization,
                'reason'         => $request->reason,
            ];

            $data['reviewer_suggestions'] = collect($data['reviewer_suggestions'])
                ->map(function ($reviewer) use ($reviewer_id, $updatedReviewer) {

                    // SAFETY for string reviewer (rare but possible)
                    if (is_string($reviewer)) {
                        $reviewer = json_decode($reviewer, true) ?? [];
                    }

                    return ($reviewer['reviewer_id'] ?? null) == $reviewer_id
                        ? $updatedReviewer
                        : $reviewer;
                })
                ->toArray();

            $application->data = $data;
            $application->save();

            return successResponse(
                "Reviewer suggestion updated successfully",
                route('admin.application.show', $uuid)
            );
        }

        return errorResponse("Invalid request method");
    }


    public function reviewer_delete($request, $uuid, $reviewer_id)
    {
        $app = Application::where('uuid', $uuid)->firstOrFail();

        // Get data safely
        $data = $app->data ?? [];

        // SAFETY: convert JSON string → array
        if (is_string($data)) {
            $data = json_decode($data, true) ?? [];
        }

        if (!isset($data['reviewer_suggestions']) || !is_array($data['reviewer_suggestions'])) {
            return errorResponse("No reviewer suggestions found.");
        }

        $updated = array_filter($data['reviewer_suggestions'], function ($reviewer) use ($reviewer_id) {

            // SAFETY for string reviewer
            if (is_string($reviewer)) {
                $reviewer = json_decode($reviewer, true) ?? [];
            }

            return ($reviewer['reviewer_id'] ?? null) !== $reviewer_id;
        });

        $data['reviewer_suggestions'] = array_values($updated);

        $app->data = $data;
        $app->save();

        return successResponse(
            'Reviewer suggestion deleted successfully',
            route('admin.application.show', $uuid)
        );
    }


    public function documents($request)
    {
        return successResponse('Progress Saved Successfully', route('admin.application.create', [
            'applicationId' => $request->uuid,
            'step' => "articleInformation"
        ]));
    }

    public function articleInformation($request)
    {
        $application = Application::where('uuid', $request->uuid)->first();

        // $artical_type = $request->article_type ?? [];
        $artical_type = [];
        if (!empty($request->article_type)) {
            array_push($artical_type, $request->article_type);
        }

        if (!empty($request->article_type_other))
            array_push($artical_type, $request->article_type_other);

        $data = [
            'article_type' => $artical_type,
            'article_check' =>  $request->article_check ?? [],
            'description' => $request->description,
        ];

        if ($application->update($data)) {
            return successResponse('Progress Saved Successfully', route('admin.application.create', [
                'applicationId' => $request->uuid,
                'step' => "coAuthors"
            ]));
        }
    }

    public function coAuthors($request)
    {
        // return successResponse('Progress Saved Successfully', route('admin.application.create', [
        //     'applicationId' => $request->uuid,
        //     'step' => "preview"
        // ]));

        $application = Application::uuid($request->uuid)->first();

        // $data = [
        //     'application_id' => $application->id,
        //     'status' => 'submitted',
        //     'remark' => $remark,
        // ];

        try {
            $updated = $application->update([
                'status' => "published",
            ]);
            // $timeline = ApplicationTimeline::create($data);
            if ($updated) {
                return  successResponse('Submitted SuccessFully', route('admin.application.create'));
            }
            return errorResponse();
        } catch (Throwable $th) {
            return throwException($th);
        }
    }



    public function published_index($request)
    {

        $perPage = $request->perPage ?? 50;

        // $staffs = Admin::where('is_admin', false)->whereHas('roles', function ($q) {
        //     $q->where('name', 'Editor');
        // })->get();
        $type = $request->type ?? '';

        $applications = Application::query()->with('attachments')->where('status', 'published');

        // Editors and reviewers see only the manuscripts they handled or
        // reviewed. The sidebar badge is scoped the same way, so the
        // "Completed" tab always matches its count and never leaks someone
        // else's work (double-blind).
        if (authUser('admin')->is_admin == 0) {
            $userId = authUser('admin')->id;

            $applications->where(function ($query) use ($userId) {
                $query->where('assigned_to_id', $userId)
                    ->orWhere('assigned_to_ex_id', $userId)
                    ->orWhereHas('timelines', function ($timeline) use ($userId) {
                        $timeline->where('assigned_to_id', $userId);
                    });
            });
        }

        $this->search($request, $applications);
        $this->sort($request, $applications);

        $applications = $applications->paginate($perPage);
        // return $applications;

        if ($request->ajax()) {

            try {
                $datatable = view('admin.application.published.datatable', compact('applications'))->render();
                return response()->json([
                    'status' => '200',
                    'msg' => 'Data loaded',
                    'data' => $datatable,
                    'paginationInfo' => getPaginationInfo($applications)
                ], 200);
            } catch (Throwable $th) {
                return throwException($th);
            }
        }
        return view('admin.application.published.index', compact('type'));
    }
    public function rejected_index($request)
    {

        $perPage = $request->perPage ?? 50;

        // $staffs = Admin::where('is_admin', false)->whereHas('roles', function ($q) {
        //     $q->where('name', 'Editor');
        // })->get();
        $type = $request->type ?? '';

        $applications = Application::query()->with('attachments')->where('status', 'rejected');


        // if (authUser('admin')->is_admin == 0) {
        //     $applications = $applications->where('assigned_to_id', authUser('admin')->id)
        //         // ->where('status','!=','incomplete')
        //         ->orWhereHas('timelines', function ($query) {
        //             $query->where('assigned_to_id', authUser('admin')->id);
        //         });
        // }

        // if (authUser('admin')->rev_type == 'external') {
        //     $applications = Application::query()->where('assigned_to_ex_id', authUser('admin')->id);
        // }


        // return $applications->get();

        $this->search($request, $applications);
        $this->sort($request, $applications);

        $applications = $applications->paginate($perPage);
        // return $applications;

        if ($request->ajax()) {

            try {
                $datatable = view('admin.application.rejected.datatable', compact('applications'))->render();
                return response()->json([
                    'status' => '200',
                    'msg' => 'Data loaded',
                    'data' => $datatable,
                    'paginationInfo' => getPaginationInfo($applications)
                ], 200);
            } catch (Throwable $th) {
                return throwException($th);
            }
        }
        return view('admin.application.rejected.index', compact('type'));
    }
    public function withdrawn_index($request)
    {

        $perPage = $request->perPage ?? 50;

        // $staffs = Admin::where('is_admin', false)->whereHas('roles', function ($q) {
        //     $q->where('name', 'Editor');
        // })->get();
        $type = $request->type ?? '';

        $applications = Application::query()->with('attachments')->where('status', 'withdrawn');


        // if (authUser('admin')->is_admin == 0) {
        //     $applications = $applications->where('assigned_to_id', authUser('admin')->id)
        //         // ->where('status','!=','incomplete')
        //         ->orWhereHas('timelines', function ($query) {
        //             $query->where('assigned_to_id', authUser('admin')->id);
        //         });
        // }

        // if (authUser('admin')->rev_type == 'external') {
        //     $applications = Application::query()->where('assigned_to_ex_id', authUser('admin')->id);
        // }


        // return $applications->get();

        $this->search($request, $applications);
        $this->sort($request, $applications);

        $applications = $applications->paginate($perPage);
        // return $applications;

        if ($request->ajax()) {

            try {
                $datatable = view('admin.application.rejected.datatable', compact('applications'))->render();
                return response()->json([
                    'status' => '200',
                    'msg' => 'Data loaded',
                    'data' => $datatable,
                    'paginationInfo' => getPaginationInfo($applications)
                ], 200);
            } catch (Throwable $th) {
                return throwException($th);
            }
        }
        return view('admin.application.rejected.index', compact('type'));
    }

    public function duplicate_index($request)
    {

        $perPage = $request->perPage ?? 50;

        $type = $request->type ?? '';

        $applications = Application::query()->with('attachments')->where('status', 'duplicate');

        $this->search($request, $applications);
        $this->sort($request, $applications);

        $applications = $applications->paginate($perPage);

        if ($request->ajax()) {

            try {
                $datatable = view('admin.application.duplicate.datatable', compact('applications'))->render();
                return response()->json([
                    'status' => '200',
                    'msg' => 'Data loaded',
                    'data' => $datatable,
                    'paginationInfo' => getPaginationInfo($applications)
                ], 200);
            } catch (Throwable $th) {
                return throwException($th);
            }
        }
        return view('admin.application.duplicate.index', compact('type'));
    }

    public function showCheckList($request, $uuid)
    {
        $application = Application::where('uuid', $uuid)->first();
        return view('admin.application.checkListModal', compact('application'));
    }


    public function publish($request, $uuid)
    {
        $application = Application::where('uuid', $uuid)->first();

        if (!$application) {
            return errorResponse('Application not found');
        }

        $user = authUser('admin');
        $isAssignedPublisher = isPublisher()
            && (int) $application->assigned_to_id === (int) $user->id;

        if (!$isAssignedPublisher) {
            return errorResponse('Only the assigned publisher can publish this manuscript.', 403);
        }

        if ($application->status !== Application::ACCEPTED_FOR_PUBLICATION_STATUS) {
            return errorResponse('This manuscript is not ready for publication.', 400);
        }

        // Handle GET Request: Show the modal/form
        if ($request->isMethod('GET')) {
            return view('admin.application.publish', compact('uuid'));
        }

        // Handle POST Request: Process the form submission
        if ($request->isMethod('POST')) {

            // 1. Validate the input
            $validator = Validator::make($request->all(), [
                'doi' => 'required|string|max:255',
            ], [
                'doi.required' => 'Please provide a DOI number to publish the manuscript.',
            ]);

            if ($validator->fails()) {
                return errorResponse($validator->errors()->first());
            }

            // 2. Update the Application
            $wasPublished = $application->status === 'published';
            $application->published_doi = $request->doi;
            $application->status = 'published'; // Adjust capitalization if your system uses 'Published'
            $application->save();

            // 3. Create a Timeline Record
            ApplicationTimeline::create([
                'application_id' => $application->id,
                'is_visible_to_author' => 1,
                'assigned_to_id'        => $user->id, // Records which publisher published it
                'status'         => 'published',
                'remark'         => 'Manuscript has been accepted and published. DOI: ' . $request->doi,
            ]);

            // 4. Notify the author, and the reviewers the first time it goes live.
            $this->notifyAuthorPublished($application, $request->doi);
            if (!$wasPublished) {
                $this->notifyReviewersPublished($application, $request->doi);
            }

            return successResponse(
                'Manuscript published successfully!',
                url()->previous() // Or you can redirect to route('admin.application.index')
            );
        }

        return errorResponse('Invalid Request');
    }

    /* write a direct publish method that skips the review process and directly publishes the manuscript with a given DOI. This should be used in exceptional cases where the manuscript needs to be published immediately without going through the standard review workflow.
    this method should check route method if GET then show a confirmation modal to enter DOI and take file input which stores in attachments table with the attachment_type 'Published With DOI' confirm direct publish, if POST then directly publish the manuscript with the provided DOI and file input, update the application status to 'published', save the DOI in the application record, create a timeline entry for this action, and return a success response.
    */
    public function direct_publish(Request $request, $uuid)
    {
        $application = Application::where('uuid', $uuid)->first();

        if (!$application) {
            return errorResponse('Application not found');
        }

        // Handle GET Request: Show the modal/form
        if ($request->isMethod('GET')) {
            return view('admin.application.direct_publish', compact('uuid'));
        }

        // Handle POST Request: Process the form submission
        if ($request->isMethod('POST')) {
            // return $request->file_input->getClientOriginalExtension();

            // 1. Validate the input
            $validator = Validator::make($request->all(), [
                'doi' => 'required|string|max:255',
                'file_input' => 'nullable|file|mimes:pdf,doc,docx', // Adjust file types as needed
            ], [
                'doi.required' => 'Please provide a DOI number to publish the manuscript.',
                'file_input.file' => 'The uploaded file must be a valid file.',
                'file_input.mimes' => 'The uploaded file must be a PDF or Word document.',
            ]);

            if ($validator->fails()) {
                return errorResponse($validator->errors()->first());
            }

            // 2. Update the Application
            $wasPublished = $application->status === 'published';
            $application->published_doi = $request->doi;
            $application->status = 'published'; // Adjust capitalization if your system uses 'Published'
            $application->save();

            // 3. Handle File Upload if provided
            if ($request->hasFile('file_input')) {

                $file = $request->file('file_input');

                // Extract metadata
                $originalName = $file->getClientOriginalName();
                $extension = $file->getClientOriginalExtension();
                $mimeType = $file->getMimeType();
                $size = $file->getSize();

                $fileName = time() . '_' . Str::slug(pathinfo($originalName, PATHINFO_FILENAME)) . '.' . $extension;

                $relativePath = 'uploads/applications/';
                $fullPath = public_path($relativePath);

                // Create directory if not exists
                if (!File::exists($fullPath)) {
                    File::makeDirectory($fullPath, 0775, true);
                }

                // Move file
                $file->move($fullPath, $fileName);

                Attachment::create([
                    'uuid'             => (string) Str::uuid(),
                    'application_id'   => $application->id,
                    'file_name'        => $fileName,
                    'file_type'        => $extension,
                    'file_size'        => $size,
                    'file_url'         => $relativePath . $fileName,
                    'attachment_type'  => 'Published with DOI',
                    'legend'           => 'Published with DOI',
                ]);
            }

            // 4. Create a Timeline Record
            ApplicationTimeline::create([
                'application_id' => $application->id,
                'is_visible_to_author' => 1,
                'assigned_to_id'        => auth()->id(), // Records which admin/editor published it
                'status'         => 'published',
                'remark'         => 'Manuscript has been directly published with DOI: ' . $request->doi,
            ]);

            // 5. Notify the author, and the reviewers the first time it goes live.
            $this->notifyAuthorPublished($application, $request->doi);
            if (!$wasPublished) {
                $this->notifyReviewersPublished($application, $request->doi);
            }

            return successResponse(
                'Manuscript directly published successfully!',
                url()->previous()
            ); // Or you can redirect to route('admin.application.index')
        }

        return errorResponse('Invalid Request');
    }


    /*
    Write a public fucnction name that if the request method is GET then provide all the manuscripts with their application_id, id and status and return to a view with a table of all the manuscripts and their status, if the request method is POST then accept a manuscript id and change its status to anything provided in the request and return a success response. and it should be bulk upload manuscripts status change by superadmin
    */
    public function change_status(Request $request)
    {
        if (!isSuperAdmin()) {
            abort(403, 'Unauthorized action.');
        }
        // return 1;
        if ($request->isMethod('GET')) {
            // Included 'application_id' as requested in your comment
            $manuscripts = Application::select('id', 'application_id', 'title', 'status')->get();
            return view('admin.application.change_status', compact('manuscripts'));
        }

        if ($request->isMethod('POST')) {
            // 1. Validate the incoming request data
            $validated = $request->validate([
                'manuscript_ids'   => 'required|array|min:1',
                'manuscript_ids.*' => 'required|integer|exists:applications,id',
                'status'           => 'required|string|max:255',
            ]);

            try {
                // 2. Perform the bulk update
                Application::whereIn('id', $validated['manuscript_ids'])
                    ->update(['status' => $validated['status']]);

                // 3. Return a success response (Ideal for AJAX/Fetch API)
                return response()->json([
                    'success' => true,
                    'message' => 'Manuscript statuses updated successfully.'
                ], 200);
            } catch (\Exception $e) {
                // Log the error for debugging
                Log::error('Bulk status update failed: ' . $e->getMessage());

                return response()->json([
                    'success' => false,
                    'message' => 'An error occurred while updating statuses.'
                ], 500);
            }
        }

        // Fallback for any other HTTP verbs (PUT, DELETE, etc.)
        return errorResponse('Invalid Request');
    }

    public function add_doi(Request $request, $uuid)
    {

        if($request->isMethod('GET')) {
            $application = Application::where('uuid', $uuid)->first();
            return view('admin.application.doi', compact('application'));
        }

        if($request->isMethod('PUT')) {
            // $request->validate([
            //     'doi' => 'required|string|max:255',
            // ], [
            //     'doi.required' => 'Please provide a DOI number.',
            // ]);
            $application = Application::where('uuid', $uuid)->first();

            if (!$application) {
                return errorResponse('Application not found');
            }

            $application->published_doi = $request->doi;
            $application->save();

            return successResponse(
                'DOI added successfully!',
                route('admin.application.show', $uuid)
            );
        }

    }
}
