<?php

namespace App\Http\Controllers;

use App\Models\Contact;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class ContactController extends Controller
{
    use ApiResponseTrait;
    public function contact(Request $request){
        if(auth('user-api')->check() || auth('driver-api')->check()){
            $validate = Validator::make($request->all(), [
                'message' => 'required',
                'contacts_types_id' => 'required',
            ]);

            if ($validate->fails()) {
                return $this->apiResponse(407, null, $validate->errors());
            }

            $message = new Contact();
            $message->message = $request->message;
            $message->contacts_types_id = $request->contacts_types_id;
            if(auth('user-api')->check()){
                $message->users_id = auth('user-api')->user()->id;
            }
            else if (auth('driver-api')->check()){
                $message->drivers_id = auth('driver-api')->user()->id;
            }
            $message->code = random_int(10000,99999);
            $message->save();

            return $this->apiResponse(200, null, 'Sent successfully.');

        } else {
            $validate = Validator::make($request->all(), [
                'message' => 'required',
                'contacts_types_id' => 'required',
                'name' => 'required',
                'phone' => 'required',
            ]);

            if ($validate->fails()) {
                return $this->apiResponse(407, null, $validate->errors());
            }

            $message = new Contact();
            $message->message = "Name : ". $request->name .
            " - Phone : " . $request->phone . " - Message : " .$request->message;
            $message->contacts_types_id = $request->contacts_types_id;
            $message->code = random_int(10000,99999);
            $message->save();

            return $this->apiResponse(200, null, 'Sent successfully.');
        }
    }

    public function getContactsTypes(){
        $contactsTypes = DB::table('contacts_types')
            ->get();
        return $this->apiResponse(200,$contactsTypes,'');
    }

}
