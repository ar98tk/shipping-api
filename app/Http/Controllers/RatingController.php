<?php

namespace App\Http\Controllers;

use App\Models\Review;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class RatingController extends Controller
{
    use ApiResponseTrait;

    public function addRating(Request $request){

        if(auth('user-api')->check()){
            $lastOrderUser = DB::table('orders')
                ->where('users_id','=',auth('user-api')->user()->id)
                ->latest()->first();

            $validate = Validator::make($request->all(), [
                'rate' => 'required|numeric|min:0|max:5',
            ]);

            if ($validate->fails()) {
                return $this->apiResponse(407, null, $validate->errors());
            }
            $review = new Review();
            $review->rate       = $request->rate;
            $review->orders_id  = $lastOrderUser->id;
            $review->users_id   = $lastOrderUser->users_id;
            $review->drivers_id = $lastOrderUser->drivers_id;
            $review->type       = 'userToDriver';
            $review->save();
            return $this->apiResponse(200,null,'Your rating has been successfully added.');
        }
        else if(auth('driver-api')->check()){
            $lastOrderDriver = DB::table('orders')
                ->where('drivers_id','=',auth('driver-api')->user()->id)
                ->latest()->first();

            $validate = Validator::make($request->all(), [
                'rate' => 'required|numeric|min:0|max:5',
            ]);

            if ($validate->fails()) {
                return $this->apiResponse(407, null, $validate->errors());
            }

            $review = new Review();
            $review->rate       = $request->rate;
            $review->orders_id  = $lastOrderDriver->id;
            $review->users_id   = $lastOrderDriver->users_id;
            $review->drivers_id = $lastOrderDriver->drivers_id;
            $review->type       = 'driverToUser';
            $review->save();
            return $this->apiResponse(200,null,'Your rating has been successfully added.');
        }

    }
}
