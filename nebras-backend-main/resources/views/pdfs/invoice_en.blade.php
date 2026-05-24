<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Nibras Payment Receipt</title>
    <style>
        @page {
            margin: 0;
        }

        body {
            font-family: Arial, sans-serif;
            background: #fff;
            margin: 0;
            padding: 0;
            color: #043458;
        }

        .invoice-box {
            margin: auto;
            box-sizing: border-box;
            position: relative;
            background-image: url("{{ public_path('assets/pdf/ar/images/watermark.png') }}");
            background-repeat: no-repeat;
            background-position: -250px 150px;
            opacity: 1;
            padding: 20mm;
        }

        .content {
            flex: 1;
            display: flex;
            flex-direction: column;
            justify-content: center;
            padding-bottom: 20px;
            position: relative;
            z-index: 2;
            /* keep text above watermark */
        }

        /* Header */
        .header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin: 30px 0;
        }

        .header h1 {
            font-size: 56px;
            margin: 70px 0 10px 0;
            font-weight: normal;
            color: #043458;
        }

        .logo-box {
            text-align: right;
            margin-top: 30px;
        }

        .logo-box img {
            height: 70px;
        }

        .logo-box p {
            margin: 3px 10px 0 0;
            font-size: 14px;
            color: #043458;
        }

        /* Info */
        .info {
            width: 100%; 
            min-width:100%;
            border-collapse: collapse; 
            margin-bottom: 10px;
        }

        .info p {
            margin: 3px 0;
        }

        .info strong {
            font-weight: bold;
        }

        hr.dashed {
            border: none;
            border-top: 1px dashed #999;
            margin: 20px 0 0 0;
        }

        /* Table */
        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 16px;
            margin-bottom: 10px;
        }

        table th,
        table td {
            padding: 15px 8px;
            text-align: left;
        }

        table th {
            border-bottom: 1px dashed #999;
            font-weight: bold;
        }

        /* Total */
        .total {
            text-align: right;
            font-size: 26px;
            font-weight: bold;
            margin-top: 10px;
        }

        /* Footer pinned at bottom */
        .footer {
            margin-top:150px;
            width: 100%;
            border-top: 1px dashed #999;
            padding-top: 8px;
            font-size: 14px;
            color: #333;
            line-height: 1.6;
            text-align: left;
            direction:ltr;
        }
    </style>
</head>

<body>
    <div class="invoice-box" style="background-size: 1500px auto">
        <div class="content">
            @php
                $isNonFirstInstallmentPayment = !empty($payment->installment_ids);
                $displayRows = [];
                if ($isNonFirstInstallmentPayment) {
                    $installments = $installmentsForInvoice ?? \App\Models\Installment::whereIn('id', $payment->installment_ids)
                        ->with('paymentItem.course.classRoom', 'paymentItem.course.teacher')
                        ->get();
                    foreach ($installments->groupBy('payment_item_id') as $paymentItemId => $group) {
                        $paymentItem = $group->first()->paymentItem;
                        if ($paymentItem) {
                            $displayRows[] = (object)[
                                'paymentItem' => $paymentItem,
                                'amount' => $group->sum('amount'),
                            ];
                        }
                    }
                } else {
                    foreach ($payment->items as $paymentItem) {
                        $displayPrice = $paymentItem->total;
                        if ($paymentItem->payment_type === 'monthly' && $paymentItem->installments->isNotEmpty()) {
                            $firstInstallment = $paymentItem->installments->sortBy('installment_number')->first();
                            if ($firstInstallment) {
                                $displayPrice = $firstInstallment->amount;
                            }
                        }
                        $displayRows[] = (object)['paymentItem' => $paymentItem, 'amount' => $displayPrice];
                    }
                }
                $subtotal = $isNonFirstInstallmentPayment
                    ? collect($displayRows)->sum('amount')
                    : $payment->items->sum('total');
                $discountAmount = $payment->discount_amount ?? 0;
                $total = $payment->amount;
            @endphp
            <div class="logo-box">
                <img src="{{ public_path('assets/pdf/en/images/en-logo.png') }}" height="70px" alt="Nibras Logo">
                <p>Nibras Learning Company</p>
            </div>

            <!-- Header -->
            <div class="header">
                <h1>INVOICE</h1>
            </div>

            <!-- Info -->
            <table class="info">
                <tr>
                    <td style="padding: 5px; text-align: left;">
                        <p><strong>Invoice No:</strong> {{ str_pad($payment->id, 6, '0', STR_PAD_LEFT) }}</p>
                    </td>
                    <td style="padding: 5px;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>
                    <td style="padding: 5px;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>
                    <td style="padding: 5px;">
                        <p><strong>Reference No:</strong> {{ $payment->order_id }}</p>
                    </td>
                </tr>
                <tr>
                    <td style="padding: 5px; text-align: left;">
                        <p><strong>Date:</strong> {{ date('d/m/Y', strtotime($payment->paid_at)) }}</p>
                    </td>
                    <td style="padding: 5px;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>
                    <td style="padding: 5px;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>
                    <td style="padding: 5px;">
                        <p><strong>Student Name:</strong> {{ $student->name }}</p>
                    </td>
                </tr>
            </table>

            <hr class="dashed" />

            <!-- Table -->
            <table>
                <thead>
                    <tr>
                        <th>Course Name</th>
                        <th>Grade</th>
                        <th>Date</th>
                        <th>Teacher Name</th>
                        <th>Payment Type</th>
                        <th>Price</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($displayRows as $row)
                    <tr>
                        <td>{{ $row->paymentItem->title_en }}</td>
                        <td>{{ $row->paymentItem->course->classRoom->name_en }}</td>
                        <td>{{ date('d/m/Y', strtotime($payment->paid_at)) }}</td>
                        <td>{{ $row->paymentItem->course->teacher->name_en }}</td>
                        <td>{{ $row->paymentItem->payment_type === 'monthly' ? 'Installments' : 'Cash' }}</td>
                        <td>${{ (new \App\Helpers\Helper)->formatNumber($row->amount) }}</td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="5"><strong>Subtotal</strong></td>
                        <td>${{ (new \App\Helpers\Helper)->formatNumber($subtotal) }}</td>
                    </tr>
                    <tr>
                        <td colspan="5"><strong>@if($payment->coupon) Discount (Coupon {{ $payment->coupon->coupon_code }} - {{ (new \App\Helpers\Helper)->formatNumber($payment->coupon->discount_percentage) }}%) @else Discount @endif</strong></td>
                        <td>-${{ (new \App\Helpers\Helper)->formatNumber($discountAmount) }}</td>
                    </tr>
                    <tr>
                        <td colspan="5"><strong>Total</strong></td>
                        <td><strong>${{ (new \App\Helpers\Helper)->formatNumber($total) }}</strong></td>
                    </tr>
                </tfoot>
            </table>

            <hr class="dashed" />

            <!-- Total -->
            <div class="total">
                Total: ${{ (new \App\Helpers\Helper)->formatNumber($payment->amount) }}
            </div>
        </div>

        <!-- Footer (always bottom of page) -->
        <div class="footer">
            <p>notifier@mynibras.com</p>
            <p>+97430102626</p>
            <p>www.mynibras.com</p>
        </div>
    </div>
</body>

</html>