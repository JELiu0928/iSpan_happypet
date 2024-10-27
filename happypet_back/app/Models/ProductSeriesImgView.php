<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductSeriesImgView extends Model
{
    use HasFactory;

    protected $table = 'seriespdimg_view'; 
    public $incrementing = false; 
    protected $keyType = null; // 沒有主鍵設null
    public $timestamps = false; 

    
}
