<?php

namespace Wave\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Wave\Post;
use Wave\plan;
use Wave\OfficeSupply;
use Wave\Countries;
use Wave\Order;
use Wave\OrdersItem;
use Wave\CustomerAddress;
use Illuminate\Support\Facades\Http;
use UpsRate;


class OfficeSuppliesController extends \App\Http\Controllers\Controller
{

    private $ups_sandbox_url;
	private $ups_live_url;
	private $ups_access_key;
	private $ups_user_id;
	private $ups_password;
	private $ups_sandbox_enable;
	private $ups_api_url;
	private $ups_shipper_number;

    public function __construct() {
        $this->ups_sandbox_enable	= config('carriers.ups_sandbox_enable');
		$this->ups_sandbox_url		= config('carriers.ups_sandbox_url');
		$this->ups_live_url			= config('carriers.ups_live_url');
		$this->ups_access_key		= config('carriers.ups_access_key');
		$this->ups_user_id			= config('carriers.ups_user_id');
		$this->ups_password			= config('carriers.ups_password');
		$this->ups_password			= config('carriers.ups_password');
		$this->ups_shipper_number	= config('carriers.ups_shipper_number');

        if ($this->ups_sandbox_enable) {
			$this->ups_api_url = config('carriers.ups_sandbox_url');
		}else{
			$this->ups_api_url = config('carriers.ups_live_url');
		}
    }

    public function index(Request $request){
        $products = OfficeSupply::where('status',1)->paginate('12');
        $cart_count = 0;
        if($request->session()->has('office_supply_cart')){
            $office_supply_cart = session('office_supply_cart');
            foreach($office_supply_cart as $id=>$supply){
                if($id != "shipping"){
                    $cart_count = $cart_count + $supply;
                }
            }
        }
        return view('theme::OfficeSupplies.index',['products'=>$products,'cart_count'=>$cart_count]);
    }

    public function officeSupplyDetail(Request $request, $id){
        $product = OfficeSupply::where('id',$id)->first();
        $cart_count = 0;
        if($request->session()->has('office_supply_cart')){
            $office_supply_cart = session('office_supply_cart');
            foreach($office_supply_cart as $id=>$supply){
                if($id != "shipping"){
                    $cart_count = $cart_count + $supply;
                }
            }
        }
        return view('theme::OfficeSupplies.detail',['product'=>$product,'cart_count'=>$cart_count]);
    }

    public function addToCart(Request $request){
        
        if($request->session()->has('office_supply_cart')){
            $office_supply_cart = session('office_supply_cart');
        }
        
        $product = OfficeSupply::where('id',$request->product_id)->first();
        $min = $product->minOrder;
        if($product->maxOrder > $product->stock){
            $max = $product->stock;
        }else{
            $max = $product->maxOrder;
        }
        if($request->quantity > $max || $request->quantity < $min){
            return response()->json([
                'success'=>false,
                'message'=>'Error: Quantity must be between '.$min.' to '.$max .'. Product not added to cart.'
            ]);
        }
        $office_supply_cart[$request->product_id] = $request->quantity;
        session(['office_supply_cart'=>$office_supply_cart]);
        $cart_count = 0;
        
        foreach($office_supply_cart as $id=>$supply){
            if($id != "shipping"){
                $cart_count = $cart_count + $supply;
            }
        }

        return response()->json([
            'success'=>true,
            'message'=>'',
            'cart_count'=>$cart_count
        ]);
    }

    public function officeSupplyCart(Request $request){
        if($request->session()->has('office_supply_cart')){
            $office_supply_cart = session('office_supply_cart');
            $cart = [];
            foreach($office_supply_cart as $id=>$supply){
                if($id != "shipping"){
                    $product = OfficeSupply::where('id',$id)->first();
                    $cart[$id] = array('name'=>$product->name,'quantity'=>$supply,'price'=>$product->price,'image'=>$product->image,'id'=>$product->id);
                }
            }
            return view('theme::OfficeSupplies.cart',['cart'=>$cart]);
        }else{
            return redirect('/office-supplies')->with('danger','No products in cart!');
        }
    }

    public function updateCart(Request $request){
        //echo '<pre>';print_r($request->quantity);die;
        if($request->session()->has('office_supply_cart')){
            $office_supply_cart = session('office_supply_cart');
        }
        $error = false;
        $message = '';
        foreach($request->quantity as $id=>$supply){
            $product = OfficeSupply::where('id',$id)->first();
            $min = $product->minOrder;
            if($product->maxOrder > $product->stock){
                $max = $product->stock;
            }else{
                $max = $product->maxOrder;
            }
            if($supply > $max || $supply < $min){
                $error = true;
                $message = $message. $product->name .' - Error: Quantity must be between '.$min.' to '.$max .'. Product not added to cart. \n';
            }
            $office_supply_cart[$id] = $supply;
        }
        session(['office_supply_cart'=>$office_supply_cart]);
        if($error){
            return response()->json([
                'success'=>false
            ]);
        }
        return response()->json([
            'success'=>true
        ]);
    }

    public function removeFromCart(Request $request, $id){
        if($request->session()->has('office_supply_cart')){
            $office_supply_cart = session('office_supply_cart');
        }
        $error = false;
        $message = '';
        
        unset($office_supply_cart[$id]);
        session(['office_supply_cart'=>$office_supply_cart]);
        if($error){
            return response()->json([
                'success'=>false
            ]);
        }
        return response()->json([
            'success'=>true
        ]);
    }

    public function officeSupplyCheckout(Request $request){
        if(!isset(auth()->user()->id)){
            return redirect('/login');
        }
        $request->session()->forget('shipping');
        $office_supply_cart = session('office_supply_cart');
        $cart = [];
        foreach($office_supply_cart as $id=>$supply){
            $product = OfficeSupply::where('id',$id)->first();
            $cart[$id] = array('name'=>$product->name,'quantity'=>$supply,'price'=>$product->price,'image'=>$product->image,'id'=>$product->id);
        }
        $countries = Countries::orderBy('country_name', 'ASC')->get();
        $address = CustomerAddress::where('user_id',auth()->user()->id)->orderBy('created_at','desc')->first();
        return view('theme::OfficeSupplies.checkout',['cart'=>$cart,'countries'=>$countries,'address'=>$address]);
    }

    public function officeSupplyPlaceOrder(Request $request){
        $office_supply_cart = session('office_supply_cart');
        $shipping = session('shipping');
        $total = 0;
        foreach($office_supply_cart as $id=>$supply){
            if($id != "shipping"){
                $product = OfficeSupply::where('id',$id)->first();
                $subtotal = $product->price * $supply;
                $total = $total + $subtotal;
            }
        }
        $total_amount = $total + $shipping;
        
        $o = Order::create([
            'user_id'=>auth()->user()->id,
            'shipping'=>'UPS',
            'shipping_amount'=>$shipping,
            'total_amount'=>$total_amount,
            'payment_status'=>'0',
            'order_status'=>'Pending'
        ]);

        $o->order_no = $this->genOrderNumber();
        $o->save();

        foreach($office_supply_cart as $id=>$supply){
            
            $product = OfficeSupply::where('id',$id)->first();
            OrdersItem::create([
                'order_id' => $o->id,
                'office_supply_id' => $id,
                'quantity' => $supply,
                'price' => $product->price,
            ]);
        }
        if($request['checkout-diff-address'] == 'on'){
            $type = 1;
        }else{
            $type = 2;
        }
        CustomerAddress::create([
            'user_id' => auth()->user()->id,
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'company_name' => $request->company_name,
            'country' => $request->to_country,
            'address1' => $request->to_address,
            'zip' => $request->to_zip,
            'city' => $request->to_city,
            'state' => $request->to_state,
            'phone' => $request->phone,
            'email' => $request->email,
            'type' => 1,
            'order_id' => $o->id
        ]);
        if($type == 2){
            CustomerAddress::create([
                'user_id' => auth()->user()->id,
                'first_name' => $request->billing_first_name,
                'last_name' => $request->billing_last_name,
                'company_name' => $request->billing_company_name,
                'country' => $request->bill_country,
                'address1' => $request->billing_address,
                'zip' => $request->billing_zip,
                'city' => $request->billing_city,
                'state' => $request->billing_state,
                'phone' => $request->billing_phone,
                'email' => $request->billing_email,
                'type' => 2,
                'order_id' => $o->id
            ]);
        }
        return view('theme::OfficeSupplies.payment',['order_id'=>$o->id,'total_amount'=>$total_amount,'email'=>$request->email,'zip'=>$request->to_zip]);
    }

    public function getShippingFee(Request $request){
        if($request->to_state == "FL"){
            $response['shipping_fee'] = 0;
            session(['shipping'=>0]);
            return response()->json($response);
        }else{

            $data['from_country'] = 'US';
            $data['from_address'] = '1117 NE 163rd St';
            $data['from_zip'] = '33162';
            $data['from_city'] = 'North Miami Beach';
            $data['from_state'] = 'FL';
            $data['to_country'] = 'US';
            $data['to_address'] = $request->to_address;
            $data['to_zip'] = $request->to_zip;
            $data['to_city'] = $request->to_city;
            $data['to_state'] = $request->to_state;

            $cart_count = 0;
            if($request->session()->has('office_supply_cart')){
                $office_supply_cart = session('office_supply_cart');
                foreach($office_supply_cart as $id=>$supply){
                    if($id != "shipping"){
                        $product = OfficeSupply::where('id',$id)->first();
                        for($s = 1;$s<=$supply;$s++){
                            $data['dimensions']['weight'][] = $product->weight;
                            $data['dimensions']['length'][] = $product->length;
                            $data['dimensions']['width'][] = $product->width;
                            $data['dimensions']['height'][] = $product->height;
                        }
                        $cart_count = $cart_count + $supply;
                    }
                }
            }

            $data['package_count'] = $cart_count;
            $data['total_value'] = 0;
            //dd($data);
            $dataObj = (object) $data;
            $response['shipping_fee'] = $this->getQuotation($dataObj);
            session(['shipping'=>$response['shipping_fee']]);
            return response()->json($response);
        }
    }

    public function paymentStatus(Request $request){
        //dd($request);
        if($request->get('stripe_session_id')){
            \Stripe\Stripe::setApiKey('sk_test_WRB0vbQpeyp1NWIQChFZLu4200dEMFMOuW');
            $session = \Stripe\Checkout\Session::retrieve($request->get('stripe_session_id'));
            if($session){
                $order_id = session('order_id');
                $order = Order::where('id',$order_id)->first();
                $order->payment_status = 1;
                //$order->stripe_token = $request->stripeToken;
                //$order->stripe_token_type = $request->stripeTokenType;
                $order->save();
                $request->session()->forget('office_supply_cart');
                $request->session()->forget('order_id');
                //return view('theme::OfficeSupplies.payment_success',['order_no'=>$order->order_no]);
                return view('theme::OfficeSupplies.payment_success',['order'=>$order]);
        
            }
        }
    }

    public function genOrderNumber(){
        $order_no = rand(10000000, 99999999);
        $order_al = Order::where('order_no',$order_no)->first();
        if(!empty($order_al)){
            $order_no = $this->genOrderNumber();
        }
        return $order_no;
    }

    public function getQuotation($request)
    {
        $response['status'] = 'success';

        $UPSminrate 	= $this->getMinUPSrates_2($request);

        $response['minRate'] = json_decode($UPSminrate,true);
        
        return $response['minRate']['total'];
    }

    public function getMinUPSrates_2($request)
    {
        $common_data = $this->common_data($request);

        $request_dimensions         = $common_data['dimensions'];
		
        $rates  = [];

        $apiURL = $this->ups_api_url.'ship/v1801/rating/Shop?additionalinfo=timeintransit'; // (API URL with Endpoint)

        
        // Headers
        $headers = [
            'accesslicensenumber'   => $this->ups_access_key,
            'username'              => $this->ups_user_id,
            'password'              => $this->ups_password,
            'content-type'          => 'application/json',
            'accept'                => 'application/json'
        ];

        $PackageBillType = '03';
        $PackagingType   = '02';
        $PackagingType_d = 'Package';
        // if ($request->shipment_type && $request->shipment_type == 'contains_document') {
        //     $PackageBillType = '02';
        //     $PackagingType   = '01';
        //     $PackagingType_d = 'UPS Letter';
        // }

        $loopCount      = $request->package_count;
        $total_weight   = 0;
        for ($i=0; $i < $loopCount; $i++) { 
            $total_weight        = $total_weight+(float)$request_dimensions['weight'][$i];
        }

        $rateArray = array();

        $rateArray['RateRequest']['Request']['SubVersion']                                      	= '1703';
        $rateArray['RateRequest']['Request']['TransactionReference']['CustomerContext']         	= '';
        
        $rateArray['RateRequest']['Shipment']['ShipmentRatingOptions']['NegotiatedRatesIndicator']	= 'Bids or Account Based Rates';

        $rateArray['RateRequest']['Shipment']['InvoiceLineTotal']['CurrencyCode']               = 'USD';
        $rateArray['RateRequest']['Shipment']['InvoiceLineTotal']['MonetaryValue']              = ($request->total_value)?(string)$request->total_value:'10';
        $rateArray['RateRequest']['Shipment']['DeliveryTimeInformation']['PackageBillType']     = $PackageBillType;
        $rateArray['RateRequest']['Shipment']['Shipper']['Name']                                = 'Therlande Louis';
        $rateArray['RateRequest']['Shipment']['Shipper']['ShipperNumber']                       = $this->ups_shipper_number;
        $rateArray['RateRequest']['Shipment']['Shipper']['Address']['AddressLine']              = '1117 NE 163rd St. Ste C';
        $rateArray['RateRequest']['Shipment']['Shipper']['Address']['City']                     = 'North Miami Beach';
        $rateArray['RateRequest']['Shipment']['Shipper']['Address']['StateProvinceCode']        = 'FL';
        $rateArray['RateRequest']['Shipment']['Shipper']['Address']['PostalCode']               = '33162';
        $rateArray['RateRequest']['Shipment']['Shipper']['Address']['CountryCode']              = 'US';
        $rateArray['RateRequest']['Shipment']['ShipTo']['Name']                                 = 'ABC';
        $rateArray['RateRequest']['Shipment']['ShipTo']['Address']['AddressLine']               = $common_data['to_address_line1']." ".$common_data['to_address_line2'];
        $rateArray['RateRequest']['Shipment']['ShipTo']['Address']['City']                      = $this->remove_special_characters($request->to_city);
        $rateArray['RateRequest']['Shipment']['ShipTo']['Address']['StateProvinceCode']         = $request->to_state;
        $rateArray['RateRequest']['Shipment']['ShipTo']['Address']['PostalCode']                = ($request->to_zip)?str_replace(" ", "", $request->to_zip):"";
        $rateArray['RateRequest']['Shipment']['ShipTo']['Address']['CountryCode']               = $request->to_country;
        $rateArray['RateRequest']['Shipment']['ShipFrom']['Name']                               = 'Therlande Louis';
        $rateArray['RateRequest']['Shipment']['ShipFrom']['Address']['AddressLine']             = $common_data['from_address_line1']." ".$common_data['from_address_line2'];
        $rateArray['RateRequest']['Shipment']['ShipFrom']['Address']['City']                    = $request->from_city;
        $rateArray['RateRequest']['Shipment']['ShipFrom']['Address']['StateProvinceCode']       = $request->from_state;
        $rateArray['RateRequest']['Shipment']['ShipFrom']['Address']['PostalCode']              = ($request->from_zip)?str_replace(" ", "", $request->from_zip):"";
        $rateArray['RateRequest']['Shipment']['ShipFrom']['Address']['CountryCode']             = $request->from_country;
        $rateArray['RateRequest']['Shipment']['ShipmentTotalWeight']['UnitOfMeasurement']['Code']   = 'LBS';
        $rateArray['RateRequest']['Shipment']['ShipmentTotalWeight']['Weight']                      = (string)$total_weight;

        for ($i=0; $i < $loopCount; $i++) { 
            $rateArray['RateRequest']['Shipment']['Package'][$i]['PackagingType']['Code']                       = $PackagingType;
            $rateArray['RateRequest']['Shipment']['Package'][$i]['PackagingType']['Description']                = $PackagingType_d;
            $rateArray['RateRequest']['Shipment']['Package'][$i]['Dimensions']['UnitOfMeasurement']['Code']     = 'IN';
            $rateArray['RateRequest']['Shipment']['Package'][$i]['Dimensions']['Length']                        = (string)$request_dimensions['length'][$i];
            $rateArray['RateRequest']['Shipment']['Package'][$i]['Dimensions']['Width']                         = (string)$request_dimensions['width'][$i];
            $rateArray['RateRequest']['Shipment']['Package'][$i]['Dimensions']['Height']                        = (string)$request_dimensions['height'][$i];
            $rateArray['RateRequest']['Shipment']['Package'][$i]['PackageWeight']['UnitOfMeasurement']['Code']  = 'LBS';
            $rateArray['RateRequest']['Shipment']['Package'][$i]['PackageWeight']['Weight']                     = (string)$request_dimensions['weight'][$i];
        }

        $http_response  = Http::withHeaders($headers)->post($apiURL, $rateArray);
        $statusCode     = $http_response->status();
                
        $responseBody   = json_decode($http_response->getBody(), true);

        
        if ($statusCode == 200) {
            $UPSresponse['status'] = 'success';
            if (isset($responseBody['RateResponse']) && isset($responseBody['RateResponse']['RatedShipment'])) {
                foreach ($responseBody['RateResponse']['RatedShipment'] as $RatedShipmentkey => $RatedShipmentvalue) {
                    if($RatedShipmentvalue['TimeInTransit']['ServiceSummary']['Service']['Description'] == "UPS Ground"){
                        $rates = [
                            'currency'              => $RatedShipmentvalue['TotalCharges']['CurrencyCode'],
                            'total'                 => isset($RatedShipmentvalue['NegotiatedRateCharges'])?$RatedShipmentvalue['NegotiatedRateCharges']['TotalCharge']['MonetaryValue']:$RatedShipmentvalue['TotalCharges']['MonetaryValue'],
                            'days_to_deliver'       => (isset($RatedShipmentvalue['TimeInTransit']['ServiceSummary']['EstimatedArrival']['TotalTransitDays']) && !empty($RatedShipmentvalue['TimeInTransit']['ServiceSummary']['EstimatedArrival']['TotalTransitDays']))?$RatedShipmentvalue['TimeInTransit']['ServiceSummary']['EstimatedArrival']['TotalTransitDays']:'',
                            'service_name'          => $RatedShipmentvalue['TimeInTransit']['ServiceSummary']['Service']['Description'],
                            'estimatedDeliveryDate' => $RatedShipmentvalue['TimeInTransit']['ServiceSummary']['EstimatedArrival']['Arrival']['Date'],
                            'estimatedDeliveryTime' => $RatedShipmentvalue['TimeInTransit']['ServiceSummary']['EstimatedArrival']['Arrival']['Time']
                        ];
                    }
                }
                
                if ($rates) {
                    $UPSresponse = json_encode($rates);
                }else{
                    $UPSresponse = '{}';
                }
            }            
        }else{
            $UPSresponse = '{}';
        }
        
        return $UPSresponse;
    }

    public function common_data($request){
        $from_address       = preg_split ('/ /', $request->from_address, 3);
        $common_data['from_address_line2']  = array_pop($from_address);
        $common_data['from_address_line1']  = implode(" ", $from_address);


        $to_address         = preg_split ('/ /', $request->to_address, 3);
        $common_data['to_address_line2']    = array_pop($to_address);
        $common_data['to_address_line1']    = implode(" ", $to_address);

        $common_data['dimensions']      = $request->dimensions; 

        return $common_data;
    }

    public function remove_special_characters($string='')
    {
        $string = str_replace("-", " ", $string); // Replaces all spaces with hyphens.
        // $string = str_replace( array( '\'', '"', ',' , ';', '<', '>' ), ' ', $string);
        return preg_replace('/[^a-zA-Z0-9_ %\[\]\.\(\)%&-]/s', '', $string); // Removes special chars.   
        // die();
    }

    public function checkoutSession(Request $request){
        if(isset($request->all()['action'])){ 
            session(['order_id'=>$request->all()['order_id']]);
            return "stripe";
        }else{
            $order_id = session('order_id');
            $o = Order::where('id',$order_id)->first();
            $office_supply_cart = session('office_supply_cart');
            $cart_count = 0;
        
            foreach($office_supply_cart as $id=>$supply){
                
                $cart_count = $cart_count + $supply;
                
            }
            \Stripe\Stripe::setApiKey('sk_test_WRB0vbQpeyp1NWIQChFZLu4200dEMFMOuW');
            header('Content-Type: application/json');

            $price = \Stripe\Price::create([
                'unit_amount'=> $o->total_amount*100,
                'currency'=> 'usd',
                'product_data' => ['name'=>$o->order_no]
            ]);
            
            $line_items = [[
                'price' => $price->id,
                'quantity' => 1
            ]];
            $checkout_session = \Stripe\Checkout\Session::create([
                'payment_method_types' => ['card'],
                'mode' => 'payment',
                'allow_promotion_codes' => true,
                'line_items' => $line_items,
                'success_url' => route('wave.paymentStatus') . '?stripe_session_id={CHECKOUT_SESSION_ID}',
                'cancel_url' => route('wave.officeSupplyCheckout'),

            ]);
            return json_encode(['id' => $checkout_session->id]);
        }
    }
}
