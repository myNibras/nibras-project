<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Installment;
use App\Models\Payment;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            return $this->getAjaxData($request);
        }

        return view('payments.index');
    }

    public function getAjaxData(Request $request)
    {
        $query = Payment::with(['student', 'items.course', 'installments'])->where("status", "success");

        // Search logic
        if ($search = $request->input('search.value')) {
            $query->where(function ($q) use ($search) {
                $q->where('order_id', 'like', "%{$search}%")
                  ->orWhere('status', 'like', "%{$search}%")
                  ->orWhereHas('student', function ($q2) use ($search) {
                      $q2->where('name', 'like', "%{$search}%")
                         ->orWhere('email', 'like', "%{$search}%");
                  })
                  ->orWhereHas('items.course', function ($q3) use ($search) {
                      $q3->where('title', 'like', "%{$search}%")
                         ->orWhere('title_en', 'like', "%{$search}%");
                  });
            });
        }

        // Column mapping
        $columns = [
            'id',
            'student_name',
            'email',
            'order_id',
            'status',
            'paid_amount',
            'discount',
            'paid_at',
            'action',
        ];

        // Sorting logic
        if ($request->has('order.0')) {
            $orderColumnIndex = $request->input('order.0.column');
            $orderDir = $request->input('order.0.dir');
            $columnName = $columns[$orderColumnIndex] ?? 'paid_at';

            if (in_array($columnName, ['student_name', 'email'])) {
                $query->join('students', 'payments.student_id', '=', 'students.id')
                    ->orderBy('students.name', $orderDir);
            } elseif ($columnName === 'discount') {
                // Sort by discount amount
                $query->orderBy('payments.discount_amount', $orderDir);
            } elseif ($columnName === 'paid_amount') {
                // Sort by payment amount
                $query->orderBy('payments.amount', $orderDir);
            } elseif ($columnName === 'paid_at') {
                // Sort by paid_at
                $query->orderBy('payments.paid_at', $orderDir);
            } else {
                $query->orderBy($columnName, $orderDir);
            }
        } else {
            $query->orderBy('paid_at', 'desc');
        }

        // Count totals
        $total = Payment::where("status", "success")->count();
        $filtered = (clone $query)->count();

        // Fetch data with pagination
        $data = $query
            ->skip($request->input('start'))
            ->take($request->input('length'))
            ->get();

        // Format response for DataTables
        $formatted = $data->map(function ($payment) {
            // Discount amount
            $discountAmount = $payment->discount_amount ?? 0;

            // This payment is an "installment payment" (created to pay specific installments via pay-installments flow)
            $isInstallmentPayment = !empty($payment->installment_ids);

            // Calculate paid amount: sum of all payment items (cart items) minus discount
            // This represents the total of all items in the cart after discount
            $paidAmount = $payment->items->sum('total') - $discountAmount;

            return [
                "id"                => $payment->id,
                "is_installment_payment" => $isInstallmentPayment,
                "student_name"      => $payment->student?->name ?? '-',
                "email"             => $payment->student?->email ?? '-',
                "order_id"          => $payment->order_id ?? '-',
                "status"            => ucfirst($payment->status),
                "paid_amount"       => "$".(new \App\Helpers\Helper)->formatNumber($payment->amount),
                "discount"          => "$".(new \App\Helpers\Helper)->formatNumber($discountAmount),
                "paid_at"           => $payment->paid_at ? $payment->paid_at->format('Y-m-d') : '-',
                "action"            => '
                    <a href="javascript:void(0)" class="btn btn-info btn-show-payment me-1" onclick="showPaymentModal('.$payment->id.')" title="'.__('app.view').'">
                        <i class="fa-solid fa-eye"></i>
                    </a>
                ',
            ];
        });


        return response()->json([
            'draw'            => intval($request->input('draw')),
            'recordsTotal'    => $total,
            'recordsFiltered' => $filtered,
            'data'            => $formatted,
        ]);
    }

    public function show($id)
    {
        $payment = Payment::with(['student', 'items.course.academicLevel', 'items.course.teacher', 'items.installments', 'installments', 'coupon'])
            ->findOrFail($id);

        if (request()->ajax()) {

            $helper = new \App\Helpers\Helper;
            $invoiceUrl = $payment->getFirstMediaUrl('invoices') ?: null;

            // Installment payment: fetch data from original payment(s) via installments
            if (!empty($payment->installment_ids)) {
                $paidInstallments = Installment::with(['paymentItem.course.classRoom', 'paymentItem.course.academicLevel', 'paymentItem.course.teacher'])
                    ->whereIn('id', $payment->installment_ids)
                    ->get();

                $paymentItemIds = $paidInstallments->pluck('payment_item_id')->unique()->filter()->values()->toArray();

                // Load ALL installments for those payment items (full schedule per course)
                $allInstallments = Installment::with(['paymentItem.course.classRoom', 'paymentItem.course.academicLevel', 'paymentItem.course.teacher'])
                    ->whereIn('payment_item_id', $paymentItemIds)
                    ->orderBy('payment_item_id')
                    ->orderBy('installment_number')
                    ->get();

                $paidIds = array_flip($payment->installment_ids);
                $thisPaymentInvoiceUrl = $payment->getFirstMediaUrl('invoices') ?: null;

                $firstItem = $paidInstallments->first()?->paymentItem;
                $paymentItemsFromInstallments = $paidInstallments->groupBy('payment_item_id')->map(function ($group) use ($helper) {
                    $item = $group->first()->paymentItem;
                    return [
                        "course_name" => $item?->getLocalizationTitle() ?? '-',
                        "teacher_name" => $item?->course?->teacher?->getLocalizationName() ?? '-',
                        "class" => $item?->course?->classRoom?->getLocalizationName() ?? '-',
                        "payment_type" => 'monthly',
                        "price" => "$".$helper->formatNumber($group->first()->amount),
                    ];
                })->values()->toArray();

                $installmentsByCourse = $allInstallments->groupBy('payment_item_id')->map(function ($group) use ($helper, $paidIds, $thisPaymentInvoiceUrl) {
                    $item = $group->first()->paymentItem;
                    $courseName = $item?->getLocalizationTitle() ?? '-';
                    $firstPaidByThisInGroup = $group->sortBy('installment_number')->first(fn ($i) => isset($paidIds[$i->id]));
                    $installmentsList = $group->sortBy('installment_number')->values()->map(function ($installment) use ($helper, $thisPaymentInvoiceUrl, $firstPaidByThisInGroup) {
                        $invoiceLink = ($firstPaidByThisInGroup && $installment->id === $firstPaidByThisInGroup->id)
                            ? $thisPaymentInvoiceUrl
                            : null;
                        return [
                            "id" => $installment->id,
                            "installment_number" => $installment->installment_number,
                            "amount" => "$".$helper->formatNumber($installment->amount),
                            "due_date" => $installment->due_date->format('Y.m.d'),
                            "due_date_raw" => $installment->due_date->format('Y-m-d'),
                            "status" => $installment->status,
                            "paid_at" => $installment->paid_at ? $installment->paid_at->format('Y.m.d H:i') : null,
                            "invoice_link" => $invoiceLink,
                        ];
                    })->toArray();
                    return [
                        "course_name" => $courseName,
                        "installments" => $installmentsList,
                    ];
                })->values()->toArray();

                $dataToReturn = [
                    "payment_id" => $payment->id,
                    "payment_status" => $payment->status,
                    "payment_amount" => "$".$helper->formatNumber($payment->amount),
                    "invoice_url" => $invoiceUrl,
                    "student_name" => $payment->student?->name ?? '-',
                    "student_phone" => $payment->student?->phone ?? '-',
                    "student_email" => $payment->student?->email ?? '-',
                    "course_name" => $firstItem?->getLocalizationTitle() ?? '-',
                    "teacher_name" => $firstItem?->course?->teacher?->getLocalizationName() ?? '-',
                    "grade_level" => $firstItem?->course?->classRoom?->getLocalizationName() ?? '-',
                    "academic_level" => $firstItem?->course?->academicLevel?->getLocalizationTitle() ?? '-',
                    "transaction_id" => $payment->order_id,
                    "bank_holder_name" => $payment->name_on_card,
                    "bank_issuer" => $payment->bank_issuer,
                    "created_at" => $payment->created_at->format('Y-m-d H:i'),
                    "payment_items" => $paymentItemsFromInstallments,
                    "total_before_discount" => "$".$helper->formatNumber($payment->amount),
                    "discount_amount" => "$0.00",
                    "coupon_code" => null,
                    "discount_percentage" => null,
                    "total_after_discount" => "$".$helper->formatNumber($payment->amount),
                    "installments_by_course" => $installmentsByCourse,
                ];
            } else {
                // Regular (cart) payment
                $dataToReturn = [
                    "payment_id" => $payment->id,
                    "payment_status" => $payment->status,
                    "payment_amount" => "$".$helper->formatNumber($payment->amount),
                    "invoice_url" => $invoiceUrl,
                    "student_name" => $payment->student?->name ?? '-',
                    "student_phone" => $payment->student?->phone ?? '-',
                    "student_email" => $payment->student?->email ?? '-',
                    "course_name" => $payment->items->first()?->getLocalizationTitle() ?? '-',
                    "teacher_name" => $payment->items->first()?->course?->teacher?->getLocalizationName() ?? '-',
                    "grade_level" => $payment->items->first()?->course?->classRoom?->getLocalizationName() ?? '-',
                    "academic_level" => $payment->items->first()?->course?->academicLevel?->getLocalizationTitle() ?? '-',
                    "transaction_id" => $payment->order_id,
                    "bank_holder_name" => $payment->name_on_card,
                    "bank_issuer" => $payment->bank_issuer,
                    "created_at" => $payment->created_at->format('Y-m-d H:i'),
                    "payment_items" => $payment->items->map(function ($item) use ($helper) {
                        return [
                            "course_name" => $item->getLocalizationTitle(),
                            "teacher_name" => $item->course?->teacher?->getLocalizationName() ?? '-',
                            "class" => $item->course?->classRoom?->getLocalizationName() ?? '-',
                            "payment_type" => $item->payment_type,
                            "price" => "$".$helper->formatNumber($item->price),
                        ];
                    })->toArray(),
                    "total_before_discount" => "$".$helper->formatNumber($payment->items->sum('total')),
                    "discount_amount" => "$".$helper->formatNumber($payment->discount_amount ?? 0),
                    "coupon_code" => $payment->coupon?->coupon_code ?? null,
                    "discount_percentage" => $payment->coupon?->discount_percentage ?? null,
                    "total_after_discount" => "$".$helper->formatNumber($payment->amount),
                    "installments_by_course" => $payment->items
                        ->where('payment_type', 'monthly')
                        ->map(function ($item) use ($payment, $helper) {
                            $installments = $item->installments ?? collect();
                            if ($installments->isEmpty()) {
                                $installments = $payment->installments ?? collect();
                            }
                            return [
                                "course_name" => $item->getLocalizationTitle(),
                                "installments" => $installments->sortBy('installment_number')->values()->map(function ($inst, $index) use ($payment) {
                                    $invoiceLink = ($index === 0) ? ($payment->getFirstMediaUrl('invoices') ?: null) : null;
                                    return [
                                        "id" => $inst->id,
                                        "installment_number" => $inst->installment_number,
                                        "amount" => "$".(new \App\Helpers\Helper)->formatNumber($inst->amount),
                                        "due_date" => $inst->due_date->format('Y.m.d'),
                                        "due_date_raw" => $inst->due_date->format('Y-m-d'),
                                        "status" => $inst->status,
                                        "paid_at" => $inst->paid_at ? $inst->paid_at->format('Y.m.d H:i') : null,
                                        "invoice_link" => $invoiceLink,
                                    ];
                                })->toArray()
                            ];
                        })
                        ->filter(fn ($g) => !empty($g['installments']))
                        ->values()
                        ->toArray()
                ];
            }

            return response()->json($dataToReturn);
        }

        return view('payments.show', compact('payment'));
    }

    public function destroy($id)
    {
        $payment = Payment::findOrFail($id);
        $payment->delete();

        return response()->json([
            'status'  => true,
            'message' => __('app.deleted successfully'),
        ]);
    }
}
