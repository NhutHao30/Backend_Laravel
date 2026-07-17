<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DiaChi extends Model
{
    protected $table = 'diachi';
    // primary key defaults to 'id'

    protected $fillable = [
        'MAKH',
        'DIACHI',
        'MACDINH',
    ];

    public function khachhang()
    {
        return $this->belongsTo(KhachHang::class, 'MAKH', 'MAKH');
    }
}
