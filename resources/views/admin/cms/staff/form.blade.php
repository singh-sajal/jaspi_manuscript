 <div class="card-body">

     <div class="form-group my-2">
         <label for="name" class="required">Name</label>
         <input type="text" name="name" class="form-control" required value="{{ $record->name ?? '' }}">
     </div>

     <div class="form-group my-2">
         <label for="email" class="required">Email</label>
         <input type="email" name="email" class="form-control" required value="{{ $record->email ?? '' }}">

     </div>
     {{-- @dd($staffRoles) --}}

     <div class="form-group my-2">
         <label for="phone" class="required">Phone</label>
         <input type="tel" name="phone" class="form-control" minlength="10" maxlength="10" required
             value="{{ $record->phone ?? '' }}">
     </div>

     <div class=" form-group col-12 my-2">
         <label class="form-label required" for="avatar">Avatar</label>
         <div class="d-flex align-items-center">

             <input class="form-control flex-grow-1 has-thumbnail" name="avatar" data-max-size="1024"
                 data-file-types="jpg,png" placeholder="" type="file">

             <div class="ml-2">
                 @php
                     $picture = $record->avatar ?? '';
                 @endphp
                 <img class="avatar avatar-xs thumbnail border border-1" alt="avatar"
                     src="{{ asset($picture ?? 'admin/avatar/user-avatar.svg') }}">
             </div>
         </div>
         <span><small> Standard Ratio: 1:1 (500px * 500px)</small></span>
         <span class="text-danger" data-error="avatar"></span>
     </div>

     {{-- <div class="form-group my-2">
         <label for="role" class="required">Role</label>
         <select name="role_id" id="roleSelect" class="form-control" required>
             <option value="">-- Select Role --</option>
             @foreach ($roles as $role)
                 <option value="{{ $role->name }}"
                     {{ !empty($staffRoles[0]) && in_array($role->id, $staffRoles[0]) ? 'selected' : '' }}>
                     {{ ucfirst($role->name) }}
                 </option>
             @endforeach
         </select>
     </div>


     <div class="form-group my-2">
         <label for="role" class="required">Secondary Role</label>
         <select name="role_id_2" id="roleSelect" class="form-control" required>
             <option value="">-- Select Role --</option>
             @foreach ($roles as $role)
             <option value="{{ $role->name }}"
                     {{ !empty($staffRoles[1]) && in_array($role->id, $staffRoles[1]) ? 'selected' : '' }}>
                     {{ ucfirst($role->name) }}
                 </option>
             @endforeach
         </select>
     </div> --}}

     {{-- ========================================== --}}
     {{-- ROLE AND ADMIN CHECKBOX (CLEAN LAYOUT)     --}}
     {{-- ========================================== --}}
     <div class="form-group my-2">
         <div class="row align-items-end">

             <div class="col-sm-9 col-8">
                 <label for="roleSelect" class="required">Role</label>
                 <select name="role_id" id="roleSelect" class="form-control" required>
                     <option value="">-- Select Role --</option>
                     @foreach ($roles as $role)
                         <option value="{{ $role->id }}"
                             {{ isset($staffRoles[0]) && $staffRoles[0] == $role->id ? 'selected' : '' }}>
                             {{ ucfirst($role->name) }}
                         </option>
                     @endforeach
                 </select>
             </div>

             <div class="col-sm-3 col-4 pb-2">
                 <div class="form-check d-flex align-items-center">
                     <input type="checkbox" name="is_admin" id="is_admin" value="1" class="form-check-input mt-0"
                         {{ ($record->is_admin ?? 0) == 1 ? 'checked' : '' }} style="cursor: pointer;">

                     <label for="is_admin" class="form-check-label ml-2 mb-0"
                         style="cursor: pointer; user-select: none;">
                         Is Admin
                     </label>
                 </div>
             </div>

         </div>
     </div>
     {{-- ========================================== --}}

     <div class="form-group my-2">
         <label for="role" class="">Secondary Role</label>
         <select name="role_id_2" id="roleSelect2" class="form-control">
             <option value="">-- Select Role --</option>
             @foreach ($roles->whereNotIn('name', ['superAdmin', 'technical editor', 'assistant editor']) as $role)
                 <option value="{{ $role->id }}"
                     {{ isset($staffRoles[1]) && $staffRoles[1] == $role->id ? 'selected' : '' }}>
                     {{ ucfirst($role->name) }}
                 </option>
             @endforeach
         </select>
     </div>





     {{-- Reviewer Type (only for Reviewer role) --}}
     <div class="form-group my-2" id="reviewerTypeWrapper">
         <label for="reviewer_type" class="required">Reviewer Type <span class="text-muted">(Only when assigning
                 reviewers)</span></label>
         <select name="rev_type" id="reviewerType" class="form-control">
             <option value="">-- Select Type --</option>
             <option value="internal" {{ ($record->rev_type ?? '') == 'internal' ? 'selected' : '' }}>Internal
             </option>
             <option value="external" {{ ($record->rev_type ?? '') == 'external' ? 'selected' : '' }}>External
             </option>
         </select>
     </div>

     {{-- Interests --}}
     <div class="form-group my-2">
         <label for="integrations" class="">Interests</label>
         <div class="d-flex flex-wrap align-items-center gap-3">
             @forelse ($integrations ?? [] as $integration)
                 <div class="form-check d-flex align-items-center">
                     <input type="checkbox" name="integrations[]" id="integration_{{ $integration->id }}"
                         value="{{ $integration->id }}" class="form-check-input mt-0"
                         {{ in_array($integration->id, $staffIntegrations ?? []) ? 'checked' : '' }}
                         style="cursor: pointer;">
                     <label for="integration_{{ $integration->id }}" class="form-check-label ml-2 mb-0"
                         style="cursor: pointer; user-select: none;">
                         {{ $integration->name }}
                     </label>
                 </div>
             @empty
                 <span class="text-muted">No interests available. <a href="{{ route('admin.integration.index') }}">Add
                         interests</a> first.</span>
             @endforelse
         </div>
     </div>




 </div>
