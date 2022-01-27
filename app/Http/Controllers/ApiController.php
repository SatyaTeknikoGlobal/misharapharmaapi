<?php

namespace App\Http\Controllers;

use JWTAuth;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

use Tymon\JWTAuth\Exceptions\JWTException;
use Validator;
use Illuminate\support\str;

use App\User;
use App\AppVersion;
use App\UserLogin;
use App\UserOtp;
use App\Visitor;
use App\Admin;
use App\Banner;
use App\Product;
use App\Wishlist;
use App\Cart;
use App\Verient;
use App\UserAddress;
use App\Order;
use App\OrderItem;



use Razorpay\Api\Api;

use Mail;
use Storage;



class ApiController extends Controller
{


    public function __construct()
    {
        $this->user = new User;
        date_default_timezone_set("Asia/Kolkata");
        //$this->url = env('BASE_URL');
        $this->url = "https://healthcare.appmantra.live/";
    }







    //============================= Fans Studio API ==================================//

    public function app_version(){
        $app_version = AppVersion::first();
        return response()->json([
            'result' => true,
            'message' => '',
            'version' => $app_version,
        ],200);
    }


    public static function sendEmail($viewPath, $viewData, $to, $from, $replyTo, $subject, $params=array()){

        try{

            Mail::send(
                $viewPath,
                $viewData,
                function($message) use ($to, $from, $replyTo, $subject, $params) {
                    $attachment = (isset($params['attachment']))?$params['attachment']:'';

                    if(!empty($replyTo)){
                        $message->replyTo($replyTo);
                    }

                    if(!empty($from)){
                        $message->from($from);
                    }

                    if(!empty($attachment)){
                        $message->attach($attachment);
                    }

                    $message->to($to);
                    $message->subject($subject);

                }
            );
        }
        catch(\Exception $e){
            // Never reached
        }

        if( count(Mail::failures()) > 0 ) {
            return false;
        }
        else {
            return true;
        }

    }

    public function verify_otp_forget_password(Request $request){
      $validator =  Validator::make($request->all(), [
        'email' => 'required|email',
        'otp' => 'required',
        'password'=>'required',
        'confirm_password'=>'required|same:password',
    ]);

      $user = null;

      if ($validator->fails()) {

        return response()->json([
            'result' => false,
            'message' => json_encode($validator->errors()),

        ],400);

    }
    $exist = UserOtp::where('email',$request->email)->first();
    if(!empty($exist)){
        if($request->otp == $exist->otp){
            UserOtp::where('email',$request->email)->update(['otp'=>null]);
            $updates = User::where('email',$request->email)->update(['password'=>bcrypt($request->password)]);
            return response()->json([
                'result' => true,
                'message' => 'Verified Successfully',
            ],200);
        }else{
           return response()->json([

            'result' => false,
            'message' => 'Invalid OTP',
        ],200);
       }

   }else{
       return response()->json([
        'result' => false,
        'message' => 'Invalid OTP',
    ],200);
   }

}

public function send_otp(Request $request)
{
    $validator =  Validator::make($request->all(), [
        'phone' => 'required|',
    ]);

    $status = 'new';

    if ($validator->fails()) {

        return response()->json([
            'result' => false,
            'otp'=> '',
            'message' => json_encode($validator->errors()),

        ],200);
    }

    $otp = 1234;

    $message = $otp." is your authentication Code to register.";
    $mobile = $request['phone'];
    $time = date("Y-m-d H:i:s",strtotime('15 minutes'));

    if(!empty($request->phone)){
            // $this->send_message($mobile,$message);
        UserOtp::updateOrcreate([
            'mobile'=>$mobile],[
                'otp'=>$otp,
                'timestamps'=>$time,
            ]);

    }
    return response()->json([
        'result' => true,
        'message' => 'SMS Sent SuccessFully',
        'otp'=>$otp,
    ],200);
}



public function verify_otp(Request $request){
    $validator =  Validator::make($request->all(), [
        'phone' => 'required',
        'otp'=>'required',

    ]);

    if ($validator->fails()) {
        return response()->json([
            'result' => false,

            'message' => json_encode($validator->errors()),

        ],400);
    }

    $mobile = isset($request->phone) ? $request->phone :'';
    $otp = isset($request->otp) ? $request->otp :'';
    $status = 'new';
    if(!empty($mobile)){
        $verify_otp  = UserOtp::where(['mobile'=>$mobile,'otp'=>$otp])->first();
    }


    $user = User::where('number',$mobile)->where('status',1)->first();
    if(!empty($user)){
        $status = 'old';
    }

    if(!empty($verify_otp)){
        return response()->json([
            'result' => true,
            'message' => 'OTP Varified SuccessFully',
            'status' => $status,
        ],200);

    }else{
        return response()->json([
            'result' => false,
            'message' => 'Inavalid OTP',
            'status' => $status,
        ],200);

    }



}


public function forget_password(Request $request){
   $validator =  Validator::make($request->all(), [
    'email' => 'required|email',
]);

   $user = null;

   if ($validator->fails()) {

    return response()->json([
        'result' => false,
        'message' => json_encode($validator->errors()),

    ],400);

}

$exist = User::where('email',$request->email)->first();
if(!empty($exist)){
    // $otp = rand(1111,9999);
    $otp = 1234;
    UserOtp::updateOrcreate([
        'email'=>$request->email],
        ['otp'=>$otp,
    ]);

    $to_email = $request->email;
    $from_email = 'satyasahoo.abc@gmail.com';
    $subject = 'Forgot Password Email - NAYAEDUCATION';
    $email_data = [];
    $email_data['otp'] = $otp;
    $success = $this->sendEmail('mail', $email_data, $to=$to_email, $from_email, $replyTo = $from_email, $subject);
    if($success){
        return response()->json([

            'result' => true,
            'message' => ' Successfully',
        ],200); 
    }else{
       return response()->json([

        'result' => false,
        'message' => ' Something Went Wrong',
    ],200);
   }




}else{
    return response()->json([

        'result' => false,
        'message' => 'User Not Exist',
    ],200);
}





}





public function send_test_notification(Request $request){


    $deviceToken = 'dzHn8qdTQROVj2H4KpX5aZ:APA91bE6NHu2jstkNRx49H7gcBBKrWgb1Gbr_r-oc-PxKW6IU_GzD9ZP0o26lpKFmPqbnq6Ewl3jGYVq6dq_uSmCCF_L96Xl_apzOs4nrD7cPaEOsdBjdnTGJhTE7Ig7R4X6z4Xj9S5y';
    $sendData = array(
        'body' => 'Test',
        'title' => 'My Door Notification',
        'sound' => 'Default',
    );
    $result = $this->fcmNotification($deviceToken,$sendData);

    print_r($result);

}



public function send_message()
{
    // $sender = "CITRUS";
    // $message = urlencode($message);
    // $msg = "sender=".$sender."&route=4&country=91&message=".$message."&mobiles=".$mobile."&authkey=284738AIuEZXRVCDfj5d26feae";

    // $ch = curl_init('http://api.msg91.com/api/sendhttp.php?');
    // curl_setopt($ch, CURLOPT_POST, true);
    // curl_setopt($ch, CURLOPT_POSTFIELDS, $msg);
    //     //curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
    // curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    // $res = curl_exec($ch);
    // $result = curl_close($ch);
    // return $res;

//$mobile,$message
    $curl = curl_init();

    curl_setopt_array($curl, [
      CURLOPT_URL => "https://api.msg91.com/api/v5/flow/",
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_ENCODING => "",
      CURLOPT_MAXREDIRS => 10,
      CURLOPT_TIMEOUT => 30,
      CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
      CURLOPT_CUSTOMREQUEST => "POST",
      CURLOPT_POSTFIELDS => "{\n  \"flow_id\": \"61e559bdd8caa36a6b0d0c53\",\n  \"sender\": \"TEKGLO\",\n  \"mobiles\": \"916370371406\",\n  \"otp\": \"1234\",\n  \"tekniko\": \"Tekniko\"\n}",
      CURLOPT_HTTPHEADER => [
        "authkey: 285140ArLurg2KnR61e3d660P1",
        "content-type: application/JSON"
    ],
]);

    $response = curl_exec($curl);
    $err = curl_error($curl);

    curl_close($curl);

    if ($err) {
      echo "cURL Error #:" . $err;
  } else {
      echo $response;
  }

}




public function logout(Request $request)
{
    $validator =  Validator::make($request->all(), [
        'token' => 'required',
    ]);

    if ($validator->fails()) {

        return response()->json([
            'result' => false,
            'message' => json_encode($validator->errors())
        ],400);
    }

    try {
        $user_login = UserLogin::where(['user_id' => $request->token])->delete();
        JWTAuth::invalidate($request->token);
        return response()->json([
            'result' => true,
            'message' => 'User logged out successfully'
        ],200);
    } catch (JWTException $exception) {
        return response()->json([
            'result' => false,
            'message' => 'Sorry, the user cannot be logged out'
        ], 500);
    }
}





public function execute_query(){
       //$success =  DB::statement("ALTER TABLE `users` ADD `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP AFTER `is_delete`, ADD `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP AFTER `created_at`");
 $success =  DB::statement("DELETE FROM users WHERE number='6370371406'");

}

public function register(Request $request)
{
    $validator = Validator::make($request->all(), [
        'name' => 'required|max:255',
        'phone' => 'required|unique:users,number',
        'email'=>'required|unique:users',
        'deviceID' => '',
        'deviceToken' => '',
        'deviceType' => '',
    ]);
    if ($validator->fails()) {
        return response()->json([
            'result' => false,
            'message' => json_encode($validator->errors()),
            'token'=>null,
            'user'=>null
        ],200);
    }

    $exist = [];
    if(!empty($request->referral_code)){
        $exist = User::where('referral_code',$request->referral_code)->first();
    }
    $user = new User();
    $user->name = $request->name;
    $user->email = $request->email;
    $user->number = $request->phone;
    $user->status = 1;
    $user->referral_code = $this->generateReferalCode(8);


    if(!empty($exist)){
        $user->referral_userID = $exist->id;
    }
    $user->image= 'user.png';
    $user->save();
    $credentials = $request->only('phone');
    $user = User::where('number',$credentials)->first();
  //  $user->image= $user->image;
    try {
        if (!empty($user)) {
            if (!$token = JWTAuth::fromUser($user)) {
                return response()->json([
                    'result' => false,
                    'token' => null,
                    'message' => 'invalid_credentials',
                    'user' => null], 200);
            }
        } else {
            return response()->json([
                'result' => false,
                'token' => null,
                'message' => 'invalid_credentials',
                'user' => null], 200);
        }

    } catch (JWTException $e) {
        return response()->json([
            'result' => false,
            'token' => null,
            'message' => 'could_not_create_token',
            'user' => null], 200);
    }
    $deviceID = $request->input("deviceID");
    $deviceToken = $request->input("deviceToken");
    $deviceType = $request->input("deviceType");
    $device_info = UserLogin::where(['user_id'=>$user->id])->first();
    UserLogin::create([
        "user_id"=>$user->id,
        "ip_address"=>$request->ip(),
        "deviceID"=>$deviceID,
        "deviceToken"=>$deviceToken,
        "deviceType"=>$deviceType,
    ]);
    unset($user->id);
    if($user->image!=='' && $user->image!=null){
       // $user->image =  asset('public/images/'.$user->image);
    }

    return response()->json([
        'result' => true,
        'token' => $token,
        'message' => 'Successful Login',
        'user' => $user
    ],200);
}


public  function generateReferalCode($length = 8) {
    $characters = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $charactersLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, $charactersLength - 1)];
    }
    $exist = User::where('referral_code',$randomString)->first();
    if(!empty($exist)){
        self::generateReferalCode($length);
    }
    return $randomString;
}




public function login(Request $request){
  $validator =  Validator::make($request->all(), [
    'phone' => 'required',
    'deviceID' => '',
    'deviceToken' => '',
    'deviceType' => '',

]);

  $user = null;

  if ($validator->fails()) {

    return response()->json([

        'result' => false,

        'token' => null,

        'message' => json_encode($validator->errors()),

        'user'=>$user

    ],400);

}

$phone = $request->input('phone');

$checkuser = User::where(['number'=>$phone])->first();


$credentials = $request->only('email');

$user = User::where('number',$request->phone)->first();
if(!empty($user->image)){
    //$user->image = $this->url.'/api/public/images/users/'.$user->image;
}
try {

    if (!empty($user)) {

        if (!$token = JWTAuth::fromUser($user)) {

            return response()->json([

                'result' => false,

                'token' => null,

                'message' => 'invalid_credentials',

                'user' => null], 400);

        }

    } else {

        return response()->json([

            'result' => false,

            'token' => null,

            'message' => 'invalid_credentials',

            'user' => null], 400);

    }



} catch (JWTException $e) {

    return response()->json([

        'result' => false,

        'token' => null,

        'message' => 'could_not_create_token',

        'user' => null], 500);

}



$deviceID = $request->input("deviceID");

$deviceToken = $request->input("deviceToken");

$deviceType = $request->input("deviceType");

$device_info = UserLogin::where(['user_id'=>$user->id])->first();

if (!empty($device_info)){

    $device_info->deviceToken = $deviceToken;

    $device_info->deviceType = $deviceType;

    $device_info->save();

            //$checkOtp->delete();

    return response()->json([

        'result' => true,

        'token' => $token,

        'message' => 'Successful Login',

        'user' => $user

    ],200);

}

UserLogin::create([

    "user_id"=>$user->id,

    "ip_address"=>$request->ip(),

    "deviceID"=>$deviceID,

    "deviceToken"=>$deviceToken,

    "deviceType"=>$deviceType,

]);



        // $checkOtp->delete();

return response()->json([

    'result' => true,

    'token' => $token,

    'message' => 'Successful Login',

    'user' => $user

],200);




}





public function update_profile(Request $request){
    $validator =  Validator::make($request->all(), [
        'token' => 'required',
    ]);
    $user = null;
    if ($validator->fails()) {
        return response()->json([
            'result' => false,
            'message' => json_encode($validator->errors()),
            'user' =>$user,
        ],400);
    }
    $user = JWTAuth::parseToken()->authenticate();
    if (empty($user)){
        return response()->json([
            'result' => false,
            'message' => '',
            'user' =>$user,
        ],401);
    }

    $dbArray= [];
    if(!empty($request->name)){
        $dbArray['name'] = $request->name;
    }

    if(!empty($request->dob)){
        $dbArray['dob'] = $request->dob;
    }
    if(!empty($request->gender)){
        $dbArray['gender'] = $request->gender;
    }
    
    if($request->hasFile('image')){
        $file = $request->file('image');
        $destinationPath = public_path("/images/users/");
        $side = $request->file('image');
        $side_name = $user->id.'_user_profile'.time().'.'.$side->getClientOriginalExtension();
        $side->move($destinationPath, $side_name);
        $dbArray['image'] = $side_name;
    }

    if(!empty($dbArray)){

        User::where('id',$user->id)->update($dbArray);
    }
    $user = User::where('id',$user->id)->first();

    if(!empty($user) && !empty($user->image)){
        //$image= $this->url.'/api/public/images/users/'.$user->image;
    }else{
        //$image= $this->url.'/api/public/images/users/user.png';
    }

    //$user->image = $image;

    return response()->json([
        'result' => true,
        'message' => 'Profile Updated successfully',
        'user'=>$user,
        'token'=>$request->token,
    ],200);

}



public function profile(Request $request){
    $validator =  Validator::make($request->all(), [
        'token' => 'required',
    ]);
    $user = array();
    $user = null;
    if ($validator->fails()) {
        return response()->json([
            'result' => false,
            'message' => json_encode($validator->errors()),
            'user' =>$user,
        ],400);
    }
    $user = JWTAuth::parseToken()->authenticate();
    if (empty($user)){
        return response()->json([
            'result' => false,
            'message' => '',
            'user' =>$user,
        ],401);
    }


    if(!empty($user) && !empty($user->image)){
        $user->image= $this->url.'/api/public/images/users/'.$user->image;
    }else{
        $user->image= $this->url.'/api/public/images/users/user.png';
    }

    return response()->json([
        'result' => true,
        'message' => 'User Profile',
        'user'=>$user,
        'token'=>$request->token,
    ],200);

}


public function change_password(Request $request){
    $validator =  Validator::make($request->all(), [
        'token' => 'required',
        'password' => 'required',
        'confirm_password' => 'required_with:password|same:password',

    ]);
    $user = null;
    if ($validator->fails()) {
        return response()->json([
            'result' => false,
            'message' => json_encode($validator->errors()),
        ],400);
    }
    $user = JWTAuth::parseToken()->authenticate();
    if (empty($user)){
        return response()->json([
            'result' => false,
            'message' => '',
        ],401);
    }
    $user = User::where('id',$user->id)->first();
    if(!empty($user)){
        User::where('id',$user->id)->update(['password'=>bcrypt($request->password)]);
    }

    $user_login = UserLogin::where(['user_id' => $user->id])->delete();
    JWTAuth::invalidate($request->token);

    return response()->json([
        'result' => true,
        'message' => 'Password Changed Successfully',
    ],200);
}




public function state_city_list(Request $request){
    $validator =  Validator::make($request->all(), [
        'token' => 'required',

    ]);
    $list = null;
    $user = null;
    if ($validator->fails()) {
        return response()->json([
            'result' => false,
            'message' => json_encode($validator->errors()),
            'list' =>$list,
        ],400);
    }
    $user = JWTAuth::parseToken()->authenticate();
    if (empty($user)){
        return response()->json([
            'result' => false,
            'message' => '',
            'list' =>$list,
        ],401);
    }

    $states = State::select('id','name')->where('status',1)->get();
    if(!empty($states)){
        foreach($states as $state){
            $cities = City::select('id','name')->where('state_id',$state->id)->where('status',1)->get();
            if(!empty($cities)){
                $state->cities = $cities;
            }
        }
    }


    return response()->json([
        'result' => true,
        'message' => 'State City List',
        'list'=>$states,
    ],200);
}


public function cmspages(Request $request){
    $validator =  Validator::make($request->all(), [
        'token' => 'required',
        'type' => 'required',
    ]);
    $pages = null;
    $user = null;
    if ($validator->fails()) {
        return response()->json([
            'result' => false,
            'message' => json_encode($validator->errors()),
            'pages' =>$pages,
        ],400);
    }
    $user = JWTAuth::parseToken()->authenticate();
    if (empty($user)){
        return response()->json([
            'result' => false,
            'message' => '',
            'pages' =>$pages,
        ],401);
    }
    $cms = DB::table('settings')->where('id',1)->first();
    if($request->type == 'contactus'){
        $pages = $cms->contactus ?? '';
    }
    if($request->type == 'about'){
      $pages = $cms->about ?? '';
  }

  if($request->type == 'privacy'){
     $pages = $cms->privacy ?? '';
 }

 if($request->type == 'terms'){
     $pages = $cms->privacy ?? '';
 }



 return response()->json([
    'result' => true,
    'message' => 'CMS Pages List',
    'pages'=>$pages,
],200);




}






// public function get_verients($product_id){

// }


public function product_list(Request $request){
    $validator =  Validator::make($request->all(), [
        'token' => 'required',
    ]);
    $products = null;
    $user = null;
    if ($validator->fails()) {
        return response()->json([
            'result' => false,
            'message' => json_encode($validator->errors()),
            'products' =>$products,
        ],400);
    }
    $user = JWTAuth::parseToken()->authenticate();
    if (empty($user)){
        return response()->json([
            'result' => false,
            'message' => '',
            'products' =>$products,
        ],401);
    }

    $productArr = [];
    $products = Product::select('id','product_image','product_name')->latest();

    if(!empty($request->discount)){

    }
    if(!empty($request->product_name)){
        $products->where('product_name', 'LIKE', '%' . $request->product_name . '%');
    }


    $products = $products->paginate(10);

    if(!empty($products)){
        foreach($products as $product){
            $product->product_image = $this->url.$product->product_image;
            $verients = $product->verients;
            if(!empty($verients)){
                foreach($verients as $var){
                    $cartcount = 0;
                    $discount_price = 0;
                    $diff = $var->price - $var->retail_price;
                    $discount_price = ($diff/$var->retail_price)* 100;
                    $discount = round($discount_price);
                    $var->discount  = $discount;
                    $cart = Cart::where('user_id',$user->id)->where('product_id',$product->id)->where('variant_id',$var->id)->first();
                    if(!empty($cart)){
                        $cartcount = $cart->qty;
                    }
                    $var->cartcount = $cartcount;
                }
            }

            $product->verients = $verients;
        }
    }
    return response()->json([
        'result' => true,
        'message' => 'Products List',
        'products'=>$productArr,
    ],200);



}




public function products($products){

}



public function add_remove_wishlist(Request $request){
    $validator =  Validator::make($request->all(), [
        'token' => 'required',
        'product_id' => '',
        'type' => '',
    ]);
    $user = null;
    if ($validator->fails()) {
        return response()->json([
            'result' => false,
            'message' => json_encode($validator->errors()),
        ],400);
    }
    $user = JWTAuth::parseToken()->authenticate();
    if (empty($user)){
        return response()->json([
            'result' => false,
            'message' => '',
        ],401);
    }
    $message = 'No Product Found';
    $products = [];
    if($request->type == 'list'){
        $wishlists = Wishlist::where('user_id',$user->id)->get()->pluck('product_id');
        if(!empty($wishlists)){
            $products = Product::select('id','product_image','product_name')->whereIn('id',$wishlists)->get();
            if(!empty($products)){
                foreach($products as $product){
                    $product->product_image = $this->url.$product->product_image;
                    $product->verients = $product->verients;
                }
            }
        }
    }else{

        $validator =  Validator::make($request->all(), [
            'token' => 'required',
            'product_id' => 'required',
        ]);
        $user = null;
        if ($validator->fails()) {
            return response()->json([
                'result' => false,
                'message' => json_encode($validator->errors()),
            ],400);
        }



        $dbArray = [];
        $dbArray['user_id'] = $user->id;
        $dbArray['product_id'] = $request->product_id;
        $exist = Wishlist::where('user_id',$user->id)->where('product_id',$request->product_id)->first();
        if (empty($exist)) {
            Wishlist::insert($dbArray);
            $message = 'Added Successfully';
        }else{
            Wishlist::where('id',$exist->id)->delete();
            $message = 'Removed Successfully';  
        }
    }


    return response()->json([
        'result' => true,
        'message' => $message,
        'products' => $products,
    ],200);

}


public function user_cart(Request $request){
   $validator =  Validator::make($request->all(), [
    'token' => 'required',
    'type' => 'required',
]);
   $carts = null;
   $user = null;
   $total_amt = 0;
   $delivery_charges = 0;
   $cart_amount = 0;
   $discount = 0;
   if ($validator->fails()) {
    return response()->json([
        'result' => false,
        'message' => json_encode($validator->errors()),
        'carts' =>$carts,
    ],400);
}
$user = JWTAuth::parseToken()->authenticate();
if (empty($user)){
    return response()->json([
        'result' => false,
        'message' => '',
        'carts' =>$carts,
    ],401);
}
$message = 'No Product Found';

if($request->type == 'add'){
   $validator =  Validator::make($request->all(), [
    'product_id' => 'required',
    'variant_id' => 'required',
    'key' => 'required',
]);
   if ($validator->fails()) {
    return response()->json([
        'result' => false,
        'message' => json_encode($validator->errors()),
    ],400);
}


$exist = Cart::where('user_id',$user->id)->where('product_id',$request->product_id)->where('variant_id',$request->variant_id)->first();
if(empty($exist) && $request->key == 'plus'){
    $dbArray = [];
    $dbArray['user_id'] = $user->id;
    $dbArray['product_id'] =$request->product_id;
    $dbArray['variant_id'] = $request->variant_id;
    $dbArray['qty'] = 1;
    Cart::insert($dbArray);
    $message = 'Added To Cart Succesfully';
}else{
    if($request->key == 'plus'){
       $dbArray = [];
       $dbArray['user_id'] = $user->id;
       $dbArray['product_id'] =$request->product_id;
       $dbArray['variant_id'] = $request->variant_id;
       $dbArray['qty'] = $exist->qty + 1;
       Cart::where('id',$exist->id)->update($dbArray);
       $message = 'Cart Updated Succesfully';

   }if($request->key == 'minus'){
    $exist = Cart::where('user_id',$user->id)->where('product_id',$request->product_id)->where('variant_id',$request->variant_id)->first();
    if(!empty($exist)){
        if($exist->qty >1){
           $dbArray = [];
           $dbArray['user_id'] = $user->id;
           $dbArray['product_id'] =$request->product_id;
           $dbArray['variant_id'] = $request->variant_id;
           $dbArray['qty'] = $exist->qty - 1;
           Cart::where('id',$exist->id)->update($dbArray);
           $message = 'Cart Updated Succesfully';
       }else{
        Cart::where('user_id',$user->id)->where('product_id',$request->product_id)->where('variant_id',$request->variant_id)->delete();
        $message = 'Product Removed Succesfully';


    }
}else{
    $message = 'No Product Found';

}


}

}
}if($request->type == 'remove'){
    $validator =  Validator::make($request->all(), [
        'product_id' => 'required',
        'variant_id' => 'required',
    ]);
    if ($validator->fails()) {
        return response()->json([
            'result' => false,
            'message' => json_encode($validator->errors()),
        ],400);
    }
    Cart::where('user_id',$user->id)->where('product_id',$request->product_id)->where('variant_id',$request->variant_id)->delete();
    $message = 'Product Removed Succesfully';


}
if($request->type == 'list'){
    $carts = Cart::where('user_id',$user->id)->get();
    

    if(!empty($carts)){
        foreach($carts as $cart){
            $product = Product::select('id','product_image','product_name')->where('id',$cart->product_id)->first();
            $product->product_image = $this->url.$product->product_image;
            $verients = Verient::select('id','name','price','retail_price')->find($cart->variant_id);
            $cart->product = $product;
            if(!empty($verients)){
                $cart_amount += $cart->qty * $verients->retail_price;
            }

            $product->verients = $verients;

            

        }
        $message = 'Product List';

    }else{
        $message = 'No Product Found';

    }
}






return response()->json([
    'result' => true,
    'message' => $message,
    'carts'=>$carts,
    'delivery_charges'=>$delivery_charges,
    'cart_amount'=>$cart_amount,
    'total_amt'=>$total_amt,
    'discount'=>$discount,
],200);


}



public function product_details(Request $request){
   $validator =  Validator::make($request->all(), [
    'token' => 'required',
    'product_id' => 'required',

]);
   $products = null;
   $user = null;
   if ($validator->fails()) {
    return response()->json([
        'result' => false,
        'message' => json_encode($validator->errors()),
        'products' =>$products,
    ],400);
}
$user = JWTAuth::parseToken()->authenticate();
if (empty($user)){
    return response()->json([
        'result' => false,
        'message' => '',
        'products' =>$products,
    ],401);
}
$products = Product::select('id','product_image','product_name')->where('id',$request->product_id)->first();


if(!empty($products)){
    $products->product_image = $this->url.$products->product_image;
    $verients = $products->verients;
    if(!empty($verients)){
        foreach($verients as $var){
            $cartcount = 0;
            $discount_price = 0;
            $diff = $var->price - $var->retail_price;
            $discount_price = ($diff/$var->retail_price)* 100;
            $var->discount = round($discount_price);
            $cart = Cart::where('user_id',$user->id)->where('product_id',$products->id)->where('variant_id',$var->id)->first();
            if(!empty($cart)){
                $cartcount = $cart->qty;
            }
            $var->cartcount = $cartcount;
        }
    }


    $products->verients = $verients;
}

return response()->json([
    'result' => true,
    'message' => 'Product Details',
    'products'=>$products,
],200);
}



public function user_address(Request $request){
    $validator =  Validator::make($request->all(), [
        'token' => 'required',
        'key' => 'required',
    ]);
    $address_list = [];
    $user = null;
    $message = 'Address List';
    if ($validator->fails()) {
        return response()->json([
            'result' => false,
            'message' => json_encode($validator->errors()),
            'address_list' =>$address_list,
        ],400);
    }
    $user = JWTAuth::parseToken()->authenticate();
    if (empty($user)){
        return response()->json([
            'result' => false,
            'message' => '',
            'address_list' =>$address_list,
        ],401);
    }

    if($request->key == 'add'){
        $validator =  Validator::make($request->all(), [
            'location' => 'required',
            'building_name' => 'required',
            'landmark' => 'required',
            'address_type' => 'required',
            'contact_person_name' => 'required',
            'contact_person_mobile' => 'required',
            'pincode' => 'required',
            'is_default' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'result' => false,
                'message' => json_encode($validator->errors()),
                'address_list' =>$address_list,
            ],400);
        }
        $dbArray = [];
        $dbArray['user_id'] = $user->id;
        $dbArray['location'] = $request->location;
        $dbArray['flat_no'] = $request->flat_no;
        $dbArray['building_name'] = $request->building_name;
        $dbArray['landmark'] = $request->landmark;
        $dbArray['address_type'] = $request->address_type;
        $dbArray['pincode'] = $request->pincode;
        $dbArray['contact_person_name'] = $request->contact_person_name;
        $dbArray['contact_person_mobile'] = $request->contact_person_mobile;
        $dbArray['is_active'] = 'Y';
        $dbArray['is_default'] = isset($request->is_default) ? $request->is_default :'N';

        UserAddress::insert($dbArray);
        $message = 'Address Added Successfully';
    }
    if($request->key == 'list'){
        $addresses = UserAddress::where('user_id',$user->id)->get();
        if(!empty($addresses)){
            $address_list = $addresses;
        }

    }
    if($request->key == 'edit'){
         $validator =  Validator::make($request->all(), [
            'address_id' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'result' => false,
                'message' => json_encode($validator->errors()),
                'address_list' =>$address_list,
            ],400);
        }
        $addresses = UserAddress::where('user_id',$user->id)->where('id',$request->address_id)->get();
        if(!empty($addresses)){
            $address_list = $addresses;
        }

    }

      if($request->key == 'delete'){
         $validator =  Validator::make($request->all(), [
            'address_id' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'result' => false,
                'message' => json_encode($validator->errors()),
                'address_list' =>$address_list,
            ],400);
        }
        $addresses = UserAddress::where('user_id',$user->id)->where('id',$request->address_id)->delete();
        // if(!empty($addresses)){
        //     $address_list = $addresses;
        // }
        $message = 'Address Deleted Succesfully';

    }


    if($request->key == 'update'){

         $validator =  Validator::make($request->all(), [
            'location' => 'required',
            'address_id' => 'required',
            'building_name' => 'required',
            'landmark' => 'required',
            'address_type' => 'required',
            'contact_person_name' => 'required',
            'contact_person_mobile' => 'required',
            'pincode' => 'required',
            'is_default' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'result' => false,
                'message' => json_encode($validator->errors()),
                'address_list' =>$address_list,
            ],400);
        }
        $dbArray = [];
        $dbArray['user_id'] = $user->id;
        $dbArray['location'] = $request->location;
        $dbArray['flat_no'] = $request->flat_no;
        $dbArray['building_name'] = $request->building_name;
        $dbArray['landmark'] = $request->landmark;
        $dbArray['address_type'] = $request->address_type;
        $dbArray['pincode'] = $request->pincode;
        $dbArray['contact_person_name'] = $request->contact_person_name;
        $dbArray['contact_person_mobile'] = $request->contact_person_mobile;
        $dbArray['is_active'] = 'Y';
        $dbArray['is_default'] = isset($request->is_default) ? $request->is_default :'N';
        UserAddress::where('id',$request->address_id)->update($dbArray);
        $message = 'Address Updated Successfully';
    }




    return response()->json([
        'result' => true,
        'message' => $message,
        'address_list'=>$address_list,
    ],200);



}





public function place_order(Request $request){
    $validator =  Validator::make($request->all(), [
        'token' => 'required',
        'address_id' => 'required',
        'payment_method' => 'required',

    ]);
    $user = null;
    if ($validator->fails()) {
        return response()->json([
            'result' => false,
            'message' => json_encode($validator->errors()),
        ],400);
    }
    $user = JWTAuth::parseToken()->authenticate();
    if (empty($user)){
        return response()->json([
            'result' => false,
            'message' => '',
        ],401);
    }

    $dbArray = [];
    $dbArray['user_id'] = $user->id;
    $dbArray['coupon_code'] = isset($request->coupon_code) ? $request->coupon_code :'';
    $dbArray['coupon_discount'] = isset($request->coupon_discount) ? $request->coupon_discount :'0.00';
    $dbArray['delivery_charges'] = isset($request->delivery_charges) ? $request->delivery_charges :'0.00';
    $dbArray['order_amount'] = isset($request->order_amount) ? $request->order_amount :'0.00';
    $dbArray['total_amount'] = isset($request->total_amount) ? $request->total_amount :'0.00';
    $dbArray['payment_method'] = isset($request->payment_method) ? $request->payment_method :'COD';
    $dbArray['delivery_date'] = isset($request->delivery_date) ? $request->delivery_date :null;
    $dbArray['delivery_slot'] = isset($request->delivery_slot) ? $request->delivery_slot :'';
    $dbArray['status'] = isset($request->status) ? $request->status :'PLACED';
    $dbArray['status_change_by'] = isset($request->status_change_by) ? $request->status_change_by :'User';
    $dbArray['order_from'] = isset($request->order_from) ? $request->order_from :'App';
    $dbArray['address_id'] = isset($request->address_id) ? $request->address_id :'';

    $order = Order::create($dbArray);

    $order_id = $order->id;

    $total = 0;

    $carts = Cart::where('user_id',$user->id)->get();
    if(!empty($carts)){

        foreach($carts as $cart){
            $dbArray1 = [];
            $product = Product::where('id',$cart->product_id)->first();
            $verient = Verient::where('id',$cart->variant_id)->first();
            $dbArray1['orderID'] = $order_id;
            $dbArray1['productID'] = $cart->product_id;
            $dbArray1['variantID'] = $cart->variant_id;
            $dbArray1['qty'] = $cart->qty;
            $dbArray1['price'] = $verient->retail_price;
            $dbArray1['net_price'] = $cart->qty * $verient->retail_price;
            $dbArray1['status'] = 'PLACED';
            $dbArray1['cat_id'] = $product->category_id;
            $dbArray1['brand_id'] = $product->brand_id;
            $dbArray1['sub_category_id'] = $product->sub_category_id;
            $total+=$cart->qty * $verient->retail_price;
            OrderItem::insert($dbArray1);
        }

        $order = Order::where('id',$order_id)->first();
        $insertArr = [];
        $insertArr['order_amount'] = $total;
        $insertArr['total_amount'] = $total;

        Order::where('id',$order_id)->update($insertArr);


    }



    return response()->json([
        'result' => true,
        'message' => 'Order Placed Succesfully',
        
    ],200);
}




public function cancel_order(Request $request){
    $validator =  Validator::make($request->all(), [
        'token' => 'required',
        'order_id' => 'required',

    ]);
    $user = null;
    if ($validator->fails()) {
        return response()->json([
            'result' => false,
            'message' => json_encode($validator->errors()),
        ],400);
    }
    $user = JWTAuth::parseToken()->authenticate();
    if (empty($user)){
        return response()->json([
            'result' => false,
            'message' => '',
        ],401);
    }

    $order = Order::where('id',$request->order_id)->first();
    if(!empty($order)){
        Order::where('id',$order->id)->update(['status'=>'CANCEL']);
        OrderItem::where('orderID',$order->id)->update(['status'=>'CANCEL']);
    }

    return response()->json([
        'result' => true,
        'message' => 'Order Cancelled Succesfully',
    ],200);

}


public function order_list(Request $request){
    $validator =  Validator::make($request->all(), [
        'token' => 'required',
    ]);
    $orders = null;
    $user = null;
    if ($validator->fails()) {
        return response()->json([
            'result' => false,
            'message' => json_encode($validator->errors()),
            'orders' =>$orders,
        ],400);
    }
    $user = JWTAuth::parseToken()->authenticate();
    if (empty($user)){
        return response()->json([
            'result' => false,
            'message' => '',
            'orders' =>$orders,
        ],401);
    }
    $orders = Order::where('user_id',$user->id)->latest()->paginate(10);
    if(!empty($orders)){
        foreach($orders as $order){
            $order_items = OrderItem::select('productID','qty','price','net_price')->where('orderID',$order->id)->get();
            if(!empty($order_items)){
                foreach($order_items as $items){
                    $product = Product::where('id',$items->productID)->first();
                    $items->product_name = $product->product_name ?? '';
                }
            }


            $order->order_items = $order_items;


        }
    }

    return response()->json([
        'result' => true,
        'message' => 'Orders List',
        'orders'=>$orders,
    ],200);

}








public function notification_list(Request $request){
    $validator =  Validator::make($request->all(), [
        'token' => 'required',

    ]);
    $notifications = null;
    $user = null;
    if ($validator->fails()) {
        return response()->json([
            'result' => false,
            'message' => json_encode($validator->errors()),
            'notifications' =>$notifications,
        ],400);
    }
    $user = JWTAuth::parseToken()->authenticate();
    if (empty($user)){
        return response()->json([
            'result' => false,
            'message' => '',
            'notifications' =>$notifications,
        ],401);
    }
    $notifications = DB::table('notifications')->select('id','user_id','text','image')->where('user_id',$user->id)->get();

    return response()->json([
        'result' => true,
        'message' => 'Notification List',
        'notifications'=>$notifications,
    ],200);

}


public function contact_us(Request $request){
 $validator =  Validator::make($request->all(), [
    'token' => 'required',
    'name' => 'required',
    'email' => 'required',
    'message' => 'required',

]);

 $user = null;
 if ($validator->fails()) {
    return response()->json([
        'result' => false,
        'message' => json_encode($validator->errors()),

    ],400);
}
$user = JWTAuth::parseToken()->authenticate();
if (empty($user)){
    return response()->json([
        'result' => false,
        'message' => '',
    ],401);
}


$dbArray = [];
$dbArray['name'] = $request->name;
$dbArray['email'] = $request->email;
$dbArray['message'] = $request->message;
$dbArray['user_id'] = $user->id;

DB::table('contact_us')->insert($dbArray);

return response()->json([
    'result' => true,
    'message' => 'Submitted Successfully',
],200); 
}




public function home(Request $request){
    $validator =  Validator::make($request->all(), [
        'token' => 'required',
    ]);

    $user = null;
    if ($validator->fails()) {
        return response()->json([
            'result' => false,
            'message' => json_encode($validator->errors()),

        ],400);
    }
    $user = JWTAuth::parseToken()->authenticate();
    if (empty($user)){
        return response()->json([
            'result' => false,
            'message' => '',
        ],401);
    }

    $home_data = [];
    $categories = [];
    $desease = [];
    $banners = Banner::where('status',1)->get();
    $home_data['banners'] = $banners;
    $home_data['categories'] = $categories;
    $home_data['desease'] = $desease;



    return response()->json([
        'result' => true,
        'message' => 'Home Data',
        'home_data'=>$home_data,


    ],200); 
}


public function transaction_history(Request $request){
    $validator =  Validator::make($request->all(), [
        'token' => 'required',
    ]);

    $user = null;
    if ($validator->fails()) {
        return response()->json([
            'result' => false,
            'message' => json_encode($validator->errors()),

        ],400);
    }
    $user = JWTAuth::parseToken()->authenticate();
    if (empty($user)){
        return response()->json([
            'result' => false,
            'message' => '',
        ],401);
    }

    
    $transaction_history = DB::table('transactions')->where('user_id',$user->id)->latest()->get();


    if(!empty($transaction_history)){
        foreach($transaction_history as $his){
            $his->paid_at = date('d M Y h:i A',strtotime($his->created_at));
        }
    }
    return response()->json([
        'result' => true,
        'message' => 'Transaction History',
        'transaction_history'=>$transaction_history,
    ],200);  


}




public function create_payment(Request $request){
    $validator =  Validator::make($request->all(), [
        'token' => 'required',
        'course_id' => 'required',
        'type' => 'required',
    ]);
    $user = null;
    if ($validator->fails()) {
        return response()->json([
            'result' => false,
            'message' => json_encode($validator->errors()),

        ],400);
    }
    $user = JWTAuth::parseToken()->authenticate();
    if (empty($user)){
        return response()->json([
            'result' => false,
            'message' => '',
        ],401);
    }
    $api_key = 'rzp_test_PrWbblwHjSxp2p';
    $api_secret = 'ecOIpaWurUy28fjgj3VxPWOw';
    $api = new Api($api_key, $api_secret);
    $amount = 0;

    $course = Course::where('id',$request->course_id)->first();
    if(!empty($course)){
        if($request->type == 'monthly'){
            $amount = $course->monthly_amount;
        }
        if($request->type == 'full'){
            $amount = $course->full_amount;
        }
    }

    if($amount == 0){
       return response()->json([
        'result' => false,
        'message' => 'No Found',
    ],400);
   }

   $paymentArr = [];
   $paymentArr['currency'] = "INR"; 
   $paymentArr['amount'] = $amount * 100; 
   $order = $api->order->create($paymentArr);
   $orderId = $order['id'];
   $user_payment = new SubscriptionHistory;

   $user_payment->user_id =  $user->id;
   $user_payment->amount = $amount;
   $user_payment->payment_type = 'online';
   $user_payment->payment_cause = 'subscription';

   $user_payment->course_id = $request->course_id;
   $user_payment->type = $request->type;
   $user_payment->order_id = $orderId;
   $user_payment->save();

   return response()->json([
    'result' => true,
    'message' => 'Succesfully',
    'payment_details' => $user_payment,
    'callback_url' => url('api/check_payment'),
],200);


}




public function check_payment(Request $request){
   $validator =  Validator::make($request->all(), [
    'razorpay_order_id' => 'required',
    'razorpay_signature' => 'required',
    'razorpay_payment_id' => 'required',
]);

   if ($validator->fails()) {
    return response()->json([
        'result' => false,
        'message' => json_encode($validator->errors()),

    ],400);
}

$data = [];

$data['razorpay_order_id'] = $request->razorpay_order_id;
$data['razorpay_signature'] = $request->razorpay_signature;
$data['razorpay_payment_id'] = $request->razorpay_payment_id;

$data = $request->all();
$user = SubscriptionHistory::where('order_id', $data['razorpay_order_id'])->first();
$user->paid_status = true;
$user->txn_no = $data['razorpay_payment_id'];
$api = new Api('rzp_test_PrWbblwHjSxp2p', 'ecOIpaWurUy28fjgj3VxPWOw');
try{
    $attributes = array(
       'razorpay_signature' => $data['razorpay_signature'],
       'razorpay_payment_id' => $data['razorpay_payment_id'],
       'razorpay_order_id' => $data['razorpay_order_id']
   );
    $order = $api->utility->verifyPaymentSignature($attributes);
    $success = true;
}catch(SignatureVerificationError $e){

    $succes = false;
}


if($success){
    $user->save();


    $course = Course::where('id',$user->course_id)->first();


    $transactionArr = [];
    $transactionArr['user_id'] = $user->user_id;
    $transactionArr['txn_no'] = $user->txn_no;
    $transactionArr['reason'] = $course->course_name ?? '';
    $transactionArr['amount'] = $user->amount;
    $transactionArr['type'] = 'debit';
    $transactionArr['status'] = 1;


    DB::table('transactions')->insert($transactionArr);

    $sub_type_exist = DB::table('user_sub_type')->where('user_id',$user->user_id)->where('course_id',$user->course_id)->first();

    if(empty($sub_type_exist)){
        $arrArr = [];
        $arrArr['user_id'] = $user->user_id;
        $arrArr['course_id'] = $user->course_id;
        $arrArr['sub_type'] = $user->type;
        DB::table('user_sub_type')->insert($arrArr);
    }
    

        ///////Update paid Status

    if(!empty($course)){
        if($course->type == 'pre_recorded'){
            if($user->type == 'monthly'){
                $dbArray = [];
                $dbArray['start_date'] = date('Y-m-d');
                $date = date('Y-m-d');
                $dbArray['end_date'] = date('Y-m-d', strtotime($date. ' + 1 months'));
                SubscriptionHistory::where('id',$user->id)->update($dbArray);
            }
            if($user->type == 'full'){
                $dbArray = [];
                $dbArray['start_date'] = date('Y-m-d');
                $dbArray['end_date'] = date('Y-m-d', strtotime(' + '.$course->duration.' months'));
                SubscriptionHistory::where('id',$user->id)->update($dbArray);
            }
        }
        if($course->type == 'live'){
            if($user->type == 'monthly'){
                $dbArray = [];
                $dbArray['start_date'] = $course->start_date;
                $date = $course->start_date;
                $dbArray['end_date'] = date("Y-m-d", strtotime($date. ' + 1 months'));
                SubscriptionHistory::where('id',$user->id)->update($dbArray);
            }
            if($user->type == 'full'){
                $dbArray = [];
                $dbArray['start_date'] = $course->start_date;
                $dbArray['end_date'] = date('Y-m-d', strtotime(' + '.$course->duration.' months'));;
                SubscriptionHistory::where('id',$user->id)->update($dbArray); 
            }

        }
    }

    return response()->json([
        'result' => true,
        'message' => 'Succesfully',
    ],200);
        // return true;

}else{

    return response()->json([
        'result' => false,
        'message' => 'Not Succesfully',
    ],200);
        // return false;

}





    // return true;

}


public function add_wallet(Request $request)
{
    $validator = Validator::make($request->all(), [
        'token' => 'required',
        'amount' => 'required',

    ]);

    $user = null;

    if($validator->fails())
    {
        return response()->json([
            'result' => false,
            'message' => json_encode($validator->errors()),
        ],400);
    }

    $user  = JWTAuth::parseToken()->authenticate();
    if(empty($user))
    {
        return resposne()->json([
            'result' => false,
            'message' => '',
        ], 401);

    }

    $api_key = 'rzp_test_PrWbblwHjSxp2p';
    $api_secret = 'ecOIpaWurUy28fjgj3VxPWOw';
    $api = new Api($api_key, $api_secret);
    $amount = $request->amount;

    if($amount == 0)
    {
        return response()->json([
            'result' => false,
            'message' => 'Amount Not Available',
        ], 400);
    }



    $paymentDetails = [];
    $paymentDetails['currency'] = 'INR';
    $paymentDetails['amount'] = $amount * 100;
    $order = $api->order->create($paymentDetails);
    $orderId = $order['id'];

    $user_order = new Order;

    $user_order->user_id = $user->id;
    $user_order->order_id = $orderId;
    $user_order->amount = $amount;
    $user_order->save();

    return response()->json([
        'result' => true,
        'message' => 'Successfully',
        'paymentDetails' => $user_order,

    ], 200);

}

public function check_wallet(Request $request)
{

    $validator = Validator::make($request->all(), [
     'razorpay_order_id' => 'required',
     'razorpay_signature' => '',
     'razorpay_payment_id' => '',
 ]);

    if($validator->fails())
    {
        return response()->json([
            'result' => false,
            'message' => json_encode($validator->errors()),
        ],400);
    }

    $details = [];
    $details['razorpay_order_id'] = $request->razorpay_order_id;
    $details['razorpay_signature'] = $request->razorpay_signature;
    $details['razorpay_payment_id'] = $request->razorpay_payment_id;

    $details = $request->all();    
    $order_data = Order::where('order_id', $details['razorpay_order_id'])->first();
    $order_data->paid_status = 1;
    $order_data->txn_no = $details['razorpay_payment_id'];  

    $user_id = $order_data->user_id;
    $order_amt = $order_data->amount; 

    $api_key = 'rzp_test_PrWbblwHjSxp2p';
    $api_secret = 'ecOIpaWurUy28fjgj3VxPWOw';

    $api = new Api($api_key , $api_secret);
    try{

        $attibutes = array(
            'razorpay_order_id' => $details['razorpay_order_id'],
            'razorpay_payment_id' => $details['razorpay_payment_id'],
            'razorpay_signature' => $details['razorpay_signature']
        );

        $order = $api->utility->verifyPaymentSignature($attibutes); 
        $success = true;    

    }catch(SignatureVerificationError $e)
    {
        $success = false;
    }

    if($success)
    {
        $order_data->update(['paid_status' => $order_data->paid_status ,'txn_no' => $order_data->txn_no]);

        $user_Details = User::select('id','wallet')->where('id',$user_id)->first();
        $wallet_amt = $user_Details->wallet;
        
        $total_amt = $wallet_amt + $order_amt;
        
        $user_Details = User::where('id',$user_id)->update(['wallet'=> $total_amt ]);

        $transactionArry = [];

        $transactionArry['user_id'] = $user->user_id;
        $transactionArry['txn_no'] = $user->txn_no;
        $transactionArry['amount'] = $total_amt;
        $transactionArry['reason'] = 'add wallet';
        $transactionArry['type'] = 'credit';
        $transactionArry['status'] = 1;

        DB::table('transactions')->insert($transactionArry);
        
        return response()->json([
            'result' => true,
            'message' => 'Succesfully',
        ],200);           

    }else{

        return response()->json([
            'result' => false,
            'message' => 'Not Succesfully',
        ],200);          
    }
}






}
