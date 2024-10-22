<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\AllProduct;

class AllProductController extends Controller
{
    // index 查詢
    public function index(Request $request){
        $productName = $request->query('productName'); //keyword查詢
        Log::info('查詢產品系列ID:', ['productName' => $productName]); 
        $filterProduct = AllProduct::where('full_name','like',"%$productName%")->get(); 
        // $filterProduct = DB::select('SELECT * FROM all_product_view 
        //                  WHERE full_name LIKE ?',["%$productName%"]);
        Log::info('查詢產品系列ID:', ['productName' => $filterProduct]); 
        return response()->json($filterProduct);

    }
}
