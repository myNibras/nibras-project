<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
    <meta charset="UTF-8">
    <title>إيصال دفع نبراس</title>
    <style>
        @page {
            margin: 0;
        }

        @font-face {
            font-family: 'Amiri';
            src: url({{ public_path('fonts/Amiri-Regular.ttf') }}) format('truetype'); /* Use your font file path */
            font-weight: normal;
            font-style: normal;
        }
        body {
            font-family: 'Amiri', sans-serif;
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
            text-align: left;
            margin-top: 30px;
        }

        .logo-box img {
            height: 70px;
            width: 100%;
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
            text-align: right;
        }

        table th {
            border-bottom: 1px dashed #999;
            font-weight: bold;
        }

        /* Total */
        .total {
            text-align: left;
            font-size: 24px;
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
        .text-right{
            text-align:right;
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
            <div class="logo-box text-right">
                <img src="{{ public_path('assets/pdf/ar/images/ar-logo.png') }}" height="70px" alt="شعار نبراس">
                <p>شركة نبراس التعليمية</p>
            </div>

            <!-- Header -->
            <div class="header">
                <h1>فاتورة</h1>
            </div>

            <table class="info">
                <tr>
                    <td style="padding: 5px; text-align: right;"><p><strong>رقم الفاتورة:</strong> {{ str_pad($payment->id, 6, '0', STR_PAD_LEFT) }}</p></td>
                    <td style="padding: 5px;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>
                    <td style="padding: 5px;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>
                    <td style="padding: 5px;"><p><strong>المرجع:</strong> {{ $payment->order_id }}</p></td>
                </tr>
                <tr>
                    <td style="padding: 5px; text-align: right;"><p><strong>التاريخ:</strong> {{ date('d/m/Y', strtotime($payment->paid_at)) }}</p></td>
                    <td style="padding: 5px;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>
                    <td style="padding: 5px;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>
                    <td style="padding: 5px;"><p><strong>اسم الطالب:</strong> {{ $student->name }}</p></td>
                </tr>
            </table>

            <hr class="dashed" />

            <!-- Table -->
            <table>
                <thead>
                    <tr>
                        <th>اسم الدورة</th>
                        <th>الصف</th>
                        <th>التاريخ</th>
                        <th>اسم المعلم</th>
                        <th>نوع الدفع</th>
                        <th>السعر</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($displayRows as $row)
                    <tr>
                        <td>{{ $row->paymentItem->title }}</td>
                        <td>{{ $row->paymentItem->course->classRoom->name }}</td>
                        <td>{{ date('d/m/Y', strtotime($payment->paid_at)) }}</td>
                        <td>{{ $row->paymentItem->course->teacher->name }}</td>
                        <td>{{ $row->paymentItem->payment_type === 'monthly' ? 'أقساط' : 'كاش' }}</td>
                        <td>{{ (new \App\Helpers\Helper)->formatNumber($row->amount) }}$</td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="5"><strong>المجموع الفرعي</strong></td>
                        <td>{{ (new \App\Helpers\Helper)->formatNumber($subtotal) }}$</td>
                    </tr>
                    <tr>
                        <td colspan="5"><strong>@if($payment->coupon) الخصم (كوبون {{ $payment->coupon->coupon_code }} - {{ (new \App\Helpers\Helper)->formatNumber($payment->coupon->discount_percentage) }}%) @else الخصم @endif</strong></td>
                        <td>-{{ (new \App\Helpers\Helper)->formatNumber($discountAmount) }}$</td>
                    </tr>
                    <tr>
                        <td colspan="5"><strong>الإجمالي</strong></td>
                        <td><strong>{{ (new \App\Helpers\Helper)->formatNumber($total) }}$</strong></td>
                    </tr>
                </tfoot>
            </table>

            <hr class="dashed" />

            <!-- Total -->
            <div class="total">
                الإجمالي: {{ (new \App\Helpers\Helper)->formatNumber($payment->amount) }}$
            </div>
        </div>

        <!-- Footer -->
        <div class="footer">
            <p>notifier@mynibras.com</p>
            <p>+97430102626</p>
            <p>www.mynibras.com</p>
        </div>
    </div>
</body>

</html>