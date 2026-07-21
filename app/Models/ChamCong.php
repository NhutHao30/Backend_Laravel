<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChamCong extends Model
{
    protected $table = 'chamcong';
    
    protected $fillable = [
        'USERNAME',
        'NGAYCHAMCONG',
        'TRANGTHAI',
    ];

    public function nhanvien()
    {
        return $this->belongsTo(NhanVien::class, 'USERNAME', 'USERNAME');
    }
}
