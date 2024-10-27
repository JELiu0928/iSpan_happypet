<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProductSeries extends Model
{
    use HasFactory;
  
    protected $table = 'product_series';
    protected $primaryKey = 'series_ai_id';
    // protected $keyType = 'int'; // 主鍵是數字的話省略
    public $timestamps = false;
    protected $fillable = [
        'series_id',
        'category_id',
        'series_name',
        'description1',
        'description2',
        'description3',
        'description4',
        'description5',
        'create_date',
        'update_date',
    ];
    public function products():HasMany{
        // Product外鍵指向ProductSeries主鍵(多指向一)
        return $this->hasMany(
            Product::class,
            'series_ai_id', //Product，外鍵
            'series_ai_id'  //ProductSeries，主鍵
        );
    }
    public function images():HasMany{
        // series一：img多
        // 這裡 'series_ai_id' 是 ProductSeriesImg 表中的外鍵，指向 Series 表中的 'series_ai_id' 主鍵
        return $this->hasMany(
            ProductSeriesImg::class,
            'series_id', //img外鍵
            'series_id' //ps主鍵
        ); 
    }
    public function pdImagesFromView():HasMany{
        return $this->hasMany(
            ProductSeriesImgView::class,
            'series_ai_id', //View，外鍵
            'series_ai_id'  //ProductSeries，主鍵
        );
    }
}
