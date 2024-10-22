<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

use finfo;

class ProductSearchController extends Controller
{
    //
    function index(Request $request){
        $nameKeyword = $request->input('nameKeyword'); // 
        // dd($nameKeyword);
        // Log::info('nameKeyword = ',['nameKeyword----->',$nameKeyword]);
        $newValue =  explode(' ',$nameKeyword);

        $resultArr = [];
        foreach ($newValue as $value) {

            $results = DB::select("SELECT series_id ,ps.series_ai_id,ps.series_name ,spv.cover_img ,spv.price,ps.category_id
                                    FROM seriespdimg_view spv join product_series ps 
                                    on spv.series_ai_id = ps.series_ai_id 
                                    WHERE ps.series_name LIKE ?
                                    group by ps.series_id,ps.series_ai_id,spv.price,ps.series_name,ps.category_id;
                                    ",["%{$value}%"]);
                                // dump($results);
                                // array_push($resultArr,$results);
                                $resultArr = array_merge($resultArr,$results);
                                // dd($resultArr);
        }
        
        // dd($resultArr);
        // Log::info('==>',$resultArr);

        
        // 將封面圖轉base64
        foreach($resultArr as &$result){
            if(isset($result->cover_img)){
                $mime_type = (new finfo(FILEINFO_MIME_TYPE))->buffer($result->cover_img);
                $result->cover_img = base64_encode($result->cover_img);
                $src = "data:{$mime_type};base64,{$result->cover_img}";
               $result->cover_img = $src;
            }
        }
        // Log::info(['result----->',$result]);
        return response()->json(["result" => $resultArr]);
    }
}
