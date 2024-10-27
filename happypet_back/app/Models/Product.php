<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;


class Product extends Model
{
    use HasFactory;
    protected $table = 'product'; 
    
    protected $fillable = [
        'product_id',         
        'series_ai_id',       
        'flavor',             
        'weight',             
        'size',               
        'style',              
        'price',              
        'GTIN',               
        'shelves_status',     
        'create_date',        
        'update_date',        
        'delete_date',        
    ];

    protected $primaryKey = 'product_id';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    // 隱藏的屬性（不顯示在 JSON 格式回傳中）
    protected $hidden = [
        'delete_date',
    ];
    // (Product多:Series一)
    public function series():BelongsTo {
        return $this->belongsTo(
            ProductSeries::class,
            'series_ai_id', //Product，外鍵
            'series_ai_id'  //ProductSeries，主鍵
        );
    }
   
    

}
