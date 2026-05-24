<?php

namespace App\Http\Controllers;

use App\Models\Coupon;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CouponController extends Controller
{
    /**
     * Display a listing of coupons.
     */
    public function index(Request $request)
    {
        if (request()->ajax()) {
            return $this->getAjaxData($request);
        }
        return view('coupons.index');
    }

    public function getAjaxData(Request $request)
    {
        $query = Coupon::query();

        // Search logic
        if ($search = $request->input('search.value')) {
            $query->where(function ($q) use ($search) {
                $q->where('coupon_code', 'like', "%{$search}%")
                    ->orWhere('discount_percentage', 'like', "%{$search}%")
                    ->orWhere('expiry_date', 'like', "%{$search}%")
                    ->orWhere('created_at', 'like', "%{$search}%");
            });
        }

        // Column mapping for sorting
        $columns = [
            'id',
            'coupon_code',
            'discount_percentage',
            'expiry_date',
            'type',
            'owner_student',
            'used_by',
            'used_total',
            'used_date',
            'status',
            'created_at',
            'action',
        ];

        // Sorting logic
        if ($request->has('order.0')) {
            $orderColumnIndex = $request->input('order.0.column');
            $orderDir = $request->input('order.0.dir');
            $columnName = $columns[$orderColumnIndex] ?? 'id';
            if ($columnName === 'created_at' || $columnName === 'expiry_date') {
                $query->orderBy($columnName, $orderDir);
            } else {
                $query->orderBy($columnName, $orderDir);
            }
        } else {
            $query->orderBy('created_at', 'desc');
        }

        // Count totals (cloned for filtering)
        $total = Coupon::count();
        $filtered = (clone $query)->count();

        // Fetch data with pagination and relationships
        $data = $query
            ->with(['student', 'ownerCoupon.student', 'payments' => function($q) {
                $q->where('status', 'success')
                  ->with('student')
                  ->orderBy('created_at', 'desc');
            }])
            ->skip($request->input('start'))
            ->take($request->input('length'))
            ->get();

        // Format response
        $formatted = $data->map(function ($coupon) {
            $viewUrl = route('coupons.show', $coupon->id);
            $editUrl = route('coupons.edit', $coupon->id);
            $deleteUrl = route('coupons.destroy', $coupon->id);
            
            $checked = ($coupon->status) ? "checked" : "";
            $message = ($coupon->status) ? __('app.are you sure you want to deactivate this coupon?') : __('app.are you sure you want to activate this coupon?');
            
            // Check if expired
            $isExpired = $coupon->expiry_date < now()->toDateString();
            $statusBadge = $isExpired 
                ? '<span class="badge bg-danger">' . __('app.expired') . '</span>'
                : ($coupon->status 
                    ? '<span class="badge bg-success">' . __('app.active') . '</span>' 
                    : '<span class="badge bg-secondary">' . __('app.inactive') . '</span>');

            // Type badge
            $couponType = $coupon->type ?? 'general';
            if ($couponType === 'owner') {
                $typeBadge = '<span class="badge bg-primary">' . __('app.owner') . '</span>';
            } elseif ($couponType === 'gift') {
                $typeBadge = '<span class="badge bg-info">' . __('app.gift') . '</span>';
            } else {
                $typeBadge = '<span class="badge bg-success">' . __('app.general') . '</span>';
            }

            // Owner Student - Show student name if coupon type is owner and has student_id
            // For gift coupons, show the owner coupon's student
            $ownerStudent = '-';
            if ($couponType === 'owner' && $coupon->student) {
                $ownerStudent = $coupon->student->name;
            } elseif ($couponType === 'gift' && $coupon->ownerCoupon && $coupon->ownerCoupon->student) {
                $ownerStudent = $coupon->ownerCoupon->student->name;
            }

            // Used By - Show last student who used it if limit_usage > 1
            $usedBy = '-';
            $usedDate = '-';
            $successfulPayments = $coupon->payments->where('status', 'success');
            $usedCount = $successfulPayments->count();
            
            if ($usedCount > 0) {
                $lastPayment = $successfulPayments->first(); // Already ordered by created_at desc
                if ($lastPayment && $lastPayment->student) {
                    $usedBy = $lastPayment->student->name;
                    $usedDate = $lastPayment->created_at->format('Y-m-d');
                }
            }

            // Used/Total - Show usage count vs limit
            $usedTotal = $usedCount . '/' . ($coupon->limit_usage ?? '∞');

            return [
                "id" => $coupon->id,
                "coupon_code" => '<strong>' . $coupon->coupon_code . '</strong>',
                "discount_percentage" => $coupon->discount_percentage . '%',
                "expiry_date" => $coupon->expiry_date->format('Y-m-d'),
                "type" => $typeBadge,
                "owner_student" => $ownerStudent,
                "used_by" => $usedBy,
                "used_total" => $usedTotal,
                "used_date" => $usedDate,
                "status" => "<div class='form-check form-switch mt-2'>
                    <input class='form-check-input update-status' 
                        name='status' 
                        type='checkbox'
                        value='1' 
                        role='switch' 
                        id='status-$coupon->id'
                        data-table='coupons'
                        data-id='$coupon->id'
                        data-message='$message'
                        $checked
                    />
                </div>",
                "status_badge" => $statusBadge,
                "created_at" => $coupon->created_at->format('Y-m-d'),
                "action" => '
                    <a href="'.$viewUrl.'" class="btn btn-info me-1" title="'.__('app.view').'">
                        <i class="fa-solid fa-eye"></i>
                    </a>
                    <a href="'.$editUrl.'" class="btn btn-primary me-1" title="'.__('app.edit').'">
                        <i class="fa-solid fa-pen-to-square"></i>
                    </a>
                    <button type="button" class="btn btn-danger btn-delete" data-table="coupons" data-id="'.$coupon->id.'" title="'.__('app.delete').'">
                        <i class="fa-solid fa-trash"></i>
                    </button>
                ',
            ];
        });

        return response()->json([
            'draw' => intval($request->input('draw')),
            'recordsTotal' => $total,
            'recordsFiltered' => $filtered,
            'data' => $formatted
        ]);
    }

    /**
     * Show the form for creating a new coupon.
     */
    public function create()
    {
        $students = Student::orderBy('name')->get();
        return view('coupons.create', compact('students'));
    }

    /**
     * Store a newly created coupon.
     */
    public function store(Request $request)
    {
        $rules = [
            'coupon_code' => 'required|string|max:50|unique:coupons,coupon_code',
            'discount_percentage' => 'required|numeric|gt:0|max:100',
            'expiry_date' => 'required|date|after_or_equal:today',
            'status' => 'nullable|boolean',
            'student_id' => 'nullable|exists:students,id',
            'number_of_coupons' => 'nullable|integer|min:0',
            'limit_usage' => 'required|integer|min:0',
        ];

        // If student is selected, number_of_coupons is required
        if ($request->filled('student_id')) {
            $rules['number_of_coupons'] = 'required|integer|min:0';
        }

        $request->validate($rules, [
            'coupon_code.required' => __('messages.coupon_code_required'),
            'coupon_code.string' => __('messages.coupon_code_string'),
            'coupon_code.max' => __('messages.coupon_code_max'),
            'coupon_code.unique' => __('messages.coupon_code_unique'),
            'discount_percentage.required' => __('messages.discount_percentage_required'),
            'discount_percentage.numeric' => __('messages.discount_percentage_numeric'),
            'discount_percentage.gt' => __('messages.discount_percentage_gt'),
            'discount_percentage.max' => __('messages.discount_percentage_max'),
            'expiry_date.required' => __('messages.expiry_date_required'),
            'expiry_date.date' => __('messages.expiry_date_date'),
            'expiry_date.after_or_equal' => __('messages.expiry_date_after_or_equal'),
            'student_id.exists' => __('messages.student_id_exists'),
            'number_of_coupons.required' => __('messages.number_of_coupons_required'),
            'number_of_coupons.integer' => __('messages.number_of_coupons_integer'),
            'number_of_coupons.min' => __('messages.number_of_coupons_min'),
            'limit_usage.required' => __('messages.number_of_coupons_required'),
            'limit_usage.integer' => __('messages.number_of_coupons_integer'),
            'limit_usage.min' => __('messages.number_of_coupons_min'),
        ]);

        // Create the main coupon - set type based on student_id
        $couponType = $request->student_id ? 'owner' : 'general';
        // If student is selected, limit_usage must be 1
        $limitUsage = $request->student_id ? 1 : ($request->limit_usage ?? 1);
        
        $coupon = Coupon::create([
            'coupon_code' => strtoupper($request->coupon_code),
            'discount_percentage' => $request->discount_percentage,
            'expiry_date' => $request->expiry_date,
            'status' => $request->has('status') ? true : false,
            'type' => $couponType,
            'new_students' => 0,
            'student_id' => $request->student_id,
            'number_of_coupons' => $request->student_id ? ($request->number_of_coupons ?? 0) : null,
            'limit_usage' => $limitUsage,
        ]);

        // If number_of_coupons > 0, create gift coupons
        $numberOfCoupons = $request->student_id ? ($request->number_of_coupons ?? 0) : 0;
        if ($numberOfCoupons > 0) {
            for ($i = 0; $i < $numberOfCoupons; $i++) {
                // Generate unique coupon code
                do {
                    $code = strtoupper(Str::random(8));
                } while (Coupon::where('coupon_code', $code)->exists());

                Coupon::create([
                    'coupon_code' => $code,
                    'discount_percentage' => $request->discount_percentage,
                    'expiry_date' => $request->expiry_date,
                    'status' => $request->has('status') ? true : false,
                    'type' => 'gift',
                    'coupon_id' => $coupon->id,
                    'new_students' => 1,
                    'student_id' => null,
                    'number_of_coupons' => null,
                    'limit_usage' => 1,
                ]);
            }
        }

        return redirect()->route('coupons.index')->with('success', __('Coupon created successfully.'));
    }

    /**
     * Display the specified coupon.
     */
    public function show(Coupon $coupon)
    {
        return view('coupons.show', compact('coupon'));
    }

    /**
     * Show the form for editing the coupon.
     */
    public function edit(Coupon $coupon)
    {
        $students = Student::orderBy('name')->get();
        return view('coupons.edit', compact('coupon', 'students'));
    }

    /**
     * Update the specified coupon.
     */
    public function update(Request $request, Coupon $coupon)
    {
        // Only validate the fields that can be edited
        $rules = [
            'discount_percentage' => 'required|numeric|gt:0|max:100',
            'expiry_date' => 'required|date',
            'status' => 'nullable|boolean',
        ];

        $request->validate($rules, [
            'discount_percentage.required' => __('messages.discount_percentage_required'),
            'discount_percentage.numeric' => __('messages.discount_percentage_numeric'),
            'discount_percentage.gt' => __('messages.discount_percentage_gt'),
            'discount_percentage.max' => __('messages.discount_percentage_max'),
            'expiry_date.required' => __('messages.expiry_date_required'),
            'expiry_date.date' => __('messages.expiry_date_date'),
        ]);

        // Only allow editing: discount_percentage, expiry_date, and status
        // All other fields remain unchanged
        $coupon->update([
            'discount_percentage' => $request->discount_percentage,
            'expiry_date' => $request->expiry_date,
            'status' => $request->has('status') ? true : false,
        ]);

        return redirect()->route('coupons.index')->with('success', __('Coupon updated successfully.'));
    }

    /**
     * Remove the specified coupon.
     */
    public function destroy(Coupon $coupon)
    {
        try {
            // Check if coupon has been used in any payment
            if ($coupon->hasBeenUsed()) {
                return response()->json([
                    'status' => 'error',
                    'message' => __('messages.coupon_cannot_be_deleted_used')
                ], 400);
            }

            $coupon->delete();

            return response()->json([
                'status' => 'success',
                'message' => __('app.deleted successfully')
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => __('Something went wrong while deleting the coupon.')
            ], 500);
        }
    }

    /**
     * Change coupon status
     */
    public function change_status($id)
    {
        try {
            $coupon = Coupon::findOrFail($id);
            $coupon->status = !$coupon->status;
            $coupon->save();

            return response()->json(['status' => 'success']);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'failure',
                'error'  => $e->getMessage()
            ]);
        }
    }

    /**
     * Generate a random coupon code
     */
    public function generateCode()
    {
        do {
            $code = strtoupper(Str::random(8));
        } while (Coupon::where('coupon_code', $code)->exists());

        return response()->json([
            'status' => 'success',
            'code' => $code
        ]);
    }
}
