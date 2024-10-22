<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use App\Models\ProductSeries;
class DetailProductController extends Controller
{
    function index(Request $request){
        $pdSeries = $request->query('pdSeries'); //?查詢字符串用query
        Log::info('pdSeries變數',['pdSeries',$pdSeries]);
        $product = DB::select("SELECT * FROM product p 
                    JOIN product_series ps 
                    ON p.series_ai_id = ps.series_ai_id 
                    WHERE series_id = ?",[$pdSeries]);
        return response()->json(["product" => $product ]);
    }
    // show：查詢單一結果
    // 系列號存在與否
    function show(Request $request){
        $pdSeries = $request->input('pdSeries');
        // Log::info('查詢產品系列ID:', ['pdSeries' => $pdSeries]); 
        $existPdSeries = ProductSeries::select('series_id', 'series_name')
                                        ->where('series_id',$pdSeries)
                                        ->first();
        if ($existPdSeries) {
            return response()->json($existPdSeries);
        } else {
            return response()->json(["error" => "查無此系列產品"]);
        }

    }
    function store(Request $request){
        try {
            $pdSeries = $request->input('pdSeries');

            $fullPdIDs = $request->input('fullID',[]);
            $flavors = $request->input('flavor',[]);
            $weights = $request->input('weight',[]);
            $sizes = $request->input('size',[]);
            $colors = $request->input('color',[]);
            $prices = $request->input('price',[]);
            $GTINs = $request->input('GTIN',[]);

            Log::info('請求資料', [
                'fullPdIDs' => $fullPdIDs,
                'flavors' => $flavors,
                'weights' => $weights,
                'sizes' => $sizes,
                'colors' => $colors,
                'prices' => $prices,
                'GTINs' => $GTINs
            ]);
            Log::info('$request:', ["request = " => $request->all()]); 
            Log::info('全產品ID', ['fullPdIDs' => $fullPdIDs]);
            
            // 開啟事務
            DB::beginTransaction();
            
            $series_num = ProductSeries::select('series_ai_id')
                ->where('series_id',$pdSeries)
                ->first();
            Log::info('series_num:', ["series_num =" => $series_num]);
            // Log::info('series_num:', ["series_num['series_ai_id'] =" => $series_num->series_ai_id]);
            
            
            // $n = DB::insert("INSERT INTO product(series_AINUM,id,flavor,weight,size,color,price,GTIN,created_at)
            // VALUES(?,?,?,?,?,?,?,?,date(NOW()))",[$pdSeries,$fullPdIDs,$flavors,$weights,$sizes,$colors,$prices,$GTINs]);
            foreach($fullPdIDs as $index=>$fullPdID){
                Log::info('處理料號', ['fullPdID' => $fullPdID]);
                $validateError = $this->inputValidation($fullPdID,$prices,$GTINs,$index,$colors,$sizes,$flavors,$weights);
                if($validateError){
                    DB::rollBack();
                    return response()->json(["error" => $validateError]);
                }
                
                // 回傳布林值
                $insertSuccess = DB::insert('INSERT INTO product(series_ai_id,product_id,flavor,weight,size,style,price,GTIN,create_date)
                        VALUES(?,?,?,?,?,?,?,?,NOW())',[
                            $series_num->series_ai_id,
                            $fullPdID,
                            $flavors[$index] ?? '',
                            $weights[$index] ?? '',
                            $sizes[$index] ?? '',
                            $colors[$index] ?? '',
                            $prices[$index] ?? '',
                            $GTINs[$index] ?? '',
                        ]);
                
                Log::info('異動筆數:', ["異動筆數" => $insertSuccess]); // 日誌查詢異動筆數
                
            };
            
            if($insertSuccess){
                DB::commit();
                return response()->json(["message" => "產品新增成功"]);
            }
            // Log::info('Insert:', ["inserted_count" => $insertedCount]);

            // echo json_encode(["message" => "產品系列新增成功"]);
        }catch(\Exception $e) {
            Log::error($e->getMessage());
            // Log::error($e->errorInfo[1]); 
            // 檢查是否是 PDOException，然後處理errorInfo
            if($e instanceof \PDOException && isset($e->errorInfo[1]) && $e->errorInfo[1] == 1062){
                DB::rollBack();
                return response()->json(["error" => "此料號已被使用"]);
            }
            DB::rollBack();
            return response()->json(["error" => "發生錯誤" . $e->getMessage()]);
        };
    }    

    function update(Request $request){
        try {
            $pdSeries = $request->input('pdSeries');
            $pdName = $request->input('pdName');
            Log::info('取得系列號:', ["-------------------->" => $pdSeries]);
            Log::info('取得系列名:', ["-------------------->" => $pdName]);
            $fullPdIDs = $request->input('fullID',[]);
            $flavors = $request->input('flavor',[]);
            $weights = $request->input('weight',[]);
            $sizes = $request->input('size',[]);
            $colors = $request->input('color',[]);
            $prices = $request->input('price',[]);
            $GTINs = $request->input('GTIN',[]);
            // 開啟事務
            DB::beginTransaction();

            // DB::update('UPDATE product_series SET series_name = ? WHERE series_id = ?',[$pdName,$pdSeries]);
            ProductSeries::where('series_id',$pdSeries)
            ->update(['series_name'=>$pdName]); //關聯陣列
            // Log::info('updatePdName:', [$updatePdName]);

            foreach($fullPdIDs as $index=>$fullPdID){
            // Log::info("-------------------->",[$fullPdIDs[$index]]);
                $validateError = $this->inputValidation($fullPdID,$prices,$GTINs,$index,$colors,$sizes,$flavors,$weights);
                if($validateError){
                    DB::rollBack();
                    return response()->json(["error" => $validateError]);
                }
                $updateSuccess = DB::update("UPDATE product
                                SET flavor = ?, weight = ?, size = ?,style = ? ,price = ?, GTIN = ?, update_date = Now()
                                WHERE product_id = ? ",[
                                        $flavors[$index] ?? '',
                                        $weights[$index] ?? '',
                                        $sizes[$index] ?? '',
                                        $colors[$index] ?? '',
                                        $prices[$index] ?? '',
                                        $GTINs[$index] ?? '',
                                        $fullPdID
                                    ]);
            }
            if($updateSuccess){
                DB::commit();
                Log::info('update:', ["update" => $updateSuccess]);

                return response()->json(["message" => "產品修改成功"]);
            }
        }catch(\Exception $e) {
            Log::error($e->getMessage());
            DB::rollBack();
            return response()->json(["error" => "發生錯誤" . $e->getMessage()]);
        };
    }
    private function inputValidation($fullPdID,$prices,$GTINs,$index,$colors,$sizes,$flavors,$weights){
        if(strlen($fullPdID) != 13 ){
            return "料號不是有效格式：種類兩碼+年份四碼+月份兩碼+系列編碼流水號三碼+料號流水號兩碼，如：ds20240700101";
        }elseif(empty($prices[$index])){
            return "價格不可以為空";
        }elseif(empty($GTINs[$index])){
            return "國際條碼不可以為空";
        }elseif(strlen($GTINs[$index]) != 13 && strlen($GTINs[$index]) != 8 && strlen($GTINs[$index]) != 14){
            return "國際條碼分為GTIN-8、GTIN-13、GTIN-14";

        }

        // 找不到會返回false
        $reg = "/df|dc|dt|dh|cf|cc|ct|ch/";
        // print_r(preg_match_all($reg,$fullPdID,$match));
        // preg_match_all() 函數用於在字串中搜尋符合的模式，傳回所有符合項目
        // strpos
        if( (strpos($fullPdID,'ds') !== false || strpos($fullPdID,'cs') !== false) &&  ( empty($colors[$index])  ||  empty($sizes[$index]) )  ){
            return "用品類尺寸與款式不得為空";
        }elseif(preg_match_all($reg,$fullPdID,$match) && (empty($flavors[$index]) || empty($weights[$index]))){
            return "食品類淨重與口味不得為空";
        }

        // if ((strpos($fullPdID, 'ds') !== false || strpos($fullPdID, 'cs') !== false) && (empty($colors[$index]) || empty($sizes[$index]))) {
        //     throw new \Exception("用品類尺寸與款式其中一個不得為空");
        // } elseif (preg_match_all($reg, $fullPdID, $match) && (empty($flavors[$index]) || empty($weights[$index]))) {
        //     throw new \Exception("食品類淨重與口味不得為空");
        // }
        return null;
    }
}
