<?php

namespace Wave\Http\Controllers;

use Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Session;
use App;
use Validator;
use Wave\User;
use Wave\KeyValue;
use Wave\ApiKey;
use TCG\Voyager\Http\Controllers\Controller;
use Wave\PaddleSubscription;
use Wave\Countries;
use Wave\Order;
use Wave\OrdersItem;
use Wave\CustomerAddress;
use Wave\OfficeSupply;
use Wave\Claim;
use Wave\Ticket;

class SettingsController extends Controller
{
    public function index($section = ''){
        $subs_id = "";
        if(auth()->user()->stripe_id){
            $subscription = PaddleSubscription::where('subscription_id', auth()->user()->stripe_id)->first();
            $subs_id = $subscription->plan_id;
        }
        if(empty($section)){
            return redirect(route('wave.settings', 'profile'));
        }
        $countries = Countries::orderBy('country_name', 'ASC')->get();
        $orders = Order::where('user_id',auth()->user()->id)->get();
        $claims = '';
        if($section == 'claims'){
            $claims = Claim::where('user_id',auth()->user()->id)->get();
        }
        $tickets = '';
        if($section == 'tickets'){
            $tickets = Ticket::where('user_id',auth()->user()->id)->get();
        }
    	return view('theme::settings.index', compact('section','subs_id','countries','orders','claims','tickets'));
    }

    public function order_details($order_id = ''){
        $section = "order-details";
        $order = Order::where('id',$order_id)->first();
        $order_items = OrdersItem::where('order_id',$order_id)->get();
        foreach($order_items as $item){
            $product = OfficeSupply::where('id',$item->office_supply_id)->first();
            $products[$product->id] = array('name'=>$product->name,'quantity'=>$item->quantity,'price'=>$item->price,'image'=>$product->image);
        }
        $address = CustomerAddress::where('order_id',$order_id)->get();
    	return view('theme::settings.index', compact('section','order','order_items','address','products'));
    }

    public function ticket_details($id){
        $section = "ticket-details";
        $ticket = Ticket::where('id',$id)->first();
    	return view('theme::settings.index', compact('section','ticket'));
    }

    public function profilePut(Request $request){
        $request->validate([
            'name' => 'required|string',
            'email' => 'sometimes|required|email|unique:users,email,' . Auth::user()->id,
            'username' => 'sometimes|required|unique:users,username,' . Auth::user()->id,
        ]);

    	$authed_user = auth()->user();

    	$authed_user->name = $request->name;
    	$authed_user->email = $request->email;
        $authed_user->shipper_phone = $request->shipper_phone;
        $authed_user->shipper_address = $request->shipper_address;
        if($request->avatar){
    	   $authed_user->avatar = $this->saveAvatar($request->avatar, $authed_user->username);
        }
    	$authed_user->save();

    	foreach(config('wave.profile_fields') as $key){
    		if(isset($request->{$key})){
	    		$type = $key . '_type__wave_keyvalue';
	    		if($request->{$type} == 'checkbox'){
	                if(!isset($request->{$key})){
	                    $request->request->add([$key => null]);
	                }
	            }

	            $row = (object)['field' => $key, 'type' => $request->{$type}, 'details' => ''];
	            $value = $this->getContentBasedOnType($request, 'themes', $row);

	    		if(!is_null($authed_user->keyValue($key))){
	    			$keyValue = KeyValue::where('keyvalue_id', '=', $authed_user->id)->where('keyvalue_type', '=', 'users')->where('key', '=', $key)->first();
	    			$keyValue->value = $value;
	    			$keyValue->type = $request->{$type};
	    			$keyValue->save();
	    		} else {
	    			KeyValue::create(['type' => $request->{$type}, 'keyvalue_id' => $authed_user->id, 'keyvalue_type' => 'users', 'key' => $key, 'value' => $value]);
	    		}
	    	}
    	}

    	return back()->with(['message' => 'Successfully updated user profile', 'message_type' => 'success']);
    }

    public function securityPut(Request $request){

        $validator = Validator::make($request->all(), [
            'current_password' => 'required',
            'password' => 'required|confirmed|min:'.config('wave.auth.min_password_length'),
        ]);

        if ($validator->fails()) {
            return back()->with(['message' => $validator->errors()->first(), 'message_type' => 'danger']);
        }

        if (! Hash::check($request->current_password, $request->user()->password)) {
            return back()->with(['message' => 'Incorrect current password entered.', 'message_type' => 'danger']);
        }

        auth()->user()->forceFill([
            'password' => bcrypt($request->password)
        ])->save();

        return back()->with(['message' => 'Successfully updated your password.', 'message_type' => 'success']);
    }

    public function paymentPost(Request $request){
        $subscribed = auth()->user()->updateCard($request->paymentMethod);
    }

    public function apiPost(Request $request){
        $request->session()->put('locale', $request->language);
        if (Session::has('locale')) {
            App::setLocale(Session::get('locale'));
        }
        if(Session::get('locale')){
            return back()->with(['message' => 'Successfully updated langugage', 'message_type' => 'success']);
        } else {
            return back()->with(['message' => 'Error Creating API Key, please make sure you entered a valid name.', 'message_type' => 'danger']);
        }
    }

    public function apiPut(Request $request, $id = null){
        if(is_null($id)){
            $id = $request->id;
        }
        $apiKey = ApiKey::findOrFail($id);
        if($apiKey->user_id != auth()->user()->id){
            return back()->with(['message' => 'Canot update key name. Invalid User', 'message_type' => 'danger']);
        }
        $apiKey->name = str_slug($request->key_name);
        $apiKey->save();
        return back()->with(['message' => 'Successfully update API Key name.', 'message_type' => 'success']);
    }

    public function apiDelete(Request $request, $id = null){
        if(is_null($id)){
            $id = $request->id;
        }
        $apiKey = ApiKey::findOrFail($id);
        if($apiKey->user_id != auth()->user()->id){
            return back()->with(['message' => 'Canot delete Key. Invalid User', 'message_type' => 'danger']);
        }
        $apiKey->delete();
        return back()->with(['message' => 'Successfully Deleted API Key', 'message_type' => 'success']);
    }

    private function saveAvatar($avatar, $filename){
    	$path = 'avatars/' . $filename . '.png';
    	Storage::disk(config('voyager.storage.disk'))->put($path, file_get_contents($avatar));
    	return $path;
    }

    public function invoice(Request $request, $invoiceId) {
        return $request->user()->downloadInvoice($invoiceId, [
            'vendor'  => setting('site.title', 'Wave'),
            'product' => ucfirst(auth()->user()->role->name) . ' Subscription Plan',
        ]);
    }

    public function get_card(){
        $stripe_id = auth()->user()->stripe_id;
        $subscription = PaddleSubscription::where('subscription_id', $stripe_id)->first();
        $customer_id = $subscription->customer_id;
        $stripe = new \Stripe\StripeClient(
            'sk_test_WRB0vbQpeyp1NWIQChFZLu4200dEMFMOuW'
          );
        $card = $stripe->customers->allSources(
            $customer_id,
            ['object' => 'card', 'limit' => 10]
        );
        return $card['data'];
    }

    public function add_card(Request $request){
        $stripe_id = auth()->user()->stripe_id;
        $subscription = PaddleSubscription::where('subscription_id', $stripe_id)->first();
        $customer_id = $subscription->customer_id;
        $stripe = new \Stripe\StripeClient(
            'sk_test_WRB0vbQpeyp1NWIQChFZLu4200dEMFMOuW'
          );
        $stripe = new \Stripe\StripeClient(
            'sk_test_WRB0vbQpeyp1NWIQChFZLu4200dEMFMOuW'
          );
        $card = $stripe->customers->createSource(
        $customer_id,
        ['source' => $request->stripeToken]
        );
        return Redirect::to('/settings/cards');
    }

    public function delete_card(Request $request){
        $stripe_id = auth()->user()->stripe_id;
        $subscription = PaddleSubscription::where('subscription_id', $stripe_id)->first();
        $customer_id = $subscription->customer_id;
        $stripe = new \Stripe\StripeClient(
            'sk_test_WRB0vbQpeyp1NWIQChFZLu4200dEMFMOuW'
          );
        $card = $stripe->customers->deleteSource(
        $customer_id,
        $request->card_id,
        []
        );
        return $card;
    }
}
