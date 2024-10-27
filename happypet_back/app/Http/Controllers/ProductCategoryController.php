<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\ProductSeries;
use App\Models\ProductSeriesImgView;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use finfo;

class ProductCategoryController extends Controller
{
    //
    function index($category){
        // $products = DB::select("SELECT * FROM seriespdimg_view WHERE product_id LIKE '{$category}%'");
        // $products = DB::select("SELECT * FROM seriespdimg_view WHERE product_id LIKE ? ",["{$category}%"]);
        $products = ProductSeriesImgView::where('product_id','LIKE',"{$category}%")->get();
        $sql = ProductSeriesImgView::where('product_id','LIKE',"{$category}%")->toSql();
        // Log::info([$sql]);
        foreach($products as &$pd){
            // print_r($pd);
            if(isset($pd->cover_img)){
                $mime_type = (new finfo(FILEINFO_MIME_TYPE))->buffer($pd->cover_img);
                $pd->cover_img = base64_encode($pd->cover_img);
                $src = "data:{$mime_type};base64,{$pd->cover_img}";
               $pd->cover_img = $src;
            }
        }
        // ProductSeriesImgView::
        // print_r($products);
        return response()->json($products);
        // return view('product1')->with('jsonString', json_encode($products, JSON_UNESCAPED_UNICODE));
    }
    // function testPage($category){
    //     $perPage = 8;
    //     $products = ProductSeriesImgView::where('product_id','LIKE',"{$category}%")->paginate($perPage);

    //     foreach($products as &$pd){
    //         // print_r($pd);
    //         if(isset($pd->cover_img)){
    //             $mime_type = (new finfo(FILEINFO_MIME_TYPE))->buffer($pd->cover_img);
    //             $pd->cover_img = base64_encode($pd->cover_img);
    //             $src = "data:{$mime_type};base64,{$pd->cover_img}";
    //            $pd->cover_img = $src;
    //         }
    //     }
    
    //     // print_r($products);
    //     return response()->json($products);
    //     // return view('product1')->with('jsonString', json_encode($products, JSON_UNESCAPED_UNICODE));
    // }
}
