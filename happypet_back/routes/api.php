<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use App\Http\Controllers\SeriesProductController; 
use App\Http\Controllers\DetailProductController; 
use App\Http\Controllers\AllProductController; 
use App\Http\Controllers\ProductCartController; 
use App\Http\Controllers\WarehouseController; 
use App\Http\Controllers\ProductCategoryController; 
use App\Http\Controllers\ProductItemController; 
use App\Http\Controllers\ProductSearchController; 

use App\Http\Controllers\HotelOrderController;
use App\Http\Controllers\BeautyFrontController; 
use App\Http\Controllers\BeautyBackController; 
use App\Http\Controllers\RegisterController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\MyPetController;
use App\Http\Controllers\UserinfoController;
// use App\Models\ProductCategory;
// use App\Models\ProductSeries;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');
//                         Liu                              //
// 產品主要資訊頁面(系列產品)
// Route::post('/product_back/info/update',[SeriesProductController::class,'update']);
Route::prefix('/product_back/info')->group(function () {
    Route::get('/categories',[SeriesProductController::class,'showCategories']);
    Route::get('/check/{seriesID?}',[SeriesProductController::class,'checkSeriesID']);
    Route::get('/show/{seriesID?}',[SeriesProductController::class,'show']);
    Route::post('/create',[SeriesProductController::class,'store'] );
    Route::post('/update',[SeriesProductController::class,'update']);
});

// 產品詳細資訊頁面
Route::prefix('/product_back/detail')->group(function () {
    Route::get('/select',[DetailProductController::class,'index'] );
    Route::post('/create',[DetailProductController::class,'store'] );
    Route::post('/update',[DetailProductController::class,'update']);
    Route::post('/show',[DetailProductController::class,'show']);
});

// 查詢種類(狗狗、貓貓專區 product.html)
Route::get('/product/{category}',[ProductCategoryController::class,'index']);

// 查詢此產品資訊(product_item.html)
Route::get('/product/{c}/{seriesProduct}',[ProductItemController::class,'index']);

// 購物車(數量以及新增至購物車)
Route::prefix('productcart')->group(function(){
    Route::get('/{user}',[ProductCartController::class,'show']);
    Route::get('/{user}/{poductID}/{quantity}',[ProductCartController::class,'store']);
});
// 入庫
Route::prefix('/product_back/warehouse')->group(function(){
    Route::post('/show',[WarehouseController::class,'show']);
    Route::post('/store',[WarehouseController::class,'store']);
});
    
// 前台keyword查詢
Route::post('/product/search',[ProductSearchController::class,'index']);

// all_product.html 產品查詢
Route::get('/product_back/allproducts',[AllProductController::class,"index"]);

//                         Liu                              //



//////////////////////////////////// HUEI ////////////////////////////////////////


Route::get("/shelves", function (Request $request) {
    $status= $request->input('status');
    $shelves_products = DB::select("select * from VW_shelves where shelves_status=?",[$status]);
    return response(json_encode($shelves_products))
        ->header("content-type", "application/json")
        ->header("charset", "utf-8")
        ->header("Access-Control-Allow-Origin", "*")     
        ->header("Access-Control-Allow-Methods", "GET");
});

Route::put("/shelves/status_update/{product_id}", function (Request $request, $product_id) {
    $status = $request->input('status');
    $price = $request->input('price');
    $inventory = $request->input('inventory');
    $shelves_product_update = DB::update("update product set shelves_status =? , price = ? , update_date= now() where product_id =?", [$status, $price, $product_id]);
    $shelves_inventory_update = DB::update("update product_warehouse set inventory =? where product_id =?", [$inventory, $product_id]);
    if (($shelves_product_update + $shelves_inventory_update) > 0) {
        return response()->json(['message' => 'Product updated successfully']);
    } else {
        return response()->json(['message' => 'No product found or updated'], 404);
    }
});


Route::post("/orders_search", function (Request $request) {
    $status = $request->input('status');
    $searchOrdernumber = $request->input('searchOrdernumber');
    $phone = $request->input('phone');
    $sql = "select * FROM orders ";
    $orderby=" order by create_time desc";

    if ($status == 'all') {
        if (Str::length($searchOrdernumber)) {
            $sql = $sql . "where order_number like ?" .$orderby;
            $orders = DB::select($sql, [$searchOrdernumber]);
        } elseif (Str::length($phone)) {
            $sql = $sql . "where user_phone like ? " .$orderby;
            $orders = DB::select($sql, [$phone]);
        } else {
            $sql = $sql . $orderby;
            $orders = DB::select($sql);
        }
    } else {
        if (Str::length($searchOrdernumber)) {
            $sql = $sql . "where order_status=? and  order_number like ?" . $orderby;
            $orders = DB::select($sql, [$status, $searchOrdernumber]);
        } else {
            $sql = $sql . "where order_status=?" . $orderby;
            $orders = DB::select($sql, [$status]);
        }
    }

    return response(json_encode($orders))
        ->header("content-type", "application/json")
        ->header("charset", "utf-8")
        ->header("Access-Control-Allow-Origin", "*")
        ->header("Access-Control-Allow-Methods", "POST");
});

Route::post("/orders_detail_search", function (Request $request) {
    $order_number = $request->input('order_number');
    $sql = "select * FROM vw_orderdetail where order_number= ?";
    $order_details = DB::select($sql, [$order_number]);
    foreach ($order_details as &$order) {
        if (!empty($order->product_pic)) {
            $order->product_pic = base64_encode($order->product_pic);
        }
    }

    return response(json_encode($order_details))
        ->header("content-type", "application/json")
        ->header("charset", "utf-8")
        ->header("Access-Control-Allow-Origin", "*")
        ->header("Access-Control-Allow-Methods", "POST");
});




Route::put("/orders/note_update/{order_number}", function (Request $request, $order_number) {
    $note = $request->input('note');
    $note = $note ?? null;
    $order_status = $request->input('order_status');
    $note_update = DB::update("update orders set note =? , order_status=?  where order_number =?", [$note, $order_status, $order_number]);

    if ($note_update > 0) {
        return response()->json(['message' => 'Product updated successfully']);
    } else {
        return response()->json(['message' => 'No product found or updated'], 404);
    }
});


Route::post("/orderdetailwithuid", function (Request $request) {
    $uid = $request->input('uid');

    $sql = "select * FROM vw_orderdetail where uid= ? and buy_status='N'";

    $order_details = DB::select($sql, [$uid]);

    foreach ($order_details as &$order) {
        if (!empty($order->product_pic)) {
            $order->product_pic = base64_encode($order->product_pic);
        }
    }

    return response(json_encode($order_details))
        ->header("content-type", "application/json")
        ->header("charset", "utf-8")
        ->header("Access-Control-Allow-Origin", "*")
        ->header("Access-Control-Allow-Methods", "POST");
});


Route::delete("/orderdetail_delete", function (Request $request) {
    $uid = $request->input('uid');
    $pid = $request->input('product_id');

    $sql = "delete FROM shopping_cart_item where uid= ? and product_id =?";

    $delete_item = DB::delete($sql, [$uid, $pid]);

    if ($delete_item > 0) {
        return response()->json(['message' => 'Product updated successfully']);
    } else {
        return response()->json(['message' => 'No product found or updated'], 404);
    }
});


Route::post("/userinfoforbill", function (Request $request) {
    $uid = $request->input('uid');
    $sql = "select * FROM user_info where uid= ?";
    $userinfo = DB::select($sql, [$uid]);

    return response(json_encode($userinfo))
        ->header("content-type", "application/json")
        ->header("charset", "utf-8")
        ->header("Access-Control-Allow-Origin", "*")
        ->header("Access-Control-Allow-Methods", "POST");
});


Route::post("/orderinsert", function (Request $request) {
    $order_number = $request->input('order_number');
    $user_name = $request->input('user_name');
    $user_phone = $request->input('user_phone');
    $user_email = $request->input('user_email');
    $consignee_name = $request->input('consignee_name');
    $consignee_phone = $request->input('consignee_phone');
    $send = $request->input('send');
    $send_address = $request->input('send_address');
    $invoice = $request->input('invoice');
    $pay = $request->input('pay');
    $total = $request->input('total');

    $sql = "insert INTO orders (order_number, user_name, user_phone, user_email, consignee_name, consignee_phone, send, send_address, invoice, pay, total, order_status, create_time, note)
            VALUES( ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, '2', now(), NULL);";

    $orders = DB::insert($sql, [$order_number, $user_name, $user_phone, $user_email, $consignee_name, $consignee_phone, $send, $send_address, $invoice, $pay, $total]);

    if ($orders > 0) {
        return response()->json($orders);
    } else {
        return response()->json($orders, 404);
    }
});


Route::post("/productqupdate", function (Request $request) {
    $nupdatedata = $request->input('nupdatedata');

    foreach ($nupdatedata as $product) {
        if ($product['product_id']) {
            $sql = "update shopping_cart_item 
            SET  quantity=?
            WHERE product_id=? and order_number =? ;";
            $pupdate = DB::update($sql, [$product['quantity'], $product['product_id'], $product['order_number']]);
        }

        // DB::table('shopping_cart_item')->where('product_id', 1)->update(['quantity' => $product['quantity']]);
    }
    if ($pupdate > 0) {
        return response()->json(['message' => 'Product updated successfully']);
    } else {
        return response()->json(['message' => 'No product found or updated'], 404);
    }
});
//////////////////////////////////// HUEI ////////////////////////////////////////

//////////////////////////////////// LIN ////////////////////////////////////////

Route::get("/beauty_front2_get_schedule_fortime/{date}", function($date) {
    $result = DB::table("beauty_order")
                ->where("date", $date)
                ->get();
    return response()->json($result);
});

Route::post('/beauty_front2_insert_order', function (Request $request) {
    // 取得請求中的資料
    $data = $request->all();

    // 插入資料到 beauty_order 表
    DB::table('beauty_order')->insert([
        'uid'        => $data['uid'],
        'pid'        => $data['pid'],
        'planid'     => $data['planid'],
        'branch'     => $data['branch'],
        'date'       => $data['date'],
        'start_time' => $data['start_time'],
        'use_time'   => $data['use_time'],
        'end_time'   => $data['end_time'],
        'price'      => $data['price'],
    ]);

    return response()->json(['message' => 'Data inserted successfully']);
});

Route::get('/back_beauty_get_history_order_onepet/{pid}/{date}',  [BeautyBackController::class, 'get_beauty_history_order_onepet']);

Route::get('back_beauty_get_order_oneweek/{first_date}/{last_date}', [BeautyBackController::class, 'get_beauty_order_oneweek']);

Route::get('/front_beauty_plan_info', [BeautyFrontController::class, 'front_beauty_plan_info']);

Route::post('/front_beauty_plan_price_time', [BeautyFrontController::class, 'front_beauty_plan_price_time']);

Route::get('/front_beauty_pet_info/{uid}', [BeautyFrontController::class, 'front_beauty_pet_info']);

Route::get('/front2_beauty_select_beauty_plan_price_time/{pet_species}/{pet_weight}/{pet_fur}/{planid}', [BeautyFrontController::class, 'front2_beauty_select_beauty_plan_price_time']);

//////////////////////////////////// LIN ////////////////////////////////////////

/////////////////////////////////////LEE/////////////////////////////////////////
//會員註冊
Route::post('/member_register', RegisterController::class);

//會員登入
Route::post('/member_login', [LoginController::class, 'login']);

//會員新增寵物
Route::post('/member_add_pet', [MyPetController::class, 'add_pet']);

//查看我的寵物資料
Route::post('/member_mypet', [MyPetController::class, 'mypet_card']);

//編輯我的寵物資料
Route::post('/member_edit_pet', [MyPetController::class, 'edit_petinfo']);


//查看會員資料
Route::post('/member_userinfo', [UserinfoController::class, 'show_userinfo']);


//編輯會員資料
Route::post('/member_userinfo_update', [UserinfoController::class, 'edit_userinfo']);
/////////////////////////////////////LEE/////////////////////////////////////////


/////////////////////////////////////chen//////////////////////////////////////


// 查日期
// 顯示訂單列表
// http://localhost/happypet_back/public/api/hotel_orders
Route::get('/hotel_orders_day', [HotelOrderController::class, 'ordersByDate']);

// 顯示房型跟數量
Route::get('/check-availability', [HotelOrderController::class, 'checkAvailability']);

// 訂單進資料庫
Route::post('/hotel_orders', [HotelOrderController::class, 'store']);

// 排房間
Route::post('/assign_rooms', [HotelOrderController::class, 'assignRoomNumber']);

// 後台選日期
Route::get('/chooseRoomNumber', [HotelOrderController::class, 'chooseRoomNumber']);

// 抓model訂單號
Route::get('/getOrderNumberByRoomNumber', [HotelOrderController::class, 'getOrderNumberByRoomNumber']);

// 抓user_info
Route::get('/getUidByRoomNumber', [HotelOrderController::class, 'getUidByRoomNumber']);
Route::get('/getUserDetailsByUid', [HotelOrderController::class, 'getUserDetailsByUid']);

// 抓寵物資料
Route::get('/getPetIdByRoomNumber', [HotelOrderController::class, 'getPetIdByRoomNumber']);
Route::get('/getPetDetailsById', [HotelOrderController::class, 'getPetDetailsById']);

// 抓訂單明細
Route::get('/getOrderDetailsByRoomNumber', [HotelOrderController::class, 'getOrderDetailsByRoomNumber']);


// 查全部-後台
Route::get('/hotel_orders_all', [HotelOrderController::class, 'allOrders']);


// 查詢訂購人
Route::get('/hotel_orders_by_user', [HotelOrderController::class, 'ordersByUser']);

// 選擇該使用者的寵物
Route::get('/hotel_user_pets', [HotelOrderController::class, 'userPetName']);

/////////////////////////////////////chen//////////////////////////////////////
