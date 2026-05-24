@extends('layouts.app')
@section('title'){{ __('app.roles') }}@endsection

@section('content')
<div class="container-fluid py-2">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card my-4">
                <div class="card-header bg-gradient-dark text-white">
                    <h6 class="mb-0 text-white">{{ __('app.roles') }} - #{{ $role->id }}</h6>
                </div>
                <div class="card-body">
                    
                    {{-- Role Names --}}
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="d-block fw-bold">{{ __('app.name') }} (AR)</label>
                            <div>
                                <p class="form-control-plaintext">{{ $role->name }}</p>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <label class="d-block fw-bold">{{ __('app.name') }} (EN)</label>
                            <div>
                                <p class="form-control-plaintext">{{ $role->name_en }}</p>
                            </div>
                        </div>
                    </div>

                    {{-- Permissions --}}
                    <div class="row mb-3">
                        <div class="col-12">
                            <label class="d-block fw-bold">{{ __('app.permissions') }}</label>
                            <div class="d-flex flex-wrap gap-2">
                                @foreach($role->permissions as $permission)
                                    <span class="badge bg-primary">{{ $permission->name }}</span>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    {{-- Actions --}}
                    <div class="d-flex justify-content-end mt-4 gap-3">
                        <a href="{{ route('roles.index') }}" class="btn btn-secondary me-2">{{ __('app.back') }}</a>
                        <a href="{{ route('roles.edit', $role->id) }}" class="btn btn-primary">{{ __('app.edit') }}</a>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>
@endsection
