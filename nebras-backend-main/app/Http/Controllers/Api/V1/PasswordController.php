<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Student;
use Illuminate\Support\Facades\Mail;
use App\Mail\SendOtpMail;

class PasswordController extends Controller
{
    // Send OTP to email
    public function sendOtp(Request $request)
    {
        $request->validate(['email' => 'required|email|exists:students,email']);

        $student = Student::where('email', $request->email)->first();

        $otp = rand(100000, 999999);

        $student->update([
            'otp_code' => $otp,
            'otp_expires_at' => now()->addMinutes(10),
        ]);

        Mail::to($student->email)->send(new SendOtpMail($otp, $student));

        return response()->json([
            'status' => true,
            'message' => 'OTP sent to email',
        ]);
    }

    // Verify OTP
    public function verifyOtp(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:students,email',
            'otp_code' => 'required|digits:6',
        ]);

        $student = Student::where('email', $request->email)
            ->where('otp_code', $request->otp_code)
            ->where('otp_expires_at', '>=', now())
            ->first();

        if (!$student) {
            return response()->json([
                'status' => false,
                'message' => 'Invalid or expired OTP',
            ], 400);
        }

        return response()->json([
            'status' => true,
            'message' => 'OTP verified successfully',
        ]);
    }

    // Reset password
    public function resetPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:students,email',
            'otp_code' => 'required|digits:6',
            'password' => 'required|string|confirmed|min:6',
        ]);

        $student = Student::where('email', $request->email)
            ->where('otp_code', $request->otp_code)
            ->where('otp_expires_at', '>=', now())
            ->first();

        if (!$student) {
            return response()->json([
                'status' => false,
                'message' => 'Invalid or expired OTP',
            ], 400);
        }

        $student->update([
            'password' => $request->password,
            'otp_code' => null,
            'otp_expires_at' => null,
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Password reset successfully',
        ]);
    }
}
