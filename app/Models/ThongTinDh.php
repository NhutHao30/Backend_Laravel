<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ThongTinDh extends Model
{
    protected $table = 'thongtindh';
    protected $primaryKey = 'MADH';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'MADH',
        'NGAYDAT',
        'NGAYGIAO',
        'TRANGTHAI',
        'DIACHI',
        'MAHD',
    ];

    public function hdban()
    {
        return $this->belongsTo(HdBan::class, 'MAHD', 'MAHD');
    }
}
