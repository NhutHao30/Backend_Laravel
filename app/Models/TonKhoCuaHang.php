<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TonKhoCuaHang extends Model
{
    protected $table = 'tonkho_cuahang';
    public $incrementing = false; // Primary key is composite, Eloquent doesn't fully support composite PKs natively but we can manage
    protected $primaryKey = null;

    protected $fillable = [
        'MACUAHANG',
        'MASP',
        'SOLUONG_TON'
    ];

    public function cuahang()
    {
        return $this->belongsTo(CuaHang::class, 'MACUAHANG', 'MACUAHANG');
    }

    public function sanpham()
    {
        return $this->belongsTo(SanPham::class, 'MASP', 'MASP');
    }
}
