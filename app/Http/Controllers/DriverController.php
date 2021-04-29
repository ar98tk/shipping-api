<?php

namespace App\Http\Controllers;

use App\Models\Driver;
use App\Models\Session;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Intervention\Image\Facades\Image;

class DriverController extends Controller
{
    use ApiResponseTrait;
    public function register(Request $request){

        $validate = Validator::make($request->all(),[
            'image'                  => 'required|mimes:png,jpg,jpeg',
            'car_photo'              => 'required|mimes:png,jpg,jpeg',
            'driving_license_image'  => 'required|mimes:png,jpg,jpeg',
            'car_license_image'      => 'required|mimes:png,jpg,jpeg',
            'id_image'               => 'required|mimes:png,jpg,jpeg',
            'name'                   => 'required',
            'country_code'           => 'required',
            'car_name'               => 'required',
            'car_model'              => 'required',
            'car_license_number'     => 'required',
            'phone'                  => 'required|unique:drivers|digits_between:7,25|numeric',
            'password'               => 'required|digits_between:6,30',
            'language'               => 'required|in:ar,en'
        ]);

        if($validate->fails()){
            return $this->apiResponse(408, null, $validate->errors());
        }

        $driver = new Driver();
        $driver->name               = $request->name;
        $driver->car_name           = $request->car_name;
        $driver->car_model          = $request->car_model;
        $driver->car_license_number = $request->car_license_number;
        $driver->phone              = $request->phone;
        $driver->language           = $request->language;
        $driver->country_code       = $request->country_code;
        $driver->trucks_types_id    = $request->trucks_types_id;
        $driver->password           = Hash::make($request->password);
        $driver->api_token          = Str::random(65);

        if ($image = $request->file('image')) {
            if ($driver->image != '') {
                if (File::exists('assets/driver_image/' . $driver->image)) {
                    unlink('assets/driver_image/' . $driver->image);
                }
            }
            $filename =  time().'-'.Str::random(7).'.'.$image->getClientOriginalExtension();
            $path = public_path("assets/driver_image/" . $filename);
            Image::make($image->getRealPath())->resize(1920, 1053, function ($constraint) {
                $constraint->aspectRatio();
            })->save($path, 100);
            $driver->image       = $filename;
        }

        if ($image = $request->file('car_photo')) {
            if ($driver->car_photo != '') {
                if (File::exists('assets/car_photo/' . $driver->car_photo)) {
                    unlink('assets/car_photo/' . $driver->car_photo);
                }
            }
            $filename =  time().Str::random(7).'.'.$image->getClientOriginalExtension();
            $path = public_path("assets/car_photo/" . $filename);
            Image::make($image->getRealPath())->resize(1920, 1053, function ($constraint) {
                $constraint->aspectRatio();
            })->save($path, 100);
            $driver->car_photo       = $filename;
        }

        if ($image = $request->file('driving_license_image')) {
            if ($driver->driving_license_image != '') {
                if (File::exists('assets/driving_license_image/' . $driver->driving_license_image)) {
                    unlink('assets/driving_license_image/' . $driver->driving_license_image);
                }
            }
            $filename =  time().Str::random(7).'.'.$image->getClientOriginalExtension();
            $path = public_path("assets/driving_license_image/" . $filename);
            Image::make($image->getRealPath())->resize(1920, 1053, function ($constraint) {
                $constraint->aspectRatio();
            })->save($path, 100);
            $driver->driving_license_image  = $filename;
        }

        if ($image = $request->file('car_license_image')) {
            if ($driver->car_license_image != '') {
                if (File::exists('assets/car_license_image/' . $driver->car_license_image)) {
                    unlink('assets/car_license_image/' . $driver->car_license_image);
                }
            }
            $filename =  time().Str::random(7).'.'.$image->getClientOriginalExtension();
            $path = public_path("assets/car_license_image/" . $filename);
            Image::make($image->getRealPath())->resize(1920, 1053, function ($constraint) {
                $constraint->aspectRatio();
            })->save($path, 100);
            $driver->car_license_image  = $filename;
        }

        if ($image = $request->file('id_image')) {
            if ($driver->id_image != '') {
                if (File::exists('assets/id_image/' . $driver->id_image)) {
                    unlink('assets/id_image/' . $driver->id_image);
                }
            }
            $filename =  time().Str::random(7).'.'.$image->getClientOriginalExtension();
            $path = public_path("assets/id_image/" . $filename);
            Image::make($image->getRealPath())->resize(1920, 1053, function ($constraint) {
                $constraint->aspectRatio();
            })->save($path, 100);
            $driver->id_image  = $filename;
        }
        $driver->save();

        $token = auth('driver-api')->attempt([
            'phone'    => $request->phone,
            'password' => $request->password,
        ]);
        DB::table('drivers')->where('id','=',auth('driver-api')->user()->id)
            ->update(['api_token' => $token]);

        $session = new Session();
        $session->drivers_id = auth('driver-api')->user()->id;
        $session->code = random_int(10000,99999);
        $session->save();

        $basic  = new \Vonage\Client\Credentials\Basic("2ae1cc75", "00XRNXoynizWxCHI");
        $client = new \Vonage\Client($basic);
        $response = $client->sms()->send(
            new \Vonage\SMS\Message\SMS("201278634620", 'Shipping', 'Your Verification Code is: '. $session->code. '     ')
        );

        if ($driver) {
            return $this->apiResponse(200, $token, 'Successfully registered');
        }
    }

    public function updatePassword(Request $request){
        $validate = Validator::make($request->all(), [
            'new_password' => 'required|digits_between:6,30',
            'old_password' => 'required|digits_between:6,30',
        ]);

        if ($validate->fails()) {
            return $this->apiResponse(407, null, $validate->errors());
        }

        if(Hash::check($request->old_password, auth('driver-api')->user()->password)){
            DB::table('drivers')->where('id', '=', auth('driver-api')->user()->id)
                ->update(['password' => bcrypt($request->new_password)]);
            return $this->apiResponse(200, null, 'Successfully Updated');
        }
        else{
            return $this->apiResponse(414, null, 'The old password is incorrect');
        }
    }

    public function login(Request $request){
        if($token = auth('driver-api')->attempt([
            'phone'    => $request->phone,
            'password' => $request->password,
        ]))
        {
            return $this->apiResponse(200, $token, 'Logged In Successfully');
        }
        else{
            return $this->apiResponse(412, null, 'Phone or Password is not correct');
        }
    }

    public function validateCode(Request $request){
        $codeCheck = DB::table('sessions')
            ->where('drivers_id', '=',auth('driver-api')->user()->id)
            ->where('code', '=', $request->code)->get();

        if($codeCheck->count() > 0)
        {
            DB::table('sessions')
                ->where('code', '=', $request->code)
                ->where('drivers_id','=', auth('driver-api')->user()->id)
                ->delete();

            DB::table('drivers')
                ->where('id','=', auth('driver-api')->user()->id)
                ->update(['is_verified' => '1']);

            return $this->apiResponse(200, null, 'Successfully done.');
        }
        else{
            return $this->apiResponse(409, null, 'Verification code is wrong or has expired.');
        }
    }

    public function logout()
    {
        auth('driver-api')->logout();
        return $this->apiResponse(200, null,'Successfully logged out');
    }

    public function changeLanguage(Request $request){

        $validate = Validator::make($request->all(),[
            'language'               => 'required|in:ar,en'
        ]);

        if($validate->fails()){
            return $this->apiResponse(408, null, $validate->errors());
        }

        DB::table('drivers')
            ->where('id','=',auth('driver-api')->user()->id)
            ->update(['language' => $request->language]);
        return $this->apiResponse(200, null,'Language Updated Successfully.');
    }

    public function myProfit(){
        $profit = DB::table('financials')
            ->where('drivers_id','=', auth('driver-api')->user()->id)
            ->get();
        return $this->apiResponse(200, $profit,'Profit.');
    }

    public function getActiveOrder(){
        $active_order = DB::table('orders')
            ->where('drivers_id','=',auth('driver-api')->user()->id)
            ->whereNotIn('status', ['awaitingPayment',
                'awaitingDriver',
                'cancelledByUser',
                'cancelledByUser',
                'cancelledByDriver',
                'closed'])
            ->get();
        return $this->apiResponse(200,$active_order,'');
    }

    public function setDriverLocation(Request $request){
        DB::table('drivers')
            ->where('id','=',auth('driver-api')->user()->id)
            ->update(['locations_id' => $request->locations_id]);

        return $this->apiResponse(200,null,'Location Updated Successfully');
    }

    public function unseenNotifications(){
        $orders = DB::table('notify_users')
            ->where('drivers_id', '=', auth('driver-api')->user()->id)
            ->where('is_seen', '=', 0)
            ->get()->count();

        return $this->apiResponse(200, $orders,'Unseen Notifications Count');
    }

    public function notifications(){
        $active_order = DB::table('orders')
            ->where('users_id','=',auth('driver-api')->user()->id)
            ->whereNotIn('status', ['awaitingPayment',
                'awaitingDriver',
                'cancelledByUser',
                'cancelledByUser',
                'cancelledByDriver',
                'closed'])
            ->get();

        $notifications = DB::table('notifications')
            ->where('orders_id','=', $active_order[0]->id)
            ->get();


        DB::table('notify_users')
            ->where('notifications_id', '=', $notifications[0]->id)
            ->update(['is_seen' => 1]);

        return $this->apiResponse(200,$notifications, 'Notification');

    }
}
