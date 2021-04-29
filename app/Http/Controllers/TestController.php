<?php

namespace App\Http\Controllers;

use App\Models\Review;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class TestController extends Controller
{
    use ApiResponseTrait;

    public function test()
    {
        $lastCode = DB::table('sessions')
            ->where('users_id','=',80)
            ->orderBy('created_at','DESC')
            ->first();

        $lsdd = Carbon::parse($lastCode->created_at)
            ->addMinute(200)
            ->format('Y-m-d H:i:s');
        //$lastCode->created_at->addMinutes(2);
        return response($lsdd);
    }

    public function tasks(){
        $tasks_done = DB::table('uz_tasks')->where('status','=','Done')
            ->get()->count();

        $tasks_pending = DB::table('uz_tasks')->where('status','=','Pending')
            ->get()->count();
        $all_tasks = "Tasks Done : " . $tasks_done . "\n" .
            "Tasks Pending : " . $tasks_pending;
        return response($all_tasks);
    }
}
