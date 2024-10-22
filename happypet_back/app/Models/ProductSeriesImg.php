<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductSeriesImg extends Model
{
    use HasFactory;
    protected $table = 'product_seriesimg';

    protected $fillable = [
        'series_id',         
        'img',               
        'pic_category_id',   
        'create_date',       
        'update_date',     
    ];
    public $timestamps = false;
}
