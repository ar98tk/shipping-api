<?php

namespace App\Http\Controllers;

use App\Models\OfflinePayment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Intervention\Image\Facades\Image;

class PaymentController extends Controller
{
    use ApiResponseTrait;
    public function paymentType(Request $request){
        $validate = Validator::make($request->all(), [
            'payment_type' => 'required|in:online,offline',
        ]);

        if ($validate->fails()) {
            return $this->apiResponse(407, null, $validate->errors());
        }


        $order = DB::table('orders')
            ->where('users_id', '=', auth('user-api')->user()->id)
            ->latest()->first();
        DB::table('bills')
            ->where('orders_id','=',$order->id)
            ->update(['payment_type' => $request->payment_type]);
        return $this->apiResponse(200, null, 'Bill Payment Type Updated Successfully');

    }

    public function offlinePayment(Request $request){
        $bills = DB::table('orders')
            ->where('users_id', '=', auth('user-api')->user()->id)
            ->join('bills', 'orders.id', '=', 'bills.orders_id')
            ->where('bills.status', '=', 'waiting')
            ->select('bills.id')
            ->get();

        $validate = Validator::make($request->all(), [
            'image_deposit' => 'required|mimes:png,jpg,jpeg',
        ]);

        if ($validate->fails()) {
            return $this->apiResponse(407, null, $validate->errors());
        }
        $offline_payment = new OfflinePayment();
        $offline_payment->bills_id = $bills[0]->id;
        $offline_payment->code = random_int(10000, 99999);

        if ($image = $request->file('image_deposit')) {
            if ($offline_payment->image_deposit != '') {
                if (File::exists('assets/payment/' . $offline_payment->image_deposit)) {
                    unlink('assets/order_image/' . $offline_payment->image_deposit);
                }
            }
            $filename = time() . '-' . Str::random(7) . '.' . $image->getClientOriginalExtension();
            $path = public_path("assets/payment/" . $filename);
            Image::make($image->getRealPath())->resize(1920, 1053, function ($constraint) {
                $constraint->aspectRatio();
            })->save($path, 100);
            $offline_payment->image_deposit = $filename;
        }
        $offline_payment->save();
        return $this->apiResponse(200,$offline_payment,'Payment Added Successfully');
    }
}
