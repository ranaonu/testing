<?php

namespace Wave\Http\Controllers;

use Illuminate\Http\Request;
use Wave\Post;
use Wave\plan;


class CartController extends \App\Http\Controllers\Controller
{
    public function index(){
        $cart_plan = \Session::get('cart_plan');
        if($cart_plan){
            return view('theme::Cart.index',compact('cart_plan'));   
        }
    }

    public function setPlan(Request $request){
        $response['status'] = 'success';
        \Session::forget('cart_plan');
        \Session::put('cart_plan', $request->all());
        return response()->json($response);
    }

    public function checkoutSession(Request $request){

        if(isset($request->all()['action'])){ 
            \Session::forget('plan_id');
            \Session::put('plan_id', $request->all()['plan_id']);
            return "stripe";
            }else{
            $plan_id = \Session::get('plan_id');
            \Stripe\Stripe::setApiKey('sk_test_WRB0vbQpeyp1NWIQChFZLu4200dEMFMOuW');
            header('Content-Type: application/json');
            $line_items = [[
                'price' => $plan_id,
                'quantity' => 1
            ]];
            $checkout_session = \Stripe\Checkout\Session::create([
                'payment_method_types' => ['card'],
                'mode' => 'subscription',
                'allow_promotion_codes' => true,
                'line_items' => $line_items,
                'success_url' => url('/sign-up?session_id={CHECKOUT_SESSION_ID}'),
                'cancel_url' => url('/cart'),

            ]);
            return json_encode(['id' => $checkout_session->id]);
        }
    }

}
