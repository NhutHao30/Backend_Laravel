<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LoaiSanPham extends Model
{
    protected $table = 'loaisanpham';
    protected $primaryKey = 'MALOAI';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'MALOAI',
        'TENLOAI',
    ];

    public function sanphams()
    {
        return $this->hasMany(SanPham::class, 'MALOAI', 'MALOAI');
    }
}
