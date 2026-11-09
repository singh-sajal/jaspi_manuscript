<?php

namespace App\Http\Controllers\Author\Application;




use App\Models\Author;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;
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

    public function file_upload(Request $request, $id = null, $type = null, $app_id = null, $legend = null)
    {
        // return $request;
        return $this->repository->fileUpload($request, $id, $type, $app_id, $legend);
    }

    public function add_document(Request $request, $type = null, $app_id = null)
    {
        // return $request;
        return $this->repository->add_document($request, $type, $app_id);
    }

    public function fileDestroy($id, $app_id)
    {
        return $this->repository->fileDestroy($id, $app_id);
    }

    public function readMistake($uuid, $timeline_id = null)
    {
        return $this->repository->readMistake($uuid, $timeline_id);
    }

    public function reviewerReadMore($uuid, $timeline_id = null)
    {
        return $this->repository->reviewerReadMore($uuid, $timeline_id);
    }

    public function techEditorReadmore($uuid, $timeline_id)
    {
        return $this->repository->techEditorReadmore($uuid, $timeline_id);
    }


    public function reSubmission(Request $request, $uuid)
    {
        return $this->repository->reSubmission($request, $uuid);
    }

    public function authorUpdate($uuid, $timeline_id = null)
    {
        return $this->repository->authorUpdate($uuid, $timeline_id);
    }



    public function toggleStatus(Request $request, $uuid)
    {
        return $this->repository->toggleStatus($request, $uuid);
    }
    public function check_list(Request $request)
    {
        return $this->repository->check_list($request);
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
    public function reviewerSuggestions(Request $request)
    {
        return $this->repository->reviewerSuggestions($request);
    }
    public function preview(Request $request)
    {
        return $this->repository->preview($request);
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


    // Helper to get the full data (cached for performance if possible)
    private function getData()
    {
        $path = Storage::get('data/country-state-city.json');

        return json_decode($path, true);
    }



    public function getCountries()
    {
        $data = $this->getData();
        // Return only ID and Name to keep it light
        $countries = collect($data)->map(fn($item) => [
            'id' => $item['id'],
            'name' => $item['name']
        ])->values();

        return response()->json($countries);
    }

    public function getStates($countryId)
    {
        $data = $this->getData();
        // Find the specific country
        $country = collect($data)->firstWhere('id', $countryId);

        // Return states if found, else empty
        $states = $country['states'] ?? [];
        return response()->json(collect($states)->map(fn($item) => [
            'id' => $item['id'],
            'name' => $item['name']
        ]));
    }

    public function getCities($countryId, $stateId)
    {
        $data = $this->getData();
        $country = collect($data)->firstWhere('id', $countryId);

        if (!$country) return response()->json([]);

        // Find the state inside that country
        $state = collect($country['states'])->firstWhere('id', $stateId);

        $cities = $state['cities'] ?? [];
        return response()->json($cities);
    }


    public function checkAuthorEmail(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        $author = \App\Models\Author::where('email', $request->email)->first();

        if ($author) {
            // LOGIC: Check if you have separate columns or need to split a full name
            // Option A: If you have separate columns in DB


            // Option B: If you only have a 'name' column, split it automatically
            // $parts = explode(' ', $author->name, 2);
            // $fName = $parts[0];
            // $lName = $parts[1] ?? '';

            return response()->json([
                'status' => 'found',
                'data' => [
                    'name' => $author->name,  // <--- Send First Name
                    'lname'  => $author->lname,  // <--- Send Last Name
                    'phone' => $author->phone,
                    'affiliation' => $author->affiliation,
                    'orcid' => $author->orcid_id,
                    'country_id' => $author->country_id,
                    'state_id' => $author->state_id,
                    'city_id' => $author->city_id,
                ]
            ]);
        }

        return response()->json(['status' => 'not_found']);
    }
    public function checkReviewerEmail(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        $reviewer = \App\Models\Admin::where('email', $request->email)->first();

        if ($reviewer && $reviewer->hasRole('Reviewer')) {
            // LOGIC: Check if you have separate columns or need to split a full name
            // Option A: If you have separate columns in DB


            // Option B: If you only have a 'name' column, split it automatically
            $parts = explode(' ', $reviewer->name, 2);
            $fname = $parts[0];
            $lname = $parts[1] ?? '';

            return response()->json([
                'status' => 'found',
                'data' => [
                    'fname' => $fname,  // <--- Send First Name
                    'lname'  => $lname,  // <--- Send Last Name

                ]
            ]);
        }

        return response()->json(['status' => 'not_found']);
    }

    public function corrected_proof(Request $request, $id, $type, $app_id, $legend = null)
    {
        return $this->repository->corrected_proof($request, $id, $type, $app_id, $legend = null);
    }
}
