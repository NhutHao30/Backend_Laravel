<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HdNhap extends Model
{
    protected $table = 'hdnhap';
    protected $primaryKey = 'MAHDNHAP';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'MAHDNHAP',
        'NGAYLAP',
        'USERNAME',
        'GHICHU',
    ];

    public function nhanvien()
    {
        return $this->belongsTo(NhanVien::class, 'USERNAME', 'USERNAME');
    }

    public function chitiets()
    {
        return $this->hasMany(ChiTietHdNhap::class, 'MAHDNHAP', 'MAHDNHAP');
    }
}
