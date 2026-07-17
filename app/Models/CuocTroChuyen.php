<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CuocTroChuyen extends Model
{
    protected $table = 'cuoctrochuyen';
    protected $primaryKey = 'MACUOCTROCHUYEN';

    protected $fillable = [
        'MAKH',
        'USERNAME_NV',
        'TRANGTHAI',
    ];

    public function khachhang()
    {
        return $this->belongsTo(KhachHang::class, 'MAKH', 'MAKH');
    }

    public function nhanvien()
    {
        return $this->belongsTo(NhanVien::class, 'USERNAME_NV', 'USERNAME');
    }

    public function tinnhans()
    {
        return $this->hasMany(TinNhan::class, 'MACUOCTROCHUYEN', 'MACUOCTROCHUYEN');
    }
}
