<?php

namespace App\Http\Controllers\Author\Application;



use Throwable;
use App\Models\Admin;
use App\Models\Service;
use App\Models\Attachment;
use App\Models\Application;
use App\Rules\FileTypeRule;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;
use App\Models\ApplicationTimeline;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use App\Mail\AuthorSubmissionMail;
use App\Mail\AuthorResubmittedMail;
use App\Mail\AuthorResubmissionConfirmationMail;
use App\Mail\CorrectedProofSubmittedMail;
use App\Services\NotificationService;
use Illuminate\Support\Facades\Validator;
use SebastianBergmann\CodeUnit\FunctionUnit;
use Symfony\Component\ErrorHandler\Error\UndefinedFunctionError;

class ApplicationRepository
{


    protected $basePath = 'uploads/author/attachments/';

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

    private function missingRevisionDocuments(Application $application)
    {
        $uploadedTypes = $application->attachments
            ->pluck('attachment_type')
            ->map(fn ($type) => trim((string) $type));

        $missing = [];
        foreach (['Rebuttal Letter', 'Highlighted Manuscript'] as $requiredType) {
            if (!$uploadedTypes->contains($requiredType)) {
                $missing[] = $requiredType;
            }
        }

        return $missing;
    }



    public function index($request)
    {

        $perPage = $request->perPage ?? 50;
        // $applications = Application::query();

        // Get the current logged-in author
        $authorId = auth()->id();

        // Filter applications by the current author
        // $applications = Application::where('author_id', $authorId)->with('attachments', 'assignee');
        if ($request->has('type')) {
            $applications = Application::where('author_id', $authorId)->where('status', $request->type)
                ->with('attachments', 'assignee');
        } else {
            $applications = Application::where('author_id', $authorId)->with('attachments', 'assignee');
        }



        $this->search($request, $applications);
        $this->sort($request, $applications);
        $applications = $applications->paginate($perPage);
        // return $applications;
        if ($request->ajax()) {

            try {
                $datatable = view('author.cms.datatable', compact('applications'))->render();
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
        return view('author.cms.index');
    }



    public function create($request)
    {
        $author = authUser()->id;

        $application = Application::where('author_id', $author)->get();


        $applicationId = $request->applicationId ?? null;

        $app_info = Application::with(['attachments', 'timelines'])->where('uuid', $applicationId)->first();
        // return $app_info;
        // return view('author.cms.create', compact('applications'));

        return view('author.cms.create_step_by_step', compact('app_info'));
    }


    public function store($request)
    {
        try {

            // return $request->all();

            // -------- ARTICLE TYPE ARRAY --------
            $article_type = [];

            if (!empty($request->article_type)) {
                $article_type[] = $request->article_type;
            }

            if (!empty($request->article_type_other)) {
                $article_type[] = $request->article_type_other;
            }

            // -------- MAIN DATA --------
            $data = [
                'submission_type'      => 'new_submission',
                'title'                => $request->title,
                'short_title'          => $request->short_title,
                'keywords'             => $request->keywords,
                'author_id'            => auth()->id(),
                'author_affiliation'   => $request->author_affiliation,
                'author_orcid_id'      => $request->author_orcid_id,
                'author_saspi_id'      => $request->author_saspi_id ?? null,
                'author_country'       => $request->author_country,
                'author_state'         => $request->author_state,
                'author_city'          => $request->author_city,
                'author_organization'  => $request->author_organization,
                'author_department'    => $request->author_department,
                'article_type'         => $article_type,
                'article_check'        => $request->article_check ?? [],
                'description'          => $request->description,
                'status'               => 'incomplete',
                'data'                 => [
                    'name' => $request->name,
                    'lname' => $request->lname
                ],
            ];
            // return [$request, $data];
            // -------- CREATE / UPDATE --------
            if (empty($request->uuid)) {

                $application = Application::create($data);
            } else {

                $application = Application::where('uuid', $request->uuid)->firstOrFail();

                // preserve revise status
                if ($application->status === 'revise') {
                    $data['status'] = 'revise';
                }

                $application->update($data);
            }

            // ===============================
            // ✅ STORE CHECKLIST IN DATA COLUMN
            // ===============================

            if (session()->has('check_list')) {

                // Decode existing data JSON safely
                $existingData = [];

                if (!empty($application->data)) {
                    $existingData =  $application->data;
                } else {
                    $existingData = [];
                }


                // Merge checklist
                $existingData['check_list'] = session('check_list');

                // Save back to DB
                $application->update([
                    'data' => $existingData
                ]);
            }

            // -------- REDIRECT --------
            return successResponse(
                'Progress Saved Successfully',
                route('author.application.create', [
                    'applicationId' => $application->uuid,
                    'step' => 'documents'
                ])
            );
        } catch (Throwable $th) {
            return throwException($th);
        }
    }

    // public function store($request)
    // {
    //     try {

    //         // -------- ARTICLE TYPE ARRAY --------
    //         $article_type = [];

    //         if (!empty($request->article_type)) {
    //             $article_type[] = $request->article_type;
    //         }

    //         if (!empty($request->article_type_other)) {
    //             $article_type[] = $request->article_type_other;
    //         }

    //         // -------- MAIN DATA --------
    //         $data = [
    //             'submission_type'      => 'new_submission',
    //             'title'                => $request->title,
    //             'author_id'            => auth()->id(),
    //             'author_affiliation'   => $request->author_affiliation,
    //             'author_orcid_id'      => $request->author_orcid_id,
    //             'author_saspi_id'      => $request->author_saspi_id ?? null,
    //             'article_type'         => $article_type,
    //             'article_check'        => $request->article_check ?? [],
    //             'description'          => $request->description,
    //             'status'               => 'incomplete',
    //         ];

    //         // -------- CREATE / UPDATE --------
    //         if (empty($request->uuid)) {

    //             $application = Application::create($data);
    //         } else {

    //             $application = Application::where('uuid', $request->uuid)->firstOrFail();

    //             // preserve revise status
    //             if ($application->status === 'revise') {
    //                 $data['status'] = 'revise';
    //             }

    //             $application->update($data);
    //         }

    //         // -------- REDIRECT --------
    //         return successResponse(
    //             'Progress Saved Successfully',
    //             route('author.application.create', [
    //                 'applicationId' => $application->uuid,
    //                 'step' => 'documents'
    //             ])
    //         );
    //     } catch (Throwable $th) {
    //         return throwException($th);
    //     }
    // }


    public function edit($uuid)
    {
        $author = authUser()->id;
        $applications = Application::where('author_id', $author)->get();
        $app_info = $this->getApplication($uuid);
        return view('author.cms.edit', compact('applications', 'app_info'));
    }

    public function update($request, $uuid)
    {
        try {
            // Find the existing application
            $application = $this->getApplication($uuid);
            $artical_type = $request->article_type ?? [];
            if (($request->article_type_other ?? '') != null)
                $artical_type['other_data'] = $request->article_type_other;
            // Prepare the updated application data
            $data = [
                'submission_type' => $request->submission_type,
                'article_type' => $artical_type,
                'title' => $request->title,
                'author_affiliation' => $request->author_affiliation,
                'author_orcid_id' => $request->author_orcid_id,
                'author_saspi_id' => $request->author_saspi_id ?? null,
                'jaspi_id' => $request->submission_type === 'revised_submission' ? $request->jaspi_id : $application->jaspi_id,
                'description' => $request->description,
                'status' => $request->status ?? $application->status,
                'article_check' =>  $request->article_check ?? [],
            ];

            // Update the application data
            $application->update($data);
            return successResponse('Manuscript Updated Successfully', route('author.application.show', $uuid));
        } catch (Throwable $th) {
            return throwException($th);
        }
    }

    public function destroy($uuid)
    {
        try {
            // Find the application by ID
            $application = $this->getApplication($uuid);

            // Delete associated attachments
            $attachments = Attachment::where('application_id', $application->id)->get();
            foreach ($attachments as $attachment) {
                // Delete the actual file from the storage
                deleteFile($attachment->file_url);
                // Remove the attachment record from the database
                $attachment->delete();
            }

            // Delete the application
            $application->delete();

            return successResponse('Manuscript deleted successfully');
        } catch (Throwable $th) {
            return throwException($th);
        }
    }


    public function show($uuid)
    {
        try {
            $application = Application::where('uuid', $uuid)
                ->with(['attachments', 'timelines'])
                ->firstOrFail();

            $attachments = $application->attachments ?? collect();

            // 1️⃣ Manuscript files (history allowed)
            $manuScriptFiles = $attachments
                ->where('attachment_type', 'Upload Manuscript')
                ->sortByDesc('created_at');

            $correctedFiles = $attachments
                ->where('attachment_type', 'Corrected Proof')
                ->sortByDesc('created_at');


            $otherFiles = $attachments
                ->where('attachment_type', '!=', 'Upload Manuscript')
                ->where('attachment_type', '!=', 'Corrected Proof');

            // return $otherFiles;
            // 3️⃣ Categories for OTHER files only
            $other_attachment_categories = $otherFiles
                ->pluck('attachment_type')
                ->unique()
                ->values();

            $authorTimelines = $application->timelines
                ->where('is_visible_to_author', 1);

            $status = $application->status;

            return view(
                'author.cms.show',
                compact(
                    'application',
                    'manuScriptFiles',
                    'otherFiles',
                    'correctedFiles',
                    'other_attachment_categories',
                    'authorTimelines',
                    'status'
                )
            );
        } catch (Throwable $th) {
            return throwException($th);
        }
    }


    private function search($request, $query)
    {
        if ($request->filled('search')) {
            $searchTerm = $request->search;

            $query->where(function ($q) use ($searchTerm) {
                $q->where('title', 'like', "%{$searchTerm}%")
                    ->orWhere('application_id', 'like', "%{$searchTerm}%")
                    ->orWhere('article_type', 'like', "%{$searchTerm}%")
                    ->orWhere('author_affiliation', 'like', "%{$searchTerm}%")
                    ->orWhere('author_orcid_id', 'like', "%{$searchTerm}%")
                    ->orWhere('author_saspi_id', 'like', "%{$searchTerm}%");
            });
        }
    }

    private function sort($request, $query, $validColumns = [])
    {

        // if (!empty($request->sortColumn) && in_array($request->sortColumn, $validColumns)) {
        if (!empty($request->sortColumn)) {
            $query->orderBy($request->sortColumn, "{$request->sortDirection}");
        } else {
            $query->latest('id');
        }
    }


    public function coAuthor($request, $uuid)
    {
        $author = authUser()->id;
        $application = Application::where('author_id', $author)
            ->where('uuid', $uuid)->first();

        if ($request->method() == 'GET') {
            return view('author.cms.co_author_form', compact('application'));
        } elseif ($request->method() == 'POST') {

            // Create the data array based on the new form fields
            $newCoauthor = [
                'coauthor_id' =>  $request->coauthor_id ?? uniqid(),

                // Name Fields
                // 'coauthor_fname' => $request->coauthor_fname,
                'coauthor_name'  => $request->coauthor_name,
                'coauthor_lname' => $request->coauthor_lname,
                // Combine them so your existing table view (which likely uses 'coauthor_name') still works

                // Contact
                'coauthor_email' => $request->coauthor_email,
                'coauthor_phone' => $request->coauthor_phone,

                // New Professional Info
                'coauthor_department'   => $request->coauthor_department,
                'coauthor_organization' => $request->coauthor_organization,
                'coauthor_affiliation'  => $request->author_affiliation, // Input name is author_affiliation

                // IDs
                'coauthor_orcid_id' => $request->author_orcid_id,
                'coauthor_saspi_id' => $request->author_saspi_id ?? null,

                // Location Data (Storing IDs)
                'coauthor_country' => $request->coauthor_country,
                'coauthor_state'   => $request->coauthor_state,
                'coauthor_city'    => $request->coauthor_city,
            ];

            // Decode current JSON to array or create a new array if empty
            $existingData = $application->co_author_data ?? [];

            // Append new co-author
            $existingData[] = $newCoauthor;

            // Save updated JSON
            $application->co_author_data = $existingData;
            $update = $application->save();
            if ($update) {
                return successResponse('Co-author added successfully', route('author.application.create', ['applicationId' => $uuid, 'app_info' => $application, 'step' => "coAuthors"]));
            } else {
                return errorResponse('Something went wrong');
            }
        } else {
            return errorResponse('Invalid request method');
        }
    }


    public function coAuthorUpdate($request, $uuid, $author_id)
    {
        $author = authUser()->id;
        $application = Application::where('author_id', $author)
            ->where('uuid', $uuid)->first();

        if (!$application) {
            return errorResponse('Application not found');
        }

        if ($request->method() == 'GET') {
            // Find the specific co-author data to pass to the Edit Modal
            $existingData = $application->co_author_data ?? [];
            $author_data = null;

            foreach ($existingData as $data) {
                if (isset($data['coauthor_id']) && $data['coauthor_id'] == $author_id) {
                    $author_data = $data;
                    break;
                }
            }

            if (!$author_data) {
                return errorResponse('Co-author not found');
            }

            return view('author.cms.co_author_edit_form', compact('application', 'author_data', 'uuid'));
        } elseif ($request->method() == 'POST') {

            $existingData = $application->co_author_data ?? [];
            $found = false;

            // Loop through and update the specific co-author
            foreach ($existingData as $key => $data) {
                if (isset($data['coauthor_id']) && $data['coauthor_id'] == $author_id) {

                    $existingData[$key] = [
                        'coauthor_id' => $author_id, // Keep original ID

                        // Update Fields
                        // 'coauthor_fname' => $request->coauthor_fname,
                        'coauthor_lname' => $request->coauthor_lname,
                        'coauthor_name'  => $request->coauthor_name,

                        'coauthor_email' => $request->coauthor_email,
                        'coauthor_phone' => $request->coauthor_phone,

                        'coauthor_department'   => $request->coauthor_department,
                        'coauthor_organization' => $request->coauthor_organization,
                        'coauthor_affiliation'  => $request->author_affiliation,

                        'coauthor_orcid_id' => $request->author_orcid_id,
                        'coauthor_saspi_id' => $request->author_saspi_id ?? null,

                        // Update Location
                        'coauthor_country' => $request->coauthor_country,
                        'coauthor_state'   => $request->coauthor_state,
                        'coauthor_city'    => $request->coauthor_city,
                    ];

                    $found = true;
                    break;
                }
            }

            if ($found) {
                $application->co_author_data = $existingData;

                $application->save();
                return successResponse('Co-author updated successfully', route('author.application.create', ['applicationId' => $uuid, 'app_info' => $application, 'step' => "coAuthors"]));
            } else {
                return errorResponse('Co-author not found for update');
            }
        }
    }

    public function coAuthorDelete($request, $uuid, $author_id)
    {
        try {
            $author = authUser()->id;

            $application = Application::where('author_id', $author)
                ->where('uuid', $uuid)
                ->first();

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

            return successResponse('Co-author deleted successfully', route('author.application.create', ['applicationId' => $uuid, 'app_info' => $application, 'step' => "coAuthors"]));
        } catch (\Exception $e) {
            return errorResponse('Error deleting co-author: ' . $e->getMessage());
        }
    }

    public function fileUpload($request, $id, $type, $app_id, $legend)
    {
        // return $request;
        $author = authUser()->id;

        // Check if the application exists and belongs to the author
        $application = Application::where('author_id', $author)
            ->where('id', $app_id)
            ->first();

        if (!$application) {
            return errorResponse('Manuscript not found');
        }

        $attachment_id = Attachment::where('application_id', $id)->first()->id ?? null;

        if ($request->isMethod('GET')) {

            return view('author.cms.file_upload', compact('id', 'type', 'app_id', 'legend'));
        }

        if ($request->isMethod('POST')) {

            // return $request;
            if (!$request->hasFile('file_upload')) {
                return errorResponse('File not found');
            }

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
                'legend'           => null,
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
                    'author.application.create',
                    ['applicationId' => $application->uuid, 'step' => "documents"]
                )
                // url()->previous()
            );
        }

        return errorResponse('Invalid request method');
    }


    public function fileDestroy($app_id, $id)
    {
        $author = authUser()->id;

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
                'author.application.create',
                ['applicationId' => $app_id, 'step' => "documents"]
            ));
        }

        return errorResponse('Error deleting file');
    }

    public function readMistake($uuid, $timeline_id = null)
    {
        $application = Application::uuid($uuid)->firstOrFail();

        if (!empty($timeline_id)) {
            $timeline = $application->timelines()
                ->where('id', $timeline_id)
                ->whereIn('status', ['editor-accepts', 'editor-revise', 'editor-rejects'])
                ->firstOrFail();

            return view('admin.application.readMistake', compact('timeline'));
        }

        $timeline = $application->timelines()
            ->whereIn('status', ['editor-accepts', 'editor-revise', 'editor-rejects'])
            ->orderByDesc('created_at')
            ->first();

        return view('admin.application.readMistake', compact('timeline'));
    }

    // public function reviewerReadMore($uuid)
    // {
    //     $application = Application::with('timelines')->where('uuid', $uuid)->first();

    //     $timeline = $application->timelines()
    //         ->whereIn('status', ['reviewer-accepts', 'reviewer-revise', 'reviewer-rejects'])
    //         ->latest()
    //         ->first();


    //     return view('author.cms.reviewer_readmore', compact('timeline'));
    // }
    public function reviewerReadMore($uuid, $timeline_id = null)
    {
        $application = Application::where('uuid', $uuid)->firstOrFail();
        $internalStatuses = [
            'internal-reviewer-accepts',
            'internal-reviewer-revise',
            'internal-reviewer-rejects',
        ];
        $externalStatuses = [
            'external-reviewer-accepts',
            'external-reviewer-revise',
            'external-reviewer-rejects',
        ];

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

            return view('author.cms.reviewer_readmore', compact('timeline1', 'timeline2'));
        }

        $timeline1 = $application->timelines()
            ->whereIn('status', $internalStatuses)
            ->orderByDesc('id')
            ->first();
        $timeline2 = $application->timelines()
            ->whereIn('status', $externalStatuses)
            ->orderByDesc('id')
            ->first();

        return view('author.cms.reviewer_readmore', compact('timeline1', 'timeline2'));
    }


    public function techEditorReadmore($uuid, $timeline_id)
    {
        // Find the application
        $application = Application::where('uuid', $uuid)->firstOrFail();

        // Find the EXACT timeline entry by its ID, ensuring it belongs to this application
        $timeline = clone $application->timelines()
            ->where('id', $timeline_id)
            ->firstOrFail();

        return view('admin.application.techEditor_readMore', compact('timeline'));
    }



    public function reSubmission($request, $uuid)
    {

        if ($request->isMethod('GET')) {

            return view('author.cms.authorReSubmitRemark', compact('uuid'));
        }

        if ($request->isMethod('POST')) {
            $application = $this->getApplication($uuid);

            $missing = $this->missingRevisionDocuments($application);
            if (!empty($missing)) {
                return errorResponse(
                    'Please upload ' . implode(' and ', $missing) . ' before resubmitting.'
                );
            }

            $existingData = is_array($application->data) ? $application->data : [];
            $staleFlowKeys = [
                'status',
                'chief_response',
                'chief_requested_by',
                'handling_editor_decision',
                'handling_editor_reason',
                'reviewer_decision',
                'reviewer_reason',
                'internal_reviewer_decision',
                'internal_reviewer_reason',
                'external_reviewer_decision',
                'external_reviewer_reason',
            ];
            foreach ($staleFlowKeys as $key) {
                unset($existingData[$key]);
            }

            $applicationUpdate = $application->update([
                'submission_type' => 'revised_submission',
                'status' => 'resubmitted',
                'assigned_to_id' => null,
                'assigned_to_ex_id' => null,
                'accept_in' => null,
                'accept_ex' => null,
                'status_ex' => null,
                'data' => $existingData,
            ]);
            $timelineUpdate = $application->timelines()->create([
                'application_id' => $application->id,
                'status' => 'resubmitted',
                'is_visible_to_author' => 1,
                'remark' => 'Your manuscript have been re-submitted',
                'data' => [
                    'comment' =>  $request->comment,
                ],
            ]);
$timelineUpdate = $application->timelines()->create([
                'application_id' => $application->id,
                'status' => 'resubmitted',
                'is_visible_to_author' => 1,
                'remark' => 'Your manuscript have been re-submitted',
                'data' => [
                    'comment' =>  $request->comment,
                ],
            ]);

            if ($applicationUpdate && $timelineUpdate) {
                // $this->notifyTechnicalEditorsResubmitted($application, $request->comment);

                if ($application->author && $application->author->email) {
                    try {
                        Mail::to($application->author->email)
                            ->cc(['jaspi@saspi.in', 'technical.editor@jaspi.saspi.in'])
                            ->send(new AuthorResubmissionConfirmationMail($application->title, $application->application_id));
                    } catch (\Exception $e) {
                        \Illuminate\Support\Facades\Log::error('Failed to send author re-submission mail: ' . $e->getMessage());
                    }

                    $this->notifyInApp($application->author, $application, [
                        'category' => 'application',
                        'type' => 'application.resubmitted',
                        'title' => 'Resubmission received',
                        'message' => 'Your resubmission for manuscript ' . $this->applicationReference($application) . ' has been received.',
                        'icon' => 'ri-send-plane-line',
                        'severity' => 'success',
                    ], true);
                }

                return successResponse('Re-submission successfully', route('author.application.show', $uuid));
            }
        }
        return errorResponse('Something went wrong while re-submission');
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


    public function toggleStatus($request, $uuid)
    {
        // $lastCode = Application::whereYear('created_at', date('Y'))
        //     ->whereNotNull('application_id')
        //     ->orderByDesc('application_id')
        //     ->value('application_id');

        $lastCode = Application::whereNotNull('application_id')
            ->orderByDesc('app_num')
            ->value('app_num');



        // Extract the numeric part
        if ($lastCode) {
            // preg_match('/JASPI\/(\d+)\/' . date('Y') . '/', $lastCode, $matches);
            // $number = isset($matches[1]) ? (int)$matches[1] : 95;
            $nextNumber = $lastCode + 1;
        } else {
            $nextNumber = 105;
        }


        // $nextNumber = str_pad($number + 1, 3, '0', STR_PAD_LEFT); // pad with zeros to 3 digits
        $app_code = "JASPI/{$nextNumber}/" . date('Y');
        // return $app_code;
        $application = Application::uuid($uuid)->first();
        $data = [
            'application_id' => $application->id,
            'status' => 'submitted',
            'is_visible_to_author' => 1,
            'remark' => 'Initial submission by the author',
        ];
        try {
            $updated = $application->update([
                'status' => $request->status,
                'application_id' => $app_code,
                'app_num' => $nextNumber,
            ]);
            $timeline = ApplicationTimeline::create($data);
            if ($updated && $timeline) {
                return  successResponse('Submitted SuccessFully', route('author.application.show', $uuid));
            }
            return errorResponse();
        } catch (Throwable $th) {
            return throwException($th);
        }
    }

    public function check_List($request)
    {
        if ($request->isMethod('GET')) {
            return view('author.cms.check_list');
        }
        if ($request->isMethod('POST')) { {
                // 1. Validate request
                $validated = $request->validate([
                    'reviewer_recommendation' => 'required|in:yes',

                    'guidelines'            => 'required|in:Yes,No,NA',
                    'authorship_approval'   => 'required|in:Yes,No,NA',
                    'ethics'                => 'required|in:Yes,No,NA',
                    'patient_consent'       => 'required|in:Yes,No,NA',
                    'tables'                => 'required|in:Yes,No,NA',
                    'figures'               => 'required|in:Yes,No,NA',
                    'ai_disclosure'         => 'required|in:Yes,No,NA',
                ]);

                // 2. Prepare structured checklist data
                $checkListData = [
                    'reviewer_recommendation' => $validated['reviewer_recommendation'],

                    'questions' => [
                        'guidelines'          => $validated['guidelines'],
                        'authorship_approval' => $validated['authorship_approval'],
                        'ethics'              => $validated['ethics'],
                        'patient_consent'     => $validated['patient_consent'],
                        'tables'              => $validated['tables'],
                        'figures'             => $validated['figures'],
                        'ai_disclosure'       => $validated['ai_disclosure'],
                    ],

                    'submitted_at' => now(),

                ];

                // 3. Store in session with key `check_list`
                session(['check_list' => $checkListData]);

                // if(session()->has('check_list')) {
                //     return session('check_list');
                // }
                return $this->create($request);
                // 4. Redirect to next step
                // return redirect()
                //     ->back()
                //     ->with('success', 'Checklist saved successfully.');
            }
        }
    }



    public function documents($request)
    {
        return successResponse('Progress Saved Successfully', route('author.application.create', [
            'applicationId' => $request->uuid,
            'step' => "coAuthors"
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
            return successResponse('Progress Saved Successfully', route('author.application.create', [
                'applicationId' => $request->uuid,
                'step' => "coAuthors"
            ]));
        }
    }

    public function coAuthors($request)
    {
        return successResponse('Progress Saved Successfully', route('author.application.create', [
            'applicationId' => $request->uuid,
            'step' => "reviewerSuggestions"
        ]));
    }

    public function reviewerSuggestions($request)
    {
        return successResponse('Progress Saved Successfully', route('author.application.create', [
            'applicationId' => $request->uuid,
            'step' => "preview"
        ]));
    }


    // public function preview($request)
    // {
    //     // 1. Find Application
    //     $application = Application::where('uuid', $request->uuid)->firstOrFail();

    //     // 2. Prepare Data (Conflict, Copyright, etc)
    //     $data = (array)json_decode($application->data) ?? [];

    //     if ($request->has('conflict_interest')) {
    //         $data['conflict_interest'] = $request->conflict_interest;
    //     }

    //     if ($request->has('copyright_agree')) {
    //         $data['copyright_agree'] = $request->copyright_agree ? true : false;
    //     }

    //     // Assign data back to model (but don't save yet, we save in the update call below)
    //     $application->data = $data;

    //     // 3. Determine Status and ID Logic
    //     $currentStatus = $application->status;

    //     // Default values (assuming it's already generated)
    //     $nextNumber = $application->app_num;
    //     $app_code = $application->application_id;
    //     $newStatus = 'submitted';
    //     $remark = 'Initial submission by the author';

    //     // --- SCENARIO A: INITIAL SUBMISSION ---
    //     // Generate NEW ID only if it doesn't exist or status is incomplete
    //     if ($currentStatus == 'incomplete' || empty($app_code)) {

    //         $lastCode = Application::whereNotNull('application_id')
    //             ->orderByDesc('app_num')
    //             ->value('app_num');

    //         if ($lastCode) {
    //             $nextNumber = $lastCode + 1;
    //         } else {
    //             $nextNumber = 105; // Start from 105
    //         }

    //         $app_code = "JASPI/{$nextNumber}/" . date('Y');
    //         $newStatus = 'submitted';
    //         $remark = 'Initial submission by the author';
    //         $submission_type = $application->submission_type;
    //     }

    //     // --- SCENARIO B: RESUBMISSION ---
    //     // Do NOT generate a new number, keep existing. Just change status.
    //     elseif ($currentStatus == 'revise') {
    //         $newStatus = 'resubmitted';
    //         $remark = 'Resubmitted by the author';
    //         $submission_type = 'revised_submission';
    //         // $nextNumber and $app_code remain unchanged from lines 25-26
    //     }

    //     // 4. Update Database
    //     try {
    //         $updated = $application->update([
    //             'status'         => $newStatus,
    //             'application_id' => $app_code, // Will be new for initial, same for resubmit
    //             'app_num'        => $nextNumber,
    //             'data'           => $data, // Save the JSON data here
    //             'submission_type' => $submission_type
    //         ]);

    //         // 5. Create Timeline Entry
    //         if ($updated) {
    //             ApplicationTimeline::create([
    //                 'application_id' => $application->id,
    //                 'status'         => $newStatus, // dynamic status (submitted/resubmitted)
    //                 'remark'         => $remark,
    //             ]);

    //             return successResponse(
    //                 $newStatus == 'resubmitted' ? 'Resubmitted Successfully' : 'Submitted Successfully',
    //                 route('author.application.show', $request->uuid)
    //             );
    //         }

    //         return errorResponse();
    //     } catch (Throwable $th) {
    //         return throwException($th);
    //     }
    // }
    public function preview($request)
    {
        // return $request;
        // 1. Find Application
        $application = Application::where('uuid', $request->uuid)->firstOrFail();

        // 2. Prepare Data (Conflict, Copyright, etc)
        // Safer decoding to handle both String(JSON) and Array cases from DB
        $rawData = $application->data;
        if (is_string($rawData)) {
            $data = json_decode($rawData, true) ?? [];
        } elseif (is_array($rawData)) {
            $data = $rawData;
        } else {
            $data = [];
        }

        // --- CONFLICT OF INTEREST LOGIC ---
        if ($request->has('conflict_interest')) {
            $data['conflict_interest'] = $request->conflict_interest;

            // NEW: Save details ONLY if "Yes" is selected
            if ($request->conflict_interest === 'yes') {
                // capture the text input we added to the blade
                $data['conflict_interest_details'] = $request->input('conflict_interest_details', '');
            } else {
                // If "No", clear any previous details to keep data clean
                $data['conflict_interest_details'] = '';
            }
        }

        // --- COPYRIGHT LOGIC ---
        if ($request->has('copyright_agree')) {
            $data['copyright_agree'] = $request->copyright_agree ? true : false;
        }

        // Assign data back to model (but don't save yet, we save in the update call below)
        $application->data = $data;

        // 3. Determine Status and ID Logic
        $currentStatus = $application->status;

        // --- REVISION DOCUMENT CHECK (REQUIRED ON RESUBMISSION) ---
        if ($currentStatus == 'revise') {
            $missing = $this->missingRevisionDocuments($application);
            if (!empty($missing)) {
                return errorResponse(
                    'Please upload ' . implode(' and ', $missing) . ' before resubmitting.'
                );
            }
        }

        // Default values (assuming it's already generated)
        $nextNumber = $application->app_num;
        $app_code = $application->application_id;
        $newStatus = 'submitted';
        $remark = 'Initial submission by the author';

        // Initialize submission type (default to current or new)
        $submission_type = $application->submission_type ?? 'new_submission';

        // --- SCENARIO A: INITIAL SUBMISSION ---
        // Generate NEW ID only if it doesn't exist or status is incomplete
        if ($currentStatus == 'incomplete' || empty($app_code)) {

            $lastCode = Application::whereNotNull('application_id')
                ->orderByDesc('app_num')
                ->value('app_num');

            if ($lastCode) {
                $nextNumber = $lastCode + 1;
            } else {
                $nextNumber = 105; // Start from 105
            }

            $app_code = "JASPI/{$nextNumber}/" . date('Y');
            $newStatus = 'submitted';
            $remark = 'Initial submission by the author';

            // Ensure submission type is set if empty
            if (empty($submission_type)) {
                $submission_type = 'new_submission';
            }
        }

        // --- SCENARIO B: RESUBMISSION ---
        // Do NOT generate a new number, keep existing. Just change status.
        elseif ($currentStatus == 'revise') {
            $newStatus = 'resubmitted';
            $remark = 'Resubmitted by the author';
            $submission_type = 'revised_submission';
            // $nextNumber and $app_code remain unchanged
        }

        // 4. Update Database
        try {
            $updated = $application->update([
                'status'         => $newStatus,
                'application_id' => $app_code,
                'app_num'        => $nextNumber,
                'data'           => $data, // Save the updated JSON data here
                'submission_type' => $submission_type,
                'assigned_to_ex_id' => null,
                'status_ex' => null,
                'accept_in' => null,
                'accept_ex' => null,
            ]);

            // 5. Create Timeline Entry
            if ($updated) {
                ApplicationTimeline::create([
                    'application_id' => $application->id,
                    'status'         => $newStatus,
                    'is_visible_to_author' => 1,
                    'remark'         => $remark,
                ]);

                if ($newStatus == 'submitted') {
                    if ($application->author && $application->author->email) {
                        try {
                            Mail::to($application->author->email)
                                ->cc(['jaspi@saspi.in', 'technical.editor@jaspi.saspi.in'])
                                ->send(new AuthorSubmissionMail($application->title, $application->application_id));
                        } catch (\Exception $e) {
                            \Illuminate\Support\Facades\Log::error('Failed to send author submission mail: ' . $e->getMessage());
                        }

                        $this->notifyInApp($application->author, $application, [
                            'category' => 'application',
                            'type' => 'application.submitted',
                            'title' => 'Submission received',
                            'message' => 'Your manuscript ' . $this->applicationReference($application) . ' has been submitted.',
                            'icon' => 'ri-send-plane-line',
                            'severity' => 'success',
                        ], true);
                    }
                } else if ($newStatus == 'resubmitted') {
                    if ($application->author && $application->author->email) {
                        try {
                            Mail::to($application->author->email)
                                ->cc(['jaspi@saspi.in', 'technical.editor@jaspi.saspi.in'])
                                ->send(new AuthorResubmissionConfirmationMail($application->title, $application->application_id));
                        } catch (\Exception $e) {
                            \Illuminate\Support\Facades\Log::error('Failed to send author re-submission mail: ' . $e->getMessage());
                        }

                        $this->notifyInApp($application->author, $application, [
                            'category' => 'application',
                            'type' => 'application.resubmitted',
                            'title' => 'Resubmission received',
                            'message' => 'Your resubmission for manuscript ' . $this->applicationReference($application) . ' has been received.',
                            'icon' => 'ri-send-plane-line',
                            'severity' => 'success',
                        ], true);
                    }
                }

                return successResponse(
                    $newStatus == 'resubmitted' ? 'Resubmitted Successfully' : 'Submitted Successfully',
                    route('author.application.show', $request->uuid)
                );
            }

            return errorResponse();
        } catch (Throwable $th) {
            return throwException($th);
        }
    }

    // public function reviewer($request, $uuid)
    // {
    //     $author = authUser()->id;

    //     $application = Application::where('author_id', $author)
    //         ->where('uuid', $uuid)
    //         ->first();

    //     if ($request->method() == 'GET') {

    //         return view('author.cms.reviewer_suggestion', compact('application'));
    //     } elseif ($request->method() == 'POST') {

    //         // New reviewer entry
    //         $newReviewer = [
    //             'reviewer_id'    => $request->reviewer_id ?? uniqid(),
    //             'title'          => $request->title,
    //             'first_name'     => $request->first_name,
    //             'last_name'      => $request->last_name,
    //             'email'          => $request->email,
    //             'institution'    => $request->institution ?? null,
    //             'specialization' => $request->specialization ?? null,
    //             'reason'         => $request->reason ?? null,
    //         ];

    //         // 🔥 FIX: data is ALREADY an array (because of $casts)
    //         $data = $application->data ?? [];

    //         // Ensure array key exists
    //         if (!isset($data['reviewer_suggestions']) || !is_array($data['reviewer_suggestions'])) {
    //             $data['reviewer_suggestions'] = [];
    //         }

    //         // Append new reviewer
    //         $data['reviewer_suggestions'][] = $newReviewer;

    //         // Save back directly as array
    //         $application->data = $data;
    //         $application->save();

    //         return successResponse(
    //             'Reviewer suggestion added successfully',
    //             route('author.application.create', [
    //                 'applicationId' => $uuid,
    //                 'app_info' => $application,
    //                 'step' => "reviewerSuggestions"
    //             ])
    //         );
    //     }

    //     return errorResponse('Invalid request method');
    // }
    public function reviewer($request, $uuid)
    {
        $author = authUser()->id;

        $application = Application::where('author_id', $author)
            ->where('uuid', $uuid)
            ->first();

        if ($request->method() == 'GET') {

            return view('author.cms.reviewer_suggestion', compact('application'));
        } elseif ($request->method() == 'POST') {

            // New reviewer entry (Updated to match your HTML form)
            $newReviewer = [
                'reviewer_id'    => $request->reviewer_id ?? uniqid(),
                'first_name'     => $request->first_name,
                'last_name'      => $request->last_name,
                'email'          => $request->email,
                'institution'    => $request->institution ?? null,
                'specialization' => $request->specialization ?? null,
                'recommended'    => $request->recommended ?? null, // Added this field
            ];

            // 🔥 FIX: data is ALREADY an array (because of $casts)
            $data = ($application->data) ?? [];

            // Ensure array key exists
            if (!isset($data['reviewer_suggestions']) || !is_array($data['reviewer_suggestions'])) {
                $data['reviewer_suggestions'] = [];
            }

            // Append new reviewer
            $data['reviewer_suggestions'][] = $newReviewer;

            // Save back directly as array
            $application->data = $data;
            $application->save();

            return successResponse(
                'Reviewer suggestion added successfully',
                route('author.application.create', [
                    'applicationId' => $uuid,
                    'app_info' => $application,
                    'step' => "reviewerSuggestions"
                ])
            );
        }

        return errorResponse('Invalid request method');
    }

    public function reviewer_update($request, $uuid, $reviewer_id)
    {
        $author = authUser()->id;

        // Get application
        $application = Application::where('author_id', $author)
            ->where('uuid', $uuid)
            ->first();

        if (!$application) {
            return errorResponse("Application not found");
        }

        // Get existing data (auto-cast to array)
        $data = $application->data ?? [];

        // Ensure reviewer_suggestions exists
        if (!isset($data['reviewer_suggestions']) || !is_array($data['reviewer_suggestions'])) {
            $data['reviewer_suggestions'] = [];
        }

        // ---------------------------------------
        // ⭐ 1) GET METHOD -> Fetch selected reviewer and send to blade
        // ---------------------------------------
        if ($request->method() === 'GET') {

            $reviewerData = collect($data['reviewer_suggestions'])
                ->firstWhere('reviewer_id', $reviewer_id);

            if (!$reviewerData) {
                return errorResponse("Reviewer not found");
            }

            return view('author.cms.reviewer_suggestion_update', [
                'application'   => $application,
                'uuid'          => $uuid,
                'reviewer_data' => $reviewerData
            ]);
        }

        // ---------------------------------------
        // ⭐ 2) POST METHOD -> Update reviewer
        // ---------------------------------------
        if ($request->method() === 'POST') {

            // New values to update (Matched to your HTML form)
            $updatedReviewer = [
                'reviewer_id'    => $reviewer_id, // Keep the original ID
                'first_name'     => $request->first_name,
                'last_name'      => $request->last_name,
                'email'          => $request->email,
                'institution'    => $request->institution ?? null,
                'specialization' => $request->specialization ?? null,
                'recommended'    => $request->recommended ?? null, // Added
                // Removed: 'title' and 'reason' as they are not in your form
            ];

            // Update the reviewer inside the array
            $data['reviewer_suggestions'] = collect($data['reviewer_suggestions'])
                ->map(function ($reviewer) use ($reviewer_id, $updatedReviewer) {
                    // If IDs match, replace with new data; otherwise keep existing
                    return $reviewer['reviewer_id'] == $reviewer_id
                        ? $updatedReviewer
                        : $reviewer;
                })
                ->toArray(); // Convert collection back to array for storage

            // Save updated data
            $application->data = $data;
            $application->save();

            return successResponse(
                "Reviewer suggestion updated successfully",
                route('author.application.create', [
                    'applicationId' => $uuid,
                    'app_info'      => $application,
                    'step'          => "reviewerSuggestions"
                ])
            );
        }

        return errorResponse("Invalid request method");
    }

    public function reviewer_delete($request, $uuid, $reviewer_id)
    {
        $app = Application::where('uuid', $uuid)->firstOrFail();

        // data is an associative array because of $casts
        $data = $app->data ?? [];

        // safety check if the key exists
        if (!isset($data['reviewer_suggestions']) || !is_array($data['reviewer_suggestions'])) {
            return errorResponse("No reviewer suggestions found.");
        }

        // filter out the reviewer to delete
        $updated = array_filter($data['reviewer_suggestions'], function ($r) use ($reviewer_id) {
            return $r['reviewer_id'] !== $reviewer_id;
        });

        // re-index array
        $data['reviewer_suggestions'] = array_values($updated);

        // save data back
        $app->data = $data;
        $app->save();

        return successResponse(
            'Reviewer suggestion deleted successfully',
            route('author.application.create', [
                'applicationId' => $uuid,
                'app_info' => $app,
                'step' => "reviewerSuggestions"
            ])
        );
    }

    public function add_document($request, $type, $app_id,)
    {
        $author = authUser()->id;

        // Check if the application exists and belongs to the author
        $application = Application::where('author_id', $author)
            ->where('id', $app_id)
            ->first();
        // return [$type, $app_id];

        if (!$application) {
            return errorResponse('Manuscript not founddddd');
        }

        // $attachment_id = Attachment::where('application_id', $id)->first()->id ?? null;

        if ($request->isMethod('GET')) {

            return view('author.cms.add_document', compact('type', 'app_id'));
        }

        if ($request->isMethod('POST')) {

            if (!$request->hasFile('file_upload')) {
                return errorResponse('File not found');
            }

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
            // return $request;


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
                'attachment_type'  => $request->file_title,
                'legend'           => $request->legend,
            ];

            // If id == 'na', create new; otherwise update existing
            // if (true) {
            $attachment = new Attachment();
            $attachment->uuid = (string) Str::uuid();
            $attachment->fill($data)->save();
            // }

            // if ($attachment_id) {
            $attachment = Attachment::find($attachment->id);
            $attachment->legend = $request->legend;
            $attachment->save();
            // }
            // return $attachment;
            return successResponse(
                'File uploaded successfully',
                route(
                    'author.application.create',
                    ['applicationId' => $application->uuid, 'step' => "documents"]
                )
            );
        }

        return errorResponse('Invalid request method');
    }

    public function corrected_proof($request, $id, $type, $app_id, $legend = null)
    {
        $author = auth()->user()->id;

        // Check if the application exists and belongs to the author
        $application = Application::where('author_id', $author)
            ->where('id', $app_id)
            ->first();

        if (!$application) {
            return errorResponse('Manuscript not found');
        }

        // Handle GET Request (Show the upload form/modal)
        if ($request->isMethod('GET')) {
            return view('author.cms.file_upload', compact('id', 'type', 'app_id', 'legend'));
        }

        // Handle POST Request (Process the file upload)
        if ($request->isMethod('POST')) {

            if (!$request->hasFile('file_upload')) {
                return errorResponse('File not found');
            }

            $validator = Validator::make($request->all(), [
                'file_upload' => [
                    'required',
                    'file',
                    'max:10240', // 10MB limit
                    new FileTypeRule([
                        'application/zip',
                        'application/pdf',
                        'application/msword',
                        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                        'application/octet-stream',
                    ])
                ],
            ], [
                'file_upload.required' => 'File is required.',
                'file_upload.max'      => 'The file size must be less than 10 MB.',
            ]);

            if ($validator->fails()) {
                return errorResponse($validator->errors()->first());
            }

            $file = $request->file('file_upload');

            // Extract metadata before moving the file
            $originalName = $file->getClientOriginalName();
            $extension = $file->getClientOriginalExtension();
            $mimeType = $file->getClientMimeType(); // Fixed: changed from getClientOriginalExtension()
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
                'legend'           => $request->legend ?? $legend,
            ];

            // If id == 'na', create new; otherwise update existing
            if ($id === 'na') {
                $attachment = new Attachment();
                $attachment->uuid = (string) Str::uuid();
                $attachment->fill($data)->save();
            } else {
                $attachment = Attachment::find($id);

                if (!$attachment) {
                    // Delete the newly uploaded file to save space if attachment record isn't found
                    File::delete($fullPath . '/' . $fileName);
                    return errorResponse('Attachment not found');
                }

                // Delete old file if it exists
                if (!empty($attachment->file_url) && File::exists(public_path($attachment->file_url))) {
                    File::delete(public_path($attachment->file_url));
                }

                $attachment->fill($data)->save();
            }

            // 1. Update Application Status
            $application->status = 'author corrections submitted';
            $application->save();

            // 2. Create Timeline Record
            // Note: Adjust the column names (e.g., 'user_id', 'description') to match your exact database schema
            ApplicationTimeline::create([
                'application_id' => $application->id,
                'status'         => 'author corrections submitted',
                'is_visible_to_author' => 1,
                'assigned_to_id' => $application->assigned_to_id,
                'remark'    => 'Author has submitted the corrected proofread file.'
            ]);

            $this->notifyPublisherCorrectedProof($application);

            return successResponse(
                'File uploaded successfully',
                url()->previous()
            );
        }

        return errorResponse('Invalid request method');
    }

    private function notifyTechnicalEditorsResubmitted(Application $application, $comment = null)
    {
        $technicalEditors = Admin::whereHas('roles', function ($query) {
            $query->where('name', 'technicalEditor');
        })->get();

        if ($technicalEditors->isEmpty()) {
            return;
        }

        foreach ($technicalEditors as $technicalEditor) {
            if (empty($technicalEditor->email)) {
                continue;
            }

            Mail::to($technicalEditor->email)->send(new AuthorResubmittedMail(
                $technicalEditor,
                $application,
                $comment,
                route('admin.application.show', $application->uuid)
            ));

            $this->notifyInApp($technicalEditor, $application, [
                'category' => 'application',
                'type' => 'application.resubmitted',
                'title' => 'Manuscript resubmitted',
                'message' => 'Manuscript ' . $this->applicationReference($application) . ' has been resubmitted.',
                'icon' => 'ri-send-plane-line',
                'severity' => 'info',
            ]);
        }
    }

    private function notifyPublisherCorrectedProof(Application $application)
    {
        $assignee = null;

        if (!empty($application->assigned_to_id)) {
            $assignee = Admin::find($application->assigned_to_id);
        }

        if (!$assignee) {
            $assignee = Admin::where('is_admin', true)->first();
        }

        if (!$assignee || empty($assignee->email)) {
            return;
        }

        Mail::to($assignee->email)->send(new CorrectedProofSubmittedMail(
            $assignee,
            $application,
            route('admin.application.show', $application->uuid)
        ));

        $this->notifyInApp($assignee, $application, [
            'category' => 'publication',
            'type' => 'publication.corrected-proof',
            'title' => 'Corrected proof submitted',
            'message' => 'The author submitted a corrected proof for manuscript ' . $this->applicationReference($application) . '.',
            'icon' => 'ri-file-check-line',
            'severity' => 'success',
        ]);
    }
}
