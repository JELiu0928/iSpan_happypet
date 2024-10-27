<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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

    public function series():BelongsTo{
        // img多：series一
        return $this->belongsTo(
            ProductSeries::class,
            'series_id', //img外鍵
            'series_id' //ps主鍵
        ); 
    }
    
}
