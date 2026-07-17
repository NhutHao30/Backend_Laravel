<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TinNhan extends Model
{
    protected $table = 'tinnhan';
    protected $primaryKey = 'MATINNHAN';

    protected $fillable = [
        'MACUOCTROCHUYEN',
        'NGUOIGUI_ID',
        'LOAINGUOIGUI',
        'NOIDUNG',
        'DADOC',
    ];

    public function cuoctrochuyen()
    {
        return $this->belongsTo(CuocTroChuyen::class, 'MACUOCTROCHUYEN', 'MACUOCTROCHUYEN');
    }
}
