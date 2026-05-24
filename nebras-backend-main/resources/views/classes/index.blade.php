@extends('layouts.app')
@section('title'){{ __('app.classes') }}@endsection

@section('content')
<div class="container-fluid py-2">
    <div class="row">
        <div class="col-12">
            <div class="card my-4">
                <div class="card-header p-0 position-relative mt-n4 mx-3 z-index-2">
                    <div class="bg-gradient-dark shadow-dark border-radius-lg pt-4 pb-3 d-flex justify-content-between align-items-center px-3 d-flex justify-content-between align-items-center px-3">
                        <h6 class="text-white text-capitalize mb-0">{{ __('app.classes') }}</h6>
                        <div class="d-flex gap-3">
                            <a href="{{ route('classes.create') }}" class="btn btn-primary mb-0 px-3 py-2 d-flex align-items-center justify-content-center gap-2"><i class="fa-solid fa-plus"></i> <span style="height:16px;">{{ __('app.add new') }}</span></a>
                            <button onclick="window.history.go(-1); return false;" class="btn btn-danger mb-0 px-3 py-2 d-flex align-items-center justify-content-center gap-2"><i class="fa-solid fa-reply"></i> <span style="height:16px;">{{ __('app.back') }}</span></button>
                        </div>
                    </div>
                </div>
                <div class="card-body px-0 pb-2">
                    <div class="table-responsive px-3">
                        <table id="classes-table" class="table align-items-center mb-0">
                            <thead>
                                <tr>
                                    <th class="text-secondary font-weight-bolder opacity-7 text-center" style="width:100px">{{ __('app.id')}}</th>
                                    <th class="text-secondary font-weight-bolder opacity-7">{{ __('app.title')}}</th>
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
    $(document).ready(function() {
        handleTable();
    });

    function handleTable(){
        $("#classes-table").dataTable().fnDestroy();
        $('#classes-table').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                "url": "{{ LaravelLocalization::localizeUrl(route('classes.index')) }}",
                "data": {},
                "type": "GET"
            },
            order: [[0, 'desc']],
            columns: [
                { data: 'id' },
                { data: 'name' },
                { data: 'created_at' },
                { data: 'action', orderable: false, searchable: false },
            ],
            columnDefs: [
                { targets: 0, width: '70px', className: 'text-center p-0'},
                { targets: 1, className: 'ps-4 py-2 pe-2' },
                { targets: 2, className: 'ps-4 py-2 pe-2' },
                { targets: 3, className: 'ps-4 py-2 pe-2' }
            ],
            language: {
                paginate: {
                    previous: '<i class="fa-solid fa-chevron-left"></i>',
                    next: '<i class="fa-solid fa-chevron-right"></i>'
                }
            }
        });
    }
</script>
@endpush
