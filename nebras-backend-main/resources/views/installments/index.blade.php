@extends('layouts.app')
@section('title'){{ __('app.installments') }}@endsection

@section('content')
<div class="container-fluid py-2">
    <div class="row">
        <div class="col-12">
            <div class="card my-4">
                <div class="card-header p-0 position-relative mt-n4 mx-3 z-index-2">
                    <div class="bg-gradient-dark shadow-dark border-radius-lg pt-4 pb-3 d-flex justify-content-between align-items-center px-3">
                        <h6 class="text-white text-capitalize mb-0">{{ __('app.installments') }}</h6>
                        <div class="d-flex gap-3">
                            <button onclick="window.history.go(-1); return false;" class="btn btn-danger mb-0 px-3 py-2 d-flex align-items-center justify-content-center gap-2"><i class="fa-solid fa-reply"></i> <span style="height:16px;">{{ __('app.back') }}</span></button>
                        </div>
                    </div>
                </div>
                <div class="card-body px-0 pb-2">
                    <div class="table-responsive px-3">
                        <table id="installments-table" class="table align-items-center mb-0">
                            <thead>
                                <tr>
                                    <th class="text-secondary font-weight-bolder opacity-7 text-center" style="width:100px">{{ __('app.id') }}</th>
                                    <th class="text-secondary font-weight-bolder opacity-7">{{ __('app.student name') }}</th>
                                    <th class="text-secondary font-weight-bolder opacity-7">{{ __('app.email') }}</th>
                                    <th class="text-secondary font-weight-bolder opacity-7">{{ __('app.transaction id') }}</th>
                                    <th class="text-secondary font-weight-bolder opacity-7">{{ __('app.paid installments') }}</th>
                                    <th class="text-secondary font-weight-bolder opacity-7">{{ __('app.paid at') }}</th>
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

{{-- Payment Details Modal (same as payments index) --}}
<div class="modal fade" id="paymentModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    Payment #<span id="payment-id"></span>
                    <span id="payment-status" class="badge ms-2" style="padding-top:13px;"></span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body">
                <ul class="nav nav-tabs" role="tablist">
                    <li class="nav-item">
                        <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#student" type="button">Student Info</button>
                    </li>
                    <li class="nav-item">
                        <button class="nav-link" data-bs-toggle="tab" data-bs-target="#course" type="button">Course Info</button>
                    </li>
                    <li class="nav-item">
                        <button class="nav-link" data-bs-toggle="tab" data-bs-target="#transaction" type="button">Transaction Info</button>
                    </li>
                    <li class="nav-item">
                        <button class="nav-link" data-bs-toggle="tab" data-bs-target="#payment-items" type="button">Payment Items</button>
                    </li>
                    <li class="nav-item">
                        <button class="nav-link" data-bs-toggle="tab" data-bs-target="#installments" type="button">Installments</button>
                    </li>
                </ul>

                <div class="tab-content mt-3">
                    <div class="tab-pane fade show active" id="student">
                        <dl class="row">
                            <dt class="col-sm-3">Name</dt>
                            <dd class="col-sm-9" id="student-name"></dd>
                            <dt class="col-sm-3">Phone</dt>
                            <dd class="col-sm-9" id="student-phone"></dd>
                            <dt class="col-sm-3">Email</dt>
                            <dd class="col-sm-9" id="student-email"></dd>
                        </dl>
                    </div>

                    <div class="tab-pane fade" id="course">
                        <dl class="row">
                            <dt class="col-sm-3">Course</dt>
                            <dd class="col-sm-9" id="course-name"></dd>
                            <dt class="col-sm-3">Teacher</dt>
                            <dd class="col-sm-9" id="teacher-name"></dd>
                            <dt class="col-sm-3">Class level</dt>
                            <dd class="col-sm-9" id="grade-level"></dd>
                            <dt class="col-sm-3">Academic Level</dt>
                            <dd class="col-sm-9" id="academic-level"></dd>
                        </dl>
                    </div>

                    <div class="tab-pane fade" id="transaction">
                        <dl class="row">
                            <dt class="col-sm-3">Transaction ID</dt>
                            <dd class="col-sm-9" id="transaction-id"></dd>
                            <dt class="col-sm-3">Bank Holder</dt>
                            <dd class="col-sm-9" id="bank-holder-name"></dd>
                            <dt class="col-sm-3">Bank Issuer</dt>
                            <dd class="col-sm-9" id="bank-issuer"></dd>
                            <dt class="col-sm-3">Created At</dt>
                            <dd class="col-sm-9" id="created-at"></dd>
                        </dl>
                    </div>

                    <div class="tab-pane fade" id="payment-items">
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Course Name</th>
                                        <th>Teacher</th>
                                        <th>Class</th>
                                        <th>Payment Type</th>
                                        <th>Price</th>
                                    </tr>
                                </thead>
                                <tbody id="payment-items-tbody"></tbody>
                            </table>
                        </div>
                    </div>

                    <div class="tab-pane fade" id="installments">
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Amount</th>
                                        <th>Due Date</th>
                                        <th>Status</th>
                                        <th>Paid At</th>
                                    </tr>
                                </thead>
                                <tbody id="installments-tbody"></tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <div class="modal-footer">
                <h6 class="me-auto">
                    Total Amount: <span id="payment-amount" class="fw-bold text-success"></span> USD
                </h6>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    var paymentDetailUrl = "{{ LaravelLocalization::localizeUrl(route('payments.show', ['id' => 1])) }}";

    $(document).ready(function() {
        handleTable();
    });

    function handleTable() {
        if ($.fn.DataTable.isDataTable('#installments-table')) {
            $('#installments-table').DataTable().destroy();
        }
        $('#installments-table').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: "{{ LaravelLocalization::localizeUrl(route('installments.index')) }}",
                data: {},
                type: "GET"
            },
            order: [[0, 'desc']],
            columns: [
                { data: 'id' },
                { data: 'student_name' },
                { data: 'email' },
                { data: 'order_id' },
                { data: 'paid_installments' },
                { data: 'paid_at' },
                { data: 'action', orderable: false, searchable: false },
            ],
            columnDefs: [
                { targets: 0, width: '70px', className: 'text-center p-0' },
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

    function showPaymentModal(paymentId) {
        var url = paymentDetailUrl.replace(/\/1$/, '/' + paymentId);
        $.ajax({
            url: url,
            type: "GET",
            success: function (payment) {
                $("#payment-id").text(payment.payment_id);
                $("#payment-status")
                    .removeClass()
                    .addClass("badge " + (payment.payment_status === "pending" ? "bg-warning" : "bg-success"))
                    .text(payment.payment_status);
                $("#payment-amount").text(payment.payment_amount);

                $("#student-name").text(payment.student_name || "-");
                $("#student-phone").text(payment.student_phone || "-");
                $("#student-email").text(payment.student_email || "-");

                $("#course-name").text(payment.course_name || "-");
                $("#teacher-name").text(payment.teacher_name || "-");
                $("#grade-level").text(payment.grade_level || "-");
                $("#academic-level").text(payment.academic_level || "-");

                $("#transaction-id").text(payment.transaction_id || "-");
                $("#bank-holder-name").text(payment.bank_holder_name || "-");
                $("#bank-issuer").text(payment.bank_issuer || "-");
                $("#created-at").text(payment.created_at || "-");

                var paymentItemsHtml = "";
                if (payment.payment_items && payment.payment_items.length > 0) {
                    payment.payment_items.forEach(function(item) {
                        var paymentTypeLabel = item.payment_type === 'monthly'
                            ? '<span class="badge bg-info">Monthly</span>'
                            : '<span class="badge bg-primary">One-off</span>';
                        paymentItemsHtml += '<tr><td>' + (item.course_name || "-") + '</td><td>' + (item.teacher_name || "-") + '</td><td>' + (item.class || "-") + '</td><td>' + paymentTypeLabel + '</td><td>' + (item.price || "-") + '</td></tr>';
                    });
                    paymentItemsHtml += '<tr class="table-secondary"><td colspan="4" class="text-end fw-bold">Total Before Discount:</td><td class="fw-bold">' + (payment.total_before_discount || "$0.00") + '</td></tr>';
                    paymentItemsHtml += '<tr class="table-secondary"><td colspan="4" class="text-end fw-bold">Discount' + (payment.coupon_code ? ' (' + payment.coupon_code + ' - ' + (payment.discount_percentage || 0) + '%)' : '') + ':</td><td class="fw-bold">' + (payment.discount_amount || "$0.00") + '</td></tr>';
                    paymentItemsHtml += '<tr class="table-success"><td colspan="4" class="text-end fw-bold">Total After Discount:</td><td class="fw-bold">' + (payment.total_after_discount || "$0.00") + '</td></tr>';
                } else {
                    paymentItemsHtml = '<tr><td colspan="5" class="text-center">No items available</td></tr>';
                }
                $("#payment-items-tbody").html(paymentItemsHtml);

                var installmentsHtml = "";
                if (payment.installments_by_course && payment.installments_by_course.length > 0) {
                    payment.installments_by_course.forEach(function(courseGroup) {
                        installmentsHtml += '<tr class="table-primary"><td colspan="5" class="fw-bold">' + (courseGroup.course_name || "-") + '</td></tr>';
                        if (courseGroup.installments && courseGroup.installments.length > 0) {
                            courseGroup.installments.forEach(function(installment) {
                                var statusBadge = installment.status === 'paid' ? '<span class="badge bg-success">Paid</span>' : installment.status === 'overdue' ? '<span class="badge bg-danger">Overdue</span>' : '<span class="badge bg-warning">Pending</span>';
                                installmentsHtml += '<tr><td>' + installment.installment_number + '</td><td>' + installment.amount + '</td><td>' + installment.due_date + '</td><td>' + statusBadge + '</td><td>' + (installment.paid_at || "-") + '</td></tr>';
                            });
                        } else {
                            installmentsHtml += '<tr><td colspan="5" class="text-center text-muted">No installments for this course</td></tr>';
                        }
                    });
                } else {
                    installmentsHtml = '<tr><td colspan="5" class="text-center">No installments available</td></tr>';
                }
                $("#installments-tbody").html(installmentsHtml);

                $("#paymentModal").modal("show");
            }
        });
    }
</script>
@endpush
