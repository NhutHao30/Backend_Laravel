<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GiamGia extends Model
{
    protected $table = 'giamgia';
    protected $primaryKey = 'MASP';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'MASP',
        'GIAM',
        'THOIGIANGIAM',
    ];

    public function sanpham()
    {
        return $this->belongsTo(SanPham::class, 'MASP', 'MASP');
    }
}
