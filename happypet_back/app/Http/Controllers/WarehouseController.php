<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductWarehouse;
use Illuminate\Http\Request;
// use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class WarehouseController extends Controller
{
    function show(Request $request){
        $productID = $request->input('productID');
        // $products = DB::select("SELECT p.product_id,CONCAT_WS(' / ', nullif(ps.series_name,''),nullif(flavor,''),
        //                         nullif(weight,''),nullif(size,''),nullif(style,'')) AS full_name
        //                         FROM product p
        //                         JOIN product_series ps 
        //                         ON p.series_ai_id = ps.series_ai_id
        //                         WHERE p.product_id = ? ",[$productID]);
        $product = Product::with('series')->where('product_id',$productID)->first();
        Log::info($product);
        // dd($ooo->toArray());
        
        if(!is_null($product)){
            // Log::info('$products[0]',['陣列',$products[0]]);
            // array_filter默認過濾掉為假(空字串/null等)的值
            $productName = implode('/',array_filter([
                $product->series->series_name,
                $product->flavor ?? '',
                $product->weight ?? '',
                $product->size ?? '',
                $product->style ?? ''
            ]));
            Log::info($productName);

            return response()->json(["productName" => $productName]);
        }else{
            return response()->json(["error" => "查無此產品"]);
        }
    }
    function store(Request $request){
        $productID = $request->input('productID');
        $mfd = $request->input('mfd');
        $exp = $request->input('exp');
        $inventory = $request->input('inventory');
        $restockDate = $request->input('restockDate');
        try{
            ProductWarehouse::create([
                'product_id'=>$productID,
                'inventory'=>$inventory,
                'mfd'=>$mfd,
                'exp'=>$exp,
                'restock_date'=>$restockDate
            ]);
            // DB::insert("INSERT INTO product_warehouse (product_id,inventory,mfd,exp,restock_date) 
            //         VALUES(?,?,?,?,?)",[$productID,$inventory,$mfd,$exp,$restockDate]);
            return response()->json(["message" => "產品已入庫"]);
        }catch(\Exception $e){
            Log::error($e->getMessage());
            return response()->json(["error" => "產品入庫失敗"]);
        }
    }
}
