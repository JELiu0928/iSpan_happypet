<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\ProductCategory;
use App\Models\ProductSeries;
use App\Models\ProductSeriesImg;
use finfo; // mime類型
class SeriesProductController extends Controller
{   
    public function showCategories(){
        // $categories = DB::select('SELECT category_id,description FROM product_category');
        // Cannot use object of type stdClass as array ，解決↓ (編碼在解碼為Array)
        // $categories = json_decode(json_encode($categories), true); //DB::select查詢出來是stdClass要轉換
        $categories = ProductCategory::all(['category_id','description']);
        Log::info('categories',['categories',$categories]);

        $categoryArr = [];
        foreach ($categories as $category) {
            $categoryArr[] = $category['category_id'] . "-" . $category['description'];
        }
        // print_r($categories);

        return response()->json([
            'categories' => $categoryArr,
        ]);
    }
    // 修改頁面的查詢
    public function show($seriesID = null){
        $seriesProduct = DB::select("SELECT * FROM product_series ps 
                                    JOIN product_seriesimg psi 
                                    ON ps.series_id  = psi.series_id
                                    WHERE ps.series_id = ?",[$seriesID]);
        // 原本圖片是BLOB 轉成 base64 (&$spdImg => 傳址，直接修改值會影響原本變數)
        foreach($seriesProduct as &$spdImg){
            // print_r($spdImg);
            if(isset($spdImg->img)){
                $mime_type = (new finfo(FILEINFO_MIME_TYPE))->buffer($spdImg->img);
                // Log::info("mime_type: " . $mime_type);
                $spdImg->img = base64_encode($spdImg->img);
                $src = "data:{$mime_type};base64,{$spdImg->img}";
                $spdImg->img = $src;
            }
        };
        #region 測試儲存路徑 
        // $seriesProduct2 = DB::select("SELECT * FROM  product_series ps 
        //                             JOIN product_seriesimg psi 
        //                             ON ps.series_id  = psi.series_id
        //                             WHERE ps.series_id = ?",[$seriesID]);
        // Log::info("seriesProduct2: ", ["xxx"=> $seriesProduct2]);
        // $seriesProduct2 = json_decode(json_encode($seriesProduct2), true);
        #endregion

        if(empty($seriesProduct)){
            return response()->json([
                'message'=>"查無此系列編號",
            ]);
        }else{
            Log::info('系列產品查詢結果',['seriesProduct'=>empty($seriesProduct)]);
            return response()->json([
                'seriesProduct'=>$seriesProduct,
                // '$seriesProduct2'=>$seriesProduct2
            ]);
        }
        
    }
    // 新增時先查詢有沒有存在
    public function checkSeriesID($seriesID = null) {
        // $seriesIDCount = DB::scalar("SELECT count(*) FROM product_series WHERE series_id = ?", [$seriesID]);
        $seriesIDCount = ProductSeries::where('series_id',$seriesID)->count();
        // Log::info('seriesIDCount',['seriesIDCount',$seriesIDCount]);
    
        // 預設message為null
        $message = null;
        if ($seriesID !== null && $seriesIDCount > 0) {
            $message = (["message" => "此產品系列編號已被使用"]);
        }
    
        return response()->json([
            'message' => $message,
        ]);
    }

    public function store(Request $request){
        try {

            // 開啟事務
            DB::beginTransaction();
            
            $pdSeries = $request->input('pdSeries');
            $category = $request->input('category');
            $pdName = $request->input('pdName');
            $description1 = $request->input('description1');
            $description2 = $request->input('description2');
            $description3 = $request->input('description3');
            $description4 = $request->input('description4');
            $description5 = $request->input('description5');
            $coverimg = $request->file('coverimg');
            $imgs = $request->file('imgs');
            $descimgs = $request->file('descimgs');
            // 驗證輸入的值，並返回錯誤給前端
            $validateError = $this->inputValidation($pdSeries,$category,$pdName,$description1,$imgs);
            if ($validateError) {
                DB::rollBack();
                return response()->json(["error" => $validateError]);
            };

            ProductSeries::create([
                'series_id'=>$pdSeries,
                'category_id'=>$category,
                'series_name'=>$pdName,
                'description1'=>$description1,
                'description2'=>$description2,
                'description3'=>$description3,
                'description4'=>$description4,
                'description5'=>$description5,
                'create_date'=>now(),
                'update_date'=>null
            ]);
            // DB::insert("INSERT INTO product_series(series_id,category_id,series_name,description1,description2,description3,description4,description5,create_date,update_date)
            // VALUES(?,?,?,?,?,?,?,?,Now(),null)",[$pdSeries,$category,$pdName,$description1,$description2,$description3,$description4,$description5]);
            
            // 處理封面圖
            if ($coverimg && $coverimg->isValid() ){
                $fileContent = $coverimg->get();
                // Log::info("coverimg",["fileContent"=>$fileContent]);
                
                Log::info("封面圖片有效，檔案大小: " . strlen($fileContent));
                ProductSeriesImg::create([
                    'series_id' => $pdSeries,
                    'img' => $fileContent,
                    'pic_category_id' => 1,
                    'create_date'=> now()
                ]);
                // DB::insert("INSERT INTO product_seriesimg(series_id,img,pic_category_id,create_date)
                //     VALUES(?,?,?,NOW())",[$pdSeries, $fileContent, 1]);
                // $stmt->execute([$pdSeries, $fileContent, 1]);
            }else{
                DB::rollBack();
                return response()->json(["error"=>"封面圖片上傳失敗"]);
            }
            #region 測試儲存路徑
            // 測試儲存路徑
            // if ($coverimg && $coverimg->isValid() ){
            //     $fileContent = $coverimg->get();
            //         // Log::info("coverimg",["fileContent"=>$fileContent]);
            //         Log::info("封面圖片有效，檔案大小: " . strlen($fileContent));
            //         // $stmt->execute([$pdSeries, $fileContent, 1]);
            //     $path = $coverimg->store('images',"public");
            //     Log::info("coverimg",["path"=>$path]);
            //     $filePath = '/storage/' . $path;
            //     Log::info("coverimg",["filePath"=>$filePath]);

            //     Log::info("封面圖片有效，檔案大小: " . strlen($filePath));
            //     DB::insert("INSERT INTO product_seriesimg(series_id,img,pic_category_id,create_date,test)
            //         VALUES(?,?,?,NOW(),?)",[$pdSeries, $fileContent, 1,$filePath]);
            //     // $stmt->execute([$pdSeries, $fileContent, 1]);
            // }else{
            //     DB::rollBack();
            //     return response()->json(["error"=>"封面圖片上傳失敗"]);
            // }
            #endregion


            // 處理其他圖片(8張)
            Log::info("imgs的if有效",["imgs"=>$imgs]);
            if ($imgs !==null && count($imgs) <= 8){
                Log::info("imgs的if有效");
                foreach ($imgs as $img) {
                    if ($img->isValid()) {
                        $fileContent = $img->get();
                        Log::info("其他圖片有效，檔案大小: " . strlen($fileContent));
                        // DB::insert("INSERT INTO product_seriesimg(series_id,img,pic_category_id,create_date)
                        //             VALUES(?,?,?,NOW())",[$pdSeries, $fileContent, 2]);
                        ProductSeriesImg::create([
                            'series_id' => $pdSeries,
                            'img' => $fileContent,
                            'pic_category_id' => 2,
                            'create_date'=> now()
                        ]);
                    }else{
                        DB::rollBack();
                        return response()->json(["error"=>"其他圖片上傳失敗"]);
                    }
                }
            }else{
                return response()->json(["error"=>"其他圖片至少一張且不可以超過八張"]);
            }
            // 處理敘述圖片
            if ($descimgs !==null ){
                foreach ($descimgs as $descimg) {
                    if ($descimg->isValid()) {
                        $fileContent = $descimg->get();
                        Log::info("敘述圖片有效，檔案大小: " . strlen($fileContent));
                        // DB::insert("INSERT INTO product_seriesimg(series_id,img,pic_category_id,create_date)
                        //             VALUES(?,?,?,NOW())",[$pdSeries, $fileContent, 3]);
                        ProductSeriesImg::create([
                            'series_id' => $pdSeries,
                            'img' => $fileContent,
                            'pic_category_id' => 3,
                            'create_date'=> now()
                        ]);
                    }else{
                        DB::rollBack();
                        return response()->json(["error"=>"敘述圖片上傳失敗"]);
                    }
                }
            }else{
                return response()->json(["error"=>"敘述圖片至少上傳一張"]);
            }
            // $this->coverimgValidation($coverimg,$pdSeries);
            // $this->otherimgsValidation($imgs,$pdSeries);
            // $this->descimgsValidation($descimgs,$pdSeries);
            DB::commit();
            return response()->json(["message" => "產品系列新增成功"]);
            // echo json_encode(["message" => "產品系列新增成功"]);
        }catch(\Exception $e) {
            Log::error($e->getMessage());
            DB::rollBack();
            return response()->json(["error" => "發生未知錯誤"]);
        }
    }
   
    public function update(Request $request){
        try {
            $pdSeries = $request->input('pdSeries');
            $category = $request->input('category');
            $pdName = $request->input('pdName');
            $description1 = $request->input('description1');
            $description2 = $request->input('description2');
            $description3 = $request->input('description3');
            $description4 = $request->input('description4');
            $description5 = $request->input('description5');
            $coverimg = $request->file('coverimg');
            $imgs = $request->file('imgs');
            $descimgs = $request->file('descimgs');
            // 驗證輸入的值，並返回錯誤給前端
            $validateError = $this->inputValidation($pdSeries,$category,$pdName,$description1,$imgs);
            if ($validateError) {
                DB::rollBack();
                return response()->json(["error" => $validateError]);
            };

            $n = DB::update("UPDATE product_series 
                    SET series_id = ? ,category_id = ?,series_name = ?,description1 = ?,description2 = ?,description3 = ?,description4 = ?,description5 = ?,update_date = Now()
                    WHERE series_id = ?",[$pdSeries,$category,$pdName,$description1,$description2,$description3,$description4,$description5,$pdSeries]);

            if(!empty($coverimg)){
                if ($coverimg->isValid() ){
                    $fileContent = $coverimg->get();
                    // DB::update("UPDATE product_seriesimg 
                    // SET series_id = ? ,img = ? ,update_date = Now()
                    // WHERE series_id = ? AND pic_category_id = 1",[$pdSeries,$fileContent,$pdSeries]);
                    ProductSeriesImg::where('series_id',$pdSeries)
                        ->where('pic_category_id',1)
                        ->update([
                            'series_id' => $pdSeries,
                            'img' => $fileContent,
                            'update_date' => now()
                    ]);
                }else{
                    DB::rollBack();
                    return response()->json(["error"=>"封面圖片上傳失敗"]);
                }
            }
            
            if (!empty($imgs)){
                if(count($imgs) <= 8){
                    ProductSeriesImg::where('series_id', $pdSeries)
                        ->where('pic_category_id', 2)
                        ->delete();
                    foreach ($imgs as $img) {
                        if ($img->isValid()) {
                            $fileContent = $img->get();
                            ProductSeriesImg::create([
                                'series_id' => $pdSeries,
                                'img' => $fileContent,
                                'pic_category_id' => 2,
                                'create_date'=> now()
                            ]);
                            // DB::insert("INSERT INTO product_seriesimg(series_id,img,pic_category_id,create_date)
                            //             VALUES(?,?,?,NOW())",[$pdSeries, $fileContent, 2]);
                        }else{
                            DB::rollBack();
                            return response()->json(["error"=>"其他圖片上傳失敗"]);
                        }
                    }
                }else{
                    return response()->json(["error"=>"不可以超過八張"]);
                }
                
            }
            
            if (!empty($descimgs)){
                ProductSeriesImg::where('series_id', $pdSeries)
                        ->where('pic_category_id', 3)
                        ->delete();
                foreach ($descimgs as $descimg) {
                    if ($descimg->isValid()) {
                        $fileContent = $descimg->get();
                        Log::info("敘述圖片有效，檔案大小: " . strlen($fileContent));
                        ProductSeriesImg::create([
                            'series_id' => $pdSeries,
                            'img' => $fileContent,
                            'pic_category_id' => 3,
                            'create_date'=> now()
                        ]);
                        // DB::insert("INSERT INTO product_seriesimg(series_id,img,pic_category_id,create_date)
                        //             VALUES(?,?,?,NOW())",[$pdSeries, $fileContent, 3]);
                    }else{
                        DB::rollBack();
                        return response()->json(["error"=>"敘述圖片上傳失敗"]);
                    }
                }
            }

            Log::info("我是修改:$n " , ['$n'=>$n]);
            if($n > 0){
                DB::commit();
                return response()->json(["message" => "產品修改成功！"]);
            }else{
                DB::rollBack();
            }

        }catch(\Exception $e) {
            Log::error($e->getMessage());
            DB::rollBack();
            return response()->json(["error" => "發生未知錯誤"]);
        }
        
    }
   

    private function inputValidation($pdSeries,$category,$pdName,$description1,$imgs){
        if(empty($pdSeries)){ 
            return "產品系列編號不可為空"; 
        }else if(strlen($pdSeries) != 11){
            return "產品系列編號為11碼";
        }else if(empty($category) || $category == 'default'){ 
            return "產品類別不可為空";
        }else if(empty($pdName)){ 
            return "產品系列名稱不可為空";
        }else if(empty($description1)){
            return "至少輸入一條敘述";
        }
        
        return null;
    }
    #region 測試判斷
    // private function coverimgValidation($coverimg,$pdSeries){
    //     if ($coverimg && $coverimg->isValid() ){
    //         $fileContent = $coverimg->get();
    //         Log::info("封面圖片有效，檔案大小: " . strlen($fileContent));
    //         DB::insert("INSERT INTO product_seriesimg(series_id,img,pic_category_id,create_date)
    //             VALUES(?,?,?,NOW())",[$pdSeries, $fileContent, 1]);
    //     }else{
    //         DB::rollBack();
    //         return response()->json(["error"=>"封面圖片上傳失敗"]);
    //     }
    // }
    // private function otherimgsValidation($imgs,$pdSeries){
    //     Log::info("imgs的if有效",["imgs"=>$imgs]);
    //     if ($imgs && count($imgs) <= 8){
    //         Log::info("imgs的if有效");
    //         foreach ($imgs as $img) {
    //             if ($img->isValid()) {
    //                 $fileContent = $img->get();
    //                 Log::info("其他圖片有效，檔案大小: " . strlen($fileContent));
    //                 DB::insert("INSERT INTO product_seriesimg(series_id,img,pic_category_id,create_date)
    //                             VALUES(?,?,?,NOW())",[$pdSeries, $fileContent, 2]);
    //             }else{
    //                 DB::rollBack();
    //                 return response()->json(["error"=>"其他圖片上傳失敗"]);
    //             }
    //         }
    //     }else{
    //         return response()->json(["error"=>"其他圖片至少一張且不可以超過八張"]);
    //     }
    // }
    // private function descimgsValidation($descimgs,$pdSeries){
    //     if ($descimgs){
    //         foreach ($descimgs as $descimg) {
    //             if ($descimg->isValid()) {
    //                 $fileContent = $descimg->get();
    //                 Log::info("敘述圖片有效，檔案大小: " . strlen($fileContent));

    //                 DB::insert("INSERT INTO product_seriesimg(series_id,img,pic_category_id,create_date)
    //                             VALUES(?,?,?,NOW())",[$pdSeries, $fileContent, 3]);
    //             }else{
    //                 DB::rollBack();
    //                 return response()->json(["error"=>"敘述圖片上傳失敗"]);
    //             }
    //         }
    //     }else{
    //         return response()->json(["error"=>"敘述圖片至少上傳一張"]);
    //     }
    // }
    #endregion
}
