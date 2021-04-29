<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Session;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    use ApiResponseTrait;

    public function register(Request $request){
        $validate = Validator::make($request->all(), [
            'name' => 'required',
            'country_code' => 'required',
            'phone' => 'required|unique:users|digits_between:7,25|numeric',
            'password' => 'required|digits_between:6,30',
            'language' => 'required|in:ar,en'
        ]);

        if ($validate->fails()) {
            return $this->apiResponse(407, null, $validate->errors());
        }

        $user = new User;
        $user->name = $request->name;
        $user->phone = $request->phone;
        $user->language = $request->language;
        $user->country_code = $request->country_code;
        $user->password = Hash::make($request->password);
        $user->api_token = Str::random(65);
        $user->save();


        $token = auth('user-api')->attempt([
            'phone'    => $request->phone,
            'password' => $request->password,
        ]);

        DB::table('users')->where('id','=',auth('user-api')->user()->id)
            ->update(['api_token' => $token]);
        $session = new Session();
        $session->users_id = auth('user-api')->user()->id;
        $session->code = random_int(10000,99999);
        $session->save();

        /*$basic  = new \Vonage\Client\Credentials\Basic("2ae1cc75", "00XRNXoynizWxCHI");
        $client = new \Vonage\Client($basic);
        $response = $client->sms()->send(
            new \Vonage\SMS\Message\SMS("201278634620", 'Shipping', 'Your Verification Code is: '. $session->code. '     ')
        );*/

        if ($user) {
            return $this->apiResponse(200, $token, 'Successfully registered');
        }
    }

    public function resendCode(){
        $lastCode = DB::table('sessions')
            ->where('users_id','=',auth('user-api')->user()->id)
            ->latest()->first();

        $expire = Carbon::parse($lastCode->created_at)
            ->subMinute(2)
            ->format('Y-m-d H:i:s');

        if($lastCode->created_at < $expire){
            $session = new Session();
            $session->users_id = auth('user-api')->user()->id;
            $session->code = random_int(10000,99999);
            $session->save();
            return $this->apiResponse(200,null,'Activation code sent');
        }else{
            return $this->apiResponse(200,null,'Failed to send last code less than 2 minutes ago.');

        }

    }

    public function validateCode(Request $request){
        $codeCheck = DB::table('sessions')
            ->where('users_id', '=',auth('user-api')->user()->id)
            ->where('code', '=', $request->code)->get();

        if($codeCheck->count() > 0)
        {
             DB::table('sessions')
                 ->where('code', '=', $request->code)
                 ->where('users_id','=', auth('user-api')->user()->id)
                 ->delete();

             DB::table('users')
                 ->where('id','=', auth('user-api')->user()->id)
                 ->update(['is_verified' => '1']);

            return $this->apiResponse(200, null, 'Successfully done.');
        }
        else{
            return $this->apiResponse(409, null, 'Verification code is wrong or has expired.');
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

        if(Hash::check($request->old_password, auth('user-api')->user()->password)){
            DB::table('users')->where('id', '=', auth('user-api')->user()->id)
            ->update(['password' => bcrypt($request->new_password)]);
            return $this->apiResponse(200, null, 'Successfully Updated');
        }
        else{
            return $this->apiResponse(414, null, 'The old password is incorrect');
        }
    }

    public function login(Request $request){
        if($token = auth('user-api')->attempt([
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

    public function logout()
    {
        auth('user-api')->logout();
        return $this->apiResponse(200, null,'Successfully logged out');
    }

    public function changeLanguage(Request $request){
        $validate = Validator::make($request->all(),[
            'language'               => 'required|in:ar,en'
        ]);

        if($validate->fails()){
            return $this->apiResponse(408, null, $validate->errors());
        }
        DB::table('users')
            ->where('id','=',auth('user-api')->user()->id)
            ->update(['language' => $request->language]);
        return $this->apiResponse(200, null,'Language Updated Successfully.');
    }

    public function getActiveOrder(){
        $active_order = DB::table('orders')
            ->where('users_id','=',auth('user-api')->user()->id)
            ->whereNotIn('status', ['awaitingPayment',
                'awaitingDriver',
                'cancelledByUser',
                'cancelledByUser',
                'cancelledByDriver',
                'closed'])
            ->get();
        return $this->apiResponse(200,$active_order,'');
    }

    public function getDriverLocation(){

        $orders = DB::table('orders')
            ->where('users_id', '=', auth('user-api')->user()->id)
            ->whereNotIn('orders.status',['awaitingPayment',
                'awaitingDriver',
                'cancelledByUser',
                'cancelledByUser',
                'cancelledByDriver',
                'closed'])
            ->join('drivers', 'orders.drivers_id', '=', 'drivers.id')
            ->select('drivers.*')
            ->get();
        if($orders->count() > 0){
            return $this->apiResponse(200,$orders,'');
        } else {
            return $this->apiResponse(432,null,'No driver assigned to your order yet.');
        }
    }

    public function unseenNotifications(){
        $orders = DB::table('notify_users')
            ->where('users_id', '=', auth('user-api')->user()->id)
            ->where('is_seen', '=', 0)
            ->get()->count();

        return $this->apiResponse(200, $orders,'Unseen Notifications Count');
    }

    public function notifications(){
        $active_order = DB::table('orders')
            ->where('users_id','=',auth('user-api')->user()->id)
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

    public function updateUserProfile(Request $request){
        $validate = Validator::make($request->all(), [
            'name'         => 'required',
            'country_code' => 'required',
            'phone'        => 'required|unique:users|digits_between:7,25|numeric',
            //'phone'        => ['nullable','numeric','digits_between:7,25', Rule::unique('users')->ignore($this->auth('user-api')->user())],
            'language'     => 'required|in:ar,en'
        ]);

        if ($validate->fails()) {
            return $this->apiResponse(415, null, $validate->errors());
        }

        $users = User::where('id', '=', auth('user-api')->user()->id)->first();
        $data['name']          = $request->name;
        $data['country_code']  = $request->country_code;
        $data['phone']         = $request->phone;
        $data['language']      = $request->language;
        $users->is_verified = 0;
        $users->update($data);
        return $this->apiResponse(200,null,'Success');

    }
}
