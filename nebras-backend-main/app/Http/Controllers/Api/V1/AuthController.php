<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Student;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Http;
use Illuminate\Validation\ValidationException;
use App\Http\Resources\Api\V1\StudentResource;
use App\Mail\RegistrationMail;
use Illuminate\Support\Facades\Mail;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        // Validate with multi-language messages
        $validator = validator($request->all(), [
            'name'     => 'required|string|max:255',
            'email'    => 'required|string|email|unique:students',
            'password' => 'required|string|min:6',
            'age'      => 'required|integer|min:1',
            'gender'   => 'required|in:0,1',
            'phone'    => 'required|string|max:20',
            'country'    => 'required|string|max:20',
            'class_id' => 'required|exists:classes,id',
        ], [
            'name.required'     => __('messages.name_required'),
            'name.string'       => __('messages.name_string'),
            'name.max'          => __('messages.name_max'),
            'email.required'    => __('messages.email_required'),
            'email.email'       => __('messages.email_invalid'),
            'email.unique'      => __('messages.email_unique'),
            'password.required' => __('messages.password_required'),
            'password.string'   => __('messages.password_string'),
            'password.min'      => __('messages.password_min'),
            'age.required'      => __('messages.age_required'),
            'age.integer'       => __('messages.age_integer'),
            'age.min'           => __('messages.age_min'),
            'gender.required'   => __('messages.gender_required'),
            'gender.in'         => __('messages.gender_in'),
            'phone.required'    => __('messages.phone_required'),
            'phone.string'      => __('messages.phone_string'),
            'phone.required'    => __('messages.phone_required'),
            'country.required'  => __('messages.country_required'),
            'country.string'    => __('messages.country_string'),
            'country.max'       => __('messages.country_max'),
            'class_id.required' => __('messages.class_required'),
            'class_id.exists'   => __('messages.class_exists'),
        ]);

        if ($validator->fails()) {
            return $this->errorResponse(__('messages.validation_failed'), $validator->errors(), 422);
        }

        $student = Student::create([
            'name'     => $request->name,
            'email'    => $request->email,
            'password' => Hash::make($request->password),
            'age'      => $request->age,
            'gender'   => $request->gender,
            'phone'    => $request->phone,
            'country'    => $request->country,
            'class_id' => $request->class_id,
        ]);

        $token = $student->createToken('auth_token')->plainTextToken;

        Mail::to($student->email)->send(new RegistrationMail($student));

        return $this->successResponse(__('messages.student_registered'), [
            'student' => new StudentResource($student),
            'token'   => $token,
        ], 201);
    }


    // Login API
    public function login(Request $request)
    {
        $validator = validator($request->all(), [
            'email'    => 'required|string|email',
            'password' => 'required|string',
        ], [
            'email.required'    => __('messages.email_required'),
            'email.email'       => __('messages.email_invalid'),
            'password.required' => __('messages.password_required'),
            'password.string'   => __('messages.password_string'),
        ]);

        if ($validator->fails()) {
            return $this->errorResponse(__('messages.validation_failed'), $validator->errors(), 422);
        }

        $student = Student::where('email', $request->email)->first();

        if (! $student || ! Hash::check($request->password, $student->password)) {
            return $this->errorResponse(__('messages.invalid_credentials'), [], 401);
        }

        $token = $student->createToken('auth_token')->plainTextToken;

        return $this->successResponse(__('messages.login_successful'), [
            'student' => new StudentResource($student),
            'token'   => $token,
        ]);
    }

    // Google Auth API
    public function authGoogle(Request $request)
    {
        $validator = validator($request->all(), [
            'access_token' => 'required|string',
        ], [
            'access_token.required' => __('messages.validation_failed'),
        ]);

        if ($validator->fails()) {
            return $this->errorResponse(__('messages.validation_failed'), $validator->errors(), 422);
        }

        // Verify access token and get user info from Google
        $response = Http::withToken($request->access_token)
            ->get('https://www.googleapis.com/oauth2/v3/userinfo');

        if (! $response->successful()) {
            return $this->errorResponse(__('messages.invalid_credentials'), [], 401);
        }

        $payload = $response->json();

        if (empty($payload['email']) || empty($payload['email_verified']) || ! $payload['email_verified']) {
            return $this->errorResponse(__('messages.email_invalid'), [], 400);
        }

        $googleId = $payload['sub'] ?? null;
        $email = $payload['email'];
        $name = $payload['name'] ?? $email;

        $student = Student::where('google_id', $googleId)->orWhere('email', $email)->first();

        if ($student) {
            if (empty($student->google_id)) {
                $student->update(['google_id' => $googleId]);
            }
        } else {
            $student = Student::create([
                'name' => $name,
                'email' => $email,
                'password' => Hash::make(Str::random(64)),
                'google_id' => $googleId,
                'age' => 0,
                'gender' => 0,
                'phone' => '',
                'country' => 'QA',
                'class_id' => null,
            ]);

            Mail::to($student->email)->send(new RegistrationMail($student));
        }

        $token = $student->createToken('auth_token')->plainTextToken;

        return $this->successResponse(__('messages.login_successful'), [
            'student' => new StudentResource($student),
            'token' => $token,
        ]);
    }

    // Logout API
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return $this->successResponse(__('messages.logged_out'));
    }

    // Standard success response
    private function successResponse($message, $data = [], $code = 200)
    {
        return response()->json([
            'status'  => true,
            'message' => $message,
            'data'    => $data,
        ], $code);
    }

    // Standard error response
    private function errorResponse($message, $data = [], $code = 400)
    {
        return response()->json([
            'status'  => false,
            'message' => $message,
            'data'    => $data,
        ], $code);
    }
}
