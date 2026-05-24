<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use Illuminate\Http\Request;

class InstallmentController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            return $this->getAjaxData($request);
        }

        return view('installments.index');
    }

    public function getAjaxData(Request $request)
    {
        $query = Payment::with(['student', 'items.course', 'installments'])
            ->where('status', 'success')
            ->whereHas('installments');

        // Search logic
        if ($search = $request->input('search.value')) {
            $query->where(function ($q) use ($search) {
                $q->where('order_id', 'like', "%{$search}%")
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
            'paid_installments',
            'paid_at',
            'action',
        ];

        // Sorting logic
        if ($request->has('order.0')) {
            $orderColumnIndex = $request->input('order.0.column');
            $orderDir = $request->input('order.0.dir');
            $columnName = $columns[$orderColumnIndex] ?? 'id';

            if (in_array($columnName, ['student_name', 'email'])) {
                $query->join('students', 'payments.student_id', '=', 'students.id')
                    ->select('payments.*')
                    ->orderBy('students.name', $orderDir);
            } elseif ($columnName === 'paid_installments') {
                // Sort by paid installments count (subquery or leave default)
                $query->orderBy('payments.id', $orderDir);
            } elseif ($columnName === 'paid_at') {
                $query->orderBy('payments.paid_at', $orderDir);
            } else {
                $query->orderBy('payments.' . ($columnName === 'order_id' ? 'order_id' : 'id'), $orderDir);
            }
        } else {
            $query->orderBy('payments.paid_at', 'desc');
        }

        // Count totals (only payments with installments)
        $total = Payment::where('status', 'success')->whereHas('installments')->count();
        $filtered = (clone $query)->count();

        // Fetch data with pagination
        $data = $query
            ->skip($request->input('start'))
            ->take($request->input('length'))
            ->get();

        // Format response for DataTables
        $formatted = $data->map(function ($payment) {
            $totalInstallments = $payment->installments->count();
            $paidInstallments = $payment->installments->where('status', 'paid')->count();
            $installmentsLabel = $paidInstallments . ' / ' . $totalInstallments;

            return [
                'id' => $payment->id,
                'student_name' => $payment->student?->name ?? '-',
                'email' => $payment->student?->email ?? '-',
                'order_id' => $payment->order_id ?? '-',
                'paid_installments' => $installmentsLabel,
                'paid_at' => $payment->paid_at ? $payment->paid_at->format('Y-m-d') : '-',
                'action' => '
                    <a href="javascript:void(0)" class="btn btn-info btn-show-payment me-1" onclick="showPaymentModal(' . $payment->id . ')" title="' . __('app.view') . '">
                        <i class="fa-solid fa-eye"></i>
                    </a>
                ',
            ];
        });

        return response()->json([
            'draw' => intval($request->input('draw')),
            'recordsTotal' => $total,
            'recordsFiltered' => $filtered,
            'data' => $formatted,
        ]);
    }
}
