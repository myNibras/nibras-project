@extends('layouts.app')
@section('title'){{ __('app.admins') }} {{ __('app.testimonials') }}@endsection

@section('content')
<div class="container-fluid py-2">
    <div class="row">
        <div class="col-12">
            <div class="card my-4">
                <div class="card-header p-0 position-relative mt-n4 mx-3 z-index-2">
                    <div class="bg-gradient-dark shadow-dark border-radius-lg pt-4 pb-3 d-flex justify-content-between align-items-center px-3 d-flex justify-content-between align-items-center px-3">
                        <h6 class="text-white text-capitalize mb-0">{{ __('app.admins') }} {{ __('app.testimonials') }}</h6>
                        <div class="d-flex gap-3">
                            <a href="{{ route('testimonials.create') }}" class="btn btn-primary mb-0 px-3 py-2 d-flex align-items-center justify-content-center gap-2"><i class="fa-solid fa-plus"></i> <span style="height:16px;">{{ __('app.add new') }}</span></a>
                            <button onclick="window.history.go(-1); return false;" class="btn btn-danger mb-0 px-3 py-2 d-flex align-items-center justify-content-center gap-2"><i class="fa-solid fa-reply"></i> <span style="height:16px;">{{ __('app.back') }}</span></button>
                        </div>
                    </div>
                </div>
                <div class="card-body px-0 pb-2">
                    <div class="px-3 pb-3 border-bottom border-light">
                        <div class="row g-2 align-items-end flex-wrap">
                            <div class="col-auto">
                                <label class="form-label small mb-0 text-secondary" for="filter-semester">{{ __('app.semester') }}</label>
                                <select id="filter-semester" class="form-select form-select-sm" style="min-width: 200px;">
                                    <option value="">{{ __('app.all') }}</option>
                                    @foreach($semesters as $semester)
                                        <option value="{{ $semester->id }}">{{ $semester->getLocalizationTitle() }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-auto">
                                <label class="form-label small mb-0 text-secondary" for="filter-academic-level">{{ __('app.academic_level') }}</label>
                                <select id="filter-academic-level" class="form-select form-select-sm" style="min-width: 200px;">
                                    <option value="">{{ __('app.all') }}</option>
                                    @foreach($academicLevels as $level)
                                        <option value="{{ $level->id }}">{{ $level->getLocalizationTitle() }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-auto">
                                <label class="form-label small mb-0 text-secondary" for="filter-course">{{ __('app.course name') }}</label>
                                <select id="filter-course" class="form-select form-select-sm" style="min-width: 260px;">
                                    <option value="">{{ __('app.all') }}</option>
                                </select>
                            </div>
                            <div class="col-auto">
                                <button type="button" id="btn-clear-testimonial-filters" class="btn btn-outline-secondary btn-sm mb-0">{{ __('app.clear filters') }}</button>
                            </div>
                        </div>
                    </div>
                    <div class="table-responsive px-3">
                        <table id="testimonials-table" class="table align-items-center mb-0">
                            <thead>
                                <tr>
                                    <th class="text-secondary font-weight-bolder opacity-7 text-center" style="width:100px">{{ __('app.id')}}</th>
                                    <th class="text-secondary font-weight-bolder opacity-7">{{ __('app.image')}}</th>
                                    <th class="text-secondary font-weight-bolder opacity-7">{{ __('app.name')}}</th>
                                    <th class="text-secondary font-weight-bolder opacity-7">{{ __('app.text')}}</th>
                                    <th class="text-secondary font-weight-bolder opacity-7">{{ __('app.class')}}</th>
                                    <th class="text-secondary font-weight-bolder opacity-7">{{ __('app.course name')}}</th>
                                    <th class="text-secondary font-weight-bolder opacity-7">{{ __('app.status')}}</th>
                                    <th class="text-secondary font-weight-bolder opacity-7">{{ __('app.created at')}}</th>
                                    <th class="text-secondary font-weight-bolder opacity-7">{{ __('app.action')}}</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    var filterCoursesUrl = "{{ LaravelLocalization::localizeUrl(route('testimonials.filter.courses')) }}";

    function loadTestimonialFilterCourses(callback) {
        var semesterId = $('#filter-semester').val();
        var levelId = $('#filter-academic-level').val();
        var $course = $('#filter-course');
        var previous = $course.val();
        $course.prop('disabled', true);
        $.get(filterCoursesUrl, { semester_id: semesterId, academic_level_id: levelId })
            .done(function(res) {
                $course.empty().append($('<option></option>').attr('value', '').text('{{ __('app.all') }}'));
                (res.courses || []).forEach(function(c) {
                    $course.append($('<option></option>').attr('value', c.id).text(c.label));
                });
                if (previous && $course.find('option[value="' + previous + '"]').length) {
                    $course.val(previous);
                }
            })
            .always(function() {
                $course.prop('disabled', false);
                if (typeof callback === 'function') {
                    callback();
                }
            });
    }

    $(document).ready(function() {
        loadTestimonialFilterCourses(function() {
            handleTable();
        });

        $('#filter-semester, #filter-academic-level').on('change', function() {
            $('#filter-course').val('');
            loadTestimonialFilterCourses(function() {
                if ($.fn.DataTable.isDataTable('#testimonials-table')) {
                    $('#testimonials-table').DataTable().ajax.reload();
                }
            });
        });
        $('#filter-course').on('change', function() {
            if ($.fn.DataTable.isDataTable('#testimonials-table')) {
                $('#testimonials-table').DataTable().ajax.reload();
            }
        });
        $('#btn-clear-testimonial-filters').on('click', function() {
            $('#filter-semester, #filter-academic-level, #filter-course').val('');
            loadTestimonialFilterCourses(function() {
                if ($.fn.DataTable.isDataTable('#testimonials-table')) {
                    $('#testimonials-table').DataTable().ajax.reload();
                }
            });
        });
    });

    function handleTable(){
        $("#testimonials-table").dataTable().fnDestroy();
        $('#testimonials-table').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                "url": "{{ LaravelLocalization::localizeUrl(route('testimonials.admins')) }}",
                "data": function(d) {
                    d.semester_id = $('#filter-semester').val() || '';
                    d.academic_level_id = $('#filter-academic-level').val() || '';
                    d.course_id = $('#filter-course').val() || '';
                },
                "type": "GET"
            },
            order: [[0, 'desc']],
            columns: [
                { data: 'id' },
                { data: 'image' },
                { data: 'name' },
                { data: 'text' },
                { data: 'class_room' },
                { data: 'course' },
                { data: 'status' },
                { data: 'created_at' },
                { data: 'action', orderable: false, searchable: false },
            ],
            columnDefs: [
                { targets: 0, width: '70px', className: 'text-center p-0'},
                { targets: 1, width: '100px', className: 'text-center p-0', orderable: false },
                { targets: 2, className: 'ps-4 py-2 pe-2' },
                { targets: 3, className: 'ps-4 py-2 pe-2' },
                { targets: 4, className: 'ps-4 py-2 pe-2' },
                { targets: 5, className: 'ps-4 py-2 pe-2' },
                { targets: 6, className: 'ps-4 py-2 pe-2' },
                { targets: 7, className: 'ps-4 py-2 pe-2' },
                { targets: 8, className: 'ps-4 py-2 pe-2' }
            ],
            language: {
                paginate: {
                    previous: '<i class="fa-solid fa-chevron-left"></i>',
                    next: '<i class="fa-solid fa-chevron-right"></i>'
                }
            }
        });
    }

    // Handle status change with confirmation
    $(document).on('change', '.change-testimonial-status', function() {
        var select = $(this);
        var testimonialId = select.data('id');
        var currentStatus = select.data('current-status');
        var newStatus = select.val();
        var selectElement = this;

        // If status hasn't changed, do nothing
        if (currentStatus === newStatus) {
            return;
        }

        // Get status labels
        var statusLabels = {
            'pending': '{{ __('app.pending') }}',
            'approved': '{{ __('app.approved') }}',
            'rejected': '{{ __('app.rejected') }}'
        };

        var message = '{{ __('app.are you sure you want to change the status from') }} "' + statusLabels[currentStatus] + '" {{ __('app.to') }} "' + statusLabels[newStatus] + '"?';

        Swal.fire({
            title: "{{ __('app.change status') }}",
            text: message,
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: '{{ __("app.yes") }}',
            cancelButtonText: '{{ __("app.cancel") }}'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    type: "POST",
                    url: "/{{ app()->getLocale() }}/testimonials/change-status/" + testimonialId,
                    data: {
                        status: newStatus
                    },
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function (response) {
                        if (response.status == "success") {
                            // Update the data attribute
                            select.data('current-status', newStatus);
                            Swal.fire({
                                title: "{{ __('app.success') }}",
                                text: response.message || "{{ __('app.status updated successfully') }}",
                                icon: "success",
                                confirmButtonText: "{{ __('app.ok') }}"
                            }).then(() => {
                                handleTable();
                            });
                        } else {
                            // Revert select to original value
                            select.val(currentStatus);
                            Swal.fire({
                                title: "{{ __('app.error') }}",
                                text: response.message || "{{ __('app.something went wrong') }}",
                                icon: "error",
                                confirmButtonText: "{{ __('app.ok') }}"
                            });
                        }
                    },
                    error: function (err) {
                        // Revert select to original value
                        select.val(currentStatus);
                        Swal.fire({
                            title: "{{ __('app.error') }}",
                            text: err.responseJSON?.message || "{{ __('app.something went wrong') }}",
                            icon: "error",
                            confirmButtonText: "{{ __('app.ok') }}"
                        });
                    }
                });
            } else {
                // User cancelled, revert to original status
                select.val(currentStatus);
            }
        });
    });
</script>
@endpush

