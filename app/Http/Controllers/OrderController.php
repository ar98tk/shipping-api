<?php

namespace App\Http\Controllers;

use App\Models\Bill;
use App\Models\DiscountCode;
use App\Models\Order;
use App\Models\Review;
use App\Models\UsersHasDiscountCode;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Intervention\Image\Facades\Image;

class OrderController extends Controller
{
    use ApiResponseTrait;

    public function discountCode($code)
    {
        if (DiscountCode::where('code', $code)->exists()) {

            if (DB::table('users_has_discount_code')
                ->where('users_id', auth('user-api')->user()->id)->exists()) {
                return $this->apiResponse(421, 0, 'Code used before.');

            } else {
                $codes = DB::table('discount_code')->where('code', '=', $code)->first();
                if ($codes->count == 0){
                    return $this->apiResponse(420, null, 'Code reached the limit.');
                }
                else if ($codes->is_active == 0){
                    return $this->apiResponse(419, null, 'Code not activated.');
                }
                else if ($codes->count > 0 && $codes->is_active = 1 && $codes->end_date > now()) {
                    $discount_apply = new UsersHasDiscountCode();
                    $discount_apply->users_id = auth('user-api')->user()->id;
                    $discount_apply->discount_code_id = $codes->id;
                    $discount_apply->save();
                    DB::table('discount_code')->where('code', '=', $code)
                        ->decrement('count');

                    return $this->apiResponse(200, null, 'Discount applied successfully.');
                }
                else {
                    return $this->apiResponse(419, null, 'Code not activated.');
                }
            }
        } else {
            return $this->apiResponse(422, 0, 'Code not exist.');
        }
    }

    public function addOrder(Request $request)
    {
        $user = DB::table('orders')->where('users_id', '=', auth('user-api')->user()->id)
            ->where('status', '!=', 'closed');
        if ($user->count() > 0) {
            return $this->apiResponse(417, auth('user-api')->user()->id, 'Cannot send the order because you have order not done yet.');
        } else {

            $lastOrder = DB::table('orders')
                ->where('users_id','=',auth('user-api')->user()->id)
                ->latest()->first();

            if($lastOrder !== null){
                if(DB::table('reviews')->where("orders_id","=",$lastOrder->id)->count()<=0){
                    return response($this->apiResponse(418,null,"Cannot send the order because you haven't rated your last order,"));
                }
            }

            else{
                $validate = Validator::make($request->all(), [

                    'image' => 'nullable|mimes:png,jpg,jpeg',
                    'locations_pickup_id' => 'required',
                    'country_code' => 'required',
                    'locations_destination_id' => 'required',
                    'load_weight' => 'nullable',
                    'descriptions' => 'nullable',
                    'phone' => 'required|digits_between:7,25|numeric',
                    'recipient_name' => 'required',
                    'goods_types_id' => 'required|numeric',
                    'trucks_types_id' => 'required|numeric',
                    'i_am_recipient' => 'required|boolean'
                ]);

                if ($validate->fails()) {
                    return $this->apiResponse(407, null, $validate->errors());
                }

                $order = new Order();
                $order->locations_pickup_id = $request->locations_pickup_id;
                $order->locations_destination_id = $request->locations_destination_id;
                $order->recipient_name = $request->recipient_name;
                $order->load_weight = $request->load_weight;
                $order->phone = $request->phone;
                $order->descriptions = $request->descriptions;
                $order->country_code = $request->country_code;
                $order->trucks_types_id = $request->trucks_types_id;
                $order->goods_types_id = $request->goods_types_id;
                $order->i_am_recipient = $request->i_am_recipient;
                $order->users_id = auth('user-api')->user()->id;
                $order->code = Str::random(5);

                if ($image = $request->file('image')) {
                    if ($order->image != '') {
                        if (File::exists('assets/order_image/' . $order->image)) {
                            unlink('assets/order_image/' . $order->image);
                        }
                    }
                    $filename = time() . '-' . Str::random(7) . '.' . $image->getClientOriginalExtension();
                    $path = public_path("assets/order_image/" . $filename);
                    Image::make($image->getRealPath())->resize(1920, 1053, function ($constraint) {
                        $constraint->aspectRatio();
                    })->save($path, 100);
                    $order->image = $filename;
                }
                $order->save();

                $pick_up = DB::table('locations')
                    ->where('id', '=', $request->locations_pickup_id)->get();

                $destination = DB::table('locations')
                    ->where('id', '=', $request->locations_destination_id)->get();

                $distance = distance($pick_up[0]->latitude, $pick_up[0]->longitude, $destination[0]->latitude, $destination[0]->longitude, "K");

                $order_id = DB::table('orders')->select('id')
                    ->where('users_id', '=', auth('user-api')->user()->id)
                    ->latest()->get();

                $bill = new Bill();
                $bill->orders_id = $order_id[0]->id;
                $bill->cost = round(2.5 * $distance, 0);
                $bill->save();
                if ($order) {
                    return $this->apiResponse(200, $bill, 'Order Successfully Submitted.');
                }
            }
        }
    }

    public function getUserOrders(){
        $orders = DB::table('orders')
            ->where('users_id', '=', auth('user-api')->user()->id)
            ->where('orders.status', '=', 'closed')
            ->join('bills', 'orders.id', '=', 'bills.orders_id')
            ->where('bills.status', '=', 'paid')
            ->select('orders.*', 'bills.status', 'orders.users_id')
            ->get();
        if ($orders->count() > 0) {
            return $this->apiResponse(200, $orders, 'User history of orders.');
        } else {
            return $this->apiResponse(417, auth('user-api')->user()->id, 'No Orders Found.');
        }
    }

    public function getUserPendingOrder(){

        $orders = DB::table('orders')
            ->where('users_id', '=', auth('user-api')->user()->id)
            ->where('orders.status', '!=', 'closed')
            ->join('bills', 'orders.id', '=', 'bills.orders_id')
            ->where('bills.status', '=', 'paid')
            ->select('orders.*', 'bills.status', 'orders.users_id','orders.status')
            ->get();
        if ($orders->count() > 0) {
            return $this->apiResponse(200, $orders, 'User active order.');
        } else {
            return $this->apiResponse(417, auth('user-api')->user()->id, 'No Orders Found.');
        }
    }

    public function getDriverOrders(){
        $orders = DB::table('orders')
            ->where('drivers_id', '=', auth('driver-api')->user()->id)
            ->where('orders.status', '=', 'closed')
            ->join('bills', 'orders.id', '=', 'bills.orders_id')
            ->where('bills.status', '=', 'paid')
            ->select('orders.*', 'bills.status', 'orders.drivers_id')
            ->get();
        if ($orders->count() > 0) {
            return $this->apiResponse(200, $orders, 'Driver history of orders.');
        } else {
            return $this->apiResponse(417, auth('driver-api')->user()->id, 'No Orders Found.');
        }
    }

    public function getDriverPendingOrder(){

        $orders = DB::table('orders')
            ->where('drivers_id', '=', auth('driver-api')->user()->id)
            ->where('orders.status', '!=', 'closed')
            ->join('bills', 'orders.id', '=', 'bills.orders_id')
            ->where('bills.status', '=', 'paid')
            ->select('orders.*', 'bills.status', 'orders.drivers_id','orders.status')
            ->get();
        if ($orders->count() > 0) {
            return $this->apiResponse(200, $orders, 'Driver active order.');
        } else {
            return $this->apiResponse(417, auth('driver-api')->user()->id, 'No Orders Found.');
        }
    }

    public function getTrucksTypes(){
        $trucksTypes = DB::table('trucks_types')
            ->get();
        return $this->apiResponse(200,$trucksTypes,'');
    }

    public function getbankAccounts(){
        $bankAccounts = DB::table('bank_accounts')
            ->get();
        return $this->apiResponse(200,$bankAccounts,'');
    }
}
