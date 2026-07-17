<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TinTuc extends Model
{
    protected $table = 'tintuc';
    protected $primaryKey = 'MATINTUC';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'MATINTUC',
        'TIEUDE',
        'HINHANH',
        'NGAYDANG',
        'MOTA',
    ];

    public function chitiets()
    {
        return $this->hasMany(ChiTietTinTuc::class, 'MATINTUC', 'MATINTUC');
    }

    public function binhluans()
    {
        return $this->hasMany(BinhLuan::class, 'MATINTUC', 'MATINTUC');
    }
}
