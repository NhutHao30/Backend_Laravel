<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GioHang extends Model
{
    protected $table = 'giohang';
    protected $primaryKey = 'MAGIOHANG';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'MAGIOHANG',
        'MAKH',
        'NGAYTAO',
    ];

    public function khachhang()
    {
        return $this->belongsTo(KhachHang::class, 'MAKH', 'MAKH');
    }

    public function chitiets()
    {
        return $this->hasMany(ChiTietGioHang::class, 'MAGIOHANG', 'MAGIOHANG');
    }
}
