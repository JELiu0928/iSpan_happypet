<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

// mysql的view
class AllProduct extends Model
{
    use HasFactory;
    protected $table = 'all_product_view'; 
    public $timestamps = false; 
}
