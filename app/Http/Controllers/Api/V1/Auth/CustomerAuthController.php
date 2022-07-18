<?php

namespace App\Http\Controllers\Api\V1\Auth;

use App\CentralLogics\Helpers;
use App\CentralLogics\SMS_module;
use App\Http\Controllers\Controller;
use App\Mail\EmailVerification;
use App\Model\BusinessSetting;
use App\Model\EmailVerifications;
use App\Model\PhoneVerification;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class CustomerAuthController extends Controller{


    public function social_login(Request $request){

        $validator = Validator::make($request->all(), [
            'access_token' => 'required',
            'username' => 'required'
        ]);

        if ($validator->fails()) {
            $response['status'] = 'fail';
            $response['message'] = 'Plese send all inputs.';
            $response['data'] = [];
            return response()->json($response, 200);
        }

        $temporary_token = Str::random(40);

        if(isset($request->phone) && $request->phone != "" && $request->phone != NULL){
            //$condition['phone'] = $request->phone;
            $checkUser = User::where(['phone' => $request->phone])->first();
            if (isset($checkUser)) {
                return response()->json(['status' => 'success', 'state' => 'login', 'message'=>'Login Successfully', 'data' => $checkUser], 200);
            }
        }

        if(isset($request->email) && $request->email != "" && $request->email != NULL){
            //$condition['email'] = $request->email;
            $checkUser = User::where(['email' => $request->email])->first();
            if (isset($checkUser)) {
                return response()->json(['status' => 'success', 'state' => 'login', 'message'=>'Login Successfully', 'data' => $checkUser], 200);
            }
        }
        
        //$user = User::where($condition)->first();
      	
        // if (isset($user)) {
        //     return response()->json(['status' => 'success', 'state' => 'login', 'message'=>'Login Successfully', 'data' => $user], 200);
        // } else {
            if(isset($request->firbase_token) && $request->firbase_token != "" && $request->imeinumber != "null"){
                $firebaseToken = $request->firbase_token;
            } else {
                $firebaseToken = NULL;
            }
            if(isset($request->device_id) && $request->device_id != "" && $request->imeinumber != "null"){
                $device_id = $request->device_id;
            } else {
                $device_id = NULL;
            }
            if(isset($request->app_version) && $request->app_version != "" && $request->imeinumber != "null"){
                $appVersion = $request->app_version;
            } else {
                $appVersion = NULL;
            }

            if(isset($request->phone) && $request->phone != "" && $request->imeinumber != "null"){
                $phone = $request->phone;
            } else {
                $phone = NULL;
            }
            if(isset($request->email) && $request->email != "" && $request->imeinumber != "null"){
                $email = $request->email;
            } else {
                $email = NULL;
            }
            if(isset($request->access_plateform) && $request->access_plateform != "" && $request->imeinumber != "null"){
                $access_plateform = $request->access_plateform;
            } else {
                $access_plateform = NULL;
            }
            if(isset($request->device_model) && $request->device_model != "" && $request->imeinumber != "null"){
                $device_model = $request->device_model;
            } else {
                $device_model = NULL;
            }
            if(isset($request->device_plateform) && $request->device_plateform != "" && $request->imeinumber != "null"){
                $device_plateform = $request->device_plateform;
            } else {
                $device_plateform = NULL;
            }
            if(isset($request->imeinumber) && $request->imeinumber != "" && $request->imeinumber != "null"){
                $imeinumber = $request->imeinumber;
            } else {
                $imeinumber = NULL;
            }

            $or_d = [
                'access_token' => $request->access_token,
                'username' => $request->username,
              	'f_name' => $request->username,
                'phone' => $phone,
                'email' => $email,
                'temporary_token' => $temporary_token,
                'cm_firebase_token' => $firebaseToken,
                'device_id' => $device_id,
                'app_version' => $appVersion,
                'access_plateform' => $access_plateform,
                'device_model' => $device_model,
                'device_plateform' => $device_plateform,
                'imeinumber' => $imeinumber,
                'status' => 1,
                'created_at' => date('Y-m-d H:i:s')
            ];

            $lastId = DB::table('users')->insertGetId($or_d);
            $user = User::where(['id' => $lastId])->first();
            return response()->json(['status' => 'success', 'state' => 'register', 'message'=>'Register Successfully', 'data' => $user], 200);
        //}
    }


    public function profile_update(Request $request){

        $validator = Validator::make($request->all(), [
            'user_id' => 'required',
            'f_name' => 'required',
            'l_name' => 'required'
        ]);

        if ($validator->fails()) {
            $response['status'] = 'fail';
            $response['message'] = 'Plese send all inputs....';
            $response['data'] = [];
            return response()->json($response, 200);
            //return response()->json(['errors' => Helpers::error_processor($validator)], 403);
        }

        $userId = $request['user_id'];

        $userRow = DB::table('users')->where('id', $userId)->get();
        
        if(isset($userRow) && isset($userRow[0])){
            
            $image = $userRow[0]->image;

            if (!empty($request->file('image'))) {
                $image = Helpers::upload('users/', 'png', $request->file('image'));
            }

            if(isset($request['email']) && $request['email'] != NULL && $request['email'] != ""){
                $email = $request['email'];
            } elseif(isset($userRow[0]->email) && $userRow[0]->email != NULL && $userRow[0]->email != ""){
                $email = $userRow[0]->email;
            } else {
                $email = NULL;
            }

            if(isset($request['phone']) && $request['phone'] != NULL && $request['phone'] != ""){
                $phone = $request['phone'];
            } elseif(isset($userRow[0]->phone) && $userRow[0]->phone != NULL && $userRow[0]->phone != ""){
                $phone = $userRow[0]->phone;
            } else {
                $phone = NULL;
            }

            $users = [
                'f_name' => $request['f_name'],
                'l_name' => $request['l_name'],
                'email' => $email,
                'phone' => $phone,
                'image' => $image
            ];

            DB::table('users')->where('id', $userId)->update($users);
            $response['status'] = 'success';
            $response['message'] = 'successfully updated.';
            $userData = DB::table('users')->where('id', $userId)->get();
            //$response['data'] = DB::table('users')->where('id', $userId)->get();
            $response['data'] = $userData[0];
            return response()->json(['message' => 'successfully updated.', 'status' => 'success', 'data' => $userData], 200);
            //return response()->json($response, 200);
            // return response()->json(['message' => 'successfully updated!'], 200);

        } else {

            $response['status'] = 'fail';
            $response['message'] = 'User not found.';
            $response['data'] = [];
            return response()->json($response, 200);

        }        

    }


    // public function profile_update(Request $request){

    //     $validator = Validator::make($request->all(), [
    //         'user_id' => 'required',
    //         'f_name' => 'required',
    //         'l_name' => 'required',
    //         'email' =>  'required',
    //         'phone' =>  'required'
    //     ]);

    //     if ($validator->fails()) {
    //         $response['status'] = 'fail';
    //         $response['message'] = 'Plese send all inputs.';
    //         $response['data'] = [];
    //         return response()->json($response, 200);
    //     }

    //     $userId = $request['user_id'];

    //     $userRow = DB::table('users')->where('id', $userId)->get();
        
    //     if(isset($userRow) && isset($userRow[0])){

    //         $registrationCertificate = $userRow[0]->registration_certificate;
    //         $gstCertificate = $userRow[0]->gst_certificate;
    //         $panCertificate = $userRow[0]->pan_certificate;
    //         $image = $userRow[0]->image;

    //         if (!empty($request->file('image'))) {
    //             $image = Helpers::upload('distributor/', 'png', $request->file('image'));
    //         }

    //         if (!empty($request->file('registration_certificate'))) {
    //             $registrationCertificate = Helpers::upload('distributor/', 'png', $request->file('registration_certificate'));
    //         }

    //         if (!empty($request->file('gst_certificate'))) {
    //             $gstCertificate = Helpers::upload('distributor/', 'png', $request->file('gst_certificate'));
    //         }

    //         if (!empty($request->file('pan_certificate'))) {
    //             $panCertificate = Helpers::upload('distributor/', 'png', $request->file('pan_certificate'));
    //         }

    //         $users = [
    //             'f_name' => $request['f_name'],
    //             'l_name' => $request['l_name'],
    //             'email' => $request['email'],
    //             'phone' => $request['phone'],
    //             'company_name' => $request['company_name'],
    //             'company_type' => $request['company_type'],
    //             'address' => $request['address'],
    //             'district' => $request['district'],
    //             'city' => $request['city'],
    //             'state' => $request['state'],
    //             'bank_name' => $request['bank_name'],
    //             'account_type' => $request['account_type'],
    //             'account_no' => $request['account_no'],
    //             'ifsc' => $request['ifsc'],
    //             'branch' => $request['branch'],
    //             'registration_certificate' => $registrationCertificate,
    //             'gst_certificate' => $gstCertificate,
    //             'image' => $image,
    //             'pan_certificate' => $panCertificate
    //         ];

    //         DB::table('users')->where('id',$userId)->update($users);
    //         $response['status'] = 'success';
    //         $response['message'] = 'successfully updated.';
    //         $response['data'] = [];
    //         return response()->json($response, 200);

    //     } else {

    //         $response['status'] = 'fail';
    //         $response['message'] = 'User not found.';
    //         $response['data'] = [];
    //         return response()->json($response, 200);

    //     }        

    // }

    public function get_profile(Request $request){
        $validator = Validator::make($request->all(), [
            'user_id' => 'required'
        ]);

        if ($validator->fails()) {
            $response['status'] = 'fail';
            $response['message'] = 'Please send all required fields.';
            return response()->json($response, 200);
        }

        $user = User::where(['id' => $request->user_id])->first();
        if (isset($user)) {
            $response['status'] = 'success';
            $response['message'] = 'User Found';
            foreach ($user as $key => $value){
                $userArray[$key] = $value;
            }
            $response['data'][] = $user;
        } else {
            $response['status'] = 'fail';
            $response['message'] = 'User Not Found';
            $response['data'] = [];
        }

        return response()->json($response, 200);

    }

    public function verify_otp(Request $request){
      
        $verify = User::where(['phone' => $request['phone'], 'phone_otp' => $request['otp']])->first();
			
        if (isset($verify)) {

            $userStatus = DB::table('users')->where('phone', $request['phone'])->update([
                'status' => 1
            ]);

            return response()->json(['message' => 'OTP verified!', 'status' => 'success', 'data' => $verify], 200);
        }

        return response()->json(['message' => 'OTP fail!', 'status' => 'fail'], 200);
    }

    public function login_register(Request $request){
        $temporary_token = Str::random(40);
        $otp = random_int(100000, 999999);

        $user = User::where(['phone' => $request->phone])->first();
        
        if (isset($user)) {
            //die("!!!!!!");
            $user->phone_otp = $otp;
            $user->save();

            $smsData = file_get_contents('https://api.msg91.com/api/sendhttp.php?authkey=378531A3SYlMChc0qI62b1a431P1&mobiles=91'.$request->phone.'&message=Dear%20user%2C%20'.$otp.'%20is%20your%20OTP%20for%20registration%20at%20TESMART.%20Happy%20shopping%21&sender=TESMAT&route=4&DLT_TE_ID=1307164908009671091');

            return response()->json(['status' => 'success', 'token' => $temporary_token, 'otp' => $otp, 'state' => 'login', 'message'=>'Login Successfully'], 200);
        } else {
            //die("@@@@@@");
            if(isset($request->firbase_token) && $request->firbase_token != ""){
                $firebaseToken = $request->firbase_token;
            } else {
                $firebaseToken = NULL;
            }
            if(isset($request->device_id) && $request->device_id != ""){
                $device_id = $request->device_id;
            } else {
                $device_id = NULL;
            }
            if(isset($request->app_version) && $request->app_version != ""){
                $appVersion = $request->app_version;
            } else {
                $appVersion = NULL;
            }
            if(isset($request->device_model) && $request->device_model != ""){
                $device_model = $request->device_model;
            } else {
                $device_model = NULL;
            }
            if(isset($request->device_plateform) && $request->device_plateform != ""){
                $device_plateform = $request->device_plateform;
            } else {
                $device_plateform = NULL;
            }
            if(isset($request->imeinumber) && $request->imeinumber != ""){
                $imeinumber = $request->imeinumber;
            } else {
                $imeinumber = NULL;
            }
            //echo "!!!!".$firebaseToken."@@@@".$device_id."####".$appVersion; die;
            // $user = User::create([
            //     'phone' => $request->phone,
            //     'phone_otp' => $otp,
            //     'temporary_token' => $temporary_token,
            //     'cm_firebase_token' => $firebaseToken,
            //     'device_id' => $device_id,
            //     'app_version' => $appVersion
            // ]);

            $or_d = [
                'phone' => $request->phone,
                'phone_otp' => $otp,
                'temporary_token' => $temporary_token,
                'cm_firebase_token' => $firebaseToken,
                'device_id' => $device_id,
                'app_version' => $appVersion,
                'device_model' => $device_model,
                'device_plateform' => $device_plateform,
                'imeinumber' => $imeinumber
            ];

            DB::table('users')->insert($or_d);

            $smsData = file_get_contents('https://api.msg91.com/api/sendhttp.php?authkey=378531A3SYlMChc0qI62b1a431P1&mobiles=91'.$request->phone.'&message=Dear%20user%2C%20'.$otp.'%20is%20your%20OTP%20for%20registration%20at%20TESMART.%20Happy%20shopping%21&sender=TESMAT&route=4&DLT_TE_ID=1307164908009671091');
            return response()->json(['status' => 'success', 'token' => $temporary_token, 'otp' => $otp, 'state' => 'register', 'message'=>'Register Successfully'], 200);
        }
    }

    public function check_phone(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'phone' => 'required|min:11|max:14|unique:users'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 403);
        }

        if (BusinessSetting::where(['key' => 'phone_verification'])->first()->value) {
            $token = rand(1000, 9999);
            DB::table('phone_verifications')->insert([
                'phone' => $request['phone'],
                'token' => $token,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            $response = SMS_module::send($request['phone'], $token);
            return response()->json([
                'message' => $response,
                'token' => 'active'
            ], 200);
        } else {
            return response()->json([
                'message' => 'Number is ready to register',
                'token' => 'inactive'
            ], 200);
        }
    }

    public function check_email(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|unique:users'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 403);
        }

        if (BusinessSetting::where(['key' => 'email_verification'])->first()->value) {
            $token = rand(1000, 9999);
            DB::table('email_verifications')->insert([
                'email' => $request['email'],
                'token' => $token,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            Mail::to($request['email'])->send(new EmailVerification($token));

            return response()->json([
                'message' => 'Email is ready to register',
                'token' => 'active'
            ], 200);
        } else {
            return response()->json([
                'message' => 'Email is ready to register',
                'token' => 'inactive'
            ], 200);
        }
    }

    public function verify_email(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 403);
        }

        $verify = EmailVerifications::where(['email' => $request['email'], 'token' => $request['token']])->first();

        if (isset($verify)) {
            $verify->delete();
            return response()->json([
                'message' => 'OTP verified!',
            ], 200);
        }

        return response()->json(['errors' => [
            ['code' => 'otp', 'message' => 'OTP is not found!']
        ]], 404);
    }

    public function verify_phone(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'phone' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 403);
        }

        $verify = PhoneVerification::where(['phone' => $request['phone'], 'token' => $request['token']])->first();

        if (isset($verify)) {
            $verify->delete();
            return response()->json([
                'message' => 'OTP verified!',
            ], 200);
        }

        return response()->json(['errors' => [
            ['code' => 'token', 'message' => 'OTP is not found!']
        ]], 404);
    }

    public function registration(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'f_name' => 'required',
            'l_name' => 'required',
            'email' => 'required|unique:users',
            'phone' => 'required|unique:users',
            'password' => 'required|min:6',
        ], [
            'f_name.required' => 'The first name field is required.',
            'l_name.required' => 'The last name field is required.',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 403);
        }
        $temporary_token = Str::random(40);
        $user = User::create([
            'f_name' => $request->f_name,
            'l_name' => $request->l_name,
            'email' => $request->email,
            'phone' => $request->phone,
            'password' => bcrypt($request->password),
            'temporary_token' => $temporary_token,
        ]);

        $phone_verification = Helpers::get_business_settings('phone_verification');
        $email_verification = Helpers::get_business_settings('email_verification');
        if ($phone_verification && !$user->is_phone_verified) {
            return response()->json(['temporary_token' => $temporary_token], 200);
        }
        if ($email_verification && !$user->is_email_verified) {
            return response()->json(['temporary_token' => $temporary_token], 200);
        }

        $token = $user->createToken('RestaurantCustomerAuth')->accessToken;

        return response()->json(['token' => $token], 200);
    }

    public function login(Request $request)
    {
        if($request->has('email_or_phone'))
        {
            $user_id = $request['email_or_phone'];

            $validator = Validator::make($request->all(), [
                'email_or_phone' => 'required',
                'password' => 'required|min:6'
            ]);

        }else
        {
            $user_id = $request['email'];

            $validator = Validator::make($request->all(), [
                'email' => 'required',
                'password' => 'required|min:6'
            ]);
        }


        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 403);
        }

        $user = User::where(['email' => $user_id])->orWhere('phone', $user_id)->first();
        if (isset($user)) {
            $user->temporary_token = Str::random(40);
            $user->save();
            $data = [
                'email' => $user->email,
                'password' => $request->password
            ];

            if (auth()->attempt($data)) {
                $token = auth()->user()->createToken('RestaurantCustomerAuth')->accessToken;
                return response()->json(['token' => $token], 200);
            }
        }

        $errors = [];
        array_push($errors, ['code' => 'auth-001', 'message' => 'Invalid credential.']);
        return response()->json([
            'errors' => $errors
        ], 401);

    }
}
