<?php

namespace App\Http\Controllers;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class OrderStatusController extends Controller
{
    use ApiResponseTrait;

    public function cancelOrderByUser(){

        $orders = DB::table('orders')
            ->where('users_id', '=', auth('user-api')->user()->id)
            ->where('orders.status', '=', 'awaitingPayment')
            ->get();

        if ($orders->count() > 0) {
            DB::table('orders')
                ->where('users_id', '=', auth('user-api')->user()->id)
                ->where('orders.status', '=', 'awaitingPayment')
                ->update(['status' => 'cancelledByUser']);

            return $this->apiResponse(200, $orders, 'Successfully canceled.');
        } else {
            return $this->apiResponse(423, auth('user-api')->user()->id, 'Can\'t cancel the order');
        }

    }

    public function acceptOrderByDriver(){

        $driverOrder = DB::table('orders')->where('drivers_id', '=', auth('driver-api')->user()->id)
            ->where('status', '!=', 'closed')->latest()->skip(1)->first();
        if($driverOrder !== null){
            return $this->apiResponse(425, null, 'Cannot send the order because you have order not done yet.');
        }
        else {
            $lastOrder = DB::table('orders')
                ->where('drivers_id','=',auth('driver-api')->user()->id)
                ->latest()->skip(1)->first();

            if($lastOrder !== null){
                if(DB::table('reviews')->where("orders_id","=",$lastOrder->id)->count()<=0){
                    return response($this->apiResponse(426,null,"Cannot send the order because you haven't rated your last order,"));
                }
            }

            else{
                $orders = DB::table('orders')
                    ->where('drivers_id', '=', auth('driver-api')->user()->id)
                    ->where('orders.status', '=', 'awaitingDriver')
                    ->get();

                if ($orders->count() > 0) {
                    DB::table('orders')
                        ->where('drivers_id', '=', auth('driver-api')->user()->id)
                        ->where('orders.status', '=', 'awaitingDriver')
                        ->update(['status' => 'acceptedByDriver']);

                    return $this->apiResponse(200, null, 'Successfully accepted.');
                } else {
                    return $this->apiResponse(424, null, 'Action can\'t completed');
                }
            }
        }

    }

    public function cancelOrderByDriver(){
        $orders = DB::table('orders')
            ->where('drivers_id', '=', auth('driver-api')->user()->id)
            ->where('orders.status', '=', 'acceptedByDriver')
            ->get();

        if ($orders->count() > 0) {
            DB::table('orders')
                ->where('drivers_id', '=', auth('driver-api')->user()->id)
                ->where('orders.status', '=', 'acceptedByDriver')
                ->update(['status' => 'cancelledByDriver']);

            return $this->apiResponse(200, $orders, 'Successfully canceled.');
        } else {
            return $this->apiResponse(427, auth('driver-api')->user()->id, 'Can\'t cancel the order,');
        }
    }

    public function arrivedToPickUpLocation(){
        $orders = DB::table('orders')
            ->where('drivers_id', '=', auth('driver-api')->user()->id)
            ->where('orders.status', '=', 'acceptedByDriver')
            ->get();

        if ($orders->count() > 0) {
            DB::table('orders')
                ->where('drivers_id', '=', auth('driver-api')->user()->id)
                ->where('orders.status', '=', 'acceptedByDriver')
                ->update(['status' => 'arrivedPickUpLocation']);

            return $this->apiResponse(200, $orders, 'Successfully updated.');
        } else {
            return $this->apiResponse(428, auth('driver-api')->user()->id, 'Action can\'t completed.');
        }
    }

    public function goodsLoading(){
        $orders = DB::table('orders')
            ->where('drivers_id', '=', auth('driver-api')->user()->id)
            ->where('orders.status', '=', 'arrivedPickUpLocation')
            ->get();

        if ($orders->count() > 0) {
            DB::table('orders')
                ->where('drivers_id', '=', auth('driver-api')->user()->id)
                ->where('orders.status', '=', 'arrivedPickUpLocation')
                ->update(['status' => 'goodsLoading']);

            return $this->apiResponse(200, $orders, 'Successfully updated.');
        } else {
            return $this->apiResponse(428, auth('driver-api')->user()->id, 'Action can\'t completed.');
        }
    }

    public function startMoving(){
        $orders = DB::table('orders')
            ->where('drivers_id', '=', auth('driver-api')->user()->id)
            ->where('orders.status', '=', 'goodsLoading')
            ->get();

        if ($orders->count() > 0) {
            DB::table('orders')
                ->where('drivers_id', '=', auth('driver-api')->user()->id)
                ->where('orders.status', '=', 'goodsLoading')
                ->update(['status' => 'startMoving']);

            return $this->apiResponse(200, $orders, 'Successfully updated.');
        } else {
            return $this->apiResponse(430, auth('driver-api')->user()->id, 'Action can\'t completed.');
        }
    }

    public function arrivedToDestinationLocation(){


        $orders = DB::table('orders')
            ->where('drivers_id', '=', auth('driver-api')->user()->id)
            ->where('orders.status', '=', 'startMoving')
            ->get();

        if ($orders->count() > 0) {
            DB::table('orders')
                ->where('drivers_id', '=', auth('driver-api')->user()->id)
                ->where('orders.status', '=', 'startMoving')
                ->update(['status' => 'arrivedToDestinationLocation']);

            return $this->apiResponse(200, $orders, 'Successfully updated.');
        } else {
            return $this->apiResponse(431, auth('driver-api')->user()->id, 'Action can\'t completed.');
        }
    }

    public function finishTripByDriver(){
        $orders = DB::table('orders')
            ->where('drivers_id', '=', auth('driver-api')->user()->id)
            ->where('orders.status', '=', 'arrivedToDestinationLocation')
            ->get();

        if ($orders->count() > 0) {
            DB::table('orders')
                ->where('drivers_id', '=', auth('driver-api')->user()->id)
                ->where('orders.status', '=', 'arrivedToDestinationLocation')
                ->update(['status' => 'finishedTripByDriver']);

            return $this->apiResponse(200, $orders, 'Successfully updated.');
        } else {
            return $this->apiResponse(432, auth('driver-api')->user()->id, 'Action can\'t completed.');
        }
    }

    public function finishTripByUser(){
        $orders = DB::table('orders')
            ->where('users_id', '=', auth('user-api')->user()->id)
            ->where('orders.status', '=', 'finishedTripByDriver')
            ->get();

        if ($orders->count() > 0) {
            DB::table('orders')
                ->where('users_id', '=', auth('user-api')->user()->id)
                ->where('orders.status', '=', 'finishedTripByDriver')
                ->update(['status' => 'fininshedTripByUser']);

            return $this->apiResponse(200, $orders, 'Successfully updated.');
        } else {
            return $this->apiResponse(433, auth('user-api')->user()->id, 'Action can\'t completed.');
        }
    }

    public function codeToCLoseTripByDriver(Request $request){

        $validate = Validator::make($request->all(), [
            'code' => 'required',
        ]);

        if ($validate->fails()) {
            return $this->apiResponse(407, null, $validate->errors());
        }

        $orders = DB::table('orders')
            ->where('drivers_id', '=', auth('driver-api')->user()->id)
            ->where('code', '=', $request->code)
            ->where('orders.status', '=', 'fininshedTripByUser')
            ->get();

        if ($orders->count() > 0) {
            /*if($orders->code = $request->code){*/
            DB::table('orders')
                ->where('drivers_id', '=', auth('driver-api')->user()->id)
                ->where('code', '=', $request->code)
                ->where('orders.status', '=', 'fininshedTripByUser')
                ->update(['status' => 'closed']);

            return $this->apiResponse(200, null, 'Done successfully.');

            /*else {
                return $this->apiResponse(435, null, 'Code is wrong.');
            }*/
        } else {
            return $this->apiResponse(434, null, 'Action can\'t completed.');
        }
    }

}
