<?php

namespace App\Models;

use App\Helper;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class Order extends Model
{
    use HasFactory;
    const UNPAY_STATUS = 0;
    const PAY_STATUS = 1;

    const NOTIFY_UNCALL_STATUS = 0;
    const NOTIFY_CALL_ERROR_STATUS = 1;
    const NOTIFY_CALL_SUCCESS_STATUS = 2;

    const STATUS_LIST = [
      self::UNPAY_STATUS =>  '未支付',
      self::PAY_STATUS =>  '已支付',
    ];

    protected $fillable = [
        'uuid',
        'price',
        'fee_price',
        'notify_url',
        'notify_id',
        'status',
        'notify_status',
        'notify_return',
    ];

    public static function getTableName(): string
    {
        return (new self())->getTable();
    }

    public static function generateOrderId(): string
    {
        $table = static::getTableName();
        $statement = DB::select("SHOW TABLE STATUS LIKE '$table'");
        $nextId = $statement[0]->Auto_increment;
        $date = date('YmdHis');
        $r1 = Str::random(3);
        $r2 = Str::random(2);
        return "$date$r1$nextId$r2";
    }

    public static function createOrder($request) {
         if (!$request->has(['notify_url','notify_id','price']))
             throw new \Exception("错误的参数,无法创建订单");

         $data = $request->only(['notify_url','notify_id','price']);
         $id = $request->get('notify_id');
         $order = Order::query()
             ->where('notify_id' ,$id)
             ->first();

         if ($order) {
             if ($order->status === self::PAY_STATUS)
                 throw new \Exception('订单已过期或者已失效.');

         } else{
             if (env('ENABLE_FEE_PRICE'))
                 $data['fee_price'] = number_format($data['price'] * 0.006 ,2);

             $data['uuid'] = self::generateOrderId();

             $order= Order::create($data);
         }

         return $order;
    }
}
