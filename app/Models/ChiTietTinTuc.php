<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChiTietTinTuc extends Model
{
    protected $table = 'ctiettintuc';
    // primaryKey is id by default

    protected $fillable = [
        'MATINTUC',
        'BOCUC',
        'ARTICLE',
        'HINHANH',
    ];

    public function tintuc()
    {
        return $this->belongsTo(TinTuc::class, 'MATINTUC', 'MATINTUC');
    }
}
