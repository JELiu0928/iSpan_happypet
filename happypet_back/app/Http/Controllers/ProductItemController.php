<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\ProductCategory;
use App\Models\ProductSeries;
use App\Models\ProductSeriesImg;
use App\Models\ProductSeriesImgView;
use finfo;

class ProductItemController extends Controller
{
    //
    function index($category,$seriesProduct){
        // $products = DB::select("SELECT * FROM seriespdimg_view WHERE product_id like '{$category}%' and series_AINUM = '{$seriesProduct}'");
        // $products = DB::select("SELECT * FROM seriespdimg_view WHERE product_id like ? and series_ai_id = ?", ["{$category}%", $seriesProduct]);
        $products = ProductSeriesImgView::where('product_id','LIKE',"{$category}%")
                                        ->where('series_ai_id',$seriesProduct)
                                        ->get();


        // $productImgs = DB::select("SELECT psi.*,spv.series_ai_id
        //                             FROM seriespdimg_view spv
        //                             JOIN product_series ps
        //                             ON spv.series_ai_id = ps.series_ai_id
        //                             JOIN product_seriesimg psi 
        //                             ON ps.series_id = psi.series_id 
        //                             WHERE spv.series_ai_id = ?
        //                             GROUP BY psi.id ,psi.series_id,psi.img,psi.pic_category_id ,psi.create_date, psi.update_date
        //                         ",[$seriesProduct]);
        // $productImgs = DB::select("SELECT psi.* 
        //                             FROM `product_seriesimg` psi
        //                             JOIN product_series ps
        //                             ON ps.series_id = psi.series_id
        //                             WHERE ps.series_ai_id = ?
        //                             GROUP BY psi.id ,psi.series_id,psi.img,psi.pic_category_id ,psi.create_date, psi.update_date
        //                         ",[$seriesProduct]);
        $productImgs = ProductSeries::where('series_ai_id',$seriesProduct)
                                        ->with('images')->get();
        // $categoryName = DB::scalar("SELECT description FROM product_category WHERE category_id = ?",["{$category}"]);
        $categoryName = ProductCategory::where('category_id',"{$category}")->value('description');
        // 處理封面圖
        foreach($products as &$pd){
            // print_r($pd);
            if(isset($pd->cover_img)){
                $mime_type = (new finfo(FILEINFO_MIME_TYPE))->buffer($pd->cover_img);
                $pd->cover_img = base64_encode($pd->cover_img);
                $src = "data:{$mime_type};base64,{$pd->cover_img}";
                $pd->cover_img = $src;
            }
        }
        // 處理其他、敘述圖
        foreach ($productImgs as &$productImg) {
            foreach($productImg->images as &$pdImg){
                // print_r($pd);
                if(isset($pdImg->img)){
                    $mime_type = (new finfo(FILEINFO_MIME_TYPE))->buffer($pdImg->img);
                    $pdImg->img = base64_encode($pdImg->img);
                    $pdImg->img = "data:{$mime_type};base64,{$pdImg->img}";
                }
            }
        }
        // foreach($productImgs as &$pdImg){
        //     // print_r($pd);
        //     if(isset($pdImg->img)){
        //         $mime_type = (new finfo(FILEINFO_MIME_TYPE))->buffer($pdImg->img);
        //         $pdImg->img = base64_encode($pdImg->img);
        //         $src = "data:{$mime_type};base64,{$pdImg->img}";
        //         $pdImg->img = $src;
        //     }
        // }
        // return response()->json($products);
        return response()->json([
            'products' => $products,
            'productImgs' => $productImgs,
            'categoryName' => $categoryName
        ]);
    }
}
