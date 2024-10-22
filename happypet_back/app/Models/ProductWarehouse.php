<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductWarehouse extends Model
{
    use HasFactory;
    protected $table = 'product_warehouse';
    public $timestamps = false;
    protected $fillable = [
        'product_id',
        'inventory',
        'mfd',
        'exp',
        'restock_date',
    ];
}
