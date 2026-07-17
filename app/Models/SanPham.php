<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SanPham extends Model
{
    use SoftDeletes;

    protected $table = 'sanpham';
    protected $primaryKey = 'MASP';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'MASP',
        'TENSP',
        'NSX',
        'HSD',
        'DVT',
        'GIABAN',
        'SOLUONG',
        'GHICHU',
        'HINHANH',
        'MALOAI',
    ];

    public function loaisanpham()
    {
        return $this->belongsTo(LoaiSanPham::class, 'MALOAI', 'MALOAI');
    }

    public function giamgia()
    {
        return $this->hasOne(GiamGia::class, 'MASP', 'MASP');
    }

    public function chitiethdnhaps()
    {
        return $this->hasMany(ChiTietHdNhap::class, 'MASP', 'MASP');
    }

    public function chitiethdbans()
    {
        return $this->hasMany(ChiTietHdBan::class, 'MASP', 'MASP');
    }

    public function chitietgiohangs()
    {
        return $this->hasMany(ChiTietGioHang::class, 'MASP', 'MASP');
    }
}
