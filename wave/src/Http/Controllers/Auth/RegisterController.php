<?php

namespace Wave\Http\Controllers\Auth;

use App\User;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Auth\Events\Registered;
use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Http\Request;
use Wave\Notifications\VerifyEmail;
use Illuminate\Support\Facades\Notification;
use Carbon\Carbon;
use Wave\Countries;
use \stdClass;
use Wave\PaddleSubscription;
use Twilio\Rest\Client;
use Mail;



class RegisterController extends \App\Http\Controllers\Controller
{
    /*
    |--------------------------------------------------------------------------
    | Register Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles the registration of new users as well as their
    | validation and creation. By default this controller uses a trait to
    | provide this functionality without requiring any additional code.
    |
    */

    use RegistersUsers;

    /**
     * Where to redirect users after registration.
     *
     * @var string
     */
    protected $redirectTo = '/welcome';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest', ['except' =>
            [
                'complete'
            ]]);
    }

    /**
     * Get a validator for an incoming registration request.
     *
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function validator(array $data)
    {
        if(setting('auth.username_in_registration') && setting('auth.username_in_registration') == 'yes'){
            return Validator::make($data, [
                'name' => 'required|string|max:255',
                'email' => 'required|string|email|max:255|unique:users',
                'username' => 'required|string|max:20|unique:users',
                'password' => 'required|string|min:6|confirmed'
            ]);
        }

        return Validator::make($data, [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6|confirmed'
        ]);
    }

    /**
     * Create a new user instance after a valid registration.
     *
     * @param  array  $data
     * @return \App\User
     */
    public function create(array $data)
    {
        $role = \TCG\Voyager\Models\Role::where('name', '=', config('voyager.user.default_role'))->first();

        $verification_code = NULL;
        $verified = 1;

        if(setting('auth.verify_email', false)){
            $verification_code = str_random(30);
            $verified = 0;
        }

        if(isset($data['username']) && !empty($data['username'])){
            $username = $data['username'];
        } elseif(isset($data['name']) && !empty($data['name'])) {
            $username = str_slug($data['name']);
        } else {
            $username = $this->getUniqueUsernameFromEmail($data['email']);
        }

        $username_original = $username;
        $counter = 1;

        while(User::where('username', '=', $username)->first()){
            $username = $username_original . (string)$counter;
            $counter += 1;
        }

        $trial_days = setting('billing.trial_days', 14);
        $trial_ends_at = null;
        // if trial days is not zero we will set trial_ends_at to ending date
        if(intval($trial_days) > 0){
            $trial_ends_at = now()->addDays(setting('billing.trial_days', 14));
        }
        $data['shipper_phone'] = $data['shipper_phone_code'].''.$data['shipper_phone'];
        if($data['isreceiver_diff'] == "no"){
            $data['consignee_name'] = $data['name'];
            $data['consignee_phone'] = $data['shipper_phone'];
            $data['consignee_country'] = $data['shipper_country'];
            $data['consignee_address'] = $data['shipper_address'];
            $data['consignee_zip'] = $data['shipper_zip'];
            $data['consignee_city'] = $data['shipper_city'];
            $data['consignee_state'] = $data['shipper_state'];
        }else{
            $data['consignee_phone'] = $data['consignee_phone_code'].''.$data['consignee_phone'];
        }
        $user = new User;
        $user->name       = $data['name'];
        $user->email        =  $data['email'];
        $user->username   = $username;
        $user->password   = bcrypt($data['password']);
        $user->role_id   = $role->id;
        $user->verification_code   = $verification_code;
        $user->verified   = $verified;
        $user->trial_ends_at   = $trial_ends_at;
        $user->shipper_phone = $data['shipper_phone'];
        $user->shipper_country = $data['shipper_country'];
        $user->shipper_address = $data['shipper_address'];
        $user->shipper_zip = $data['shipper_zip'];
        $user->shipper_city = $data['shipper_city'];
        $user->shipper_state = $data['shipper_state'];
        $user->consignee_name = $data['consignee_name'];
        $user->consignee_phone = $data['consignee_phone'];
        $user->consignee_country = $data['consignee_country'];
        $user->consignee_address = $data['consignee_address'];
        $user->consignee_zip = $data['consignee_zip'];
        $user->consignee_city = $data['consignee_city'];
        $user->consignee_state = $data['consignee_state'];
        $user->isreceiver_diff = $data['isreceiver_diff'];
        $user->whatsapp_prefer = $data['whatsapp_prefer'];
        $user->stripe_id = $data['subs_id'];
        $user->save();

        if($data['whatsapp_prefer'] == "yes"){
            $this->sendWhatsappNotificationShipper($data['name'],$data['shipper_phone']);
            if($data['isreceiver_diff'] == "yes"){
                //$this->sendWhatsappNotificationConsignee($data['consignee_name'],$data['consignee_phone']);
            }
        }else{
            $this->sendMessageNotificationShipper($data['name'],$data['shipper_phone']);
            if($data['isreceiver_diff'] == "yes"){
                //$this->sendMessageNotificationConsignee($data['consignee_name'],$data['consignee_phone']);
            }
        }
        $this->sendEmailNotification($data['name'],$data['email']);
        if($data['subs_id']){
            $subscription = PaddleSubscription::create([
                'subscription_id' => $data['subs_id'],
                'plan_id' => $data['plan_id'],
                'customer_id' => $data['customer_id'],
                'user_id' => $user->id,
                'status' => 'active', // https://paddle.com/docs/subscription-status-reference/
                'next_bill_data' => \Carbon\Carbon::now()->addMonths(1)->toDateTimeString(),
                'cancel_url' => "test", //$subscription->cancel_url,
                'update_url' => "test", //$subscription->update_url
            ]);
        }
        // $user = User::create([
        //     'name' => $data['name'],
        //     'email' => $data['email'],
        //     'username' => $username,
        //     'password' => bcrypt($data['password']),
        //     'role_id' => $role->id,
        //     'verification_code' => $verification_code,
        //     'verified' => $verified,
        //     'trial_ends_at' => $trial_ends_at,
        //     'shipper_country' => $data['shipper_country']
        // ]);

        if(setting('auth.verify_email', false)){
            $this->sendVerificationEmail($user);
        }

        return $user;
    }

    /**
     * Complete a new user registration after they have purchased
     *
     * @param  Request  $request
     * @return redirect
     */
    public function complete(Request $request){

        if(setting('auth.username_in_registration') && setting('auth.username_in_registration') == 'yes'){
            $request->validate([
                'name' => 'required|string|min:3|max:255',
                'username' => 'required|string|max:20|unique:users,username,' . auth()->user()->id,
                'password' => 'required|string|min:6'
            ]);
        } else {
            $request->validate([
                'name' => 'required|string|min:3|max:255',
                'password' => 'required|string|min:6'
            ]);
        }

        // Update the user info
        $user = auth()->user();
        $user->name = $request->name;
        $user->username = $request->username;
        $user->password = bcrypt($request->password);
        $user->save();


        return redirect()->route('wave.dashboard')->with(['message' => 'Successfully updated your profile information.', 'message_type' => 'success']);

    }

    private function sendVerificationEmail($user){
        Notification::route('mail', $user->email)->notify(new VerifyEmail($user));
    }

    public function showRegistrationForm()
    {
        if(setting('billing.card_upfront')){
            return redirect()->route('wave.pricing');
        }
        return view('theme::auth.register');
    }

    public function showFreeRegistrationForm(Request $request)
    {
        $customer = new \stdClass();
        if($request->get('session_id')){
            \Stripe\Stripe::setApiKey('sk_test_WRB0vbQpeyp1NWIQChFZLu4200dEMFMOuW');
            $session = \Stripe\Checkout\Session::retrieve($request->get('session_id'));
            $customer = \Stripe\Customer::retrieve($session->customer);
            $customer_id = $customer->id;
            $subscriptions_id = $customer->subscriptions->data[0]->id;
            $plan_id = $customer->subscriptions->data[0]->plan->id;
        }else{
            $subscriptions_id = "";
            $customer->name = "";
            $customer->email = "";
            $plan_id = "";
            $customer_id = "";
        }
        $countries = Countries::orderBy('country_name', 'ASC')->get();
        return view('theme::auth.register', compact('countries','customer','subscriptions_id','plan_id','customer_id'));
    }

    public function verify(Request $request, $verification_code){
        $user = User::where('verification_code', '=', $verification_code)->first();

        $user->verification_code = NULL;
        $user->verified = 1;
        $user->email_verified_at = Carbon::now();
        $user->save();

        return redirect()->route('login')->with(['message' => 'Successfully verified your email. You can now login.', 'message_type' => 'success']);
    }

    /**
     * Handle a registration request for the application.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function register(Request $request)
    {
        $this->validator($request->all())->validate();

        event(new Registered($user = $this->create($request->all())));

        if(setting('auth.verify_email')){
            // send email verification
            return redirect()->route('login')->with(['message' => 'Thanks for signing up! Please check your email to verify your account.', 'message_type' => 'success']);
        } else {
            $this->guard()->login($user);

            return $this->registered($request, $user)
                        ?: redirect($this->redirectPath())->with(['message' => 'Thanks for signing up!', 'message_type' => 'success']);
        }
    }

    public function getUniqueUsernameFromEmail($email)
    {
        $username = strtolower(trim(str_slug(explode('@', $email)[0])));

        $new_username = $username;

        $user_exists = \Wave\User::where('username', '=', $username)->first();
        $counter = 1;
        while (isset($user_exists->id) ) {
            $new_username = $username . $counter;
            $counter += 1;
            $user_exists = \Wave\User::where('username', '=', $new_username)->first();
        }

        $username = $new_username;

        if(strlen($username) < 4){
            $username = $username . uniqid();
        }

        return strtolower($username);
    }

    public function sendWhatsappNotificationShipper(string $name , string $recipient)
    {
        $sid = getenv("TWILIO_AUTH_SID");
        $token = getenv("TWILIO_AUTH_TOKEN");
        $wa_from = getenv("TWILIO_WHATSAPP_FROM");
        $twilio = new Client($sid, $token);
        $body = "Thank you for signup with us *{$name}* ,
        \nYour account number is: *ZSE{}* 
        \nSend all your shipments to your private address bellow:
        \n1117 NE 163rd St. Suite C-ZSE{}
        \nNorth Miami Beach FL 33162.
        
        \n\nUse the coupon code: {}  to get 10% OFF your next shipments.
        \nThis coupon is expire on {}.
        \nFor further details please call us on (305) 515-2616
        
        \n\nZion Shipping!";
        try{
        $message = $twilio->messages->create("whatsapp:$recipient",array("from" => $wa_from, "body" => $body));
        }catch(Exception $e) {

        }
    }

    public function sendWhatsappNotificationConsignee(string $name , string $recipient)
    {
        $sid    = getenv("TWILIO_AUTH_SID");
        $token  = getenv("TWILIO_AUTH_TOKEN");
        $wa_from= getenv("TWILIO_WHATSAPP_FROM");
        $twilio = new Client($sid, $token);
        "Mesi paskew' kreye kont ou ak nou *{$name}* ,
        \nNimewo kont ou an se: *ZSE{}* 
        \nVoye tout komand ou yo nan adres prive ou a konsa:
        \n1117 NE 163rd St. Suite C-ZSE{}
        \nNorth Miami Beach FL 33162.

        \n\nPa bliye chak fwa ou refere yon zanmiw' kreye kont pal' wap benefisye 10.00 USD. Pou plis detay rele oubyen WhatsApp nou nan 3421-5356 / 4259-8159

        \n\nZion Shipping!";
        try{
        $message = $twilio->messages->create("whatsapp:$recipient",["from" => $wa_from, "body" => $body]);
        }catch(Exception $e) {

        }
    }

    public function sendMessageNotificationShipper(string $name , string $recipient)
    {
        $sid    = getenv("TWILIO_AUTH_SID");
        $token  = getenv("TWILIO_AUTH_TOKEN");
        $wa_from= '+19062144627';
        $twilio = new Client($sid, $token);
        $shipper_sms = "$name, Your account No: ZSE{}. Use the coupon {} to get 10% OFF your shipments.\r\n Expire {}";
        try{
        $message = $twilio->messages->create($recipient,array("from" => $wa_from, "body" => $shipper_sms));
        }catch(Exception $e) {

        }
    }

    public function sendMessageNotificationConsignee(string $name , string $recipient)
    {
        $sid    = getenv("TWILIO_AUTH_SID");
        $token  = getenv("TWILIO_AUTH_TOKEN");
        $wa_from= '+19062144627';
        $twilio = new Client($sid, $token);
        $consignee_sms = "$name, Nimewo kont ou se: ZSE{}. Chak fwa ou refere yon zanmiw' kreye kont Zion pal' wap gen $10us.\r\nRele 3421-5356 pou plis detay.";
        try{
        $message = $twilio->messages->create($recipient,array("from" => $wa_from, "body" => $$consignee_sms));
        }catch(Exception $e) {

        }
    }
    public function sendEmailNotification($name,$email)
    { 
        $data = array('name'=>$name);
        Mail::send('mail', $data, function($message) use($email) {
            $message->to($email, 'Tutorials Point')->subject
                ('Laravel Basic Testing Mail');
            $message->from('vinitkaushik00@gmail.com','Virat Gandhi');
        });
    }
}
