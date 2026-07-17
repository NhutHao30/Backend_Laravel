<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChiTietGioHang extends Model
{
    protected $table = 'chitietgiohang';
    public $incrementing = false;
    protected $primaryKey = null; // Composite key

    protected $fillable = [
        'MAGIOHANG',
        'MASP',
        'SOLUONG',
        'DonGia',
        'GHICHU',
    ];

    public function giohang()
    {
        return $this->belongsTo(GioHang::class, 'MAGIOHANG', 'MAGIOHANG');
    }

    public function sanpham()
    {
        return $this->belongsTo(SanPham::class, 'MASP', 'MASP');
    }
}
