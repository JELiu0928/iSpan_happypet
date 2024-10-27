<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\ProductSeries;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

use finfo;
use Illuminate\Database\Eloquent\Builder;

class ProductSearchController extends Controller
{
    //
    function index(Request $request){
        $nameKeyword = $request->input('nameKeyword'); // 
        // dd($nameKeyword);
        // Log::info('nameKeyword = ',['nameKeyword----->',$nameKeyword]);
        $keywords = explode(' ',$nameKeyword);
        // $resultArr = [];
        // foreach ($newValue as $value) {
        //     $results = DB::select("SELECT series_id ,ps.series_ai_id,ps.series_name ,spv.cover_img ,spv.price,ps.category_id
        //                             FROM seriespdimg_view spv JOIN product_series ps 
        //                             ON spv.series_ai_id = ps.series_ai_id 
        //                             WHERE ps.series_name LIKE ?
        //                             GROUP BY ps.series_id,ps.series_ai_id,spv.price,ps.series_name,ps.category_id;
        //                             ",["%{$value}%"]);
        //     // dump($results);
        //     // array_push($resultArr,$results);
        //     $resultArr = array_merge($resultArr,$results);
        //     // dd($resultArr);
        // }
        // dd($resultArr);
        // foreach ($newValue as $value) {
            $query = ProductSeries::query()
                            ->select('series_id','series_ai_id','series_name','category_id')
                            ->with(['pdImagesFromView'=>function($query){
                                $query->select('series_ai_id','cover_img','price');
                            }]);
        
            foreach($keywords as $index=>$keyword){
                if($index === 0){
                    $query->where('series_name','LIKE',"%$keyword%"); // 一個關鍵字時
                }else{
                    $query->orWhere('series_name','LIKE',"%$keyword%"); // 有其他關鍵字時
                }
            }     
            $results = $query->get(); 
            // dd($results);
            Log::info('$results = $query->get(); :', [$results]);

            foreach($results as $res){
                // dump($res->toArray());
                Log::info('pd_images_from_view', [$res->pdImagesFromView]);
                foreach($res->pdImagesFromView as &$img){
                    $mime_type = (new finfo(FILEINFO_MIME_TYPE))->buffer($img->cover_img);
                    $img->cover_img = base64_encode($img->cover_img);
                    $img->cover_img = "data:{$mime_type};base64,{$img->cover_img}";
                    // return $img;   
                }

            }
            Log::info('Results data:', [$results]);

            // Log::info('emmpty====> ',[$results->first()->first()['pd_images_from_view']]);
            // $results = $query->get()->first();
            // $imagesCollection = $results->map(function($item) {
            //     // 記錄 pd_images_from_view
            //     Log::info('$result->pd_images_from_view====> ', [$item['pd_images_from_view']]);
                
            //     // 返回 pd_images_from_view 的內容
            //     return $item->pd_images_from_view;
            // });
            // $results = $results->map(function($result){
         
            //         Log::info('$result->pd_images_from_view====> ',[$result->pd_images_from_view]);
                // if($result->pd_images_from_view){

                //     $result->pd_images_from_view = $result->pd_images_from_view->map(function($img){
                //         $mime_type = (new finfo(FILEINFO_MIME_TYPE))->buffer($img->cover_img);
                //         $img->cover_img = base64_encode($img->cover_img);
                //         $img->cover_img = "data:{$mime_type};base64,{$img->cover_img}";
                //         return $img;                        
                //     });
                // }
                // return $result;                        

            // });
            // Log::info('處理完後---> ',[$results]);

            // Log::info('圖片處理====> ',[$results]);
            // Log::info('$results = $query->get(); ',[$results]);
        // }
        // $results = DB::select("SELECT series_id ,ps.series_ai_id,ps.series_name ,spv.cover_img ,spv.price,ps.category_id
        //                             FROM seriespdimg_view spv JOIN product_series ps 
        //                             ON spv.series_ai_id = ps.series_ai_id 
        //                             WHERE ps.series_name LIKE ?
        //                             GROUP BY ps.series_id,ps.series_ai_id,spv.price,ps.series_name,ps.category_id;
        //                             ",["%{$nameKeyword}%"]);
        // 將封面圖轉base64
        // dd($results);
        // Log::info($results->toArray());    
        // foreach($results as $result){
        //     // Log::info($result->toArray());    

        //     foreach($result->pd_images_from_view as $pdView){
        //         if(isset($pdView->cover_img)){
        //             $mime_type = (new finfo(FILEINFO_MIME_TYPE))->buffer($pdView->cover_img);
        //             $pdView->cover_img = base64_encode($pdView->cover_img);
        //             $pdView->cover_img = "data:{$mime_type};base64,{$pdView->cover_img}";
        //         }
        //     }
        // }
      
        // foreach($resultArr as &$result){
        //     if(isset($result->cover_img)){
        //         $mime_type = (new finfo(FILEINFO_MIME_TYPE))->buffer($result->cover_img);
        //         $result->cover_img = base64_encode($result->cover_img);
        //         $src = "data:{$mime_type};base64,{$result->cover_img}";
        //         $result->cover_img = $src;
        //     }
        // }
        // Log::info('==>',$resultArr);
        // Log::info(['result----->',$result]);
        // return response()->json(["result" => $resultArr]);
        return response()->json(["result" => $results]);
    }
}
