<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChiTietHdBan extends Model
{
    protected $table = 'chitiethdban';
    public $incrementing = false;
    protected $primaryKey = null; // Composite key

    protected $fillable = [
        'MAHD',
        'MASP',
        'SOLUONG',
        'DONGIA',
    ];

    public function hdban()
    {
        return $this->belongsTo(HdBan::class, 'MAHD', 'MAHD');
    }

    public function sanpham()
    {
        return $this->belongsTo(SanPham::class, 'MASP', 'MASP');
    }
}
