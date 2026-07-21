<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class KhachHang extends Model
{
    use SoftDeletes;

    protected $table = 'khachhang';
    protected $primaryKey = 'MAKH';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'MAKH',
        'HOTEN',
        'NGAYSINH',
        'SDT',
        'GioiTinh',
        'DIACHI',
        'DIEMTICHLUY',
        'USERNAME',
        'MACUAHANG',
    ];

    public function taikhoan()
    {
        return $this->belongsTo(TaiKhoan::class, 'USERNAME', 'USERNAME');
    }

    public function diachis()
    {
        return $this->hasMany(DiaChi::class, 'MAKH', 'MAKH');
    }

    public function hdbans()
    {
        return $this->hasMany(HdBan::class, 'MAKH', 'MAKH');
    }

    public function giohang()
    {
        return $this->hasOne(GioHang::class, 'MAKH', 'MAKH');
    }

    public function binhluans()
    {
        return $this->hasMany(BinhLuan::class, 'MAKH', 'MAKH');
    }

    public function cuoctrochuyens()
    {
        return $this->hasMany(CuocTroChuyen::class, 'MAKH', 'MAKH');
    }

    public function cuahang()
    {
        return $this->belongsTo(CuaHang::class, 'MACUAHANG', 'MACUAHANG');
    }
}
