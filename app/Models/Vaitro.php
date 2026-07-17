<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VaiTro extends Model
{
    protected $table = 'vaitro';
    protected $primaryKey = 'MAROLE';
    public $incrementing = false;
    protected $keyType = 'integer';

    protected $fillable = [
        'MAROLE',
        'MOTA',
    ];

    public function taikhoans()
    {
        return $this->hasMany(TaiKhoan::class, 'MAROLE', 'MAROLE');
    }
}
