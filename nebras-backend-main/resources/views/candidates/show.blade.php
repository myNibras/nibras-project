@extends('layouts.app')
@section('title'){{ __('app.candidates') }} - #{{ $candidate->id }}@endsection

@section('content')
<div class="container-fluid py-2">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card my-4">
                <div class="card-header bg-gradient-dark text-white">
                    <h6 class="mb-0 text-white">{{ __('app.candidates') }} - #{{ $candidate->id }}</h6>
                </div>
                <div class="card-body">

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="col-form-label fw-bold">{{ __('app.full name') }}</label>
                            <p class="form-control-plaintext">{{ $candidate->full_name }}</p>
                        </div>
                        <div class="col-md-6">
                            <label class="col-form-label fw-bold">{{ __('app.email') }}</label>
                            <p class="form-control-plaintext">{{ $candidate->email }}</p>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="col-form-label fw-bold">{{ __('app.phone number') }}</label>
                            <p class="form-control-plaintext">{{ $candidate->phone_number }}</p>
                        </div>
                        <div class="col-md-6">
                            <label class="col-form-label fw-bold">{{ __('app.years of experience') }}</label>
                            <p class="form-control-plaintext">{{ $candidate->years_of_experience }}</p>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-12">
                            <label class="col-form-label fw-bold">{{ __('app.major specialization') }}</label>
                            <p class="form-control-plaintext">{{ $candidate->major_specialization }}</p>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="col-form-label fw-bold">{{ __('app.submission date') }}</label>
                            <p class="form-control-plaintext">{{ $candidate->created_at->format('Y-m-d H:i') }}</p>
                        </div>
                        <div class="col-md-6">
                            <label class="col-form-label fw-bold">{{ __('app.cv') }}</label>
                            <div>
                                @if($candidate->cv_url)
                                    <a href="{{ $candidate->cv_url }}" target="_blank" class="btn btn-sm btn-outline-primary">
                                        <i class="fa-solid fa-download"></i> {{ __('app.download cv') }}
                                    </a>
                                @else
                                    <span class="text-muted">{{ __('app.no cv uploaded') }}</span>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end mt-4 gap-3">
                        <a href="{{ route('candidates.index') }}" class="btn btn-secondary">
                            {{ __('app.back') }}
                        </a>
                        <button type="button" class="btn btn-danger btn-delete" data-table="candidates" data-id="{{ $candidate->id }}">
                            {{ __('app.delete') }}
                        </button>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        $(document).on('click', '.btn-delete', function (e) {
            e.preventDefault();
            var id = $(this).data("id");
            var table = $(this).data("table");
            var url = '/{{ app()->getLocale() }}/' + table + '/' + id;
            Swal.fire({
                title: "",
                text: "{{ __('app.are you sure you want delete this record!') }}",
                icon: "warning",
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: '{{ __("app.yes, delete it!") }}',
                cancelButtonText: '{{ __("app.cancel") }}'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        type: "DELETE",
                        url: url,
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        success: function (response) {
                            Swal.fire({
                                title: "",
                                text: "{{ __('app.deleted successfully') }}",
                                icon: "success",
                                confirmButtonText: "{{ __('app.ok') }}"
                            }).then(() => {
                                window.location.href = "{{ route('candidates.index') }}";
                            });
                        },
                        error: function (err) {
                            Swal.fire({
                                title: "Error",
                                text: err.responseJSON && err.responseJSON.message ? err.responseJSON.message : "{{ __('app.something went wrong') }}",
                                icon: "error",
                                confirmButtonText: "{{ __('app.ok') }}"
                            });
                        }
                    });
                }
            });
        });
    });
</script>
@endpush
