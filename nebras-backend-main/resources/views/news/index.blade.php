@extends('layouts.app')
@section('title'){{ __('app.news') }}@endsection

@section('content')
<div class="container-fluid py-2">
    <div class="row">
        <div class="col-12">
            <div class="card my-4">
                <div class="card-header p-0 position-relative mt-n4 mx-3 z-index-2">
                    <div class="bg-gradient-dark shadow-dark border-radius-lg pt-4 pb-3 d-flex justify-content-between align-items-center px-3">
                        <div class="d-flex align-items-center gap-2">
                            <h6 class="text-white text-capitalize mb-0">{{ __('app.news') }}</h6>
                            <button type="button" class="btn text-white px-2 py-1" onclick="openAdditionalInfoModal()" title="{{ __('app.edit additional information') }}" style="min-width: 36px; height: 36px; display: flex; align-items: center; justify-content: center;">
                                <i class="fa-solid fa-pen-to-square"></i>
                            </button>
                        </div>
                        <div class="d-flex gap-3">
                            <a href="{{ route('news.create') }}" class="btn btn-primary mb-0 px-3 py-2 d-flex align-items-center justify-content-center gap-2"><i class="fa-solid fa-plus"></i> <span style="height:16px;">{{ __('app.add new') }}</span></a>
                            <button onclick="window.history.go(-1); return false;" class="btn btn-danger mb-0 px-3 py-2 d-flex align-items-center justify-content-center gap-2"><i class="fa-solid fa-reply"></i> <span style="height:16px;">{{ __('app.back') }}</span></button>
                        </div>
                    </div>
                </div>
                <div class="card-body px-0 pb-2">
                    <div class="table-responsive px-3">
                        <table id="news-table" class="table align-items-center mb-0">
                            <thead>
                                <tr>
                                    <th class="text-secondary font-weight-bolder opacity-7 text-center" style="width:100px">{{ __('app.id')}}</th>
                                    <th class="text-secondary font-weight-bolder opacity-7">{{ __('app.title')}}</th>
                                    <th class="text-secondary font-weight-bolder opacity-7">{{ __('app.small description')}}</th>
                                    <th class="text-secondary font-weight-bolder opacity-7">{{ __('app.expiry date')}}</th>
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

<!-- Additional Information Modal -->
<div class="modal fade" id="additionalInfoModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">{{ __('app.additional information') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="additionalInfoForm">
                @csrf
                <div class="modal-body">
                    <div id="additionalInfoErrors" class="alert alert-danger d-none"></div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="additional_title" class="form-label fw-bold">{{ __('app.title') }} (AR)</label>
                            <input type="text" name="title" id="additional_title" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label for="additional_title_en" class="form-label fw-bold">{{ __('app.title') }} (EN)</label>
                            <input type="text" name="title_en" id="additional_title_en" class="form-control" required>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="additional_description" class="form-label fw-bold">{{ __('app.description') }} (AR)</label>
                            <textarea name="description" id="additional_description" class="form-control" rows="5"></textarea>
                        </div>
                        <div class="col-md-6">
                            <label for="additional_description_en" class="form-label fw-bold">{{ __('app.description') }} (EN)</label>
                            <textarea name="description_en" id="additional_description_en" class="form-control" rows="5"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('app.cancel') }}</button>
                    <button type="submit" class="btn btn-primary">{{ __('app.save') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="toast-container position-fixed bottom-0 end-0 p-3" id="toastContainer"></div>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        handleTable();
    });

    function handleTable(){
        $("#news-table").dataTable().fnDestroy();
        $('#news-table').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                "url": "{{ LaravelLocalization::localizeUrl(route('news.index')) }}",
                "data": {},
                "type": "GET"
            },
            order: [[0, 'desc']],
            columns: [
                { data: 'id' },
                { data: 'title' },
                { data: 'small_description' },
                { data: 'expiry_date' },
                { data: 'status' },
                { data: 'created_at' },
                { data: 'action', orderable: false, searchable: false },
            ],
            columnDefs: [
                { targets: 0, width: '70px', className: 'text-center p-0'},
                { targets: 1, className: 'ps-4 py-2 pe-2' },
                { targets: 2, className: 'ps-4 py-2 pe-2' },
                { targets: 3, className: 'ps-4 py-2 pe-2' },
                { targets: 4, className: 'ps-4 py-2 pe-2' },
                { targets: 5, className: 'ps-4 py-2 pe-2' },
                { targets: 6, className: 'ps-4 py-2 pe-2' }
            ],
            language: {
                paginate: {
                    previous: '<i class="fa-solid fa-chevron-left"></i>',
                    next: '<i class="fa-solid fa-chevron-right"></i>'
                }
            }
        });
    }

    function showToast(message, type) {
        type = type || 'success';
        var container = document.getElementById('toastContainer');
        var id = 'toast-' + Date.now();
        var cls = type === 'error' ? 'text-bg-danger' : 'text-bg-success';
        container.insertAdjacentHTML('beforeend', '<div id="' + id + '" class="toast align-items-center ' + cls + ' border-0 show mb-2" role="alert"><div class="d-flex"><div class="toast-body text-white">' + message + '</div><button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button></div></div>');
        var el = document.getElementById(id);
        var toast = new bootstrap.Toast(el, { autohide: true, delay: 5000 });
        toast.show();
        el.addEventListener('hidden.bs.toast', function() { el.remove(); });
    }

    function openAdditionalInfoModal() {
        $.ajax({
            url: "{{ route('news.additional-info.get', ['type' => 'news']) }}",
            type: 'GET',
            success: function(response) {
                if (response.data) {
                    $('#additional_title').val(response.data.title || '');
                    $('#additional_title_en').val(response.data.title_en || '');
                    $('#additional_description').val(response.data.description || '');
                    $('#additional_description_en').val(response.data.description_en || '');
                } else {
                    $('#additionalInfoForm')[0].reset();
                }
                $('#additionalInfoErrors').addClass('d-none').html('');
                var modal = new bootstrap.Modal(document.getElementById('additionalInfoModal'));
                modal.show();
            },
            error: function() {
                $('#additionalInfoForm')[0].reset();
                $('#additionalInfoErrors').addClass('d-none').html('');
                var modal = new bootstrap.Modal(document.getElementById('additionalInfoModal'));
                modal.show();
            }
        });
    }

    $('#additionalInfoForm').on('submit', function(e) {
        e.preventDefault();
        $('#additionalInfoErrors').addClass('d-none').html('');
        $.ajax({
            url: "{{ route('news.additional-info.store') }}",
            type: 'POST',
            data: $(this).serialize(),
            success: function(response) {
                var modal = bootstrap.Modal.getInstance(document.getElementById('additionalInfoModal'));
                modal.hide();
                showToast(response.message || '{{ __('app.saved successfully') }}', 'success');
            },
            error: function(xhr) {
                if (xhr.status === 422 && xhr.responseJSON && xhr.responseJSON.errors) {
                    var errors = '';
                    $.each(xhr.responseJSON.errors, function(key, value) { errors += '<li>' + value[0] + '</li>'; });
                    $('#additionalInfoErrors').removeClass('d-none').html('<ul class="mb-0">' + errors + '</ul>');
                } else {
                    $('#additionalInfoErrors').removeClass('d-none').html('<ul class="mb-0"><li>' + (xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : '{{ __('app.something went wrong') }}') + '</li></ul>');
                }
            }
        });
    });
</script>
@endpush

