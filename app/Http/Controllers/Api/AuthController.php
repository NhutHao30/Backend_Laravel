<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Models\TaiKhoan;
use App\Models\KhachHang;
use Illuminate\Validation\Rules\Password;
use App\Jobs\SendOtpEmailJob;
use Carbon\Carbon;

class AuthController extends Controller
{
    /**
     * API Login
     */
    public function login(Request $request)
    {
        $request->validate([
            'USERNAME' => 'required|string',
            'PASSWORD' => 'required|string',
        ]);

        $credentials = [
            'USERNAME' => $request->USERNAME,
            'password' => $request->PASSWORD,
        ];

        /** @var \Tymon\JWTAuth\JWTGuard $guard */
        $guard = Auth::guard('api');

        if (! $token = $guard->attempt($credentials)) {
            return response()->json(['error' => 'Tài khoản hoặc mật khẩu không chính xác'], 401);
        }

        $user = Auth::guard('api')->user();
        if ($user->MAROLE == 1 || $user->MAROLE == 0 || $user->MAROLE == 2) {
            $nhanVien = \App\Models\NhanVien::where('USERNAME', $user->USERNAME)->first();
            if ($nhanVien && $nhanVien->TRANGTHAI === 'Nghỉ việc') {
                $guard->logout();
                return response()->json(['error' => 'Tài khoản của bạn đã bị vô hiệu hóa'], 403);
            }
        }

        return $this->respondWithToken($token);
    }

    /**
     * Customer Registration API
     */
    public function register(Request $request)
    {
        $request->validate([
            'USERNAME' => 'required|string|unique:taikhoan,USERNAME',
            'PASSWORD' => ['required', 'string', Password::min(12)->mixedCase()->numbers()->symbols()],
            'EMAIL'    => 'required|email|unique:taikhoan,EMAIL',
            'HOTEN'    => 'required|string',
            'SDT'      => 'required|string|size:10',
        ]);

        DB::beginTransaction();
        try {
            // 1. Create an account (Right 2 - Customer)
            $taikhoan = TaiKhoan::create([
                'USERNAME' => $request->USERNAME,
                'PASSWORD' => $request->PASSWORD,
                'MAROLE'   => 3, // 3: Customer
                'EMAIL'    => $request->EMAIL,
            ]);

            // 2. Create Customer Information
            KhachHang::create([
                'MAKH'     => 'KH' . time(),
                'HOTEN'    => $request->HOTEN,
                'SDT'      => $request->SDT,
                'USERNAME' => $taikhoan->USERNAME,
            ]);

            DB::commit();

            /** @var \Tymon\JWTAuth\JWTGuard $guard */
            $guard = Auth::guard('api');
            $token = $guard->login($taikhoan);
            
            return $this->respondWithToken($token);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Đăng ký thất bại, vui lòng thử lại!'], 500);
        }
    }

    /**
     * API to retrieve current user information.
     */
    public function me()
    {
        /** @var \App\Models\TaiKhoan $user */
        $user = Auth::guard('api')->user();
        
        if ($user->MAROLE == 3) {
            $user->load('khachhang');
        } else {
            $user->load('nhanvien');
        }

        return response()->json($user);
    }

    /**
     * API Logout
     */
    public function logout()
    {
        /** @var \Tymon\JWTAuth\JWTGuard $guard */
        $guard = Auth::guard('api');
        $guard->logout();
        
        return response()->json(['message' => 'Đăng xuất thành công']);
    }

    /**
     * API Refresh Tokens
     */
    public function refresh()
    {
        /** @var \Tymon\JWTAuth\JWTGuard $guard */
        $guard = Auth::guard('api');
        
        return $this->respondWithToken($guard->refresh());
    }

    /**
     * API Forgot Password - Send OTP
     */
    public function forgotPassword(Request $request)
    {
        $request->validate(['EMAIL' => 'required|email']);

        $user = TaiKhoan::where('EMAIL', $request->EMAIL)->first();
        if (!$user) {
            return response()->json(['error' => 'Email không tồn tại trong hệ thống'], 404);
        }

        // Generate a 6-digit OTP.
        $otp = rand(100000, 999999);
        $user->update([
            'OTP_CODE' => $otp,
            'OTP_EXPIRES_AT' => Carbon::now()->addMinutes(15)
        ]);

        // Push Jobs into RabbitMQ
        SendOtpEmailJob::dispatch($user->EMAIL, $otp);

        return response()->json(['message' => 'Mã OTP đã được đưa vào hàng đợi để gửi đến email của bạn']);
    }

    /**
     * API Reset password with OTP
     */
    public function resetPassword(Request $request)
    {
        $request->validate([
            'EMAIL' => 'required|email',
            'OTP_CODE' => 'required|string',
            'NEW_PASSWORD' => ['required', 'string', Password::min(12)->mixedCase()->numbers()->symbols()]
        ]);

        $user = TaiKhoan::where('EMAIL', $request->EMAIL)->first();

        if (!$user || $user->OTP_CODE !== $request->OTP_CODE) {
            return response()->json(['error' => 'Mã OTP không hợp lệ'], 400);
        }

        if (Carbon::now()->greaterThan($user->OTP_EXPIRES_AT)) {
            return response()->json(['error' => 'Mã OTP đã hết hạn'], 400);
        }

        // Update your password and delete the OTP.
        $user->update([
            'PASSWORD' => $request->NEW_PASSWORD,
            'OTP_CODE' => null,
            'OTP_EXPIRES_AT' => null
        ]);

        return response()->json(['message' => 'Đặt lại mật khẩu thành công']);
    }

    /**
     * A general function used to format the returned result.
     */
    protected function respondWithToken($token)
    {
        /** @var \Tymon\JWTAuth\JWTGuard $guard */
        $guard = Auth::guard('api');

        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => $guard->factory()->getTTL() * 60,
            'user' => $guard->user()
        ]);
    }
}
