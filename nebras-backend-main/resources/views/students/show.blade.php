@extends('layouts.app')
@section('title'){{ __('app.students') }}@endsection

@section('content')
<div class="container-fluid py-2">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card my-4">
                <div class="card-header bg-gradient-dark text-white">
                    <h6 class="mb-0 text-white">{{ __('app.students') }} - #{{ $student->id }}</h6>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        {{-- Profile Picture --}}
                        @if($student->profile_picture)
                        <div class="col-md-12 mb-3 text-center">
                            <label class="d-block fw-bold mb-2">{{ __('app.profile picture') }}</label>
                            <img src="{{ $student->profile_picture }}" alt="{{ $student->name }}" 
                                class="img-thumbnail" style="max-width: 200px; max-height: 200px; object-fit: cover; border-radius: 50%;">
                        </div>
                        @endif
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="d-block fw-bold">{{ __('app.full name') }}</label>
                            <div>
                                <p class="form-control-plaintext">{{ $student->name }}</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="d-block fw-bold">{{ __('app.email') }}</label>
                            <div>
                                <p class="form-control-plaintext">{{ $student->email }}</p>
                            </div>                            
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="d-block fw-bold">{{ __('app.phone number') }}</label>
                            <div>
                                <p class="form-control-plaintext">{{ $student->phone }}</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="d-block fw-bold">{{ __('app.age') }}</label>
                            <div>
                                <p class="form-control-plaintext">{{ $student->age }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="d-block fw-bold">{{ __('app.gender') }}</label>
                            <div>
                                <p class="form-control-plaintext">
                                    {{ $student->gender == 0 ? __('app.male') : __('app.female') }}
                                </p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="d-block fw-bold">{{ __('app.class') }}</label>
                            <div>
                                <p class="form-control-plaintext">
                                    {{ $student->classRoom ? $student->classRoom->getLocalizationName() : '-' }}
                                </p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="d-block fw-bold">{{ __('app.created at') }}</label>
                            <div>
                                <p class="form-control-plaintext">{{ $student->created_at ? $student->created_at->format('Y-m-d') : '-' }}</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="d-block fw-bold">{{ __('app.updated at') }}</label>
                            <div>
                                <p class="form-control-plaintext">{{ $student->updated_at ? $student->updated_at->format('Y-m-d') : '-' }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end mt-4 gap-3">
                        <a href="{{ route('students.index') }}" class="btn btn-secondary me-2">{{ __('app.back') }}</a>
                        <a href="{{ route('students.edit', $student->id) }}" class="btn btn-primary">{{ __('app.edit') }}</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
