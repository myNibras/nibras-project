@extends('layouts.app')
@section('title'){{ __('app.faqs') }}@endsection

@push('styles')
<style>
    #faqs-table tbody tr.draggable-row { cursor: default; }
    #faqs-table .drag-handle { font-size: 1.1rem; padding: 0 .5rem; user-select: none; }
    #faqs-table .drag-handle:hover { color: #344767 !important; }
    #faqs-table tbody tr.sortable-ghost { opacity: 0.4; background: #e9f3ff !important; }
    #faqs-table tbody tr.sortable-chosen { background: #f8f9fa !important; }
    #faqs-table tbody tr.sortable-drag { background: #fff !important; box-shadow: 0 6px 18px rgba(0,0,0,.12); }
    #reorder-status { font-size: .85rem; }
</style>
@endpush

@section('content')
<div class="container-fluid py-2">
    <div class="row">
        <div class="col-12">
            <div class="card my-4">
                <div class="card-header p-0 position-relative mt-n4 mx-3 z-index-2">
                    <div class="bg-gradient-dark shadow-dark border-radius-lg pt-4 pb-3 d-flex justify-content-between align-items-center px-3">
                        <div class="d-flex align-items-center gap-2">
                            <h6 class="text-white text-capitalize mb-0">{{ __('app.faqs') }}</h6>
                            <button type="button" class="btn text-white px-2 py-1" onclick="openAdditionalInfoModal()" title="{{ __('app.edit additional information') }}" style="min-width: 36px; height: 36px; display: flex; align-items: center; justify-content: center;">
                                <i class="fa-solid fa-pen-to-square"></i>
                            </button>
                            <span id="reorder-status" class="text-white ms-2"></span>
                        </div>
                        <div class="d-flex gap-3">
                            <a href="{{ route('faqs.create') }}" class="btn btn-primary mb-0 px-3 py-2 d-flex align-items-center justify-content-center gap-2"><i class="fa-solid fa-plus"></i> <span style="height:16px;">{{ __('app.add new') }}</span></a>
                            <button onclick="window.history.go(-1); return false;" class="btn btn-danger mb-0 px-3 py-2 d-flex align-items-center justify-content-center gap-2"><i class="fa-solid fa-reply"></i> <span style="height:16px;">{{ __('app.back') }}</span></button>
                        </div>
                    </div>
                </div>
                <div class="card-body px-0 pb-2">
                    <div class="px-3 pb-2 text-secondary" style="font-size: .9rem;">
                        <i class="fa-solid fa-circle-info"></i>
                        {{ __('app.drag rows by the handle to reorder faqs') }}
                    </div>
                    <div class="table-responsive px-3">
                        <table id="faqs-table" class="table align-items-center mb-0">
                            <thead>
                                <tr>
                                    <th class="text-secondary font-weight-bolder opacity-7 text-center" style="width:50px"></th>
                                    <th class="text-secondary font-weight-bolder opacity-7">{{ __('app.question') }}</th>
                                    <th class="text-secondary font-weight-bolder opacity-7">{{ __('app.answer') }}</th>
                                    <th class="text-secondary font-weight-bolder opacity-7">{{ __('app.status') }}</th>
                                    <th class="text-secondary font-weight-bolder opacity-7">{{ __('app.created at') }}</th>
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

<!-- Toast Container -->
<div class="toast-container position-fixed bottom-0 end-0 p-3" id="toastContainer"></div>
@endsection

@push('scripts')
<!-- SortableJS for drag-and-drop row reordering -->
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.2/Sortable.min.js"></script>
<script>
    function showToast(message, type = 'success') {
        const toastContainer = document.getElementById('toastContainer');
        const toastId = 'toast-' + Date.now();
        const typeClasses = {
            'success': 'text-bg-success',
            'error': 'text-bg-danger',
            'warning': 'text-bg-warning',
            'info': 'text-bg-info'
        };
        const toastClass = typeClasses[type] || typeClasses['success'];
        const toastHTML = `
            <div id="${toastId}" class="toast align-items-center ${toastClass} border-0 show mb-2" role="alert" aria-live="assertive" aria-atomic="true">
                <div class="d-flex">
                    <div class="toast-body text-white">${message}</div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
                </div>
            </div>
        `;
        toastContainer.insertAdjacentHTML('beforeend', toastHTML);
        const toastElement = document.getElementById(toastId);
        const toast = new bootstrap.Toast(toastElement, { autohide: true, delay: 5000 });
        toast.show();
        toastElement.addEventListener('hidden.bs.toast', function() { toastElement.remove(); });
    }

    let sortableInstance = null;

    $(document).ready(function() {
        handleTable();
    });

    function handleTable(){
        if ($.fn.DataTable.isDataTable('#faqs-table')) {
            $('#faqs-table').DataTable().destroy();
        }
        const table = $('#faqs-table').DataTable({
            processing: true,
            serverSide: true,
            ordering: false,        // disable column sorting — manual order rules
            paging: false,          // show all rows so drag-drop spans the entire list
            info: false,
            ajax: {
                "url": "{{ LaravelLocalization::localizeUrl(route('faqs.index')) }}",
                "data": {},
                "type": "GET"
            },
            columns: [
                { data: 'drag', orderable: false, searchable: false, className: 'text-center align-middle' },
                { data: 'question' },
                { data: 'answer' },
                { data: 'status', orderable: false, searchable: false },
                { data: 'created_at' },
                { data: 'action', orderable: false, searchable: false },
            ],
            columnDefs: [
                { targets: 0, width: '50px' },
                { targets: 1, className: 'ps-4 py-2 pe-2' },
                { targets: 2, className: 'ps-4 py-2 pe-2' },
                { targets: 3, className: 'ps-4 py-2 pe-2' },
                { targets: 4, className: 'ps-4 py-2 pe-2' },
                { targets: 5, className: 'ps-4 py-2 pe-2' },
            ],
            language: {
                paginate: {
                    previous: '<i class="fa-solid fa-chevron-left"></i>',
                    next: '<i class="fa-solid fa-chevron-right"></i>'
                }
            },
            createdRow: function(row, data) {
                $(row).addClass('draggable-row');
            },
            drawCallback: function() {
                initSortable();
            }
        });
    }

    function initSortable() {
        const tbody = document.querySelector('#faqs-table tbody');
        if (!tbody) return;
        if (sortableInstance) { sortableInstance.destroy(); sortableInstance = null; }

        sortableInstance = Sortable.create(tbody, {
            handle: '.drag-handle',
            animation: 150,
            ghostClass: 'sortable-ghost',
            chosenClass: 'sortable-chosen',
            dragClass: 'sortable-drag',
            onEnd: function() {
                const ids = $('#faqs-table tbody tr').map(function() {
                    return $(this).data('id');
                }).get().filter(function(v) { return v !== undefined && v !== null && v !== ''; });

                if (!ids.length) return;
                saveOrder(ids);
            }
        });
    }

    function saveOrder(ids) {
        const $status = $('#reorder-status');
        $status.html('<i class="fa-solid fa-circle-notch fa-spin"></i> {{ __('app.saving') }}...');
        $.ajax({
            url: "{{ route('faqs.reorder') }}",
            type: 'POST',
            data: {
                _token: $('meta[name="csrf-token"]').attr('content'),
                ids: ids
            },
            success: function(response) {
                $status.html('<i class="fa-solid fa-check"></i> {{ __('app.saved') }}');
                showToast(response.message || '{{ __('app.order updated successfully') }}', 'success');
                setTimeout(function(){ $status.html(''); }, 2000);
            },
            error: function() {
                $status.html('<i class="fa-solid fa-triangle-exclamation"></i> {{ __('app.something went wrong') }}');
                showToast('{{ __('app.something went wrong') }}', 'error');
            }
        });
    }

    function openAdditionalInfoModal() {
        $.ajax({
            url: "{{ route('faqs.additional-info.get', ['type' => 'faq']) }}",
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
            url: "{{ route('faqs.additional-info.store') }}",
            type: 'POST',
            data: $(this).serialize(),
            success: function(response) {
                var modal = bootstrap.Modal.getInstance(document.getElementById('additionalInfoModal'));
                modal.hide();
                showToast(response.message || '{{ __('app.saved successfully') }}', 'success');
            },
            error: function(xhr) {
                if (xhr.status === 422) {
                    var errors = '';
                    if (xhr.responseJSON && xhr.responseJSON.errors) {
                        $.each(xhr.responseJSON.errors, function(key, value) {
                            errors += '<li>' + value[0] + '</li>';
                        });
                    }
                    $('#additionalInfoErrors').removeClass('d-none').html('<ul class="mb-0">' + errors + '</ul>');
                } else {
                    $('#additionalInfoErrors').removeClass('d-none').html('<ul class="mb-0"><li>' + (xhr.responseJSON?.message || '{{ __('app.something went wrong') }}') + '</li></ul>');
                }
            }
        });
    });
</script>
@endpush
