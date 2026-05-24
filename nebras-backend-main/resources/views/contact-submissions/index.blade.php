@extends('layouts.app')
@section('title'){{ __('app.contact submissions') }}@endsection

@section('content')
<div class="container-fluid py-2">
    <div class="row">
        <div class="col-12">
            <div class="card my-4">
                <div class="card-header p-0 position-relative mt-n4 mx-3 z-index-2">
                    <div class="bg-gradient-dark shadow-dark border-radius-lg pt-4 pb-3 d-flex justify-content-between align-items-center px-3">
                        <h6 class="text-white text-capitalize mb-0">{{ __('app.contact submissions') }}</h6>
                        <button onclick="window.history.go(-1); return false;" class="btn btn-danger mb-0 px-3 py-2 d-flex align-items-center justify-content-center gap-2"><i class="fa-solid fa-reply"></i> <span style="height:16px;">{{ __('app.back') }}</span></button>
                    </div>
                </div>
                <div class="card-body px-0 pb-2">
                    <div class="table-responsive px-3">
                        <table id="contact-submissions-table" class="table align-items-center mb-0">
                            <thead>
                                <tr>
                                    <th class="text-secondary font-weight-bolder opacity-7 text-center" style="width:80px">{{ __('app.id') }}</th>
                                    <th class="text-secondary font-weight-bolder opacity-7">{{ __('app.full name') }}</th>
                                    <th class="text-secondary font-weight-bolder opacity-7">{{ __('app.email') }}</th>
                                    <th class="text-secondary font-weight-bolder opacity-7">{{ __('app.subject') }}</th>
                                    <th class="text-secondary font-weight-bolder opacity-7">{{ __('app.submission date') }}</th>
                                    <th class="text-secondary font-weight-bolder opacity-7">{{ __('app.action') }}</th>
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

<!-- Details Modal -->
<div class="modal fade" id="detailsModal" tabindex="-1" aria-labelledby="detailsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-gradient-dark">
                <h5 class="modal-title text-white" id="detailsModalLabel">{{ __('app.contact submission details') }}</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <dl class="row mb-0">
                    <dt class="col-sm-4 text-muted">{{ __('app.id') }}</dt>
                    <dd class="col-sm-8" id="detail-id">—</dd>

                    <dt class="col-sm-4 text-muted">{{ __('app.full name') }}</dt>
                    <dd class="col-sm-8" id="detail-full_name">—</dd>

                    <dt class="col-sm-4 text-muted">{{ __('app.email') }}</dt>
                    <dd class="col-sm-8" id="detail-email">—</dd>

                    <dt class="col-sm-4 text-muted">{{ __('app.phone number') }}</dt>
                    <dd class="col-sm-8" id="detail-phone">—</dd>

                    <dt class="col-sm-4 text-muted">{{ __('app.country') }}</dt>
                    <dd class="col-sm-8" id="detail-country">—</dd>

                    <dt class="col-sm-4 text-muted">{{ __('app.subject') }}</dt>
                    <dd class="col-sm-8" id="detail-subject">—</dd>

                    <dt class="col-sm-4 text-muted">{{ __('app.message') }}</dt>
                    <dd class="col-sm-8" id="detail-message">—</dd>

                    <dt class="col-sm-4 text-muted">{{ __('app.submission date') }}</dt>
                    <dd class="col-sm-8" id="detail-created_at">—</dd>
                </dl>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('app.close') }}</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        handleTable();
    });

    function handleTable() {
        if ($.fn.DataTable.isDataTable('#contact-submissions-table')) {
            $('#contact-submissions-table').DataTable().destroy();
            $('#contact-submissions-table').empty();
        }
        $('#contact-submissions-table').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: "{{ LaravelLocalization::localizeUrl(route('contact-submissions.index')) }}",
                data: {},
                type: "GET"
            },
            order: [[0, 'desc']],
            columns: [
                { data: 'id' },
                { data: 'full_name' },
                { data: 'email' },
                { data: 'subject' },
                { data: 'created_at' },
                { data: 'action', orderable: false, searchable: false },
            ],
            columnDefs: [
                { targets: 0, width: '70px', className: 'text-center' },
                { targets: 5, orderable: false },
            ],
            language: {
                paginate: {
                    previous: '<i class="fa-solid fa-chevron-left"></i>',
                    next: '<i class="fa-solid fa-chevron-right"></i>'
                }
            }
        });
    }

    $(document).on('click', '.btn-view-details', function() {
        var url = $(this).data('url');
        var modal = new bootstrap.Modal(document.getElementById('detailsModal'));
        $.get(url)
            .done(function(data) {
                $('#detail-id').text(data.id);
                $('#detail-full_name').text(data.full_name);
                $('#detail-email').text(data.email);
                $('#detail-phone').text(data.phone);
                $('#detail-country').text(data.country);
                $('#detail-subject').text(data.subject);
                $('#detail-message').text(data.message);
                $('#detail-created_at').text(data.created_at);
                modal.show();
            })
            .fail(function() {
                Swal.fire({
                    title: "{{ __('app.error') }}",
                    text: "{{ __('app.something went wrong') }}",
                    icon: "error",
                    confirmButtonText: "{{ __('app.ok') }}"
                });
            });
    });
</script>
@endpush
