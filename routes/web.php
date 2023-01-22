<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/


Route::get('/apy',function () {
    $request = request();
    $notifyUrl = $request->get('notify_url', "http://test.app.test/api/notify");
    $notifyId = $request->get('notify_id',\App\Models\Order::generateOrderId());
    $price = $request->get('price',100);
    $feePrice = number_format($price * 0.006,2);
    return view('hello' , [
        'notifyUrl' => $notifyUrl,
        'notifyId' => $notifyId,
        'price' => $price,
        'feePrice' => $feePrice,
    ]);
});

