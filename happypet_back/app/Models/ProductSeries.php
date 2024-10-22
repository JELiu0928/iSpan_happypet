<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
}
