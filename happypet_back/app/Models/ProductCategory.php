<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductCategory extends Model
{
    use HasFactory;
    protected $table = 'product_category';
    protected $primaryKey = 'category_id';
    public $incrementing = false; //主鍵不是自增
    public $timestamps = false; //不需要updated_at和created_at 
    protected $fillable = ['name','description']; //https://laravel.tw/docs/5.0/eloquent#mass-assignment

}
