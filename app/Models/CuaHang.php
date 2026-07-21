<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CuaHang extends Model
{
    protected $table = 'cuahang';
    protected $primaryKey = 'MACUAHANG';
    
    protected $fillable = [
        'TENCUAHANG',
        'DIACHI',
        'SDT',
        'TRANGTHAI',
    ];

    public function nhanviens()
    {
        return $this->hasMany(NhanVien::class, 'MACUAHANG', 'MACUAHANG');
    }

    public function hdbans()
    {
        return $this->hasMany(HdBan::class, 'MACUAHANG', 'MACUAHANG');
    }
}
