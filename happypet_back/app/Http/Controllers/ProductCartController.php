<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;



class ProductCartController extends Controller
{
    //
    function show($user){
        if(!isset($user)) return;
        // COALESCE()：返回參數中第一個非空 (non-null) 的值
        $totalAmount = DB::scalar("SELECT COALESCE(SUM(quantity), 0) FROM shopping_cart_item WHERE uid = ?",[$user]);
        // Log::info('====>', ['productcart的user' => $user,'我是數量'=>$totalAmount]); 
        echo $totalAmount;
    }

    function store($user,$productID,$quantity){
        // Log::info('====>', ['productcart的user' => $user,'我是pdID'=>$productID,'我是數量'=>$quantity]); 
        // Log::info('我是登入user編號 :', ['user' => $user]); 
        // 會傳回異動筆數
        if($user === null){
            Log::info('沒有登入');
            return;
        }
        if($user && (!isset($productID) || !isset($quantity))){
            return;
        }
        
        $ordernumber_old = DB::select('SELECT order_number FROM shopping_cart_item WHERE uid = ? limit 1',[$user]);
        if(!empty($ordernumber_old)){
            $ordernumber_old = $ordernumber_old[0]->order_number;
            $pdCount = DB::scalar("SELECT count(*) FROM shopping_cart_item WHERE product_id = ? AND uid = ?",[$productID,$user]);
            Log::info('pdCount====>', [$pdCount]); 

            Log::info('ordernumber_old :', ['ordernumber_old' => $ordernumber_old]); 
            
            if($pdCount > 0){
                $updateResult = DB::update("UPDATE shopping_cart_item 
                                SET quantity = quantity + ?
                                WHERE product_id = ? ",[$quantity,$productID]);
                                // WHERE product_id = ? AND order_number = ? ",[$quantity,$productID,$ordernumber_old]);
                echo $updateResult;
                Log::info('訂單編號與產品存在，update====>', [$updateResult]); 
            }else{
                $insertResult = DB::insert("INSERT INTO shopping_cart_item(order_number,uid,product_id,quantity,create_time)
                            VALUES(?,?,?,?,NOW())",[$ordernumber_old,$user,$productID,$quantity]);
                Log::info('insert====>', [$insertResult]); 
                echo $insertResult;
            }           
        }else{
            // uid
            // $aaa = DB::select('SELECT count(uid) FROM shopping_cart_item WHERE uid = ?',["qwe123"]);        
            DB::select("call giveOrderNumber(@current_order)");
            $callProcedure = DB::select('select @current_order');
            // Log::info('callProcedure:', $callProcedure);
            // Log::info('orderNumber_1 :', $callProcedure[0]->{'@current_order'});
            $orderNumber = $callProcedure[0]->{'@current_order'}; //取得orderNumber
            // Log::info('orderNumber_2 :', ['orderNumber' => $orderNumber]); 
            
            $insertResult = DB::insert("INSERT INTO shopping_cart_item(order_number,uid,product_id,quantity,create_time)
                    VALUES(?,?,?,?,NOW())",[$orderNumber,$user,$productID,$quantity]);
            // echo "異動筆數".$insertResult;
            echo $insertResult;
            Log::info('====>', ['如果訂單編號不存在則新增編號再insert，數量：' => $insertResult]); 
        }   
    }   
}
