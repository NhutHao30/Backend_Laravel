<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChiTietHdNhap extends Model
{
    protected $table = 'chitiethdnhap';
    public $incrementing = false;
    protected $primaryKey = null; // Composite primary key

    protected $fillable = [
        'MAHDNHAP',
        'MASP',
        'SOLUONGTCT',
        'SOLUONGTN',
        'DONGIANHAP',
        'GHICHU',
    ];

    public function hdnhap()
    {
        return $this->belongsTo(HdNhap::class, 'MAHDNHAP', 'MAHDNHAP');
    }

    public function sanpham()
    {
        return $this->belongsTo(SanPham::class, 'MASP', 'MASP');
    }
}
