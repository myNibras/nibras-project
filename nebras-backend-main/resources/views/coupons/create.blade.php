@extends('layouts.app')
@section('title'){{ __('app.coupons') }}@endsection

@section('content')
<div class="container-fluid py-2">
    <div class="row">
        <div class="col-12">
            <div class="card my-4">
                <div class="card-header p-0 position-relative mt-n4 mx-3 z-index-2">
                    <div class="bg-gradient-dark shadow-dark border-radius-lg pt-4 pb-3 d-flex justify-content-between align-items-center px-3">
                        <h6 class="text-white text-capitalize px-3">{{ __('app.coupons') }} - {{ __('app.add new') }}</h6>
                        <button onclick="window.history.go(-1); return false;" class="btn btn-danger mb-0 px-3 py-2 d-flex align-items-center justify-content-center gap-2"><i class="fa-solid fa-reply"></i> <span style="height:16px;">{{ __('app.back') }}</span></button>
                    </div>
                </div>
                <div class="card-body px-0 pb-2">
                    
                    {{-- Validation Errors --}}
                    @if ($errors->any())
                        <div class="alert alert-danger mx-4">
                            <ul class="mb-0 text-white">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form action="{{ route('coupons.store') }}" method="POST">
                        @csrf
                        <div class="row mx-3 row-gap-3 w-100">

                            <div class="row">
                                {{-- Coupon Code --}}
                                <div class="col-md-6">
                                    <label for="coupon_code" class="form-label fw-bold">{{ __('app.coupon code') }} <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <input type="text" name="coupon_code" id="coupon_code" 
                                            class="form-control" value="{{ old('coupon_code') }}" 
                                            placeholder="{{ __('app.coupon code') }}" required>
                                        <button type="button" class="btn btn-outline-primary" id="generate-code-btn" onclick="generateCouponCode()">
                                            <i class="fa-solid fa-shuffle"></i> {{ __('app.generate') }}
                                        </button>
                                    </div>
                                </div>

                                {{-- Discount Percentage --}}
                                <div class="col-md-6">
                                    <label for="discount_percentage" class="form-label fw-bold">{{ __('app.discount percentage') }} (%) <span class="text-danger">*</span></label>
                                    <input type="number" name="discount_percentage" id="discount_percentage" 
                                        class="form-control" value="{{ old('discount_percentage') }}" 
                                        min="0.01" max="100" step="0.01" required>
                                </div>
                            </div>

                            <div class="row mt-3">
                                {{-- Expiry Date --}}
                                <div class="col-md-6">
                                    <label for="expiry_date" class="form-label fw-bold">{{ __('app.expiry date') }} <span class="text-danger">*</span></label>
                                    <input type="date" name="expiry_date" id="expiry_date" 
                                        class="form-control" value="{{ old('expiry_date') }}" 
                                        min="{{ date('Y-m-d') }}" required>
                                </div>

                                {{-- Student --}}
                                <div class="col-md-6">
                                    <label for="student_id" class="form-label fw-bold">{{ __('app.student') }} ({{ __('app.optional') }})</label>
                                    <select name="student_id" id="student_id" class="form-control select2 handle-select2">
                                        <option value="">{{ __('app.select') }}</option>
                                        @foreach($students as $student)
                                            <option value="{{ $student->id }}" {{ old('student_id') == $student->id ? 'selected' : '' }}>
                                                {{ $student->name }} ({{ $student->email }})
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="row mt-3">
                                {{-- Number of Coupons --}}
                                <div class="col-md-6" id="number_of_coupons_wrapper" style="display: none;">
                                    <label for="number_of_coupons" class="form-label fw-bold">{{ __('app.number of coupons') }} <span class="text-danger">*</span></label>
                                    <input type="number" name="number_of_coupons" id="number_of_coupons" 
                                        class="form-control" value="{{ old('number_of_coupons', 0) }}" 
                                        min="0" step="1">
                                </div>

                                {{-- Limit Usage --}}
                                <div class="col-md-6">
                                    <label for="limit_usage" class="form-label fw-bold">{{ __('app.limit usage') }} <span class="text-danger">*</span></label>
                                    <input type="number" name="limit_usage" id="limit_usage" 
                                        class="form-control" value="{{ old('limit_usage', 1) }}" 
                                        min="0" step="1" required 
                                        {{ old('student_id') ? 'readonly' : '' }}>
                                </div>
                            </div>

                            <div class="row mt-3">
                                {{-- Status --}}
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">{{ __('app.status') }}</label>
                                    <div class="form-check form-switch mt-2">
                                        <input class="form-check-input" type="checkbox" 
                                            name="status" id="status" value="1" 
                                            {{ old('status') ? 'checked' : '' }}>
                                        <label class="form-check-label" for="status">
                                            {{ __('app.active') }}
                                        </label>
                                    </div>
                                </div>
                            </div>
                            
                        </div>

                        {{-- Submit --}}
                        <div class="d-flex justify-content-end mt-4 gap-3 mx-3 gap-3">
                            <a href="{{ route('coupons.index') }}" class="btn btn-secondary me-2">
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
        // Initialize Select2 for student dropdown
        $('#student_id').select2({
            placeholder: "{{ __('app.select') }}",
            allowClear: true,
            language: {
                noResults: function() {
                    return "{{ __('app.no results found') }}";
                },
                searching: function() {
                    return "{{ __('app.searching') }}...";
                }
            }
        });

        // Show/hide number_of_coupons field and toggle limit_usage based on student selection
        function toggleNumberOfCoupons() {
            var studentId = $('#student_id').val();
            if (studentId && studentId !== '') {
                $('#number_of_coupons_wrapper').show();
                $('#number_of_coupons').prop('required', true);
                // If student is selected, set limit_usage to 1 and make it readonly
                $('#limit_usage').val(1).prop('readonly', true);
            } else {
                $('#number_of_coupons_wrapper').hide();
                $('#number_of_coupons').prop('required', false);
                $('#number_of_coupons').val(0);
                // If student is not selected, allow editing limit_usage
                $('#limit_usage').prop('readonly', false);
            }
        }

        // Check on page load if student is already selected
        toggleNumberOfCoupons();

        // Listen for changes in student dropdown
        $('#student_id').on('change', function() {
            toggleNumberOfCoupons();
        });

    });

    function generateCouponCode() {
        $.ajax({
            url: "{{ LaravelLocalization::localizeUrl(route('coupons.generate-code')) }}",
            type: 'GET',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                if (response.status === 'success') {
                    $('#coupon_code').val(response.code);
                }
            },
            error: function() {
                toastr.error('{{ __('app.failed to generate code') }}');
            }
        });
    }
</script>
@endpush

