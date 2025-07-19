   @php
       $savedArticles = !empty($app_info) && is_array($app_info->article_type) ? $app_info->article_type : [];
       $articles = [
           'Original article',
           'Systematic Review and Meta-Analysis',
           'Narrative Review',
           'Letter to Editor',
           'Brief Communication',
           'Infectious Diseases Cases/Vignette',
           'Editorial',
           'Other type',
       ];
       $hasChecked = false;

       $article_checks = ['Plagiarism Check', 'Cross reference Check'];
       $savedArticleChecks = !empty($app_info) && !empty($app_info->article_check) ? $app_info->article_check : [];

   @endphp

   <div class="card-body">

       @php
           $currentStatus = $app_info->status ?? '';
           $applicationId = $app_info->application_id ?? '';
       @endphp
       <div class="row">
           <div class="col-7">
               <div class="card">
                   <div class="card-body">
                       <div class="mb-3">
                           <label for="submission_type" class="form-label required">Submission Type</label>
                           <select class="form-select form-control" id="submission_type" name="submission_type"
                               onchange="toggleJaspiIdField()" required>
                               <option value="">--Select--</option>

                               @if ($currentStatus != 'revised')
                                   <option value="new_submission"
                                       {{ ($app_info->submission_type ?? '') == 'new_submission' ? ' selected' : '' }}>
                                       New Submission</option>
                               @endif

                               @if ($currentStatus == 'revised')
                                   <option value="revised_submission" selected>
                                       Revised Submission</option>
                               @endif
                           </select>

                       </div>
                       <div class="mb-3" id="jaspi_id_field" style="display:none;">
                           <label for="jaspi_id" class="form-label">JASPI ID</label>
                           <select class="form-select form-control" id="jaspi_id" name="jaspi_id">
                               <option value="{{ $applicationId }}">{{ $applicationId }}</option>


                           </select>
                       </div>
                       <div class="mb-3">
                           <label for="title" class="form-label required">Title</label>
                           <input type="text" class="form-control" id="title" name="title"
                               placeholder="Enter application title" required value="{{ $app_info->title ?? '' }}">
                       </div>
                       <div class="mb-3">
                           <label for="author_affiliation" class="form-label required">Author Affiliation</label>
                           <input type="text" class="form-control" id="author_affiliation" name="author_affiliation"
                               placeholder="Enter author affiliation" required
                               value="{{ $app_info->author_affiliation ?? '' }}">
                       </div>
                       <div class="mb-3">
                           <label for="author_orcid_id" class="form-label required">ORCID ID</label>
                           <input type="text" class="form-control" id="author_orcid_id" name="author_orcid_id"
                               placeholder="Enter ORCID ID" required value="{{ $app_info->author_orcid_id ?? '' }}">
                       </div>
                       <div class="mb-3">
                           <label for="author_saspi_id" class="form-label">SASPI ID</label>
                           <input type="text" class="form-control" id="author_saspi_id" name="author_saspi_id"
                               placeholder="Enter SASPI ID" value="{{ $app_info->author_saspi_id ?? '' }}">
                       </div>
                   </div>
               </div>
           </div>
           <div class="col-5">
               <div class="card">
                   <div class="card-header">
                       <h4 class="card-title">Article Type <span class="required text-danger"></span></h4>
                   </div>
                   <div class="card-body">
                       <input type="checkbox" name="atLeastOne" id="atLeastOne" {{ $hasChecked ? '' : 'required' }}
                           data-error-message="At least one option is required" style="display:none">
                       @foreach ($articles as $index => $article)
                           <div class="col form-check mb-2">
                               <input class="form-check-input article-checkbox" type="checkbox" name="article_type[]"
                                   value='{{ $article }}' id='article_{{ $index }}'
                                   {{ in_array($article, $savedArticles) ? 'checked' : '' }}
                                   onchange="toggleRequired()">
                               <label class="form-check-label"
                                   for="article_{{ $index }}">{{ $article }}</label>
                           </div>
                       @endforeach
                       @if (array_key_exists('other_data', $savedArticles))
                           <div id="other_article_type_container">
                               <input type="text" class="form-control" id="article_type_other"
                                   name="article_type_other" placeholder="Enter other article type"
                                   value="{{ $savedArticles['other_data'] ?? '' }}">
                           @else
                               <div id="other_article_type_container" style="display: none;">
                                   <input type="text" class="form-control" id="article_type_other"
                                       name="article_type_other" placeholder="Enter other article type" value="">
                       @endif
                   </div>
                   <span class="text-danger mt-2" data-error="atLeastOne"></span>
               </div>
           </div>
       </div>
       <div class="col-12">
           <div class="card">
               <div class="card-header">
                   <h4 class="card-title">Article Checked <span class="text-danger"></span></h4>
               </div>
               <div class="card-body">
                   <div class="d-flex">
                       @foreach ($article_checks as $article_check)
                           <div class="col form-check">
                               <input class="form-check-input article-checkbox" type="checkbox"
                                   name="article_check[]" value='{{ $article_check }}'
                                   id='article_{{ $index }}'
                                   {{ in_array($article_check, $savedArticleChecks) ? 'checked' : '' }}>
                               <label class="form-check-label"
                                   for="article_check{{ $index }}">{{ $article_check }}</label>
                           </div>
                       @endforeach
                       {{-- <span class="text-danger mt-2" data-error="atLeastOne"></span> --}}
                   </div>
               </div>
           </div>
       </div>

       <div class="col-12 mb-3">


           <label for="description" class="form-label">Description</label>
           <div id="editor" style="height:230px !important;">
               {!! $app_info->description ?? '' !!}
           </div>
           <input id="description" name="description" type="text" class="d-none"></input>

       </div>
       <span class="text-danger mt-1" data-error="description"></span>
   </div>
   </div>
