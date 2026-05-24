@extends('layouts.app')
@section('title'){{ __('app.testimonials') }} - {{ __('app.add new') }}@endsection

@section('content')
<div class="container-fluid py-2">
    <div class="row">
        <div class="col-12">
            <div class="card my-4">
                <div class="card-header p-0 position-relative mt-n4 mx-3 z-index-2">
                    <div class="bg-gradient-dark shadow-dark border-radius-lg pt-4 pb-3 d-flex justify-content-between align-items-center px-3">
                        <h6 class="text-white text-capitalize px-3">{{ __('app.testimonials') }} - {{ __('app.add new') }}</h6>
                        <button onclick="window.history.go(-1); return false;" class="btn btn-danger mb-0 px-3 py-2 d-flex align-items-center justify-content-center gap-2"><i class="fa-solid fa-reply"></i> <span style="height:16px;">{{ __('app.back') }}</span></button>
                    </div>
                </div>
                <div class="card-body px-0 pb-2">

                    <form action="{{ route('testimonials.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="row mx-3 row-gap-3 w-100">

                            <div class="row mb-3">
                                {{-- Name --}}
                                <div class="col-md-6">
                                    <label for="name" class="form-label fw-bold">{{ __('app.name') }}</label>
                                    <input type="text" name="name" id="name" placeholder="{{ __('app.name') }}"
                                        class="form-control" value="{{ old('name') }}">
                                    @error('name')
                                        <span class="text-danger small">{{ $message }}</span>
                                    @enderror
                                </div>

                                {{-- Status --}}
                                <div class="col-md-6">
                                    <label for="status" class="form-label fw-bold">{{ __('app.status') }}</label>
                                    <select name="status" id="status" class="form-control form-select px-2">
                                        <option value="pending" {{ old('status') == 'pending' ? 'selected' : '' }}>{{ __('app.pending') }}</option>
                                        <option value="approved" {{ old('status') == 'approved' ? 'selected' : '' }}>{{ __('app.approved') }}</option>
                                        <option value="rejected" {{ old('status') == 'rejected' ? 'selected' : '' }}>{{ __('app.rejected') }}</option>
                                    </select>
                                    @error('status')
                                        <span class="text-danger small">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>

                            <div class="row mb-3">
                                {{-- Text --}}
                                <div class="col-md-12">
                                    <label for="text" class="form-label fw-bold">{{ __('app.text') }}</label>
                                    <textarea name="text" id="text" rows="5" placeholder="{{ __('app.text') }}"
                                        class="form-control">{{ old('text') }}</textarea>
                                    @error('text')
                                        <span class="text-danger small">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>

                            <div class="row mb-3">
                                {{-- Rate --}}
                                <div class="col-md-4">
                                    <label for="rate" class="form-label fw-bold">{{ __('app.rate') }}</label>
                                    <select name="rate" id="rate" class="form-control form-select px-2">
                                        @for($i = 0; $i <= 5; $i++)
                                            <option value="{{ $i }}" {{ (string)old('rate', 0) === (string)$i ? 'selected' : '' }}>
                                                {{ $i }}
                                            </option>
                                        @endfor
                                    </select>
                                    @error('rate')
                                        <span class="text-danger small">{{ $message }}</span>
                                    @enderror
                                </div>

                                {{-- Class Room (Grade Level) --}}
                                <div class="col-md-4">
                                    <label for="class_id" class="form-label fw-bold">{{ __('app.class') }}</label>
                                    <select name="class_id" id="class_id" class="form-control form-select px-2">
                                        <option value="">{{ __('app.select') }}</option>
                                        @foreach($classRooms as $classRoom)
                                            <option value="{{ $classRoom->id }}" {{ old('class_id') == $classRoom->id ? 'selected' : '' }}>
                                                {{ $classRoom->getLocalizationName() }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('class_id')
                                        <span class="text-danger small">{{ $message }}</span>
                                    @enderror
                                </div>

                                {{-- Course --}}
                                <div class="col-md-4" id="course-wrapper" style="display: none;">
                                    <label for="course_id" class="form-label fw-bold">{{ __('app.course name') }}</label>
                                    <select name="course_id" id="course_id" class="form-control form-select px-2">
                                        <option value="">{{ __('app.select') }}</option>
                                        @foreach($courses as $course)
                                            <option value="{{ $course->id }}" {{ old('course_id') == $course->id ? 'selected' : '' }}>
                                                {{ $course->getLocalizationTitleWithTeacher() }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('course_id')
                                        <span class="text-danger small">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>

                            <div class="row mb-3">
                                {{-- Image --}}
                                <div class="col-md-12">
                                    <label for="image">{{ __('app.image') }} - {{ __('app.size')}} (80x80px)</label>
                                    <input type="file" id="image" name="image" data-plugins="dropify" data-height="150" data-allowed-file-extensions="png jpg jpeg webp" />
                                    <small class="mt-2 d-block">Allow (jpg, jpeg, png, webp)</small>
                                    @error('image')
                                        <span class="text-danger small d-block mt-1">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>

                        </div>

                        {{-- Submit --}}
                        <div class="d-flex justify-content-end mt-4 gap-3 mx-3 gap-3 mb-3">
                            <a href="{{ route('testimonials.admins') }}" class="btn btn-secondary me-2">
                                {{ __('app.cancel') }}
                            </a>
                            <button type="submit" class="btn btn-primary">
                                {{ __('app.save') }}
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
    $(document).ready(function() {
        var oldCourseId = {{ old('course_id') ? old('course_id') : 'null' }};
        
        // Filter courses based on selected class
        $('#class_id').on('change', function() {
            var classId = $(this).val();
            var courseSelect = $('#course_id');
            var courseWrapper = $('#course-wrapper');
            
            // Clear current options except the first one
            courseSelect.find('option:not(:first)').remove();
            
            if (classId) {
                // Show course field when class is selected
                courseWrapper.show();
                // Show loading state
                courseSelect.prop('disabled', true);
                courseSelect.html('<option value="">{{ __('app.select') }}</option><option value="">Loading...</option>');
                
                // Fetch courses by class_id
                $.ajax({
                    url: "{{ route('testimonials.courses.by-class') }}",
                    type: 'GET',
                    data: { class_id: classId },
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(response) {
                        courseSelect.html('<option value="">{{ __('app.select') }}</option>');
                        
                        if (response.courses && response.courses.length > 0) {
                            $.each(response.courses, function(index, course) {
                                var selected = (oldCourseId && course.id == oldCourseId) ? 'selected' : '';
                                courseSelect.append('<option value="' + course.id + '" ' + selected + '>' + course.title + '</option>');
                            });
                        }                        
                        courseSelect.prop('disabled', false);
                    },
                    error: function() {
                        courseSelect.html('<option value="">{{ __('app.select') }}</option>');
                        courseSelect.prop('disabled', false);
                        alert('{{ __('app.something went wrong while fetching courses') }}');
                    }
                });
            } else {
                // If no class selected, hide course field and clear options
                courseWrapper.hide();
                courseSelect.html('<option value="">{{ __('app.select') }}</option>');
            }
        });
        
        // Initial state on page load
        var initialClassId = $('#class_id').val();
        if (initialClassId) {
            $('#class_id').trigger('change');
            $('#course-wrapper').show();
        } else {
            $('#course-wrapper').hide();
        }
    });
</script>
@endpush

