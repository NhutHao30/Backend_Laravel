<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HdBan extends Model
{
    protected $table = 'hdban';
    protected $primaryKey = 'MAHD';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'MAHD',
        'NGAYLAP',
        'MAVANDON',
        'DonViVanChuyen',
        'GHICHU',
        'MAKH',
        'TONGTIEN',
        'PHUONGTHUCTHANHTOAN',
        'TRANGTHAITHANHTOAN',
        'MAGIAODICH_MOMO',
    ];

    public function khachhang()
    {
        return $this->belongsTo(KhachHang::class, 'MAKH', 'MAKH');
    }

    public function chitiets()
    {
        return $this->hasMany(ChiTietHdBan::class, 'MAHD', 'MAHD');
    }

    public function thongtindh()
    {
        return $this->hasOne(ThongTinDh::class, 'MAHD', 'MAHD');
    }
}
