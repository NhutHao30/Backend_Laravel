<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BinhLuan extends Model
{
    protected $table = 'binhluan';
    protected $primaryKey = 'MABL';

    protected $fillable = [
        'MAKH',
        'MATINTUC',
        'NGAYDANG',
        'NOIDUNG',
    ];

    public function khachhang()
    {
        return $this->belongsTo(KhachHang::class, 'MAKH', 'MAKH');
    }

    public function tintuc()
    {
        return $this->belongsTo(TinTuc::class, 'MATINTUC', 'MATINTUC');
    }
}
