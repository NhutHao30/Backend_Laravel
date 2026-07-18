<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Tymon\JWTAuth\Contracts\JWTSubject;

class TaiKhoan extends Authenticatable implements JWTSubject
{
    use Notifiable;

    protected $table = 'taikhoan';
    protected $primaryKey = 'USERNAME';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'USERNAME',
        'PASSWORD',
        'MAROLE',
        'EMAIL',
        'OTP_CODE',
        'OTP_EXPIRES_AT',
        'DEVICE_TOKEN',
    ];

    protected $hidden = [
        'PASSWORD',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'PASSWORD' => 'hashed', // Tự động mã hóa mật khẩu bằng Bcrypt
            'OTP_EXPIRES_AT' => 'datetime',
        ];
    }

    // --- JWT ---
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getAuthPassword()
    {
        return $this->PASSWORD;
    }

    public function getJWTCustomClaims()
    {
        return [
            'role' => $this->MAROLE,
            'email' => $this->EMAIL
        ];
    }

    // --- MỐI QUAN HỆ ---

    public function vaitro()
    {
        return $this->belongsTo(VaiTro::class, 'MAROLE', 'MAROLE');
    }

    public function nhanvien()
    {
        return $this->hasOne(NhanVien::class, 'USERNAME', 'USERNAME');
    }

    public function khachhang()
    {
        return $this->hasOne(KhachHang::class, 'USERNAME', 'USERNAME');
    }
}
