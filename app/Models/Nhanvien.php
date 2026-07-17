<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NhanVien extends Model
{
    protected $table = 'nhanvien';
    protected $primaryKey = 'USERNAME';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'USERNAME',
        'HOTEN',
        'NGAYSINH',
        'GioiTinh',
        'DIACHI',
        'SDT',
        'CHUCVU',
        'LUONG',
    ];

    public function taikhoan()
    {
        return $this->belongsTo(TaiKhoan::class, 'USERNAME', 'USERNAME');
    }

    public function hdnhaps()
    {
        return $this->hasMany(HdNhap::class, 'USERNAME', 'USERNAME');
    }

    public function cuoctrochuyens()
    {
        return $this->hasMany(CuocTroChuyen::class, 'USERNAME_NV', 'USERNAME');
    }
}
