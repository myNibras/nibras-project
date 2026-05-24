@extends('layouts.app')
@section('title'){{ __('app.roles') }}@endsection

@section('content')
<div class="container-fluid py-2">
    <div class="row">
        <div class="col-12">
            <div class="card my-4">
                <div class="card-header p-0 position-relative mt-n4 mx-3 z-index-2">
                    <div class="bg-gradient-dark shadow-dark border-radius-lg pt-4 pb-3 d-flex justify-content-between align-items-center px-3">
                        <h6 class="text-white text-capitalize px-3">{{ __('app.roles') }} - {{ __('app.edit') }}</h6>
                        <button onclick="window.history.go(-1); return false;" 
                                class="btn btn-danger mb-0 px-3 py-2 d-flex align-items-center justify-content-center gap-2">
                            <i class="fa-solid fa-reply"></i> 
                            <span style="height:16px;">{{ __('app.back') }}</span>
                        </button>
                    </div>
                </div>

                <div class="card-body px-0 pb-2">

                    {{-- Validation Errors --}}
                    @if ($errors->any())
                        <div class="alert alert-danger mx-4">
                            <ul class="mb-0 text-white">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form action="{{ route('roles.update', $role->id) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="row mx-3 row-gap-3">

                            {{-- Role Name (Arabic) --}}
                            <div class="col-md-6 mb-3">
                                <label for="name" class="form-label fw-bold">{{ __('app.name') }} (AR)</label>
                                <input type="text" name="name" id="name" class="form-control"
                                       value="{{ old('name', $role->name) }}">
                            </div>

                            {{-- Role Name (English) --}}
                            <div class="col-md-6 mb-3">
                                <label for="name_en" class="form-label fw-bold">{{ __('app.name') }} (EN)</label>
                                <input type="text" name="name_en" id="name_en" class="form-control"
                                       value="{{ old('name_en', $role->name_en) }}">
                            </div>

                            {{-- Permissions --}}
                            <div class="row mt-3">
                                <label class="form-label fw-bold">{{ __('app.permissions') }}</label>

                                @foreach($permissions as $type => $group)
                                    <div class="col-12 mb-3">
                                        <div class="bg-light p-3 border rounded mb-2">
                                            <div class="d-flex justify-content-between align-items-center mb-2">
                                                <h6 class="text-dark fw-bold text-capitalize mb-0">
                                                    <input type="checkbox" id="check_all_{{ str_replace(' ', '_', $type) }}" 
                                                           class="check-all-type"
                                                           data-type="{{ str_replace(' ', '_', $type) }}">
                                                    <label for="check_all_{{ str_replace(' ', '_', $type) }}" class="text-dark fw-bold text-capitalize mb-0">
                                                        {{ str_replace('_', ' ', $type) }}
                                                    </label>
                                                </h6>
                                            </div>

                                            <div class="row">
                                                @foreach($group as $permission)
                                                    <div class="col-md-3 mb-2">
                                                        <input type="checkbox" 
                                                               name="permissions[]" 
                                                               value="{{ $permission->name }}"
                                                               id="perm_{{ $permission->id }}"
                                                               class="perm-item perm-type-{{ str_replace(' ', '_', $type) }}"
                                                               {{ in_array($permission->name, old('permissions', $rolePermissions)) ? 'checked' : '' }}>
                                                        <label for="perm_{{ $permission->id }}" class="form-check-label">
                                                            {{ $permission->name }}
                                                        </label>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>

                        </div>

                        {{-- Submit --}}
                        <div class="d-flex justify-content-end mt-4 gap-3 mx-3 gap-3">
                            <a href="{{ route('roles.index') }}" class="btn btn-secondary me-2">
                                {{ __('app.cancel') }}
                            </a>
                            <button type="submit" class="btn btn-primary">
                                {{ __('app.update') }}
                            </button>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>
</div>
@endsection


@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    // Handle Select All for each permission type
    document.querySelectorAll('.check-all-type').forEach(function (checkbox) {
        checkbox.addEventListener('change', function () {
            const type = this.dataset.type;
            const checked = this.checked;
            document.querySelectorAll('.perm-type-' + type).forEach(function (perm) {
                perm.checked = checked;
            });
        });
    });

    // Sync Select All when individual permissions change
    document.querySelectorAll('.perm-item').forEach(function (perm) {
        perm.addEventListener('change', function () {
            const type = Array.from(this.classList)
                .find(c => c.startsWith('perm-type-'))
                .replace('perm-type-', '');
            const allPerms = document.querySelectorAll('.perm-type-' + type);
            const allChecked = Array.from(allPerms).every(p => p.checked);
            document.querySelector('#check_all_' + type).checked = allChecked;
        });
    });

    // Pre-check Select All if all permissions already checked
    document.querySelectorAll('.check-all-type').forEach(function (checkAll) {
        const type = checkAll.dataset.type;
        const allPerms = document.querySelectorAll('.perm-type-' + type);
        const allChecked = Array.from(allPerms).every(p => p.checked);
        checkAll.checked = allChecked;
    });
});
</script>
@endpush
