<?php
namespace App\Http\Controllers\API;

use Illuminate\Support\Facades\Mail;
use App\Mail\ResetPasswordMail;
use Carbon\Carbon;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Facades\JWTAuth;
use App\Models\UserToken;
use App\Constants\HttpStatusCode;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $validated = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        if (Auth::attempt(['email' => $validated['email'], 'password' => $validated['password']])) {
            $user = Auth::user();

            $otp = rand(100000, 999999);

            $user->otp = $otp;
            $user->otp_expiry = Carbon::now()->addMinutes(5);
            $user->save();

            try {
                Mail::to($user->email)->send(new \App\Mail\SendOtpMail($otp));

                return response()->json([
                    'status' => HttpStatusCode::SUCCESS,
                    'message' => 'OTP sent to your email address.',
                ]);
            } catch (\Exception $e) {
                return response()->json([
                    'status' => HttpStatusCode::INTERNAL_SERVER_ERROR,
                    'message' => 'Error sending OTP: ' . $e->getMessage(),
                ]);
            }
        } else {
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid email or password',
            ]);
        }
    }


    public function verifyOtp(Request $request)
    {
        try {
            $request->validate([
                'email' => 'required|email',
                'otp' => 'required|digits:6',
            ]);



            $user = User::where('email', $request->email)->first();

            if (!$user) {
                return response()->json([
                    'status' => HttpStatusCode::NOT_FOUND,
                    'message' => 'OTP not generated'

                ]);
            }
            if (!$user->email) {
                return response()->json([
                    'status' => HttpStatusCode::BAD_REQUEST,
                    'message' => 'User email is not found'
                ]);
            }


            if ($user->otp === $request->otp && Carbon::now()->lessThan($user->otp_expiry)) {
                $token = JWTAuth::fromUser($user);
                UserToken::create([
                    'user_id' => $user->id,
                    'token' => $token,
                ]);

                $user->otp = null;
                $user->otp_expiry = null;
                $user->save();

                return response()->json([
                    'status' => HttpStatusCode::SUCCESS,
                    'message' => 'User logged in successfully',
                    'data' => $user,
                    'token' => $token

                ]);

            } else {
                return response()->json([
                    'status' => HttpStatusCode::BAD_REQUEST,
                    'message' => 'Invalid or expired OTP',
                ]);
            }
        } catch (\Exception $e) {
            return response()->json([
                'status' => HttpStatusCode::BAD_REQUEST,
                'message' => 'An error occurred while verifying OTP: ' . $e->getMessage(),
            ]);
        }
    }


    public function logout(Request $request)
    {
        try {
            $token = JWTAuth::getToken();

            if (!$token) {
                return response()->json([
                    'status' => HttpStatusCode::BAD_REQUEST,
                    'message' => 'Token is required.'
                ]);
            }

            $user = Auth::user();

            if (!$user) {
                return response()->json([
                    'status' => HttpStatusCode::UNAUTHORIZED,
                    'message' => 'User not authenticated.'
                ]);
            }

            JWTAuth::invalidate($token);

            UserToken::where('user_id', $user->id)
                ->where('token', $token)
                ->delete();

            return response()->json([
                'status' => HttpStatusCode::SUCCESS,
                'message' => 'User logged out successfully.'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => HttpStatusCode::BAD_REQUEST,
                'message' => 'Logout failed: ' . $e->getMessage(),
            ]);
        }
    }



    public function forgotPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
        ]);

        $token = Str::random(64);

        DB::table('password_resets')->updateOrInsert(
            ['email' => $request->email],
            [
                'email' => $request->email,
                'token' => $token,
                'created_at' => Carbon::now()
            ]
        );

        try {
            Mail::to($request->email)->send(new ResetPasswordMail($token));
            return response()->json([
                HttpStatusCode::SUCCESS,
                'message' => 'Reset password link sent to your email.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 400,
                'message' => 'Error sending email: ' . $e->getMessage()
            ]);
        }
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email|exists:users,email',
            'password' => 'required|string|min:6',
        ]);

        $record = DB::table('password_resets')
            ->where('token', $request->token)
            ->where('email', $request->email)
            ->first();

        if (!$record) {
            return response()->json([
                'status' => 400,
                'message' => 'Invalid token or email.'
            ]);
        }

        if (Carbon::parse($record->created_at)->addHour()->isPast()) {
            return response()->json([
                'status' => 400,
                'message' => 'Token has expired.'
            ]);
        }

        $user = User::where('email', $request->email)->first();
        $user->update(['password' => Hash::make($request->password)]);
        $user->save();

        DB::table('password_resets')->where('email', $request->email)->delete();

        return response()->json([
            HttpStatusCode::SUCCESS,
            'message' => 'Password reset successfully.'
        ]);
    }




}