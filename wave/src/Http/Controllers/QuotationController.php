<?php

namespace Wave\Http\Controllers;

use Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Validator;
use Wave\User;
use Wave\KeyValue;
use Wave\ApiKey;
use Wave\Quotation;
use Wave\Countries;
use Wave\Addresses;
use TCG\Voyager\Http\Controllers\Controller;
use Illuminate\Support\Facades\Http;
use UpsRate;
use Wave\Consignee;

class QuotationController extends Controller
{
	private $dhl_sandbox_url;
	private $dhl_live_url;
	private $dhl_api_key;
	private $dhl_api_secret;
	private $dhl_sandbox_enable;
	private $dhl_api_url;
	
    private $ups_sandbox_url;
	private $ups_live_url;
	private $ups_access_key;
	private $ups_user_id;
	private $ups_password;
	private $ups_sandbox_enable;
	private $ups_api_url;
	private $ups_shipper_number;
    
    private $fedex_sandbox_url;
    private $fedex_live_url;
    private $fedex_sandbox_client_id;
    private $fedex_sandbox_client_secret;
    private $fedex_live_client_id;
    private $fedex_live_client_secret;
    private $fedex_sandbox_accountNumber;
    private $fedex_live_accountNumber;
    private $fedex_accountNumber;
    private $fedex_sandbox_enable;
    private $fedex_api_url;
    private $fedex_client_id;
    private $fedex_client_secret;
    
    private $usps_api_url;
    private $usps_test_api_url;
    private $usps_username;
    private $usps_sandbox_enable;

	public function __construct() {
	    $this->dhl_sandbox_enable 	= config('carriers.dhl_sandbox_enable');
		$this->dhl_sandbox_url 		= config('carriers.dhl_sandbox_url');
		$this->dhl_live_url			= config('carriers.dhl_live_url');
		$this->dhl_api_key			= config('carriers.dhl_api_key');
		$this->dhl_api_secret		= config('carriers.dhl_api_secret');
		
        $this->ups_sandbox_enable	= config('carriers.ups_sandbox_enable');
		$this->ups_sandbox_url		= config('carriers.ups_sandbox_url');
		$this->ups_live_url			= config('carriers.ups_live_url');
		$this->ups_access_key		= config('carriers.ups_access_key');
		$this->ups_user_id			= config('carriers.ups_user_id');
		$this->ups_password			= config('carriers.ups_password');
		$this->ups_password			= config('carriers.ups_password');
		$this->ups_shipper_number	= config('carriers.ups_shipper_number');

        $this->fedex_sandbox_url            = config('carriers.fedex_sandbox_url');
        $this->fedex_live_url               = config('carriers.fedex_live_url');
        $this->fedex_sandbox_client_id      = config('carriers.fedex_sandbox_client_id');
        $this->fedex_sandbox_client_secret  = config('carriers.fedex_sandbox_client_secret');
        $this->fedex_live_client_id         = config('carriers.fedex_live_client_id');
        $this->fedex_live_client_secret     = config('carriers.fedex_live_client_secret');
        $this->fedex_sandbox_accountNumber  = config('carriers.fedex_sandbox_accountNumber');
        $this->fedex_live_accountNumber     = config('carriers.fedex_live_accountNumber');
        $this->fedex_sandbox_enable         = config('carriers.fedex_sandbox_enable');

        $this->usps_api_url             = config('carriers.usps_api_url');
        $this->usps_test_api_url        = config('carriers.usps_test_api_url');
        $this->usps_username            = config('carriers.usps_username');
        $this->usps_sandbox_enable      = config('carriers.usps_sandbox_enable');

		if ($this->dhl_sandbox_enable) {
			$this->dhl_api_url = config('carriers.dhl_sandbox_url');			
		}else{
			$this->dhl_api_url = config('carriers.dhl_live_url');
		}

		if ($this->ups_sandbox_enable) {
			$this->ups_api_url = config('carriers.ups_sandbox_url');
		}else{
			$this->ups_api_url = config('carriers.ups_live_url');
		}

        if ($this->fedex_sandbox_enable) {
            $this->fedex_api_url        = config('carriers.fedex_sandbox_url');
            $this->fedex_client_id      = config('carriers.fedex_sandbox_client_id');
            $this->fedex_client_secret  = config('carriers.fedex_sandbox_client_secret');
            $this->fedex_accountNumber  = config('carriers.fedex_sandbox_accountNumber');
        }else{
            $this->fedex_api_url        = config('carriers.fedex_live_url');
            $this->fedex_client_id      = config('carriers.fedex_live_client_id');
            $this->fedex_client_secret  = config('carriers.fedex_live_client_secret');
            $this->fedex_accountNumber  = config('carriers.fedex_live_accountNumber');
        }

	}

    public function index($quote_id=null){
        $quote_form_data = \Session::get('quote_form');
        $old_quotation = Quotation::select('request')->where('id', $quote_id)->first();
        $quote_request = array();
        //dd($request);
        if ($old_quotation) {
            $quote_data = json_decode($old_quotation->request, true);
            if ($quote_data) {
                $quote_request['from_country_name'] = $quote_data['customerDetails']['shipperDetails']['countyName']; 
                $quote_request['from_country']      = $quote_data['customerDetails']['shipperDetails']['countryCode'];
                $quote_request['from_address']      = $quote_data['customerDetails']['shipperDetails']['addressLine1']." ".$quote_data['customerDetails']['shipperDetails']['addressLine2'];
                $quote_request['from_zip']          = $quote_data['customerDetails']['shipperDetails']['postalCode'];
                $quote_request['from_city']         = $quote_data['customerDetails']['shipperDetails']['cityName'];
                $quote_request['from_state']        = $quote_data['customerDetails']['shipperDetails']['addressLine3'];
                $quote_request['to_country_name']   = $quote_data['customerDetails']['receiverDetails']['countyName'];
                $quote_request['to_country']        = $quote_data['customerDetails']['receiverDetails']['countryCode'];
                $quote_request['to_address']        = $quote_data['customerDetails']['receiverDetails']['addressLine1']." ".$quote_data['customerDetails']['receiverDetails']['addressLine2'];
                $quote_request['to_zip']            = $quote_data['customerDetails']['receiverDetails']['postalCode'];
                $quote_request['to_city']           = $quote_data['customerDetails']['receiverDetails']['cityName'];
                $quote_request['to_state']          = $quote_data['customerDetails']['receiverDetails']['addressLine3'];

                $documents = false;
                
                $packages = array();
                foreach ($quote_data['packages'] as $package_count => $package_data) {
                    $packages[$package_count]['weight'] = $package_data['weight'];
                    $packages[$package_count]['length'] = $package_data['dimensions']['length'];
                    $packages[$package_count]['width']  = $package_data['dimensions']['width'];
                    $packages[$package_count]['height'] = $package_data['dimensions']['height'];
                    if ($package_data['typeCode'] == '2BP') {
                        $documents = true;        
                    }
                }
                $quote_request['package_count']          = count($quote_data['packages']);
                if ($documents) {
                    $quote_request['flat_rate']     = 'on';
                    $quote_request['shipment_type'] = 'contains_document';
                }
                $quote_request['packages']     = $packages;
                $quote_request['total_value']  = $quote_data['monetaryAmount'][0]['value'];
            }
        }elseif($quote_form_data){
            foreach($quote_form_data['dimensions'] as $key => $value){
                $quote_form_data['dimensions'][$key] = $value['0'];
              }
              $quote_form_data['from_country'] = 'US';
              $quote_request['to_country'] = $quote_form_data['to_country'];
              $quote_request = $quote_form_data;
              $quote_request['packages']['0'] = $quote_form_data['dimensions'];
              \Session::forget('quote_form');
        }else{
            
        }
        $consignees = array();
        if (auth()->id() != '') {
            $current_user_id = auth()->id(); 
            $consignees = Consignee::orderBy('consignee_name', 'ASC')->where('user_id', $current_user_id)->get();
        }
        $countries = Countries::orderBy('country_name', 'ASC')->get();
        return view('theme::quotations.index', compact('countries', 'quote_request', 'consignees'));
    }

    public function getQuotation(Request $request)
    {
        $response['status'] = 'success';

        $DHLrates 	= $this->getDHLrates($request);
        $UPSrates 	= $this->getUPSrates_2($request);
		$FedExrates = $this->getFedExrates($request);
        $UspsRates = $this->getUspsRates($request);
        if ($request->from_country != $request->to_country || ($request->from_state == "FL" && $request->to_state == "FL")) {
            $ZionRates = $this->getZionRates($request);
        }
		
        $home_del_selected = '';
        $pickup_selected = '';
        if($request->delivery_location == "Home Delivery"){
            $home_del_selected = 'selected';
        }
        if($request->delivery_location == "Pickup in Zion Office"){
            $pickup_selected = 'selected';
        }
        $quotations = '<div class="col-lg-12"><div class="form-card"><div class="form-head"><h3>DELIVERY OPTIONS & COUPON</h3></div><div class="form-body"><div class="row"><div class="col-lg-6"><div class="form-group"><label class="form-label">Delivery location<span class="required">*</span></label><select class="form-control form-select delivery_location" required title="Please fill out this field" data-dashlane-rid="75f5f5b7a59a2f59" data-form-type="other" name="delivery_location" onchange="selDelLoc(this);"><option disabled="disabled" selected="selected">--Select Delivery Location--</option><option value="Pickup in Zion Office" '.$pickup_selected.'>Pickup in Office</option><option value="Home Delivery" '.$home_del_selected.'>Home Delivery</option></select></div></div><div class="col-lg-6"><div class="form-group"><label class="form-label">Coupon/Promo Code<span class="required"></span></label><div class="track-input" data-dashlane-rid="456648454bd978f3" data-form-type="other"><input type="text" class="form-control" data-dashlane-rid="9003aae30a21933a" data-form-type="other" name="promo"><button type="submit" class="track-btn btn" data-dashlane-rid="ed07a78e3f185e5a" data-form-type="other" data-dashlane-label="true">Apply</button></div></div></div></div></div></div></div><div class="col-lg-12"><div class="form-card"><div class="form-head"><h3>QUOTE INFORMATION</h3></div><div class="form-body"><div class="delivery-info-block">';

        if ($request->from_country != $request->to_country || ($request->from_state == "FL" && $request->to_state == "FL")) {
            $quotations .= $ZionRates['html'];
        }
        if ($request->from_country != $request->to_country) {
        	$quotations .= $DHLrates['html'];
        }
        $quotations .= $UPSrates['html'];
        $quotations .= $FedExrates['html'];
        $quotations .= $UspsRates['html'];
        $quotations .= '</div></div></div></div>';

        $response['html'] = $quotations;
        return response()->json($response);
    }

    public function setQuotation(Request $request)
    {
        $response['status'] = 'success';
        \Session::forget('quote_form');
        \Session::put('quote_form', $request->all());
        return response()->json($response);
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

    public function ordinal($number) {
        $ends = array('th','st','nd','rd','th','th','th','th','th','th');
        if ((($number % 100) >= 11) && (($number%100) <= 13))
            return $number. 'th';
        else
            return $number. $ends[$number % 10];
    }

    public function getZionRates($request){
        $quote_date                 =   date("F j, Y, g:i a");
        $quote_date_f               =   date("Y-m-d");
        $shipper_address_country    =   $request->from_country_name;

        $consignee_address_country  =   $request->to_country;
        $consignee_address_city     =   $request->to_city;

        $package_value              =   $request->total_value;
        $holidayDates               =   array();

        $quotations = '';

        $shipment_prices    =   array();

        $flat_rate_list = '';

        // $pickup                     =   $request->pickup;
        // $delivery_option            =   $request->delivery_option;
        $delivery_location          =   $request->delivery_location;

        // $promocode                  =   $_POST['users'];

        $numb_of_packages=(int) $request->package_count;

        $common_data = $this->common_data($request);

        $dimensions         = $common_data['dimensions'];

        // recall insurance fee
        $shipment_add_insurance = 0;
        $home_delivery_fee_total = 0;

        for($i=0;$i<$numb_of_packages;$i++){
            $package_weight=$dimensions['weight'][$i];
            $package_weight=str_replace(',','.',$package_weight);
            $package_weight = ceil($package_weight);

            $package_length=(int) $dimensions['length'][$i];
            $package_width=(int) $dimensions['width'][$i];
            $package_height=(int) $dimensions['height'][$i];

            $package_volume= $package_length * $package_width * $package_height/120;
            $package_volume = ceil($package_volume);

            $count5WD = 0;


            $today_day = date('D');
            
            // Initiate the $today_for_delivery variable
            $today_for_delivery = date("F j, Y");
            
            // Push the delivery date for shipment that created on Hollydays.
            if ((in_array($quote_date_f, $holidayDates)) && (!in_array($today_day, array('Sat','Sun')))){
                $today_for_delivery = date("F j, Y", strtotime("+1 day"));
                $today_day = date('D',strtotime("+1 day"));
            //$today_for_delivery = date("F j, Y", strtotime("+1 day $hours_fixed_digit hour -00 minutes" , strtotime($today_for_delivery)));
            }


            // Push the delivery date for shipment that created on Saturday or Sunday.
            if ($today_day == "Sat"){
                $today_for_delivery = date("F j, Y", strtotime("+2 day +0 hour -00 minutes" , strtotime($today_for_delivery)));
            }
            else if ($today_day == "Sun"){
                $today_for_delivery = date("F j, Y", strtotime("+1 day +0 hour -00 minutes" , strtotime($today_for_delivery)));
            }
            else{
                $today_for_delivery = date("F j, Y", strtotime("+0 day +0 hour -00 minutes" , strtotime($today_for_delivery))); 
            }
                

            $temp = strtotime("$today_for_delivery");

            // CALCULATE CUTTING TIME
            $after_cutting_time= 0;
            $cutting_time_hour = date("Hi", strtotime($quote_date));
            if (($cutting_time_hour > "1500") && (in_array($today_day, array('Mon','Tue','Wed','Thu','Fri'))) && (!in_array($quote_date_f, $holidayDates))) {
                $after_cutting_time = 1;
            }

            // Delivery date for DHL
            $expected_delivery_DHLDays = 2 + $after_cutting_time;
            while($count5WD<$expected_delivery_DHLDays){
                $next1WD = strtotime('+1 weekday', $temp);
                $next1WDDate = date('Y-m-d', $next1WD);
                if(!in_array($next1WDDate, $holidayDates)){
                    $count5WD++;
                }
                $temp = $next1WD;
            }

            $next5WD = date("Y-m-d", $temp);

            $expected_delivery_show_DHLDays = date("F j, Y", strtotime($next5WD));


            // Delivery date for 1 Days Standard
            $expected_delivery_1Days = 2 + $after_cutting_time;
            while($count5WD<$expected_delivery_1Days){
                $next1WD = strtotime('+1 weekday', $temp);
                $next1WDDate = date('Y-m-d', $next1WD);
                if(!in_array($next1WDDate, $holidayDates)){
                    $count5WD++;
                }
                $temp = $next1WD;
            }

            $next5WD = date("Y-m-d", $temp);

            $expected_delivery_show_1Days = date("F j, Y", strtotime($next5WD));

            // Delivery date for 2 Days Standard
            $expected_delivery_2Days = 3 + $after_cutting_time;
            while($count5WD<$expected_delivery_2Days){
                $next1WD = strtotime('+1 weekday', $temp);
                $next1WDDate = date('Y-m-d', $next1WD);
                if(!in_array($next1WDDate, $holidayDates)){
                    $count5WD++;
                }
                $temp = $next1WD;
            }

            $next5WD = date("Y-m-d", $temp);

            $expected_delivery_show_2Days = date("F j, Y", strtotime($next5WD));

            // Delivery date for 3 Days Express
            $expected_delivery_3Days = 5 + $after_cutting_time;
            while($count5WD<$expected_delivery_3Days){
                $next1WD = strtotime('+1 weekday', $temp);
                $next1WDDate = date('Y-m-d', $next1WD);
                if(!in_array($next1WDDate, $holidayDates)){
                    $count5WD++;
                }
                $temp = $next1WD;
            }

            $next5WD = date("Y-m-d", $temp);

            $expected_delivery_show_3Days = date("F j, Y", strtotime($next5WD));


            // Delivery date for 6 Days Express
            $expected_delivery_6Days = 8 + $after_cutting_time;
            while($count5WD<$expected_delivery_6Days){
                $next1WD = strtotime('+1 weekday', $temp);
                $next1WDDate = date('Y-m-d', $next1WD);
                if(!in_array($next1WDDate, $holidayDates)){
                    $count5WD++;
                }
                $temp = $next1WD;
            }

            $next5WD = date("Y-m-d", $temp);

            $expected_delivery_show_6Days = date("F j, Y", strtotime($next5WD));

            // Delivery date for 10 Days Express
            $expected_delivery_10Days = 10;
            while($count5WD<$expected_delivery_10Days){
                $next1WD = strtotime('+1 weekday', $temp);
                $next1WDDate = date('Y-m-d', $next1WD);
                if(!in_array($next1WDDate, $holidayDates)){
                    $count5WD++;
                }
                $temp = $next1WD;
            }

            $next5WD = date("Y-m-d", $temp);

            $expected_delivery_show_10Days = date("F j, Y", strtotime($next5WD));

            // Delivery date for 12 Days Express
            $expected_delivery_12Days = 12;
            while($count5WD<$expected_delivery_12Days){
                $next1WD = strtotime('+1 weekday', $temp);
                $next1WDDate = date('Y-m-d', $next1WD);
                if(!in_array($next1WDDate, $holidayDates)){
                    $count5WD++;
                }
                $temp = $next1WD;
            }

            $next5WD = date("Y-m-d", $temp);

            $expected_delivery_show_12Days = date("F j, Y", strtotime($next5WD));

            // Delivery date for 15 Days Express
            $expected_delivery_15Days = 15;
            while($count5WD<$expected_delivery_15Days){
                $next1WD = strtotime('+1 weekday', $temp);
                $next1WDDate = date('Y-m-d', $next1WD);
                if(!in_array($next1WDDate, $holidayDates)){
                    $count5WD++;
                }
                $temp = $next1WD;
            }

            $next5WD = date("Y-m-d", $temp);

            $expected_delivery_show_15Days = date("F j, Y", strtotime($next5WD));


            // Delivery date for 20 Days Express
            $expected_delivery_20Days = 20;
            while($count5WD<$expected_delivery_20Days){
                $next1WD = strtotime('+1 weekday', $temp);
                $next1WDDate = date('Y-m-d', $next1WD);
                if(!in_array($next1WDDate, $holidayDates)){
                    $count5WD++;
                }
                $temp = $next1WD;
            }

            $next5WD = date("Y-m-d", $temp);

            $expected_delivery_show_20Days = date("F j, Y", strtotime($next5WD));

            // Delivery date for 25 Days Express
            $expected_delivery_25Days = 25;
            while($count5WD<$expected_delivery_25Days){
                $next1WD = strtotime('+1 weekday', $temp);
                $next1WDDate = date('Y-m-d', $next1WD);
                if(!in_array($next1WDDate, $holidayDates)){
                    $count5WD++;
                }
                $temp = $next1WD;
            }

            $next5WD = date("Y-m-d", $temp);

            $expected_delivery_show_25Days = date("F j, Y", strtotime($next5WD));

            // Delivery date for 30 Days Express
            $expected_delivery_30Days = 30;
            while($count5WD<$expected_delivery_30Days){
                $next1WD = strtotime('+1 weekday', $temp);
                $next1WDDate = date('Y-m-d', $next1WD);
                if(!in_array($next1WDDate, $holidayDates)){
                    $count5WD++;
                }
                $temp = $next1WD;
            }

            $next5WD = date("Y-m-d", $temp);

            $expected_delivery_show_30Days = date("F j, Y", strtotime($next5WD));

            // GET THE CHARGEABLE WEIGHT
            if ($package_weight >= $package_volume){
                $chargeable_weight = $package_weight;
            }else {
                $chargeable_weight = $package_volume;
            }

            // GET THE CUBIC FEET
            $cubic_feet = $package_volume / 14.40;
            $cubic_feet_rounded = (ceil($cubic_feet));


            // Get the quote orm the three dierent delivery Options
            $delivery_option_15Days = "Regular Boat";
            $delivery_option_10Days = "Express Container";
            $delivery_option_5Days = "Economical Air";
            $delivery_option_3Days = "Standard Air";
            $delivery_option_2Days = "Express Air";
            $delivery_option_1Days = "Super Express";

            switch ($consignee_address_country) {
                // PRICING FOR HAITI
                case ("HT"):
        
                //if statement colis livraison en 20 a 30 jours son pois est superrieur a sa volume			
                if ($cubic_feet_rounded <= 2.0){
                    $shipment_price_15Days = 30.0;
                    $shipment_price_10Days = 40.0;
                }elseif (($cubic_feet_rounded > 2.0)&& ($cubic_feet_rounded  <= 5.0)) {
                        $shipment_price_15Days = $cubic_feet_rounded * 12.0;
                        $shipment_price_10Days = $cubic_feet_rounded * 15.0;
                } elseif (($cubic_feet_rounded > 5.0)&& ($cubic_feet_rounded  <= 10.0)) {
                    $shipment_price_15Days = $cubic_feet_rounded * 11.50;
                    $shipment_price_10Days = $cubic_feet_rounded * 14.0;
                } elseif (($cubic_feet_rounded > 10.0)&& ($cubic_feet_rounded  <= 15.0)) {
                    $shipment_price_15Days = $cubic_feet_rounded * 10.0;
                    $shipment_price_10Days = $cubic_feet_rounded * 13.0;
                } elseif (($cubic_feet_rounded <= 500.0)&& ($cubic_feet_rounded  > 15.0)) {
                    $shipment_price_15Days = $cubic_feet_rounded * 9.0;
                    $shipment_price_10Days = $cubic_feet_rounded * 12.0;
                } elseif ($cubic_feet_rounded > 5000.0) {
                    $shipment_price_15Days = $cubic_feet_rounded * 0.0;
                    $shipment_price_10Days = $cubic_feet_rounded * 0.0;
                }
            
                //*****************************************************************************************************************					
                // Calculation for 5 to 10 Days Option				 
                //if statement colis livraison en 5 a 10 jours son pois est superrieur a sa volume		
                if ($chargeable_weight * 3.5 <= 20.0){
                    $shipment_price_5Days = 20.0;
                        }
                elseif (($chargeable_weight <= 5000.0) && ($chargeable_weight * 3.5 > 20.0)) {
                    $shipment_price_5Days = $chargeable_weight * 3.5;
                }			
                elseif ($chargeable_weight >= 5000.0) {
                    $shipment_price_5Days = $chargeable_weight * 0.0;
                }																   
                elseif (($package_volume >= 10000.0) || ($package_weight >= 5000.0)) {
                    $shipment_price_5Days = $package_volume * 0.0;
                }
                            
                //*****************************************************************************************************************					
                // Calculation for 3 to 5 Days Option						
                
                //if statement colis livraison en 3 a 5 jours son pois et  son volume est inferieur a 15
                if ($chargeable_weight  <= 10.0){
                    $shipment_price_3Days = 45.0;
                }elseif (($chargeable_weight <= 5000.0) && ($chargeable_weight > 10.0)) {
                    $shipment_price_3Days = $chargeable_weight * 4.5;
                }elseif ($chargeable_weight >= 5000.0) {
                    $shipment_price_3Days = $chargeable_weight * 0.0;
                }																              
                // raise up 3 to 5 Days shipment that is processed on selected Days
                if ((($consignee_address_country == "HAITI") && ($today_day == "Fri")) || (($consignee_address_country == "HAITI") && ($today_day == "Thu")) || (($consignee_address_country == "HAITI") && ($today_day == "Wed"))){
        
                    //$shipment_price_format_3Days = $shipment_price_3Days * 1.5;
                }
                            
                            
                            
                //*****************************************************************************************************************					
                // Calculation for 2 a 3 Days Option
                //*****************************************************************************************************************					
                
                //if statement colis livraison en 2 a 3 jours minimum rate
                if ($chargeable_weight  <= 2.0){
                        $shipment_price_2Days = 65.0;
                } elseif (($chargeable_weight > 2.0)&& ($chargeable_weight <= 5.0)) {
                    $shipment_price_2Days = $chargeable_weight * 25.0;
                }elseif (($chargeable_weight > 5.0) && ($chargeable_weight <= 10.0)) {
                    $shipment_price_2Days = $chargeable_weight * 18.0;
                }elseif (($chargeable_weight > 10.0) && ($chargeable_weight <= 15.0)) {
                    $shipment_price_2Days = $chargeable_weight * 14.0;
                }elseif (($chargeable_weight > 15.0) && ($chargeable_weight <= 25.0)) {
                    $shipment_price_2Days = $chargeable_weight * 12.0;
                }elseif (($chargeable_weight > 25.0) && ($chargeable_weight <= 30.0)) {
                    $shipment_price_2Days = $chargeable_weight * 10.0;
                }elseif (($chargeable_weight > 30.0) && ($chargeable_weight <= 40.0)) {
                    $shipment_price_2Days = $chargeable_weight * 9.0;
                }elseif (($chargeable_weight > 40.0) && ($chargeable_weight <= 5000.0)) {
                    $shipment_price_2Days = $chargeable_weight * 8.0;
                }elseif (($package_weight >= 5000.0) && ($package_weight >= $package_volume)) {
                    $shipment_price_2Days = $package_weight * 0.0;
                }elseif (($package_volume >= 10000.0)  && ($package_weight <= $package_volume)) {
                    $shipment_price_2Days = $package_volume * 0.0;
                }
                            
        
                //*****************************************************************************************************************					
                // Calculation for 1 a 2 Days Option
                //*****************************************************************************************************************	
                $shipment_price_1Days = $shipment_price_2Days * 1.45;
        
        
                            
                                                
                // CUT ALL THE HAITI PRICE FOR 20%
                $shipment_price_15Days = $shipment_price_15Days * 0.75;
                $shipment_price_10Days = $shipment_price_10Days * 1.00;
                $shipment_price_5Days = $shipment_price_5Days * 0.75;
                $shipment_price_3Days = $shipment_price_3Days * 1.00;
                $shipment_price_2Days = $shipment_price_2Days * 1.00;
                $shipment_price_1Days = $shipment_price_1Days * 1.00;

                if(isset($shipment_prices[$delivery_option_2Days]['price'])){
                    $shipment_prices[$delivery_option_1Days]['price'] = $shipment_prices[$delivery_option_1Days]['price'] + $shipment_price_1Days;
                    $shipment_prices[$delivery_option_1Days]['commitment'] = $expected_delivery_show_1Days;

                    $shipment_prices[$delivery_option_2Days]['price'] = $shipment_prices[$delivery_option_2Days]['price'] + $shipment_price_2Days;
                    $shipment_prices[$delivery_option_2Days]['commitment'] = $expected_delivery_show_2Days;
                
                    $shipment_prices[$delivery_option_3Days]['price'] = $shipment_prices[$delivery_option_3Days]['price'] + $shipment_price_3Days;
                    $shipment_prices[$delivery_option_3Days]['commitment'] = $expected_delivery_show_3Days;

                    $shipment_prices[$delivery_option_5Days]['price'] = $shipment_prices[$delivery_option_5Days]['price'] + $shipment_price_5Days;
                    $shipment_prices[$delivery_option_5Days]['commitment'] = $expected_delivery_show_10Days;

                    $shipment_prices[$delivery_option_10Days]['price'] = $shipment_prices[$delivery_option_10Days]['price'] + $shipment_price_10Days;
                    $shipment_prices[$delivery_option_10Days]['commitment'] = $expected_delivery_show_12Days;

                    $shipment_prices[$delivery_option_15Days]['price'] = $shipment_prices[$delivery_option_15Days]['price'] + $shipment_price_15Days;
                    $shipment_prices[$delivery_option_15Days]['commitment'] = $expected_delivery_show_15Days;
                }else{
                    $shipment_prices[$delivery_option_1Days]['price'] = $shipment_price_1Days;
                    $shipment_prices[$delivery_option_1Days]['commitment'] = $expected_delivery_show_1Days;

                    $shipment_prices[$delivery_option_2Days]['price'] = $shipment_price_2Days;
                    $shipment_prices[$delivery_option_2Days]['commitment'] = $expected_delivery_show_2Days;
                
                    $shipment_prices[$delivery_option_3Days]['price'] = $shipment_price_3Days;
                    $shipment_prices[$delivery_option_3Days]['commitment'] = $expected_delivery_show_3Days;

                    $shipment_prices[$delivery_option_5Days]['price'] = $shipment_price_5Days;
                    $shipment_prices[$delivery_option_5Days]['commitment'] = $expected_delivery_show_10Days;

                    $shipment_prices[$delivery_option_10Days]['price'] = $shipment_price_10Days;
                    $shipment_prices[$delivery_option_10Days]['commitment'] = $expected_delivery_show_12Days;

                    $shipment_prices[$delivery_option_15Days]['price'] = $shipment_price_15Days;
                    $shipment_prices[$delivery_option_15Days]['commitment'] = $expected_delivery_show_15Days;
                }

                break;

                // DOMINICAN REPUBLIC FOR THIRD PARTY
                case ("DO"):
                    // HUB 20 PROGRAM PRICE				 
                    if (($package_volume == 0.0) && ($flat_rate_list == "FLAT BOX 14X14X14")){
                        $shipment_price_flat = $flat_item_quantity * 80.0;
                    } elseif (($package_volume == 0.0) && ($flat_rate_list == "FLAT BOX 18X18X16")){
                        $shipment_price_flat = $flat_item_quantity * 90.0;
                    } elseif (($package_volume == 0.0) && ($flat_rate_list == "FLAT BOX 18X18X24")){
                        $shipment_price_flat = $flat_item_quantity * 100.0;
                    } elseif (($package_volume == 0.0) && ($flat_rate_list == "FLAT BOX 18X24X24")){
                        $shipment_price_flat = $flat_item_quantity * 140.0;
                    } elseif (($package_volume == 0.0) && ($flat_rate_list == "BARREL 55 GAL (DRUM)")){
                        $shipment_price_flat = $flat_item_quantity * 180.0;
                    } elseif (($package_volume == 0.0) && ($flat_rate_list == "LUGGAGE (CHECKED BAG)")){
                        $shipment_price_flat = $flat_item_quantity * 90.0;				 
                    }

                    
                    // REGULAR BOX PRICE
                    if ($cubic_feet_rounded < 5.0){ 
                        $shipment_price_reg_boat = 90.0;
                    } else {
                        $shipment_price_reg_boat = $cubic_feet_rounded * 20.0;
                    }
                    $expected_delivery_show_reg_boat = $expected_delivery_show_20Days;
                        
                        
                    // REGULAR BOX PRICE FOR AIR
                    if (($chargeable_weight >= 0.0) && ($chargeable_weight <= 5.0)){
                        $shipment_price_economical_air = 39.60;
                    } elseif (($chargeable_weight > 5.0) && ($chargeable_weight <= 10.0)){
                        $shipment_price_economical_air = 67.0;
                    } else {
                        if (($chargeable_weight > 10.0) && ($chargeable_weight <= 15.0)){
                            $shipment_price_economical_air = 90.0;
                        } elseif (($chargeable_weight > 15.0) && ($chargeable_weight <= 20.0)){
                            $shipment_price_economical_air = 117.0;
                        } elseif (($chargeable_weight > 20.0) && ($chargeable_weight <= 25.0)){
                            $shipment_price_economical_air = 141.0;
                        }elseif (($chargeable_weight > 25.0) && ($chargeable_weight <= 30.0)){
                            $shipment_price_economical_air = 166.0;
                        }elseif (($chargeable_weight > 30.0) && ($chargeable_weight <= 35.0)){
                            $shipment_price_economical_air = 161.0;
                        }elseif (($chargeable_weight > 35.0) && ($chargeable_weight <= 40.0)){
                            $shipment_price_economical_air = 180.0;
                        }elseif (($chargeable_weight > 40.0) && ($chargeable_weight <= 45.0)){
                            $shipment_price_economical_air = 200.0;
                        }elseif (($chargeable_weight > 45.0) && ($chargeable_weight <= 50.0)){
                            $shipment_price_economical_air = 222.0;
                        }elseif (($chargeable_weight > 50.0) && ($chargeable_weight <= 55.0)){
                            $shipment_price_economical_air = 242.0;
                        }elseif (($chargeable_weight > 55.0) && ($chargeable_weight <= 60.0)){
                            $shipment_price_economical_air = 261.0;
                        }elseif (($chargeable_weight >60.0) && ($chargeable_weight <= 65.0)){
                            $shipment_price_economical_air = 281.0;
                        }elseif (($chargeable_weight > 65.0) && ($chargeable_weight <= 70.0)){
                            $shipment_price_economical_air = 303.0;
                        }elseif (($chargeable_weight > 70.0) && ($chargeable_weight <= 75.0)){
                            $shipment_price_economical_air = 288.0;
                        }elseif (($chargeable_weight > 75.0) && ($chargeable_weight <= 80.0)){
                            $shipment_price_economical_air = 306.0;
                        }elseif (($chargeable_weight > 80.0) && ($chargeable_weight <= 85.0)){
                            $shipment_price_economical_air = 324.0;
                        }elseif (($chargeable_weight > 85.0) && ($chargeable_weight <= 90.0)){
                            $shipment_price_economical_air = 342.0;
                        }elseif (($chargeable_weight > 90.0) && ($chargeable_weight <= 95.0)){
                            $shipment_price_economical_air = 360.0;
                        }elseif (($chargeable_weight > 95.0) && ($chargeable_weight <= 100.0)){
                            $shipment_price_economical_air = 378.0;
                        }else{
                            $shipment_price_economical_air = $chargeable_weight * 3.50;
                        }
                    }
                    $expected_delivery_show_economical_air = $expected_delivery_show_6Days;

                    if(isset($shipment_prices[$delivery_option_5Days]['price'])){
                        $shipment_prices[$delivery_option_5Days]['price'] = $shipment_prices[$delivery_option_5Days]['price'] + $shipment_price_economical_air;
                        $shipment_prices[$delivery_option_5Days]['commitment'] = $expected_delivery_show_economical_air;
    
                        $shipment_prices[$delivery_option_15Days]['price'] = $shipment_prices[$delivery_option_15Days]['price'] + $shipment_price_reg_boat;
                        $shipment_prices[$delivery_option_15Days]['commitment'] = $expected_delivery_show_reg_boat;
                    }else{
                        $shipment_prices[$delivery_option_5Days]['price'] = $shipment_price_economical_air;
                        $shipment_prices[$delivery_option_5Days]['commitment'] = $expected_delivery_show_economical_air;

                        $shipment_prices[$delivery_option_15Days]['price'] = $shipment_price_reg_boat;
                        $shipment_prices[$delivery_option_15Days]['commitment'] = $expected_delivery_show_reg_boat;
                    }
                    break;


                    // GUATEMALA FOR THIRD PARTY
                    case ("GT"):

                        if (($package_volume == 0.0) && ($flat_rate_list == "FLAT BOX 18X18X24")){
                            $shipment_price_flat = $flat_item_quantity * 180.0;
                        } elseif (($package_volume == 0.0) && ($flat_rate_list == "FLAT BOX 18X24X24")){
                            $shipment_price_flat = $flat_item_quantity * 210.0;
                        }
                        
                        if ($cubic_feet_rounded < 3.0){
                            $shipment_price_reg_boat = 160.0;
                        } else {
                            $shipment_price_reg_boat = $cubic_feet_rounded * 45.0;
                        }
                        $expected_delivery_show_reg_boat = $expected_delivery_show_20Days;
                                        
                        // REGULAR BOX PRICE FOR AIR
                        if (($chargeable_weight >= 0.0) && ($chargeable_weight <= 5.0)){
                            $shipment_price_economical_air = 57.00;
                        } elseif (($chargeable_weight > 5.0) && ($chargeable_weight <= 10.0)){
                                $shipment_price_economical_air = 90.0;
                        } else {
                            if (($chargeable_weight > 10.0) && ($chargeable_weight <= 15.0)){
                                $shipment_price_economical_air = 122.0;
                            } elseif (($chargeable_weight > 15.0) && ($chargeable_weight <= 20.0)){
                                $shipment_price_economical_air = 136.0;
                            } elseif (($chargeable_weight > 20.0) && ($chargeable_weight <= 25.0)){
                                $shipment_price_economical_air = 171.0;
                            }elseif (($chargeable_weight > 25.0) && ($chargeable_weight <= 30.0)){
                                $shipment_price_economical_air = 173.0;
                            }elseif (($chargeable_weight > 30.0) && ($chargeable_weight <= 35.0)){
                                $shipment_price_economical_air = 190.0;
                            }elseif (($chargeable_weight > 35.0) && ($chargeable_weight <= 40.0)){
                                $shipment_price_economical_air = 217.0;
                            }elseif (($chargeable_weight > 40.0) && ($chargeable_weight <= 45.0)){
                                $shipment_price_economical_air = 244.0;
                            }elseif (($chargeable_weight > 45.0) && ($chargeable_weight <= 50.0)){
                                $shipment_price_economical_air = 271.0;
                            }elseif (($chargeable_weight > 50.0) && ($chargeable_weight <= 55.0)){
                                $shipment_price_economical_air = 298.0;
                            }
                            elseif (($chargeable_weight > 55.0) && ($chargeable_weight <= 60.0)){
                                $shipment_price_economical_air = 325.0;
                            }elseif (($chargeable_weight >60.0) && ($chargeable_weight <= 65.0)){
                                $shipment_price_economical_air = 352.0;
                            }elseif (($chargeable_weight > 65.0) && ($chargeable_weight <= 70.0)){
                                $shipment_price_economical_air = 379.0;
                            }elseif (($chargeable_weight > 70.0) && ($chargeable_weight <= 75.0)){
                                $shipment_price_economical_air = 406.0;
                            }elseif (($chargeable_weight > 75.0) && ($chargeable_weight <= 80.0)){
                                $shipment_price_economical_air = 433.0;
                            }elseif (($chargeable_weight > 80.0) && ($chargeable_weight <= 85.0)){
                                $shipment_price_economical_air = 460.0;
                            }elseif (($chargeable_weight > 85.0) && ($chargeable_weight <= 90.0)){
                                $shipment_price_economical_air = 487.0;
                            }elseif (($chargeable_weight > 90.0) && ($chargeable_weight <= 95.0)){
                                $shipment_price_economical_air = 514.0;
                            }elseif (($chargeable_weight > 95.0) && ($chargeable_weight <= 100.0)){
                                $shipment_price_economical_air = 541.0;
                            }else
                                $shipment_price_economical_air = $chargeable_weight * 5.35;
                            }
                            $expected_delivery_show_economical_air = $expected_delivery_show_6Days;				   				   
                            if(isset($shipment_prices[$delivery_option_5Days]['price'])){
                                $shipment_prices[$delivery_option_5Days]['price'] = $shipment_prices[$delivery_option_5Days]['price'] + $shipment_price_economical_air;
                                $shipment_prices[$delivery_option_5Days]['commitment'] = $expected_delivery_show_economical_air;
            
                                $shipment_prices[$delivery_option_15Days]['price'] = $shipment_prices[$delivery_option_15Days]['price'] + $shipment_price_reg_boat;
                                $shipment_prices[$delivery_option_15Days]['commitment'] = $expected_delivery_show_reg_boat;
                            }else{
                                $shipment_prices[$delivery_option_5Days]['price'] = $shipment_price_economical_air;
                                $shipment_prices[$delivery_option_5Days]['commitment'] = $expected_delivery_show_economical_air;
        
                                $shipment_prices[$delivery_option_15Days]['price'] = $shipment_price_reg_boat;
                                $shipment_prices[$delivery_option_15Days]['commitment'] = $expected_delivery_show_reg_boat;
                            }
                        break;	
                        
                        
                        // EL SALVADOR FOR THIRD PARTY
                        case ("SV"):
                        
                        
                            if (($package_volume == 0.0) && ($flat_rate_list == "FLAT BOX 18X18X24")){
                                $shipment_price_thirdParty = $flat_item_quantity * 220.0;
                            } elseif (($package_volume == 0.0) && ($flat_rate_list == "FLAT BOX 18X24X24")){
                                $shipment_price_thirdParty = $flat_item_quantity * 260.0;
                            } 
                            
                            if ($cubic_feet_rounded < 2.0){
                                $shipment_price_reg_boat = 140.0;
                            } else {
                                $shipment_price_reg_boat = $cubic_feet_rounded * 40.0;
                            }
                            $expected_delivery_show_reg_boat = $expected_delivery_show_30Days;
                            if(isset($shipment_prices[$delivery_option_5Days]['price'])){
                                $shipment_prices[$delivery_option_15Days]['price'] = $shipment_prices[$delivery_option_15Days]['price'] + $shipment_price_reg_boat;
                                $shipment_prices[$delivery_option_15Days]['commitment'] = $expected_delivery_show_reg_boat;
                            }else{
                                $shipment_prices[$delivery_option_15Days]['price'] = $shipment_price_reg_boat;
                                $shipment_prices[$delivery_option_15Days]['commitment'] = $expected_delivery_show_reg_boat;
                            }                                    
                        break;
                        
                        
                        // HONDURAS FOR THIRD PARTY
                        case ("HN"):
                        
                            // HUB 20 PROGRAM PRICE				 
                            if (($package_volume == 0.0) && ($flat_rate_list == "FLAT BOX 14X14X14")){
                                            $shipment_price_thirdParty = $flat_item_quantity * 80.0;
                            } elseif (($package_volume == 0.0) && ($flat_rate_list == "FLAT BOX 18X18X16")){
                                            $shipment_price_thirdParty = $flat_item_quantity * 90.0;
                            } elseif (($package_volume == 0.0) && ($flat_rate_list == "FLAT BOX 18X18X24")){
                                            $shipment_price_thirdParty = $flat_item_quantity * 100.0;
                            } elseif (($package_volume == 0.0) && ($flat_rate_list == "FLAT BOX 18X24X24")){
                                            $shipment_price_thirdParty = $flat_item_quantity * 130.0;
                            } 
                            
                            if ($cubic_feet_rounded < 3.0){
                                $shipment_price_reg_boat = 80.0;
                            } else {
                                $shipment_price_reg_boat = $cubic_feet_rounded * 20.0;
                            }
                            $expected_delivery_show_reg_boat = $expected_delivery_show_15Days;

                            // REGULAR BOX PRICE FOR AIR
                            if ($chargeable_weight < 10.0){
                                $shipment_price_economical_air = 60.0;
                            } else {
                                $shipment_price_economical_air = $chargeable_weight * 6.0;
                            }
                            $expected_delivery_show_economical_air = $expected_delivery_show_3Days;
                                        
                            if(isset($shipment_prices[$delivery_option_5Days]['price'])){
                                $shipment_prices[$delivery_option_5Days]['price'] = $shipment_prices[$delivery_option_5Days]['price'] + $shipment_price_economical_air;
                                $shipment_prices[$delivery_option_5Days]['commitment'] = $expected_delivery_show_economical_air;
            
                                $shipment_prices[$delivery_option_15Days]['price'] = $shipment_prices[$delivery_option_15Days]['price'] + $shipment_price_reg_boat;
                                $shipment_prices[$delivery_option_15Days]['commitment'] = $expected_delivery_show_reg_boat;
                            }else{
                                $shipment_prices[$delivery_option_5Days]['price'] = $shipment_price_economical_air;
                                $shipment_prices[$delivery_option_5Days]['commitment'] = $expected_delivery_show_economical_air;
        
                                $shipment_prices[$delivery_option_15Days]['price'] = $shipment_price_reg_boat;
                                $shipment_prices[$delivery_option_15Days]['commitment'] = $expected_delivery_show_reg_boat;
                            }
                        break;	
                        
                        
                        
                        // NICARAGUA FOR THIRD PARTY
                        case ("NI"):
                        
                            if (($package_volume == 0.0) && ($flat_rate_list == "FLAT BOX 18X18X24")){
                                $shipment_price_thirdParty = $flat_item_quantity * 220.0;
                            } elseif (($package_volume == 0.0) && ($flat_rate_list == "FLAT BOX 18X24X24")){
                                $shipment_price_thirdParty = $flat_item_quantity * 260.0;
                            }
                            
                            if ($cubic_feet_rounded < 3.0){
                                $shipment_price_reg_boat = 160.0;
                            } else {
                                $shipment_price_reg_boat = $cubic_feet_rounded * 45.0;
                            }
                            $expected_delivery_show_reg_boat = $expected_delivery_show_30Days;
                                        
                            // REGULAR PRICE FOR AIR
                            if ($chargeable_weight < 12.0){
                                $shipment_price_economical_air = 70.0;
                            } else {
                                $shipment_price_economical_air = $chargeable_weight * 7.0;
                            }
                            $expected_delivery_show_economical_air = $expected_delivery_show_10Days;
                            
                            if(isset($shipment_prices[$delivery_option_5Days]['price'])){
                                $shipment_prices[$delivery_option_5Days]['price'] = $shipment_prices[$delivery_option_5Days]['price'] + $shipment_price_economical_air;
                                $shipment_prices[$delivery_option_5Days]['commitment'] = $expected_delivery_show_economical_air;
            
                                $shipment_prices[$delivery_option_15Days]['price'] = $shipment_prices[$delivery_option_15Days]['price'] + $shipment_price_reg_boat;
                                $shipment_prices[$delivery_option_15Days]['commitment'] = $expected_delivery_show_reg_boat;
                            }else{
                                $shipment_prices[$delivery_option_5Days]['price'] = $shipment_price_economical_air;
                                $shipment_prices[$delivery_option_5Days]['commitment'] = $expected_delivery_show_economical_air;
        
                                $shipment_prices[$delivery_option_15Days]['price'] = $shipment_price_reg_boat;
                                $shipment_prices[$delivery_option_15Days]['commitment'] = $expected_delivery_show_reg_boat;
                            }
                        break;
                        
                        
                        // VENEZUELA FOR THIRD PARTY
                        case ("VE"):
                        
                            // HUB 20 PROGRAM PRICE				 
                            if (($package_volume == 0.0) && ($flat_rate_list == "FLAT BOX 14X14X14")){
                                            $shipment_price_thirdParty = $flat_item_quantity * 60.0;
                            } elseif (($package_volume == 0.0) && ($flat_rate_list == "FLAT BOX 18X18X16")){
                                            $shipment_price_thirdParty = $flat_item_quantity * 90.0;
                            } elseif (($package_volume == 0.0) && ($flat_rate_list == "FLAT BOX 18X18X24")){
                                            $shipment_price_thirdParty = $flat_item_quantity * 120.0;
                            } elseif (($package_volume == 0.0) && ($flat_rate_list == "FLAT BOX 18X24X24")){
                                            $shipment_price_thirdParty = $flat_item_quantity * 160.0;
                            }
                            
                            if ($cubic_feet_rounded < 3.0){
                                $shipment_price_reg_boat = 80.0;
                            } else {
                                $shipment_price_reg_boat = $cubic_feet_rounded * 30.0;
                            }
                            $expected_delivery_show_reg_boat = $expected_delivery_show_25Days;	
                                        
                        // REGULAR BOX PRICE FOR AIR 
                            if ($chargeable_weight < 15.0){
                                $shipment_price_economical_air = 90.0;
                            } else {
                                $shipment_price_economical_air = $chargeable_weight * 6.0;
                            }
                            $expected_delivery_show_economical_air = $expected_delivery_show_10Days;
                            
                            if(isset($shipment_prices[$delivery_option_5Days]['price'])){
                                $shipment_prices[$delivery_option_5Days]['price'] = $shipment_prices[$delivery_option_5Days]['price'] + $shipment_price_economical_air;
                                $shipment_prices[$delivery_option_5Days]['commitment'] = $expected_delivery_show_economical_air;
            
                                $shipment_prices[$delivery_option_15Days]['price'] = $shipment_prices[$delivery_option_15Days]['price'] + $shipment_price_reg_boat;
                                $shipment_prices[$delivery_option_15Days]['commitment'] = $expected_delivery_show_reg_boat;
                            }else{
                                $shipment_prices[$delivery_option_5Days]['price'] = $shipment_price_economical_air;
                                $shipment_prices[$delivery_option_5Days]['commitment'] = $expected_delivery_show_economical_air;
        
                                $shipment_prices[$delivery_option_15Days]['price'] = $shipment_price_reg_boat;
                                $shipment_prices[$delivery_option_15Days]['commitment'] = $expected_delivery_show_reg_boat;
                            }
                        break;
                        
                        
                        
                        // COLOMBIA FOR THIRD PARTY
                        case ("CO"):
                         
                                        // REGULAR BOX PRICE FOR AIR 
                            if ($chargeable_weight < 18.0){
                                $shipment_price_economical_air = 125.0;
                            } else {
                                $shipment_price_economical_air = $chargeable_weight * 7.0;
                            }
                            $expected_delivery_show_economical_air = $expected_delivery_show_10Days;
                            if(isset($shipment_prices[$delivery_option_5Days]['price'])){
                                $shipment_prices[$delivery_option_5Days]['price'] = $shipment_prices[$delivery_option_5Days]['price'] + $shipment_price_economical_air;
                                $shipment_prices[$delivery_option_5Days]['commitment'] = $expected_delivery_show_economical_air;
                            }else{
                                $shipment_prices[$delivery_option_5Days]['price'] = $shipment_price_economical_air;
                                $shipment_prices[$delivery_option_5Days]['commitment'] = $expected_delivery_show_economical_air;
                            }			   				   
                        break;
                        
                        
                        
                        // MEXICO FOR THIRD PARTY
                        case ("MX"):
                        		
                            if ($chargeable_weight <= 10.0){
                                $shipment_price_reg_boat = 80.0;
                            } else {
                                $shipment_price_reg_boat = $chargeable_weight * 4.00;
                            }
                            $expected_delivery_show_reg_boat = $expected_delivery_show_15Days;
                                        
                                        
                                        // REGULAR BOX PRICE FOR AIR 
                            if ($chargeable_weight < 17.0){
                                $shipment_price_economical_air = 85.0;
                            } else {
                                $shipment_price_economical_air = $chargeable_weight * 5.0;
                            }
                            $expected_delivery_show_economical_air = $expected_delivery_show_10Days;

                            if(isset($shipment_prices[$delivery_option_5Days]['price'])){
                                $shipment_prices[$delivery_option_5Days]['price'] = $shipment_prices[$delivery_option_5Days]['price'] + $shipment_price_economical_air;
                                $shipment_prices[$delivery_option_5Days]['commitment'] = $expected_delivery_show_economical_air;
            
                                $shipment_prices[$delivery_option_15Days]['price'] = $shipment_prices[$delivery_option_15Days]['price'] + $shipment_price_reg_boat;
                                $shipment_prices[$delivery_option_15Days]['commitment'] = $expected_delivery_show_reg_boat;
                            }else{
                                $shipment_prices[$delivery_option_5Days]['price'] = $shipment_price_economical_air;
                                $shipment_prices[$delivery_option_5Days]['commitment'] = $expected_delivery_show_economical_air;
        
                                $shipment_prices[$delivery_option_15Days]['price'] = $shipment_price_reg_boat;
                                $shipment_prices[$delivery_option_15Days]['commitment'] = $expected_delivery_show_reg_boat;
                            }
                        break;
                        
                        
                        // CHILE FOR THIRD PARTY
                        case ("CL"):
                        			  
                        // REGULAR BOX PRICE
                            if ($cubic_feet_rounded < 2.0){
                                $shipment_price_reg_boat = 100.0;
                            } else {
                                $shipment_price_reg_boat = $cubic_feet_rounded * 40.0;
                            }
                            $expected_delivery_show_reg_boat = $expected_delivery_show_25Days;
                            
                                // REGULAR BOX PRICE FOR AIR
                            if (($chargeable_weight >= 0.0) && ($chargeable_weight <= 5.0)){
                                $shipment_price_economical_air = 126.0;
                            } elseif (($chargeable_weight > 5.0) && ($chargeable_weight <= 10.0)){
                                $shipment_price_economical_air = 207.0;
                            } else {
                                if (($chargeable_weight > 10.0) && ($chargeable_weight <= 15.0)){
                                    $shipment_price_economical_air = 272.0;
                                } elseif (($chargeable_weight > 15.0) && ($chargeable_weight <= 20.0)){
                                    $shipment_price_economical_air = 333.0;
                                } elseif (($chargeable_weight > 20.0) && ($chargeable_weight <= 25.0)){
                                    $shipment_price_economical_air = 364.0;
                                }
                                elseif (($chargeable_weight > 25.0) && ($chargeable_weight <= 30.0)){
                                    $shipment_price_economical_air = 420.0;
                                }
                                elseif (($chargeable_weight > 30.0) && ($chargeable_weight <= 35.0)){
                                    $shipment_price_economical_air = 452.0;
                                }
                                elseif (($chargeable_weight > 35.0) && ($chargeable_weight <= 40.0)){
                                    $shipment_price_economical_air = 502.0;
                                }
                                elseif (($chargeable_weight > 40.0) && ($chargeable_weight <= 45.0)){
                                    $shipment_price_economical_air = 564.0;
                                }
                                elseif (($chargeable_weight > 45.0) && ($chargeable_weight <= 50.0)){
                                    $shipment_price_economical_air = 618.0;
                                }
                                elseif (($chargeable_weight > 50.0) && ($chargeable_weight <= 55.0)){
                                    $shipment_price_economical_air = 635.0;
                                }
                                elseif (($chargeable_weight > 55.0) && ($chargeable_weight <= 60.0)){
                                    $shipment_price_economical_air = 700.0;
                                }
                                elseif (($chargeable_weight >60.0) && ($chargeable_weight <= 65.0)){
                                    $shipment_price_economical_air = 720.0;
                                }
                                elseif (($chargeable_weight > 65.0) && ($chargeable_weight <= 70.0)){
                                    $shipment_price_economical_air = 780.0;
                                }
                                elseif (($chargeable_weight > 70.0) && ($chargeable_weight <= 75.0)){
                                    $shipment_price_economical_air = 830.0;
                                }
                                elseif (($chargeable_weight > 75.0) && ($chargeable_weight <= 80.0)){
                                    $shipment_price_economical_air = 870.0;
                                }
                                elseif (($chargeable_weight > 80.0) && ($chargeable_weight <= 85.0)){
                                    $shipment_price_economical_air = 880.0;
                                }
                                elseif (($chargeable_weight > 85.0) && ($chargeable_weight <= 90.0)){
                                    $shipment_price_economical_air = 900.0;
                                }
                                elseif (($chargeable_weight > 90.0) && ($chargeable_weight <= 95.0)){
                                    $shipment_price_economical_air = 930.0;
                                } 
                                elseif (($chargeable_weight > 95.0) && ($chargeable_weight <= 100.0)){
                                    $shipment_price_economical_air = 950.0;
                                } 
                                else
                                    $shipment_price_economical_air = $chargeable_weight * 9.45;
                            }
                            $expected_delivery_show_economical_air = $expected_delivery_show_10Days;
                            
                            
                            $shipment_price_economical_air = $shipment_price_economical_air * 0.75;
                            
                            // CALCULATE SGIPMENT COLLECT FOR CHILE
                            $shipment_price_collect = $shipment_price_economical_air * 0.75;
                            $shipment_price_collect = $shipment_price_collect + ($package_value * 0.07);
                            $shipment_price_collect = $shipment_price_collect * 1.07;

                            if(isset($shipment_prices[$delivery_option_5Days]['price'])){
                                $shipment_prices[$delivery_option_5Days]['price'] = $shipment_prices[$delivery_option_5Days]['price'] + $shipment_price_economical_air;
                                $shipment_prices[$delivery_option_5Days]['commitment'] = $expected_delivery_show_economical_air;
            
                                $shipment_prices[$delivery_option_15Days]['price'] = $shipment_prices[$delivery_option_15Days]['price'] + $shipment_price_reg_boat;
                                $shipment_prices[$delivery_option_15Days]['commitment'] = $expected_delivery_show_reg_boat;
                            }else{
                                $shipment_prices[$delivery_option_5Days]['price'] = $shipment_price_economical_air;
                                $shipment_prices[$delivery_option_5Days]['commitment'] = $expected_delivery_show_economical_air;
        
                                $shipment_prices[$delivery_option_15Days]['price'] = $shipment_price_reg_boat;
                                $shipment_prices[$delivery_option_15Days]['commitment'] = $expected_delivery_show_reg_boat;
                            }
                        break;
                        
                        
                        // BRAZIL FOR THIRD PARTY
                        case ("BR"):
                        
                            // HUB 20 PROGRAM PRICE				 
                            if (($package_volume == 0.0) && ($flat_rate_list == "FLAT BOX 14X14X14")){
                                $shipment_price_thirdParty = $flat_item_quantity * 0.0;
                            } else
                            if (($package_volume == 0.0) && ($flat_rate_list == "FLAT BOX 18X18X16")){
                                $shipment_price_thirdParty = $flat_item_quantity * 0.0;
                            } else
                            if (($package_volume == 0.0) && ($flat_rate_list == "FLAT BOX 18X18X24")){
                                $shipment_price_thirdParty = $flat_item_quantity * 0.0;
                            } else
                            if (($package_volume == 0.0) && ($flat_rate_list == "FLAT BOX 18X24X24")){
                                $shipment_price_thirdParty = $flat_item_quantity * 0.0;
                            }
                                        
                                        
                            // REGULAR BOX PRICE
                            if ($cubic_feet_rounded < 3.0){
                                $shipment_price_reg_boat = 300.0;
                            } else {
                                $shipment_price_reg_boat = $cubic_feet_rounded * 80.0;
                            }
                            $expected_delivery_show_reg_boat = $expected_delivery_show_20Days;
                                        
                                        
                            // REGULAR BOX PRICE FOR AIR 
                            if ($chargeable_weight < 12.0){
                                $shipment_price_economical_air = 600.0;
                            } else {
                                $shipment_price_economical_air = $chargeable_weight * 50.0;
                            }
                            $expected_delivery_show_economical_air = $expected_delivery_show_10Days;

                            if(isset($shipment_prices[$delivery_option_5Days]['price'])){
                                $shipment_prices[$delivery_option_5Days]['price'] = $shipment_prices[$delivery_option_5Days]['price'] + $shipment_price_economical_air;
                                $shipment_prices[$delivery_option_5Days]['commitment'] = $expected_delivery_show_economical_air;
            
                                $shipment_prices[$delivery_option_15Days]['price'] = $shipment_prices[$delivery_option_15Days]['price'] + $shipment_price_reg_boat;
                                $shipment_prices[$delivery_option_15Days]['commitment'] = $expected_delivery_show_reg_boat;
                            }else{
                                $shipment_prices[$delivery_option_5Days]['price'] = $shipment_price_economical_air;
                                $shipment_prices[$delivery_option_5Days]['commitment'] = $expected_delivery_show_economical_air;
        
                                $shipment_prices[$delivery_option_15Days]['price'] = $shipment_price_reg_boat;
                                $shipment_prices[$delivery_option_15Days]['commitment'] = $expected_delivery_show_reg_boat;
                            }
                        break;
                        
                        
                        
                        
                        // ARGENTINA FOR THIRD PARTY
                        case ("AR"):
                                        
                        
                                // REGULAR BOX PRICE FOR AIR
                            if (($chargeable_weight >= 0.0) && ($chargeable_weight <= 5.0)){
                                $shipment_price_economical_air = 47.0;
                            } elseif (($chargeable_weight > 5.0) && ($chargeable_weight <= 10.0)){
                                $shipment_price_economical_air = 68.0;
                            } else {
                                if (($chargeable_weight > 10.0) && ($chargeable_weight <= 15.0)){
                                    $shipment_price_economical_air = 91.0;
                                } elseif (($chargeable_weight > 15.0) && ($chargeable_weight <= 20.0)){
                                    $shipment_price_economical_air = 113.0;
                                } elseif (($chargeable_weight > 20.0) && ($chargeable_weight <= 25.0)){
                                    $shipment_price_economical_air = 140.0;
                                }elseif (($chargeable_weight > 25.0) && ($chargeable_weight <= 30.0)){
                                    $shipment_price_economical_air = 162.0;
                                }elseif (($chargeable_weight > 30.0) && ($chargeable_weight <= 35.0)){
                                    $shipment_price_economical_air = 172.0;
                                }elseif (($chargeable_weight > 35.0) && ($chargeable_weight <= 40.0)){
                                    $shipment_price_economical_air = 192.0;
                                }elseif (($chargeable_weight > 40.0) && ($chargeable_weight <= 45.0)){
                                    $shipment_price_economical_air = 216.0;
                                }elseif (($chargeable_weight > 45.0) && ($chargeable_weight <= 50.0)){
                                    $shipment_price_economical_air = 235.0;
                                }elseif (($chargeable_weight > 50.0) && ($chargeable_weight <= 55.0)){
                                    $shipment_price_economical_air = 259.0;
                                }elseif (($chargeable_weight > 55.0) && ($chargeable_weight <= 60.0)){
                                    $shipment_price_economical_air = 279.0;
                                }elseif (($chargeable_weight >60.0) && ($chargeable_weight <= 65.0)){
                                    $shipment_price_economical_air = 298.0;
                                }elseif (($chargeable_weight > 65.0) && ($chargeable_weight <= 70.0)){
                                    $shipment_price_economical_air = 324.0;
                                }elseif (($chargeable_weight > 70.0) && ($chargeable_weight <= 75.0)){
                                    $shipment_price_economical_air = 333.0;
                                }elseif (($chargeable_weight > 75.0) && ($chargeable_weight <= 80.0)){
                                    $shipment_price_economical_air = 336.0;
                                }elseif (($chargeable_weight > 80.0) && ($chargeable_weight <= 85.0)){
                                    $shipment_price_economical_air = 358.0;
                                }elseif (($chargeable_weight > 85.0) && ($chargeable_weight <= 90.0)){
                                    $shipment_price_economical_air = 378.0;
                                }elseif (($chargeable_weight > 90.0) && ($chargeable_weight <= 95.0)){
                                    $shipment_price_economical_air = 396.0;
                                }elseif (($chargeable_weight > 95.0) && ($chargeable_weight <= 100.0)){
                                    $shipment_price_economical_air = 415.0;
                                }else
                                    $shipment_price_economical_air = $chargeable_weight * 4.10;
                                }
                                $expected_delivery_show_economical_air = $expected_delivery_show_10Days;
                                if(isset($shipment_prices[$delivery_option_5Days]['price'])){
                                    $shipment_prices[$delivery_option_5Days]['price'] = $shipment_prices[$delivery_option_5Days]['price'] + $shipment_price_economical_air;
                                    $shipment_prices[$delivery_option_5Days]['commitment'] = $expected_delivery_show_economical_air;
                                }else{
                                    $shipment_prices[$delivery_option_5Days]['price'] = $shipment_price_economical_air;
                                    $shipment_prices[$delivery_option_5Days]['commitment'] = $expected_delivery_show_economical_air;
                                }
                        break;
                        
                        
                        
                        // PARAGUAY FOR THIRD PARTY
                        case ("PY"):
                        
                        		
                                        // REGULAR BOX PRICE FOR AIR
                            if (($chargeable_weight >= 0.0) && ($chargeable_weight <= 5.0)){
                                $shipment_price_economical_air = 109.0;
                            } elseif (($chargeable_weight > 5.0) && ($chargeable_weight <= 10.0)){
                                $shipment_price_economical_air = 165.0;
                            } else {
                                if (($chargeable_weight > 10.0) && ($chargeable_weight <= 15.0)){
                                    $shipment_price_economical_air = 201.0;
                                } elseif (($chargeable_weight > 15.0) && ($chargeable_weight <= 20.0)){
                                    $shipment_price_economical_air = 246.0;
                                } elseif (($chargeable_weight > 20.0) && ($chargeable_weight <= 25.0)){
                                    $shipment_price_economical_air = 277.0;
                                }elseif (($chargeable_weight > 25.0) && ($chargeable_weight <= 30.0)){
                                    $shipment_price_economical_air = 315.0;
                                }elseif (($chargeable_weight > 30.0) && ($chargeable_weight <= 35.0)){
                                    $shipment_price_economical_air = 349.0;
                                }elseif (($chargeable_weight > 35.0) && ($chargeable_weight <= 40.0)){
                                    $shipment_price_economical_air = 376.0;
                                }elseif (($chargeable_weight > 40.0) && ($chargeable_weight <= 45.0)){
                                    $shipment_price_economical_air = 412.0;
                                }elseif (($chargeable_weight > 45.0) && ($chargeable_weight <= 50.0)){
                                    $shipment_price_economical_air = 453.0;
                                }elseif (($chargeable_weight > 50.0) && ($chargeable_weight <= 55.0)){
                                    $shipment_price_economical_air = 495.0;
                                }elseif (($chargeable_weight > 55.0) && ($chargeable_weight <= 60.0)){
                                    $shipment_price_economical_air = 527.0;
                                }elseif (($chargeable_weight >60.0) && ($chargeable_weight <= 65.0)){
                                    $shipment_price_economical_air = 576.0;
                                }elseif (($chargeable_weight > 65.0) && ($chargeable_weight <= 70.0)){
                                    $shipment_price_economical_air = 612.0;
                                }elseif (($chargeable_weight > 70.0) && ($chargeable_weight <= 75.0)){
                                    $shipment_price_economical_air = 694.0;
                                }elseif (($chargeable_weight > 75.0) && ($chargeable_weight <= 80.0)){
                                    $shipment_price_economical_air = 727.0;
                                }elseif (($chargeable_weight > 80.0) && ($chargeable_weight <= 85.0)){
                                    $shipment_price_economical_air = 759.0;
                                }elseif (($chargeable_weight > 85.0) && ($chargeable_weight <= 90.0)){
                                    $shipment_price_economical_air = 792.0;
                                }elseif (($chargeable_weight > 90.0) && ($chargeable_weight <= 95.0)){
                                    $shipment_price_economical_air = 833.0;
                                }elseif (($chargeable_weight > 95.0) && ($chargeable_weight <= 100.0)){
                                    $shipment_price_economical_air = 867.0;
                                }else
                                    $shipment_price_economical_air = $chargeable_weight * 8.60;
                            }
                            $expected_delivery_show_economical_air = $expected_delivery_show_10Days;
                            if(isset($shipment_prices[$delivery_option_5Days]['price'])){
                                $shipment_prices[$delivery_option_5Days]['price'] = $shipment_prices[$delivery_option_5Days]['price'] + $shipment_price_economical_air;
                                $shipment_prices[$delivery_option_5Days]['commitment'] = $expected_delivery_show_economical_air;
                            }else{
                                $shipment_prices[$delivery_option_5Days]['price'] = $shipment_price_economical_air;
                                $shipment_prices[$delivery_option_5Days]['commitment'] = $expected_delivery_show_economical_air;
                            }
                        break;
                        
                        
                        
                        // PANAMA FOR THIRD PARTY
                        case ("PA"):
                        
                            // HUB 20 PROGRAM PRICE				 
                            if (($package_volume == 0.0) && ($flat_rate_list == "FLAT BOX 14X14X14")){
                                $shipment_price_thirdParty = $flat_item_quantity * 90.0;
                            } elseif (($package_volume == 0.0) && ($flat_rate_list == "FLAT BOX 18X18X16")){
                                $shipment_price_thirdParty = $flat_item_quantity * 130.0;
                            } elseif (($package_volume == 0.0) && ($flat_rate_list == "FLAT BOX 18X18X24")){
                                $shipment_price_thirdParty = $flat_item_quantity * 150.0;
                            } elseif (($package_volume == 0.0) && ($flat_rate_list == "FLAT BOX 18X24X24")){
                                $shipment_price_thirdParty = $flat_item_quantity * 200.0;
                            }				 
                                        
                                        
                        // REGULAR BOX PRICE
                            if ($cubic_feet_rounded < 2.0){
                                $shipment_price_reg_boat = 80.0;
                            } else {
                                $shipment_price_reg_boat = $cubic_feet_rounded * 30.0;
                            }
                            $expected_delivery_show_reg_boat = $expected_delivery_show_20Days;
                                        
                            // REGULAR BOX PRICE FOR AIR 
                            if ($chargeable_weight < 10.0){
                            $shipment_price_economical_air = 70.0;
                            } else {
                                $shipment_price_economical_air = $chargeable_weight * 7.0;
                                }
                            $expected_delivery_show_economical_air = $expected_delivery_show_10Days;

                            if(isset($shipment_prices[$delivery_option_5Days]['price'])){
                                $shipment_prices[$delivery_option_5Days]['price'] = $shipment_prices[$delivery_option_5Days]['price'] + $shipment_price_economical_air;
                                $shipment_prices[$delivery_option_5Days]['commitment'] = $expected_delivery_show_economical_air;
            
                                $shipment_prices[$delivery_option_15Days]['price'] = $shipment_prices[$delivery_option_15Days]['price'] + $shipment_price_reg_boat;
                                $shipment_prices[$delivery_option_15Days]['commitment'] = $expected_delivery_show_reg_boat;
                            }else{
                                $shipment_prices[$delivery_option_5Days]['price'] = $shipment_price_economical_air;
                                $shipment_prices[$delivery_option_5Days]['commitment'] = $expected_delivery_show_economical_air;
        
                                $shipment_prices[$delivery_option_15Days]['price'] = $shipment_price_reg_boat;
                                $shipment_prices[$delivery_option_15Days]['commitment'] = $expected_delivery_show_reg_boat;
                            }
                        break;
                        
                        
                        // ECUADOR FOR THIRD PARTY
                        case ("EC"):
                        	 
                                        
                        // REGULAR BOX PRICE
                            if ($cubic_feet_rounded <= 2.0){
                                $shipment_price_reg_boat = 130.0;
                            } else {
                                $shipment_price_reg_boat = $cubic_feet_rounded * 45.0;
                            }
                            $expected_delivery_show_reg_boat = $expected_delivery_show_20Days;
                                     
                                        // REGULAR BOX PRICE FOR AIR 
                            if ($chargeable_weight < 8.0){
                                $shipment_price_economical_air = 80.0;
                            } else {
                                $shipment_price_economical_air = $chargeable_weight * 8.0;
                            }
                            $expected_delivery_show_economical_air = $expected_delivery_show_10Days;

                            if(isset($shipment_prices[$delivery_option_5Days]['price'])){
                                $shipment_prices[$delivery_option_5Days]['price'] = $shipment_prices[$delivery_option_5Days]['price'] + $shipment_price_economical_air;
                                $shipment_prices[$delivery_option_5Days]['commitment'] = $expected_delivery_show_economical_air;
            
                                $shipment_prices[$delivery_option_15Days]['price'] = $shipment_prices[$delivery_option_15Days]['price'] + $shipment_price_reg_boat;
                                $shipment_prices[$delivery_option_15Days]['commitment'] = $expected_delivery_show_reg_boat;
                            }else{
                                $shipment_prices[$delivery_option_5Days]['price'] = $shipment_price_economical_air;
                                $shipment_prices[$delivery_option_5Days]['commitment'] = $expected_delivery_show_economical_air;
        
                                $shipment_prices[$delivery_option_15Days]['price'] = $shipment_price_reg_boat;
                                $shipment_prices[$delivery_option_15Days]['commitment'] = $expected_delivery_show_reg_boat;
                            }
                        break;
                        
                        
                        
                        // COSTA RICA FOR THIRD PARTY
                        case ("CR"):
                        
                            if (($package_volume == 0.0) && ($flat_rate_list == "FLAT BOX 18X18X16")){
                                $shipment_price_thirdParty = $flat_item_quantity * 200.0;
                            } elseif (($package_volume == 0.0) && ($flat_rate_list == "FLAT BOX 18X18X24")){
                                $shipment_price_thirdParty = $flat_item_quantity * 230.0;
                            } elseif (($package_volume == 0.0) && ($flat_rate_list == "FLAT BOX 18X24X24")){
                                $shipment_price_thirdParty = $flat_item_quantity * 300.0;
                            }
                        
                                    
                            // REGULAR BOX PRICE
                            if ($chargeable_weight <= 12.0){
                                $shipment_price_reg_boat = 100.0;
                            } else {
                                $shipment_price_reg_boat = $chargeable_weight * 9.0;
                            }
                            $expected_delivery_show_reg_boat = $expected_delivery_show_20Days;
                            
                            // REGULAR BOX PRICE FOR AIR
                            if (($chargeable_weight >= 0.0) && ($chargeable_weight <= 5.0)){
                                $shipment_price_economical_air = 35.0;
                            } elseif (($chargeable_weight > 5.0) && ($chargeable_weight <= 10.0)){
                                $shipment_price_economical_air = 61.0;
                            } else {
                                if (($chargeable_weight > 10.0) && ($chargeable_weight <= 15.0)){
                                    $shipment_price_economical_air = 84.0;
                                } elseif (($chargeable_weight > 15.0) && ($chargeable_weight <= 20.0)){
                                    $shipment_price_economical_air = 113.0;
                                } elseif (($chargeable_weight > 20.0) && ($chargeable_weight <= 25.0)){
                                    $shipment_price_economical_air = 131.0;
                                }
                                elseif (($chargeable_weight > 25.0) && ($chargeable_weight <= 30.0)){
                                    $shipment_price_economical_air = 149.0;
                                }
                                elseif (($chargeable_weight > 30.0) && ($chargeable_weight <= 35.0)){
                                    $shipment_price_economical_air = 167.0;
                                }
                                elseif (($chargeable_weight > 35.0) && ($chargeable_weight <= 40.0)){
                                    $shipment_price_economical_air = 185.0;
                                }
                                elseif (($chargeable_weight > 40.0) && ($chargeable_weight <= 45.0)){
                                    $shipment_price_economical_air = 195.0;
                                }
                                elseif (($chargeable_weight > 45.0) && ($chargeable_weight <= 50.0)){
                                    $shipment_price_economical_air = 210.0;
                                }
                                elseif (($chargeable_weight > 50.0) && ($chargeable_weight <= 55.0)){
                                    $shipment_price_economical_air = 223.0;
                                }
                                elseif (($chargeable_weight > 55.0) && ($chargeable_weight <= 60.0)){
                                    $shipment_price_economical_air = 239.0;
                                }
                                elseif (($chargeable_weight >60.0) && ($chargeable_weight <= 65.0)){
                                    $shipment_price_economical_air = 252.0;
                                }
                                elseif (($chargeable_weight > 65.0) && ($chargeable_weight <= 70.0)){
                                    $shipment_price_economical_air = 268.0;
                                }
                                elseif (($chargeable_weight > 70.0) && ($chargeable_weight <= 75.0)){
                                    $shipment_price_economical_air = 282.0;
                                }
                                elseif (($chargeable_weight > 75.0) && ($chargeable_weight <= 80.0)){
                                    $shipment_price_economical_air = 295.0;
                                }
                                elseif (($chargeable_weight > 80.0) && ($chargeable_weight <= 85.0)){
                                    $shipment_price_economical_air = 311.0;
                                }
                                elseif (($chargeable_weight > 85.0) && ($chargeable_weight <= 90.0)){
                                    $shipment_price_economical_air = 324.0;
                                }
                                elseif (($chargeable_weight > 90.0) && ($chargeable_weight <= 95.0)){
                                    $shipment_price_economical_air = 340.0;
                                } 
                                elseif (($chargeable_weight > 95.0) && ($chargeable_weight <= 100.0)){
                                    $shipment_price_economical_air = 352.0;
                                } 
                                else
                                    $shipment_price_economical_air = $chargeable_weight * 3.50;
                            }
                            $expected_delivery_show_economical_air = $expected_delivery_show_10Days;

                            if(isset($shipment_prices[$delivery_option_5Days]['price'])){
                                $shipment_prices[$delivery_option_5Days]['price'] = $shipment_prices[$delivery_option_5Days]['price'] + $shipment_price_economical_air;
                                $shipment_prices[$delivery_option_5Days]['commitment'] = $expected_delivery_show_economical_air;
            
                                $shipment_prices[$delivery_option_15Days]['price'] = $shipment_prices[$delivery_option_15Days]['price'] + $shipment_price_reg_boat;
                                $shipment_prices[$delivery_option_15Days]['commitment'] = $expected_delivery_show_reg_boat;
                            }else{
                                $shipment_prices[$delivery_option_5Days]['price'] = $shipment_price_economical_air;
                                $shipment_prices[$delivery_option_5Days]['commitment'] = $expected_delivery_show_economical_air;
        
                                $shipment_prices[$delivery_option_15Days]['price'] = $shipment_price_reg_boat;
                                $shipment_prices[$delivery_option_15Days]['commitment'] = $expected_delivery_show_reg_boat;
                            }
                        break;
                        
                        
                        
                        // SPAIN FOR THIRD PARTY
                        case ("ES"):
                                        // REGULAR BOX PRICE FOR AIR 
                            if ($chargeable_weight < 10.0){
                                $shipment_price_economical_air = 120.0;
                            } else {
                                $shipment_price_economical_air = $chargeable_weight * 13.0;
                            }
                            $expected_delivery_show_economical_air = $expected_delivery_show_10Days;
                                        
                            if(isset($shipment_prices[$delivery_option_5Days]['price'])){
                                $shipment_prices[$delivery_option_5Days]['price'] = $shipment_prices[$delivery_option_5Days]['price'] + $shipment_price_economical_air;
                                $shipment_prices[$delivery_option_5Days]['commitment'] = $expected_delivery_show_economical_air;
                            }else{
                                $shipment_prices[$delivery_option_5Days]['price'] = $shipment_price_economical_air;
                                $shipment_prices[$delivery_option_5Days]['commitment'] = $expected_delivery_show_economical_air;
                            }
                        break;
                        
                        
                        // COSTA RICA FOR THIRD PARTY
                        case ("PR"):
                        
                            // REGULAR BOX PRICE FOR AIR
                            if (($chargeable_weight >= 0.0) && ($chargeable_weight <= 5.0)){
                                $shipment_price_economical_air = 58.0;
                            } elseif (($chargeable_weight > 5.0) && ($chargeable_weight <= 10.0)){
                                $shipment_price_economical_air = 95.0;
                            } else {
                                if (($chargeable_weight > 10.0) && ($chargeable_weight <= 15.0)){
                                    $shipment_price_economical_air = 131.0;
                                } elseif (($chargeable_weight > 15.0) && ($chargeable_weight <= 20.0)){
                                    $shipment_price_economical_air = 167.0;
                                } elseif (($chargeable_weight > 20.0) && ($chargeable_weight <= 25.0)){
                                    $shipment_price_economical_air = 203.0;
                                }
                                elseif (($chargeable_weight > 25.0) && ($chargeable_weight <= 30.0)){
                                    $shipment_price_economical_air = 239.0;
                                }
                                elseif (($chargeable_weight > 30.0) && ($chargeable_weight <= 35.0)){
                                    $shipment_price_economical_air = 253.0;
                                }
                                elseif (($chargeable_weight > 35.0) && ($chargeable_weight <= 40.0)){
                                    $shipment_price_economical_air = 286.0;
                                }
                                elseif (($chargeable_weight > 40.0) && ($chargeable_weight <= 45.0)){
                                    $shipment_price_economical_air = 316.0;
                                }
                                elseif (($chargeable_weight > 45.0) && ($chargeable_weight <= 50.0)){
                                    $shipment_price_economical_air = 349.0;
                                }
                                elseif (($chargeable_weight > 50.0) && ($chargeable_weight <= 55.0)){
                                    $shipment_price_economical_air = 379.0;
                                }
                                elseif (($chargeable_weight > 55.0) && ($chargeable_weight <= 60.0)){
                                    $shipment_price_economical_air = 412.0;
                                }
                                elseif (($chargeable_weight >60.0) && ($chargeable_weight <= 65.0)){
                                    $shipment_price_economical_air = 442.0;
                                }
                                elseif (($chargeable_weight > 65.0) && ($chargeable_weight <= 70.0)){
                                    $shipment_price_economical_air = 475.0;
                                }
                                elseif (($chargeable_weight > 70.0) && ($chargeable_weight <= 75.0)){
                                    $shipment_price_economical_air = 547.0;
                                }
                                elseif (($chargeable_weight > 75.0) && ($chargeable_weight <= 80.0)){
                                    $shipment_price_economical_air = 577.0;
                                }
                                elseif (($chargeable_weight > 80.0) && ($chargeable_weight <= 85.0)){
                                    $shipment_price_economical_air = 606.0;
                                }
                                elseif (($chargeable_weight > 85.0) && ($chargeable_weight <= 90.0)){
                                    $shipment_price_economical_air = 637.0;
                                }
                                elseif (($chargeable_weight > 90.0) && ($chargeable_weight <= 95.0)){
                                    $shipment_price_economical_air = 667.0;
                                } 
                                elseif (($chargeable_weight > 95.0) && ($chargeable_weight <= 100.0)){
                                    $shipment_price_economical_air = 695.0;
                                } 
                                else
                                    $shipment_price_economical_air = $chargeable_weight * 6.90;
                            }
                            $expected_delivery_show_economical_air = $expected_delivery_show_10Days;

                            if(isset($shipment_prices[$delivery_option_5Days]['price'])){
                                $shipment_prices[$delivery_option_5Days]['price'] = $shipment_prices[$delivery_option_5Days]['price'] + $shipment_price_economical_air;
                                $shipment_prices[$delivery_option_5Days]['commitment'] = $expected_delivery_show_economical_air;
                            }else{
                                $shipment_prices[$delivery_option_5Days]['price'] = $shipment_price_economical_air;
                                $shipment_prices[$delivery_option_5Days]['commitment'] = $expected_delivery_show_economical_air;
                            }
                    				   				   
                        break;
                        
                        
                        
                        // ECUADOR FOR THIRD PARTY
                        case ("PE"):
                        			    
                                        
                            // REGULAR BOX PRICE
                            if ($chargeable_weight <= 9.0){
                                $shipment_price_reg_boat = 110.0;
                            } else {
                                $shipment_price_reg_boat = $chargeable_weight * 13.0;
                            }
                            $expected_delivery_show_reg_boat = $expected_delivery_show_20Days;
                                
                            // REGULAR BOX PRICE FOR AIR
                            if (($chargeable_weight >= 0.0) && ($chargeable_weight <= 5.0)){
                                $shipment_price_economical_air = 110.0;
                            } else
                            if (($chargeable_weight > 5.0) && ($chargeable_weight <= 10.0)){
                                $shipment_price_economical_air = 166.0;
                            } else {
                                if (($chargeable_weight > 10.0) && ($chargeable_weight <= 15.0)){
                                    $shipment_price_economical_air = 202.0;
                                } elseif (($chargeable_weight > 15.0) && ($chargeable_weight <= 20.0)){
                                    $shipment_price_economical_air = 246.0;
                                } elseif (($chargeable_weight > 20.0) && ($chargeable_weight <= 25.0)){
                                    $shipment_price_economical_air = 278.0;
                                }
                                elseif (($chargeable_weight > 25.0) && ($chargeable_weight <= 30.0)){
                                    $shipment_price_economical_air = 315.0;
                                }
                                elseif (($chargeable_weight > 30.0) && ($chargeable_weight <= 35.0)){
                                    $shipment_price_economical_air = 350.0;
                                }
                                elseif (($chargeable_weight > 35.0) && ($chargeable_weight <= 40.0)){
                                    $shipment_price_economical_air = 377.0;
                                }
                                elseif (($chargeable_weight > 40.0) && ($chargeable_weight <= 45.0)){
                                    $shipment_price_economical_air = 413.0;
                                }
                                elseif (($chargeable_weight > 45.0) && ($chargeable_weight <= 50.0)){
                                    $shipment_price_economical_air = 453.0;
                                }
                                elseif (($chargeable_weight > 50.0) && ($chargeable_weight <= 55.0)){
                                    $shipment_price_economical_air = 495.0;
                                }
                                elseif (($chargeable_weight > 55.0) && ($chargeable_weight <= 60.0)){
                                    $shipment_price_economical_air = 527.0;
                                }
                                elseif (($chargeable_weight >60.0) && ($chargeable_weight <= 65.0)){
                                    $shipment_price_economical_air = 576.0;
                                }
                                elseif (($chargeable_weight > 65.0) && ($chargeable_weight <= 70.0)){
                                    $shipment_price_economical_air = 612.0;
                                }
                                elseif (($chargeable_weight > 70.0) && ($chargeable_weight <= 75.0)){
                                    $shipment_price_economical_air = 695.0;
                                }
                                elseif (($chargeable_weight > 75.0) && ($chargeable_weight <= 80.0)){
                                    $shipment_price_economical_air = 727.0;
                                }
                                elseif (($chargeable_weight > 80.0) && ($chargeable_weight <= 85.0)){
                                    $shipment_price_economical_air = 760.0;
                                }
                                elseif (($chargeable_weight > 85.0) && ($chargeable_weight <= 90.0)){
                                    $shipment_price_economical_air = 792.0;
                                }
                                elseif (($chargeable_weight > 90.0) && ($chargeable_weight <= 95.0)){
                                    $shipment_price_economical_air = 833.0;
                                } 
                                elseif (($chargeable_weight > 95.0) && ($chargeable_weight <= 100.0)){
                                    $shipment_price_economical_air = 868.0;
                                } 
                                else
                                    $shipment_price_economical_air = $chargeable_weight * 8.00;
                            }
                            $expected_delivery_show_economical_air = $expected_delivery_show_10Days;
                            
                            if(isset($shipment_prices[$delivery_option_5Days]['price'])){
                                $shipment_prices[$delivery_option_5Days]['price'] = $shipment_prices[$delivery_option_5Days]['price'] + $shipment_price_economical_air;
                                $shipment_prices[$delivery_option_5Days]['commitment'] = $expected_delivery_show_economical_air;
            
                                $shipment_prices[$delivery_option_15Days]['price'] = $shipment_prices[$delivery_option_15Days]['price'] + $shipment_price_reg_boat;
                                $shipment_prices[$delivery_option_15Days]['commitment'] = $expected_delivery_show_reg_boat;
                            }else{
                                $shipment_prices[$delivery_option_5Days]['price'] = $shipment_price_economical_air;
                                $shipment_prices[$delivery_option_5Days]['commitment'] = $expected_delivery_show_economical_air;
        
                                $shipment_prices[$delivery_option_15Days]['price'] = $shipment_price_reg_boat;
                                $shipment_prices[$delivery_option_15Days]['commitment'] = $expected_delivery_show_reg_boat;
                            }
                        break;
                        

                        
                        // ECUADOR FOR THIRD PARTY
                        case ("CU"):
                                        
                                        
                            // REGULAR BOX PRICE
                            if ($chargeable_weight <= 11.0){
                                $shipment_price_reg_boat = 75.0;
                            } else {
                                $shipment_price_reg_boat = $chargeable_weight * 7.0;
                            }
                            $expected_delivery_show_reg_boat = $expected_delivery_show_20Days;
                                
                            // REGULAR BOX PRICE FOR AIR 
                            if ($chargeable_weight < 10.0){
                                $shipment_price_economical_air = 80.0;
                            } else {
                                $shipment_price_economical_air = $chargeable_weight * 8.0;
                            }
                            $expected_delivery_show_economical_air = $expected_delivery_show_10Days;					   				   
                            if(isset($shipment_prices[$delivery_option_5Days]['price'])){
                                $shipment_prices[$delivery_option_5Days]['price'] = $shipment_prices[$delivery_option_5Days]['price'] + $shipment_price_economical_air;
                                $shipment_prices[$delivery_option_5Days]['commitment'] = $expected_delivery_show_economical_air;
            
                                $shipment_prices[$delivery_option_15Days]['price'] = $shipment_prices[$delivery_option_15Days]['price'] + $shipment_price_reg_boat;
                                $shipment_prices[$delivery_option_15Days]['commitment'] = $expected_delivery_show_reg_boat;
                            }else{
                                $shipment_prices[$delivery_option_5Days]['price'] = $shipment_price_economical_air;
                                $shipment_prices[$delivery_option_5Days]['commitment'] = $expected_delivery_show_economical_air;
        
                                $shipment_prices[$delivery_option_15Days]['price'] = $shipment_price_reg_boat;
                                $shipment_prices[$delivery_option_15Days]['commitment'] = $expected_delivery_show_reg_boat;
                            }
                        break;
                        
                        
                        
                        // JAMAICA FOR THIRD PARTY
                        case ("JM"):
                            // HUB 20 PROGRAM PRICE				 
                            if (($package_volume == 0.0) && ($flat_rate_list == "FLAT BOX 14X14X14")){
                                $shipment_price_thirdParty = $flat_item_quantity * 65.0;
                            } elseif (($package_volume == 0.0) && ($flat_rate_list == "FLAT BOX 18X18X16")){
                                $shipment_price_thirdParty = $flat_item_quantity * 75.0;
                            } else
                            if (($package_volume == 0.0) && ($flat_rate_list == "FLAT BOX 18X18X24")){
                                $shipment_price_thirdParty = $flat_item_quantity * 85.0;
                            } else
                            if (($package_volume == 0.0) && ($flat_rate_list == "FLAT BOX 18X24X24")){
                                $shipment_price_thirdParty = $flat_item_quantity * 110.0;
                            } elseif (($package_volume == "") && ($flat_rate_list == "BARREL 55 GAL (DRUM)")){
                                $shipment_price_thirdParty = (($flat_item_quantity * 120.0) - 4.0);
                            } elseif (($package_volume == "") && ($flat_rate_list == "E-CONTAINER (42*29*25)")){
                                $shipment_price_thirdParty = (($flat_item_quantity * 185.0) - 4.0);
                            } 


                            if ($cubic_feet_rounded < 6.0){
                                $shipment_price_reg_boat = 105.0;
                            }else {
                                $shipment_price_reg_boat = $cubic_feet_rounded * 20.00;
                            }
                            $expected_delivery_show_reg_boat = $expected_delivery_show_20Days;
                                  
                                        // REGULAR BOX PRICE FOR AIR
                            if (($chargeable_weight >= 0.0) && ($chargeable_weight <= 5.0)){
                                $shipment_price_economical_air = 83.0;
                                } else
                            if (($chargeable_weight > 5.0) && ($chargeable_weight <= 10.0)){
                                $shipment_price_economical_air = 112.0;
                                } else {
                                if (($chargeable_weight > 10.0) && ($chargeable_weight <= 15.0)){
                                    $shipment_price_economical_air = 142.0;
                                    } else				 
                                if (($chargeable_weight > 15.0) && ($chargeable_weight <= 20.0)){
                                    $shipment_price_economical_air = 173.0;
                                    } else				 
                                if (($chargeable_weight > 20.0) && ($chargeable_weight <= 25.0)){
                                    $shipment_price_economical_air = 184.0;
                                    }
                                    else				 
                                if (($chargeable_weight > 25.0) && ($chargeable_weight <= 30.0)){
                                    $shipment_price_economical_air = 212.0;
                                    }
                                    else				 
                                if (($chargeable_weight > 30.0) && ($chargeable_weight <= 35.0)){
                                    $shipment_price_economical_air = 243.0;
                                    }
                                    else				 
                                if (($chargeable_weight > 35.0) && ($chargeable_weight <= 40.0)){
                                    $shipment_price_economical_air = 283.0;
                                    }
                                    else				 
                                if (($chargeable_weight > 40.0) && ($chargeable_weight <= 45.0)){
                                    $shipment_price_economical_air = 298.0;
                                    }
                                    else				 
                                if (($chargeable_weight > 45.0) && ($chargeable_weight <= 50.0)){
                                    $shipment_price_economical_air = 320.0;
                                    }
                                    else				 
                                if (($chargeable_weight > 50.0) && ($chargeable_weight <= 55.0)){
                                    $shipment_price_economical_air = 260.0;
                                    }
                                    else				 
                                if (($chargeable_weight > 55.0) && ($chargeable_weight <= 60.0)){
                                    $shipment_price_economical_air = 392.0;
                                    }
                                    else				 
                                if (($chargeable_weight >60.0) && ($chargeable_weight <= 65.0)){
                                    $shipment_price_economical_air = 419.0;
                                    }
                                    else				 
                                if (($chargeable_weight > 65.0) && ($chargeable_weight <= 70.0)){
                                    $shipment_price_economical_air = 452.0;
                                    }
                                    else				 
                                if (($chargeable_weight > 70.0) && ($chargeable_weight <= 75.0)){
                                    $shipment_price_economical_air = 473.0;
                                    }
                                    else				 
                                if (($chargeable_weight > 75.0) && ($chargeable_weight <= 80.0)){
                                    $shipment_price_economical_air = 504.0;
                                    }
                                    else				 
                                if (($chargeable_weight > 80.0) && ($chargeable_weight <= 85.0)){
                                    $shipment_price_economical_air = 529.0;
                                    }
                                    else				 
                                if (($chargeable_weight > 85.0) && ($chargeable_weight <= 90.0)){
                                    $shipment_price_economical_air = 556.0;
                                    }
                                    else				 
                                if (($chargeable_weight > 90.0) && ($chargeable_weight <= 95.0)){
                                    $shipment_price_economical_air = 572.0;
                                    } 
                                    else				 
                                if (($chargeable_weight > 95.0) && ($chargeable_weight <= 100.0)){
                                    $shipment_price_economical_air = 633.0;
                                    } 
                                    else
                                        $shipment_price_economical_air = $chargeable_weight * 6.25;
                            }
                            $expected_delivery_show_economical_air = $expected_delivery_show_10Days;
                                    
                            if(isset($shipment_prices[$delivery_option_5Days]['price'])){
                                $shipment_prices[$delivery_option_5Days]['price'] = $shipment_prices[$delivery_option_5Days]['price'] + $shipment_price_economical_air;
                                $shipment_prices[$delivery_option_5Days]['commitment'] = $expected_delivery_show_economical_air;
            
                                $shipment_prices[$delivery_option_15Days]['price'] = $shipment_prices[$delivery_option_15Days]['price'] + $shipment_price_reg_boat;
                                $shipment_prices[$delivery_option_15Days]['commitment'] = $expected_delivery_show_reg_boat;
                            }else{
                                $shipment_prices[$delivery_option_5Days]['price'] = $shipment_price_economical_air;
                                $shipment_prices[$delivery_option_5Days]['commitment'] = $expected_delivery_show_economical_air;
        
                                $shipment_prices[$delivery_option_15Days]['price'] = $shipment_price_reg_boat;
                                $shipment_prices[$delivery_option_15Days]['commitment'] = $expected_delivery_show_reg_boat;
                            }
                                                            
                        break;						
                        
                        
                        
                        // CURACAO FOR THIRD PARTY
                        case ("CW"):
                                        
                            if ($cubic_feet_rounded < 6.0){
                                $shipment_price_reg_boat = 105.0;
                            }
                            else {
                                $shipment_price_reg_boat = $cubic_feet_rounded * 20.00;
                            }
                            $expected_delivery_show_reg_boat = $expected_delivery_show_20Days;
                                        
                                        
                                        
                                        // REGULAR BOX PRICE FOR AIR
                            if (($chargeable_weight >= 0.0) && ($chargeable_weight <= 5.0)){
                                $shipment_price_economical_air = 100.0;
                            } elseif (($chargeable_weight > 5.0) && ($chargeable_weight <= 10.0)){
                                $shipment_price_economical_air = 131.0;
                            } else {
                                if (($chargeable_weight > 10.0) && ($chargeable_weight <= 15.0)){
                                    $shipment_price_economical_air = 158.0;
                                    } else				 
                                if (($chargeable_weight > 15.0) && ($chargeable_weight <= 20.0)){
                                    $shipment_price_economical_air = 185.0;
                                    } else				 
                                if (($chargeable_weight > 20.0) && ($chargeable_weight <= 25.0)){
                                    $shipment_price_economical_air = 205.0;
                                    }
                                    else				 
                                if (($chargeable_weight > 25.0) && ($chargeable_weight <= 30.0)){
                                    $shipment_price_economical_air = 232.0;
                                    }
                                    else				 
                                if (($chargeable_weight > 30.0) && ($chargeable_weight <= 35.0)){
                                    $shipment_price_economical_air = 257.0;
                                    }
                                    else				 
                                if (($chargeable_weight > 35.0) && ($chargeable_weight <= 40.0)){
                                    $shipment_price_economical_air = 280.0;
                                    }
                                    else				 
                                if (($chargeable_weight > 40.0) && ($chargeable_weight <= 45.0)){
                                    $shipment_price_economical_air = 295.0;
                                    }
                                    else				 
                                if (($chargeable_weight > 45.0) && ($chargeable_weight <= 50.0)){
                                    $shipment_price_economical_air = 313.0;
                                    }
                                    else				 
                                if (($chargeable_weight > 50.0) && ($chargeable_weight <= 55.0)){
                                    $shipment_price_economical_air = 334.0;
                                    }
                                    else				 
                                if (($chargeable_weight > 55.0) && ($chargeable_weight <= 60.0)){
                                    $shipment_price_economical_air = 349.0;
                                    }
                                    else				 
                                if (($chargeable_weight >60.0) && ($chargeable_weight <= 65.0)){
                                    $shipment_price_economical_air = 370.0;
                                    }
                                    else				 
                                if (($chargeable_weight > 65.0) && ($chargeable_weight <= 70.0)){
                                    $shipment_price_economical_air = 385.0;
                                    }
                                    else				 
                                if (($chargeable_weight > 70.0) && ($chargeable_weight <= 75.0)){
                                    $shipment_price_economical_air = 442.0;
                                    }
                                    else				 
                                if (($chargeable_weight > 75.0) && ($chargeable_weight <= 80.0)){
                                    $shipment_price_economical_air = 462.0;
                                    }
                                    else				 
                                if (($chargeable_weight > 80.0) && ($chargeable_weight <= 85.0)){
                                    $shipment_price_economical_air = 473.0;
                                    }
                                    else				 
                                if (($chargeable_weight > 85.0) && ($chargeable_weight <= 90.0)){
                                    $shipment_price_economical_air = 489.0;
                                    }
                                    else				 
                                if (($chargeable_weight > 90.0) && ($chargeable_weight <= 95.0)){
                                    $shipment_price_economical_air = 504.0;
                                    } 
                                    else				 
                                if (($chargeable_weight > 95.0) && ($chargeable_weight <= 100.0)){
                                    $shipment_price_economical_air = 550.0;
                                    } 
                                    else
                                        $shipment_price_economical_air = $chargeable_weight * 5.40;
                            }
                            $expected_delivery_show_economical_air = $expected_delivery_show_10Days;
                            
                            if(isset($shipment_prices[$delivery_option_5Days]['price'])){
                                $shipment_prices[$delivery_option_5Days]['price'] = $shipment_prices[$delivery_option_5Days]['price'] + $shipment_price_economical_air;
                                $shipment_prices[$delivery_option_5Days]['commitment'] = $expected_delivery_show_economical_air;
            
                                $shipment_prices[$delivery_option_15Days]['price'] = $shipment_prices[$delivery_option_15Days]['price'] + $shipment_price_reg_boat;
                                $shipment_prices[$delivery_option_15Days]['commitment'] = $expected_delivery_show_reg_boat;
                            }else{
                                $shipment_prices[$delivery_option_5Days]['price'] = $shipment_price_economical_air;
                                $shipment_prices[$delivery_option_5Days]['commitment'] = $expected_delivery_show_economical_air;
        
                                $shipment_prices[$delivery_option_15Days]['price'] = $shipment_price_reg_boat;
                                $shipment_prices[$delivery_option_15Days]['commitment'] = $expected_delivery_show_reg_boat;
                            }
                        break;
                        
                        
                        
                        // ARUBA FOR THIRD PARTY
                        case ("AW"):
                                        
                            if ($cubic_feet_rounded < 6.0){
                                $shipment_price_reg_boat = 105.0;
                            }
                            else {
                                $shipment_price_reg_boat = $cubic_feet_rounded * 20.00;
                            }
                            $expected_delivery_show_reg_boat = $expected_delivery_show_20Days;
                                        
                                        
                                        
                            // REGULAR BOX PRICE FOR AIR
                            if (($chargeable_weight >= 0.0) && ($chargeable_weight <= 5.0)){
                                $shipment_price_economical_air = 100.0;
                                } else
                            if (($chargeable_weight > 5.0) && ($chargeable_weight <= 10.0)){
                                $shipment_price_economical_air = 131.0;
                            } else {
                                if (($chargeable_weight > 10.0) && ($chargeable_weight <= 15.0)){
                                    $shipment_price_economical_air = 158.0;
                                    } else				 
                                if (($chargeable_weight > 15.0) && ($chargeable_weight <= 20.0)){
                                    $shipment_price_economical_air = 185.0;
                                    } else				 
                                if (($chargeable_weight > 20.0) && ($chargeable_weight <= 25.0)){
                                    $shipment_price_economical_air = 205.0;
                                    }
                                    else				 
                                if (($chargeable_weight > 25.0) && ($chargeable_weight <= 30.0)){
                                    $shipment_price_economical_air = 232.0;
                                    }
                                    else				 
                                if (($chargeable_weight > 30.0) && ($chargeable_weight <= 35.0)){
                                    $shipment_price_economical_air = 257.0;
                                    }
                                    else				 
                                if (($chargeable_weight > 35.0) && ($chargeable_weight <= 40.0)){
                                    $shipment_price_economical_air = 280.0;
                                    }
                                    else				 
                                if (($chargeable_weight > 40.0) && ($chargeable_weight <= 45.0)){
                                    $shipment_price_economical_air = 295.0;
                                    }
                                    else				 
                                if (($chargeable_weight > 45.0) && ($chargeable_weight <= 50.0)){
                                    $shipment_price_economical_air = 313.0;
                                    }
                                    else				 
                                if (($chargeable_weight > 50.0) && ($chargeable_weight <= 55.0)){
                                    $shipment_price_economical_air = 334.0;
                                    }
                                    else				 
                                if (($chargeable_weight > 55.0) && ($chargeable_weight <= 60.0)){
                                    $shipment_price_economical_air = 349.0;
                                    }
                                    else				 
                                if (($chargeable_weight >60.0) && ($chargeable_weight <= 65.0)){
                                    $shipment_price_economical_air = 370.0;
                                    }
                                    else				 
                                if (($chargeable_weight > 65.0) && ($chargeable_weight <= 70.0)){
                                    $shipment_price_economical_air = 385.0;
                                    }
                                    else				 
                                if (($chargeable_weight > 70.0) && ($chargeable_weight <= 75.0)){
                                    $shipment_price_economical_air = 442.0;
                                    }
                                    else				 
                                if (($chargeable_weight > 75.0) && ($chargeable_weight <= 80.0)){
                                    $shipment_price_economical_air = 462.0;
                                    }
                                    else				 
                                if (($chargeable_weight > 80.0) && ($chargeable_weight <= 85.0)){
                                    $shipment_price_economical_air = 473.0;
                                    }
                                    else				 
                                if (($chargeable_weight > 85.0) && ($chargeable_weight <= 90.0)){
                                    $shipment_price_economical_air = 489.0;
                                    }
                                    else				 
                                if (($chargeable_weight > 90.0) && ($chargeable_weight <= 95.0)){
                                    $shipment_price_economical_air = 504.0;
                                    } 
                                    else				 
                                if (($chargeable_weight > 95.0) && ($chargeable_weight <= 100.0)){
                                    $shipment_price_economical_air = 550.0;
                                    } 
                                    else
                                        $shipment_price_economical_air = $chargeable_weight * 5.40;
                            }
                            $expected_delivery_show_economical_air = $expected_delivery_show_10Days;
                                        
                            if(isset($shipment_prices[$delivery_option_5Days]['price'])){
                                $shipment_prices[$delivery_option_5Days]['price'] = $shipment_prices[$delivery_option_5Days]['price'] + $shipment_price_economical_air;
                                $shipment_prices[$delivery_option_5Days]['commitment'] = $expected_delivery_show_economical_air;
            
                                $shipment_prices[$delivery_option_15Days]['price'] = $shipment_prices[$delivery_option_15Days]['price'] + $shipment_price_reg_boat;
                                $shipment_prices[$delivery_option_15Days]['commitment'] = $expected_delivery_show_reg_boat;
                            }else{
                                $shipment_prices[$delivery_option_5Days]['price'] = $shipment_price_economical_air;
                                $shipment_prices[$delivery_option_5Days]['commitment'] = $expected_delivery_show_economical_air;
        
                                $shipment_prices[$delivery_option_15Days]['price'] = $shipment_price_reg_boat;
                                $shipment_prices[$delivery_option_15Days]['commitment'] = $expected_delivery_show_reg_boat;
                            }
                                                            
                        break;
                        
                        
                        
                        // BAHAMAS FOR THIRD PARTY
                        case ("BS"):
                        
                                        
                            if ($cubic_feet_rounded < 6.0){
                                $shipment_price_reg_boat = 105.0;
                            }else {
                                $shipment_price_reg_boat = $cubic_feet_rounded * 20.00;
                            }
                            $expected_delivery_show_reg_boat = $expected_delivery_show_20Days;
                                        
                                        // REGULAR BOX PRICE FOR AIR
                            if (($chargeable_weight >= 0.0) && ($chargeable_weight <= 5.0)){
                                $shipment_price_economical_air = 100.0;
                                } else
                            if (($chargeable_weight > 5.0) && ($chargeable_weight <= 10.0)){
                                $shipment_price_economical_air = 131.0;
                            } else {
                                if (($chargeable_weight > 10.0) && ($chargeable_weight <= 15.0)){
                                    $shipment_price_economical_air = 158.0;
                                    } else				 
                                if (($chargeable_weight > 15.0) && ($chargeable_weight <= 20.0)){
                                    $shipment_price_economical_air = 185.0;
                                    } else				 
                                if (($chargeable_weight > 20.0) && ($chargeable_weight <= 25.0)){
                                    $shipment_price_economical_air = 205.0;
                                    }
                                    else				 
                                if (($chargeable_weight > 25.0) && ($chargeable_weight <= 30.0)){
                                    $shipment_price_economical_air = 232.0;
                                    }
                                    else				 
                                if (($chargeable_weight > 30.0) && ($chargeable_weight <= 35.0)){
                                    $shipment_price_economical_air = 257.0;
                                    }
                                    else				 
                                if (($chargeable_weight > 35.0) && ($chargeable_weight <= 40.0)){
                                    $shipment_price_economical_air = 280.0;
                                    }
                                    else				 
                                if (($chargeable_weight > 40.0) && ($chargeable_weight <= 45.0)){
                                    $shipment_price_economical_air = 295.0;
                                    }
                                    else				 
                                if (($chargeable_weight > 45.0) && ($chargeable_weight <= 50.0)){
                                    $shipment_price_economical_air = 313.0;
                                    }
                                    else				 
                                if (($chargeable_weight > 50.0) && ($chargeable_weight <= 55.0)){
                                    $shipment_price_economical_air = 334.0;
                                    }
                                    else				 
                                if (($chargeable_weight > 55.0) && ($chargeable_weight <= 60.0)){
                                    $shipment_price_economical_air = 349.0;
                                    }
                                    else				 
                                if (($chargeable_weight >60.0) && ($chargeable_weight <= 65.0)){
                                    $shipment_price_economical_air = 370.0;
                                    }
                                    else				 
                                if (($chargeable_weight > 65.0) && ($chargeable_weight <= 70.0)){
                                    $shipment_price_economical_air = 385.0;
                                    }
                                    else				 
                                if (($chargeable_weight > 70.0) && ($chargeable_weight <= 75.0)){
                                    $shipment_price_economical_air = 442.0;
                                    }
                                    else				 
                                if (($chargeable_weight > 75.0) && ($chargeable_weight <= 80.0)){
                                    $shipment_price_economical_air = 462.0;
                                    }
                                    else				 
                                if (($chargeable_weight > 80.0) && ($chargeable_weight <= 85.0)){
                                    $shipment_price_economical_air = 473.0;
                                    }
                                    else				 
                                if (($chargeable_weight > 85.0) && ($chargeable_weight <= 90.0)){
                                    $shipment_price_economical_air = 489.0;
                                    }
                                    else				 
                                if (($chargeable_weight > 90.0) && ($chargeable_weight <= 95.0)){
                                    $shipment_price_economical_air = 504.0;
                                    } 
                                    else				 
                                if (($chargeable_weight > 95.0) && ($chargeable_weight <= 100.0)){
                                    $shipment_price_economical_air = 550.0;
                                    } 
                                    else
                                        $shipment_price_economical_air = $chargeable_weight * 5.40;
                            }
                            $expected_delivery_show_economical_air = $expected_delivery_show_10Days;
                                        
                            if(isset($shipment_prices[$delivery_option_5Days]['price'])){
                                $shipment_prices[$delivery_option_5Days]['price'] = $shipment_prices[$delivery_option_5Days]['price'] + $shipment_price_economical_air;
                                $shipment_prices[$delivery_option_5Days]['commitment'] = $expected_delivery_show_economical_air;
            
                                $shipment_prices[$delivery_option_15Days]['price'] = $shipment_prices[$delivery_option_15Days]['price'] + $shipment_price_reg_boat;
                                $shipment_prices[$delivery_option_15Days]['commitment'] = $expected_delivery_show_reg_boat;
                            }else{
                                $shipment_prices[$delivery_option_5Days]['price'] = $shipment_price_economical_air;
                                $shipment_prices[$delivery_option_5Days]['commitment'] = $expected_delivery_show_economical_air;
        
                                $shipment_prices[$delivery_option_15Days]['price'] = $shipment_price_reg_boat;
                                $shipment_prices[$delivery_option_15Days]['commitment'] = $expected_delivery_show_reg_boat;
                            }

                        break;
                        
                        
                        
                        // MARTINIQUE FOR THIRD PARTY
                        case ("MQ"):
                                
                                        
                            if ($cubic_feet_rounded < 6.0){
                                $shipment_price_reg_boat = 105.0;
                            }else {
                                $shipment_price_reg_boat = $cubic_feet_rounded * 20.00;
                            }
                            $expected_delivery_show_reg_boat = $expected_delivery_show_20Days;
                            
                                     
                                        // REGULAR BOX PRICE FOR AIR
                            if (($chargeable_weight >= 0.0) && ($chargeable_weight <= 5.0)){
                                $shipment_price_economical_air = 100.0;
                            } else
                            if (($chargeable_weight > 5.0) && ($chargeable_weight <= 10.0)){
                                $shipment_price_economical_air = 131.0;
                            } else {
                                if (($chargeable_weight > 10.0) && ($chargeable_weight <= 15.0)){
                                    $shipment_price_economical_air = 158.0;
                                    } else				 
                                if (($chargeable_weight > 15.0) && ($chargeable_weight <= 20.0)){
                                    $shipment_price_economical_air = 185.0;
                                    } else				 
                                if (($chargeable_weight > 20.0) && ($chargeable_weight <= 25.0)){
                                    $shipment_price_economical_air = 205.0;
                                    }
                                    else				 
                                if (($chargeable_weight > 25.0) && ($chargeable_weight <= 30.0)){
                                    $shipment_price_economical_air = 232.0;
                                    }
                                    else				 
                                if (($chargeable_weight > 30.0) && ($chargeable_weight <= 35.0)){
                                    $shipment_price_economical_air = 257.0;
                                    }
                                    else				 
                                if (($chargeable_weight > 35.0) && ($chargeable_weight <= 40.0)){
                                    $shipment_price_economical_air = 280.0;
                                    }
                                    else				 
                                if (($chargeable_weight > 40.0) && ($chargeable_weight <= 45.0)){
                                    $shipment_price_economical_air = 295.0;
                                    }
                                    else				 
                                if (($chargeable_weight > 45.0) && ($chargeable_weight <= 50.0)){
                                    $shipment_price_economical_air = 313.0;
                                    }
                                    else				 
                                if (($chargeable_weight > 50.0) && ($chargeable_weight <= 55.0)){
                                    $shipment_price_economical_air = 334.0;
                                    }
                                    else				 
                                if (($chargeable_weight > 55.0) && ($chargeable_weight <= 60.0)){
                                    $shipment_price_economical_air = 349.0;
                                    }
                                    else				 
                                if (($chargeable_weight >60.0) && ($chargeable_weight <= 65.0)){
                                    $shipment_price_economical_air = 370.0;
                                    }
                                    else				 
                                if (($chargeable_weight > 65.0) && ($chargeable_weight <= 70.0)){
                                    $shipment_price_economical_air = 385.0;
                                    }
                                    else				 
                                if (($chargeable_weight > 70.0) && ($chargeable_weight <= 75.0)){
                                    $shipment_price_economical_air = 442.0;
                                    }
                                    else				 
                                if (($chargeable_weight > 75.0) && ($chargeable_weight <= 80.0)){
                                    $shipment_price_economical_air = 462.0;
                                    }
                                    else				 
                                if (($chargeable_weight > 80.0) && ($chargeable_weight <= 85.0)){
                                    $shipment_price_economical_air = 473.0;
                                    }
                                    else				 
                                if (($chargeable_weight > 85.0) && ($chargeable_weight <= 90.0)){
                                    $shipment_price_economical_air = 489.0;
                                    }
                                    else				 
                                if (($chargeable_weight > 90.0) && ($chargeable_weight <= 95.0)){
                                    $shipment_price_economical_air = 504.0;
                                    } 
                                    else				 
                                if (($chargeable_weight > 95.0) && ($chargeable_weight <= 100.0)){
                                    $shipment_price_economical_air = 550.0;
                                    } 
                                    else
                                        $shipment_price_economical_air = $chargeable_weight * 5.40;
                            }
                            $expected_delivery_show_economical_air = $expected_delivery_show_10Days;
                                               
                            if(isset($shipment_prices[$delivery_option_5Days]['price'])){
                                $shipment_prices[$delivery_option_5Days]['price'] = $shipment_prices[$delivery_option_5Days]['price'] + $shipment_price_economical_air;
                                $shipment_prices[$delivery_option_5Days]['commitment'] = $expected_delivery_show_economical_air;
            
                                $shipment_prices[$delivery_option_15Days]['price'] = $shipment_prices[$delivery_option_15Days]['price'] + $shipment_price_reg_boat;
                                $shipment_prices[$delivery_option_15Days]['commitment'] = $expected_delivery_show_reg_boat;
                            }else{
                                $shipment_prices[$delivery_option_5Days]['price'] = $shipment_price_economical_air;
                                $shipment_prices[$delivery_option_5Days]['commitment'] = $expected_delivery_show_economical_air;
        
                                $shipment_prices[$delivery_option_15Days]['price'] = $shipment_price_reg_boat;
                                $shipment_prices[$delivery_option_15Days]['commitment'] = $expected_delivery_show_reg_boat;
                            }
                        break;
                        
                        
                        // ST. MAARTEN FOR THIRD PARTY
                        case ("MF"):
                                        
                            if ($cubic_feet_rounded < 6.0){
                                $shipment_price_reg_boat = 105.0;
                            }
                            else {
                                $shipment_price_reg_boat = $cubic_feet_rounded * 20.00;
                            }
                            $expected_delivery_show_reg_boat = $expected_delivery_show_20Days;
                                        
                                        
                                    // REGULAR BOX PRICE FOR AIR
                            if (($chargeable_weight >= 0.0) && ($chargeable_weight <= 5.0)){
                                $shipment_price_economical_air = 100.0;
                            } else
                            if (($chargeable_weight > 5.0) && ($chargeable_weight <= 10.0)){
                                $shipment_price_economical_air = 131.0;
                            } else {
                                if (($chargeable_weight > 10.0) && ($chargeable_weight <= 15.0)){
                                    $shipment_price_economical_air = 158.0;
                                    } else				 
                                if (($chargeable_weight > 15.0) && ($chargeable_weight <= 20.0)){
                                    $shipment_price_economical_air = 185.0;
                                    } else				 
                                if (($chargeable_weight > 20.0) && ($chargeable_weight <= 25.0)){
                                    $shipment_price_economical_air = 205.0;
                                    }
                                    else				 
                                if (($chargeable_weight > 25.0) && ($chargeable_weight <= 30.0)){
                                    $shipment_price_economical_air = 232.0;
                                    }
                                    else				 
                                if (($chargeable_weight > 30.0) && ($chargeable_weight <= 35.0)){
                                    $shipment_price_economical_air = 257.0;
                                    }
                                    else				 
                                if (($chargeable_weight > 35.0) && ($chargeable_weight <= 40.0)){
                                    $shipment_price_economical_air = 280.0;
                                    }
                                    else				 
                                if (($chargeable_weight > 40.0) && ($chargeable_weight <= 45.0)){
                                    $shipment_price_economical_air = 295.0;
                                    }
                                    else				 
                                if (($chargeable_weight > 45.0) && ($chargeable_weight <= 50.0)){
                                    $shipment_price_economical_air = 313.0;
                                    }
                                    else				 
                                if (($chargeable_weight > 50.0) && ($chargeable_weight <= 55.0)){
                                    $shipment_price_economical_air = 334.0;
                                    }
                                    else				 
                                if (($chargeable_weight > 55.0) && ($chargeable_weight <= 60.0)){
                                    $shipment_price_economical_air = 349.0;
                                    }
                                    else				 
                                if (($chargeable_weight >60.0) && ($chargeable_weight <= 65.0)){
                                    $shipment_price_economical_air = 370.0;
                                    }
                                    else				 
                                if (($chargeable_weight > 65.0) && ($chargeable_weight <= 70.0)){
                                    $shipment_price_economical_air = 385.0;
                                    }
                                    else				 
                                if (($chargeable_weight > 70.0) && ($chargeable_weight <= 75.0)){
                                    $shipment_price_economical_air = 442.0;
                                    }
                                    else				 
                                if (($chargeable_weight > 75.0) && ($chargeable_weight <= 80.0)){
                                    $shipment_price_economical_air = 462.0;
                                    }
                                    else				 
                                if (($chargeable_weight > 80.0) && ($chargeable_weight <= 85.0)){
                                    $shipment_price_economical_air = 473.0;
                                    }
                                    else				 
                                if (($chargeable_weight > 85.0) && ($chargeable_weight <= 90.0)){
                                    $shipment_price_economical_air = 489.0;
                                    }
                                    else				 
                                if (($chargeable_weight > 90.0) && ($chargeable_weight <= 95.0)){
                                    $shipment_price_economical_air = 504.0;
                                    } 
                                    else				 
                                if (($chargeable_weight > 95.0) && ($chargeable_weight <= 100.0)){
                                    $shipment_price_economical_air = 550.0;
                                    } 
                                    else
                                        $shipment_price_economical_air = $chargeable_weight * 5.40;
                            }
                            $expected_delivery_show_economical_air = $expected_delivery_show_10Days;
                                                
                            if(isset($shipment_prices[$delivery_option_5Days]['price'])){
                                $shipment_prices[$delivery_option_5Days]['price'] = $shipment_prices[$delivery_option_5Days]['price'] + $shipment_price_economical_air;
                                $shipment_prices[$delivery_option_5Days]['commitment'] = $expected_delivery_show_economical_air;
            
                                $shipment_prices[$delivery_option_15Days]['price'] = $shipment_prices[$delivery_option_15Days]['price'] + $shipment_price_reg_boat;
                                $shipment_prices[$delivery_option_15Days]['commitment'] = $expected_delivery_show_reg_boat;
                            }else{
                                $shipment_prices[$delivery_option_5Days]['price'] = $shipment_price_economical_air;
                                $shipment_prices[$delivery_option_5Days]['commitment'] = $expected_delivery_show_economical_air;
        
                                $shipment_prices[$delivery_option_15Days]['price'] = $shipment_price_reg_boat;
                                $shipment_prices[$delivery_option_15Days]['commitment'] = $expected_delivery_show_reg_boat;
                            }
                        break;
                        
                        
                        // GUADELOUPE FOR THIRD PARTY
                        case ("GP"):
                                        
                            if ($cubic_feet_rounded < 6.0){
                                $shipment_price_reg_boat = 105.0;
                            }else {
                                $shipment_price_reg_boat = $cubic_feet_rounded * 20.00;
                            }
                            $expected_delivery_show_reg_boat = $expected_delivery_show_20Days;
                            
                                      
                                        // REGULAR BOX PRICE FOR AIR
                            if (($chargeable_weight >= 0.0) && ($chargeable_weight <= 5.0)){
                                $shipment_price_economical_air = 100.0;
                                } else
                            if (($chargeable_weight > 5.0) && ($chargeable_weight <= 10.0)){
                                $shipment_price_economical_air = 131.0;
                            } else {
                                if (($chargeable_weight > 10.0) && ($chargeable_weight <= 15.0)){
                                    $shipment_price_economical_air = 158.0;
                                    } else				 
                                if (($chargeable_weight > 15.0) && ($chargeable_weight <= 20.0)){
                                    $shipment_price_economical_air = 185.0;
                                    } else				 
                                if (($chargeable_weight > 20.0) && ($chargeable_weight <= 25.0)){
                                    $shipment_price_economical_air = 205.0;
                                    }
                                    else				 
                                if (($chargeable_weight > 25.0) && ($chargeable_weight <= 30.0)){
                                    $shipment_price_economical_air = 232.0;
                                    }
                                    else				 
                                if (($chargeable_weight > 30.0) && ($chargeable_weight <= 35.0)){
                                    $shipment_price_economical_air = 257.0;
                                    }
                                    else				 
                                if (($chargeable_weight > 35.0) && ($chargeable_weight <= 40.0)){
                                    $shipment_price_economical_air = 280.0;
                                    }
                                    else				 
                                if (($chargeable_weight > 40.0) && ($chargeable_weight <= 45.0)){
                                    $shipment_price_economical_air = 295.0;
                                    }
                                    else				 
                                if (($chargeable_weight > 45.0) && ($chargeable_weight <= 50.0)){
                                    $shipment_price_economical_air = 313.0;
                                    }
                                    else				 
                                if (($chargeable_weight > 50.0) && ($chargeable_weight <= 55.0)){
                                    $shipment_price_economical_air = 334.0;
                                    }
                                    else				 
                                if (($chargeable_weight > 55.0) && ($chargeable_weight <= 60.0)){
                                    $shipment_price_economical_air = 349.0;
                                    }
                                    else				 
                                if (($chargeable_weight >60.0) && ($chargeable_weight <= 65.0)){
                                    $shipment_price_economical_air = 370.0;
                                    }
                                    else				 
                                if (($chargeable_weight > 65.0) && ($chargeable_weight <= 70.0)){
                                    $shipment_price_economical_air = 385.0;
                                    }
                                    else				 
                                if (($chargeable_weight > 70.0) && ($chargeable_weight <= 75.0)){
                                    $shipment_price_economical_air = 442.0;
                                    }
                                    else				 
                                if (($chargeable_weight > 75.0) && ($chargeable_weight <= 80.0)){
                                    $shipment_price_economical_air = 462.0;
                                    }
                                    else				 
                                if (($chargeable_weight > 80.0) && ($chargeable_weight <= 85.0)){
                                    $shipment_price_economical_air = 473.0;
                                    }
                                    else				 
                                if (($chargeable_weight > 85.0) && ($chargeable_weight <= 90.0)){
                                    $shipment_price_economical_air = 489.0;
                                    }
                                    else				 
                                if (($chargeable_weight > 90.0) && ($chargeable_weight <= 95.0)){
                                    $shipment_price_economical_air = 504.0;
                                    } 
                                    else				 
                                if (($chargeable_weight > 95.0) && ($chargeable_weight <= 100.0)){
                                    $shipment_price_economical_air = 550.0;
                                    } 
                                    else
                                        $shipment_price_economical_air = $chargeable_weight * 5.40;
                            }
                            $expected_delivery_show_economical_air = $expected_delivery_show_10Days;
                                                    
                            if(isset($shipment_prices[$delivery_option_5Days]['price'])){
                                $shipment_prices[$delivery_option_5Days]['price'] = $shipment_prices[$delivery_option_5Days]['price'] + $shipment_price_economical_air;
                                $shipment_prices[$delivery_option_5Days]['commitment'] = $expected_delivery_show_economical_air;
            
                                $shipment_prices[$delivery_option_15Days]['price'] = $shipment_prices[$delivery_option_15Days]['price'] + $shipment_price_reg_boat;
                                $shipment_prices[$delivery_option_15Days]['commitment'] = $expected_delivery_show_reg_boat;
                            }else{
                                $shipment_prices[$delivery_option_5Days]['price'] = $shipment_price_economical_air;
                                $shipment_prices[$delivery_option_5Days]['commitment'] = $expected_delivery_show_economical_air;
        
                                $shipment_prices[$delivery_option_15Days]['price'] = $shipment_price_reg_boat;
                                $shipment_prices[$delivery_option_15Days]['commitment'] = $expected_delivery_show_reg_boat;
                            }
                        break;
                        
                        
                        // DOMINICA FOR THIRD PARTY
                        case ("DM"):
                                        
                            if ($cubic_feet_rounded < 6.0){
                                $shipment_price_reg_boat = 105.0;
                            }else {
                                $shipment_price_reg_boat = $cubic_feet_rounded * 20.00;
                            }
                            $expected_delivery_show_reg_boat = $expected_delivery_show_20Days;
                                        
                                        
                                        
                                // REGULAR BOX PRICE FOR AIR
                            if (($chargeable_weight >= 0.0) && ($chargeable_weight <= 5.0)){
                                $shipment_price_economical_air = 100.0;
                            } elseif (($chargeable_weight > 5.0) && ($chargeable_weight <= 10.0)){
                                $shipment_price_economical_air = 131.0;
                            } else {
                                if (($chargeable_weight > 10.0) && ($chargeable_weight <= 15.0)){
                                    $shipment_price_economical_air = 158.0;
                                } else				 
                                if (($chargeable_weight > 15.0) && ($chargeable_weight <= 20.0)){
                                    $shipment_price_economical_air = 185.0;
                                } else				 
                                if (($chargeable_weight > 20.0) && ($chargeable_weight <= 25.0)){
                                    $shipment_price_economical_air = 205.0;
                                    }
                                    else				 
                                if (($chargeable_weight > 25.0) && ($chargeable_weight <= 30.0)){
                                    $shipment_price_economical_air = 232.0;
                                    }
                                    else				 
                                if (($chargeable_weight > 30.0) && ($chargeable_weight <= 35.0)){
                                    $shipment_price_economical_air = 257.0;
                                    }
                                    else				 
                                if (($chargeable_weight > 35.0) && ($chargeable_weight <= 40.0)){
                                    $shipment_price_economical_air = 280.0;
                                    }
                                    else				 
                                if (($chargeable_weight > 40.0) && ($chargeable_weight <= 45.0)){
                                    $shipment_price_economical_air = 295.0;
                                    }
                                    else				 
                                if (($chargeable_weight > 45.0) && ($chargeable_weight <= 50.0)){
                                    $shipment_price_economical_air = 313.0;
                                    }
                                    else				 
                                if (($chargeable_weight > 50.0) && ($chargeable_weight <= 55.0)){
                                    $shipment_price_economical_air = 334.0;
                                    }
                                    else				 
                                if (($chargeable_weight > 55.0) && ($chargeable_weight <= 60.0)){
                                    $shipment_price_economical_air = 349.0;
                                    }
                                    else				 
                                if (($chargeable_weight >60.0) && ($chargeable_weight <= 65.0)){
                                    $shipment_price_economical_air = 370.0;
                                    }
                                    else				 
                                if (($chargeable_weight > 65.0) && ($chargeable_weight <= 70.0)){
                                    $shipment_price_economical_air = 385.0;
                                    }
                                    else				 
                                if (($chargeable_weight > 70.0) && ($chargeable_weight <= 75.0)){
                                    $shipment_price_economical_air = 442.0;
                                    }
                                    else				 
                                if (($chargeable_weight > 75.0) && ($chargeable_weight <= 80.0)){
                                    $shipment_price_economical_air = 462.0;
                                    }
                                    else				 
                                if (($chargeable_weight > 80.0) && ($chargeable_weight <= 85.0)){
                                    $shipment_price_economical_air = 473.0;
                                    }
                                    else				 
                                if (($chargeable_weight > 85.0) && ($chargeable_weight <= 90.0)){
                                    $shipment_price_economical_air = 489.0;
                                    }
                                    else				 
                                if (($chargeable_weight > 90.0) && ($chargeable_weight <= 95.0)){
                                    $shipment_price_economical_air = 504.0;
                                    } 
                                    else				 
                                if (($chargeable_weight > 95.0) && ($chargeable_weight <= 100.0)){
                                    $shipment_price_economical_air = 550.0;
                                    } 
                                    else
                                        $shipment_price_economical_air = $chargeable_weight * 5.40;
                            }
                            $expected_delivery_show_economical_air = $expected_delivery_show_10Days;
                                                                
                            if(isset($shipment_prices[$delivery_option_5Days]['price'])){
                                $shipment_prices[$delivery_option_5Days]['price'] = $shipment_prices[$delivery_option_5Days]['price'] + $shipment_price_economical_air;
                                $shipment_prices[$delivery_option_5Days]['commitment'] = $expected_delivery_show_economical_air;
            
                                $shipment_prices[$delivery_option_15Days]['price'] = $shipment_prices[$delivery_option_15Days]['price'] + $shipment_price_reg_boat;
                                $shipment_prices[$delivery_option_15Days]['commitment'] = $expected_delivery_show_reg_boat;
                            }else{
                                $shipment_prices[$delivery_option_5Days]['price'] = $shipment_price_economical_air;
                                $shipment_prices[$delivery_option_5Days]['commitment'] = $expected_delivery_show_economical_air;
        
                                $shipment_prices[$delivery_option_15Days]['price'] = $shipment_price_reg_boat;
                                $shipment_prices[$delivery_option_15Days]['commitment'] = $expected_delivery_show_reg_boat;
                            }
                        break;
                        
                        
                        // BOLIVIA FOR THIRD PARTY
                        case ("BO"):
                                        
                            if ($cubic_feet_rounded < 6.0){
                                $shipment_price_thirdParty = 105.0;
                            }
                            else {
                                $shipment_price_thirdParty = $cubic_feet_rounded * 20.00;
                            }
                            $expected_delivery_show_reg_boat = $expected_delivery_show_20Days;
                                        
                                        
                            // REGULAR BOX PRICE FOR AIR
                            if (($chargeable_weight >= 0.0) && ($chargeable_weight <= 5.0)){
                                $shipment_price_economical_air = 50.0;
                            } elseif (($chargeable_weight > 5.0) && ($chargeable_weight <= 10.0)){
                                $shipment_price_economical_air = 90.0;
                            } else {
                                if (($chargeable_weight > 10.0) && ($chargeable_weight <= 15.0)){
                                    $shipment_price_economical_air = 127.0;
                                    } else				 
                                if (($chargeable_weight > 15.0) && ($chargeable_weight <= 20.0)){
                                    $shipment_price_economical_air = 169.0;
                                    } else				 
                                if (($chargeable_weight > 20.0) && ($chargeable_weight <= 25.0)){
                                    $shipment_price_economical_air = 205.0;
                                    }
                                    else				 
                                if (($chargeable_weight > 25.0) && ($chargeable_weight <= 30.0)){
                                    $shipment_price_economical_air = 243.0;
                                    }
                                    else				 
                                if (($chargeable_weight > 30.0) && ($chargeable_weight <= 35.0)){
                                    $shipment_price_economical_air = 282.0;
                                    }
                                    else				 
                                if (($chargeable_weight > 35.0) && ($chargeable_weight <= 40.0)){
                                    $shipment_price_economical_air = 322.0;
                                    }
                                    else				 
                                if (($chargeable_weight > 40.0) && ($chargeable_weight <= 45.0)){
                                    $shipment_price_economical_air = 360.0;
                                    }
                                    else				 
                                if (($chargeable_weight > 45.0) && ($chargeable_weight <= 50.0)){
                                    $shipment_price_economical_air = 399.0;
                                    }
                                    else				 
                                if (($chargeable_weight > 50.0) && ($chargeable_weight <= 55.0)){
                                    $shipment_price_economical_air = 437.0;
                                    }
                                    else				 
                                if (($chargeable_weight > 55.0) && ($chargeable_weight <= 60.0)){
                                    $shipment_price_economical_air = 477.0;
                                    }
                                    else				 
                                if (($chargeable_weight >60.0) && ($chargeable_weight <= 65.0)){
                                    $shipment_price_economical_air = 514.0;
                                    }
                                    else				 
                                if (($chargeable_weight > 65.0) && ($chargeable_weight <= 70.0)){
                                    $shipment_price_economical_air = 554.0;
                                    }
                                    else				 
                                if (($chargeable_weight > 70.0) && ($chargeable_weight <= 75.0)){
                                    $shipment_price_economical_air = 592.0;
                                    }
                                    else				 
                                if (($chargeable_weight > 75.0) && ($chargeable_weight <= 80.0)){
                                    $shipment_price_economical_air = 631.0;
                                    }
                                    else				 
                                if (($chargeable_weight > 80.0) && ($chargeable_weight <= 85.0)){
                                    $shipment_price_economical_air = 669.0;
                                    }
                                    else				 
                                if (($chargeable_weight > 85.0) && ($chargeable_weight <= 90.0)){
                                    $shipment_price_economical_air = 709.0;
                                    }
                                    else				 
                                if (($chargeable_weight > 90.0) && ($chargeable_weight <= 95.0)){
                                    $shipment_price_economical_air = 747.0;
                                    } 
                                    else				 
                                if (($chargeable_weight > 95.0) && ($chargeable_weight <= 100.0)){
                                    $shipment_price_economical_air = 786.0;
                                    } 
                                    else
                                        $shipment_price_economical_air = $chargeable_weight * 7.80;
                            }
                            $expected_delivery_show_economical_air = $expected_delivery_show_10Days;
                                                
                            if(isset($shipment_prices[$delivery_option_5Days]['price'])){
                                $shipment_prices[$delivery_option_5Days]['price'] = $shipment_prices[$delivery_option_5Days]['price'] + $shipment_price_economical_air;
                                $shipment_prices[$delivery_option_5Days]['commitment'] = $expected_delivery_show_economical_air;
            
                                $shipment_prices[$delivery_option_15Days]['price'] = $shipment_prices[$delivery_option_15Days]['price'] + $shipment_price_reg_boat;
                                $shipment_prices[$delivery_option_15Days]['commitment'] = $expected_delivery_show_reg_boat;
                            }else{
                                $shipment_prices[$delivery_option_5Days]['price'] = $shipment_price_economical_air;
                                $shipment_prices[$delivery_option_5Days]['commitment'] = $expected_delivery_show_economical_air;
        
                                $shipment_prices[$delivery_option_15Days]['price'] = $shipment_price_reg_boat;
                                $shipment_prices[$delivery_option_15Days]['commitment'] = $expected_delivery_show_reg_boat;
                            }
                        break;
                        
                        
                        // GUATEMALA FOR THIRD PARTY
                        case ("CA"):
                        
                        
                                        
                        // REGULAR BOX PRICE FOR AIR
                            if (($chargeable_weight >= 0.0) && ($chargeable_weight <= 5.0)){
                                $shipment_price_economical_air = 100.0;
                                } else
                            if (($chargeable_weight > 5.0) && ($chargeable_weight <= 10.0)){
                                $shipment_price_economical_air = 135.0;
                            } else {
                                if (($chargeable_weight > 10.0) && ($chargeable_weight <= 15.0)){
                                    $shipment_price_economical_air = 155.0;
                                    } else				 
                                if (($chargeable_weight > 15.0) && ($chargeable_weight <= 20.0)){
                                    $shipment_price_economical_air = 170.0;
                                    } else				 
                                if (($chargeable_weight > 20.0) && ($chargeable_weight <= 25.0)){
                                    $shipment_price_economical_air = 190.0;
                                    }
                                    else				 
                                if (($chargeable_weight > 25.0) && ($chargeable_weight <= 30.0)){
                                    $shipment_price_economical_air = 200.0;
                                    }
                                    else				 
                                if (($chargeable_weight > 30.0) && ($chargeable_weight <= 35.0)){
                                    $shipment_price_economical_air = 210.0;
                                    }
                                    else				 
                                if (($chargeable_weight > 35.0) && ($chargeable_weight <= 40.0)){
                                    $shipment_price_economical_air = 225.0;
                                    }
                                    else				 
                                if (($chargeable_weight > 40.0) && ($chargeable_weight <= 45.0)){
                                    $shipment_price_economical_air = 250.0;
                                    }
                                    else				 
                                if (($chargeable_weight > 45.0) && ($chargeable_weight <= 50.0)){
                                    $shipment_price_economical_air = 270.0;
                                    }
                                    else				 
                                if (($chargeable_weight > 50.0) && ($chargeable_weight <= 55.0)){
                                    $shipment_price_economical_air = 290.0;
                                    }
                                    else				 
                                if (($chargeable_weight > 55.0) && ($chargeable_weight <= 60.0)){
                                    $shipment_price_economical_air = 300.0;
                                    }
                                    else				 
                                if (($chargeable_weight >60.0) && ($chargeable_weight <= 65.0)){
                                    $shipment_price_economical_air = 310.0;
                                    }
                                    else				 
                                if (($chargeable_weight > 65.0) && ($chargeable_weight <= 70.0)){
                                    $shipment_price_economical_air = 320.0;
                                    }
                                    else				 
                                if (($chargeable_weight > 70.0) && ($chargeable_weight <= 75.0)){
                                    $shipment_price_economical_air = 325.0;
                                    }
                                    else				 
                                if (($chargeable_weight > 75.0) && ($chargeable_weight <= 80.0)){
                                    $shipment_price_economical_air = 330.0;
                                    }
                                    else				 
                                if (($chargeable_weight > 80.0) && ($chargeable_weight <= 85.0)){
                                    $shipment_price_economical_air = 335.0;
                                    }
                                    else				 
                                if (($chargeable_weight > 85.0) && ($chargeable_weight <= 90.0)){
                                    $shipment_price_economical_air = 340.0;
                                    }
                                    else				 
                                if (($chargeable_weight > 90.0) && ($chargeable_weight <= 95.0)){
                                    $shipment_price_economical_air = 345.0;
                                    } 
                                    else				 
                                if (($chargeable_weight > 95.0) && ($chargeable_weight <= 100.0)){
                                    $shipment_price_economical_air = 350.0;
                                    } 
                                    else
                                        $shipment_price_economical_air = $chargeable_weight * 3.25;
                            }
                                    
                            $shipment_price_economical_air = $shipment_price_economical_air * 0.50;
                            $expected_delivery_show_economical_air = $expected_delivery_show_10Days;
                                                                    
                            $shipment_price_collect = $shipment_price_economical_air * 0.75;
                            $shipment_price_collect = $shipment_price_collect + ($package_value * 0.07);
                            $shipment_price_collect = $shipment_price_collect * 1.07;
                                    
                                    
                            if(isset($shipment_prices[$delivery_option_5Days]['price'])){
                                $shipment_prices[$delivery_option_5Days]['price'] = $shipment_prices[$delivery_option_5Days]['price'] + $shipment_price_economical_air;
                                $shipment_prices[$delivery_option_5Days]['commitment'] = $expected_delivery_show_economical_air;
            
                            }else{
                                $shipment_prices[$delivery_option_5Days]['price'] = $shipment_price_economical_air;
                                $shipment_prices[$delivery_option_5Days]['commitment'] = $expected_delivery_show_economical_air;
                            }
                                                            
                        break;
            }

            $home_delivery_fee = 0;

            //if($consignee_address_country != "HT"){
                $shipment_add_insurance = ($package_value * 0.07); 
                $shipment_add_insurance = number_format($shipment_add_insurance,2,'.','');
            //}else{
            if($consignee_address_country == "HT"){
                if(in_array(strtolower($consignee_address_city), array('port-au-prince','port au prince','delmas','petion-ville','petion ville','bon-repos','bon repos','tabarre'))){
                    if ($chargeable_weight <5){
                        $home_delivery_fee = 0.0;
                    }
                        
                        
                    if($delivery_location == "Home Delivery"){
                        $home_delivery_fee = $home_delivery_fee + 10.0;
                    }
                }else{
                    if($delivery_location == "Home Delivery"){
                        if ($chargeable_weight <2){
                            $home_delivery_fee = 15.00;
                        } else if (($chargeable_weight >=2) && ($chargeable_weight <10)){
                            $home_delivery_fee = 20.0;
                        }else if (($chargeable_weight >=10) && ($chargeable_weight <25)){
                            $home_delivery_fee = 25.0;
                        }else{	
                            $home_delivery_fee = 25.0 + ($chargeable_weight * 0.1);
                        }
                    
                    
                        $home_delivery_fee = $home_delivery_fee + 5.0 ;
                    }
                }
                $home_delivery_fee_total = $home_delivery_fee_total + $home_delivery_fee;
            }
        }

        // recall insurance fee
        
        
        $home_delivery_fee_total = number_format($home_delivery_fee_total,2,'.','');

        $current_user_id = auth()->user()->id;
        $consignee_id = '';
        if($request->consignees_id){
            $consignee_id = $request->consignees_id;
        }
        $k = 0;
        //dd($shipment_prices);

        
        foreach($shipment_prices as $del=>$shipment_price){
            $quotations .= '<div class="del-card"><div class="date-info"><figure class="deli-logo"><img src="themes/tailwind/images/logo.png" width="100px"></figure><div class="date_inn">';

                                
            $commitment = $shipment_price['commitment'];
            $freight = $shipment_price['price'];
            $price = $shipment_price['price'] + $shipment_add_insurance + $home_delivery_fee;
            $tax = ($price * 0.07); 
            $tax = number_format($tax,2,'.','');
            $price = $price + $tax;
            if ($commitment != '') {
                $quotations .= '<h3>ARRIVES ON</h3><p>'.$commitment.'</p>';
            }

            $quotations .= '</div></div>';

            $quotations .= '<ul class="del-item-list"><li><div class="del-head"><div class="del-time-info"><h3>DELIVERED BY</h3><span class="time"></span><p>11:59 PM<br>'.$del.'</p></div>';
            $latest_saved_quotation = '25'; //test
            $quotations .= '<div class="del-action"><a href="javascript:void(0);" data-url="/shipping/'.$current_user_id.'/'.$latest_saved_quotation.'/'.base64_encode('usps').'/'.base64_encode(preg_replace('@<(\w+)\b.*?>.*?</\1>@si', '', $del)).'/'.$consignee_id.'" class="cstm-btn do_ship">$'.number_format($price,2,'.','').'</a><a href="javascript:void(0);" class="del-toggle" data-tag="shipper_'.$k.'"><i class="fas fa-chevron-down"></i></a></div></div><ul id="shipper_'.$k.'" class="rate-listing" >';
            $quotations .= '<li><p>Freight</p><span class="price_in">$'.$freight.'</span></li>';
            $quotations .= '<li><p>Insurance</p><span class="price_in">$'.$shipment_add_insurance.'</span></li>';
            $quotations .= '<li><p>Home Delivery</p><span class="price_in">$'.$home_delivery_fee_total.'</span></li>';
            $quotations .= '<li><p>Tax</p><span class="price_in">$'.$tax.'</span></li>';
            $quotations .= '</ul></li></ul></div><hr><br>';

            $rates[] = [
                'currency'                      => 'USD',
                'total'                         => $price,
                'service_name'                  => preg_replace('@<(\w+)\b.*?>.*?</\1>@si', '', $del),
                'estimatedDeliveryDateAndTime'  => $commitment,
            ];
            $k++;
        }
        $response['html']   = $quotations;
        return $response;
    }

    public function getUspsRates($request){

        $response['status'] = 'error';

        $common_data = $this->common_data($request);

        $dimensions         = $common_data['dimensions'];

        $packages           = '';

        $loopCount = $request->package_count;

        $rates = [];

        if ($request->from_country != $request->to_country) { //International
            $country = Countries::where('alpha_2_code', $request->to_country)->first();
            for ($j=0; $j < $loopCount; $j++) { 
                $pno = $j + 1;
                $ordinal = strtoupper($this->ordinal($pno));
                if(trim($request->to_zip) != ''){
                    $zipRelData = '<AcceptanceDateTime>'. date('Y-m-d') .'T'. date('h:i:s').'-'.date('h:i',strtotime('06:00')) .'</AcceptanceDateTime>
                                    <DestinationPostalCode>'.$request->to_zip.'</DestinationPostalCode>';
                }else{
                    $zipRelData = '';
                }
                $package   =  '<Package ID="'.$ordinal. '">
                                    <Pounds>'. (float)$dimensions['weight'][$j] .'</Pounds>
                                    <Ounces>0</Ounces>
                                    <Machinable>True</Machinable>
                                    <MailType>FLATRATE</MailType>
                                    <GXG>
                                        <POBoxFlag>N</POBoxFlag>
                                        <GiftFlag>N</GiftFlag>
                                    </GXG>
                                    <ValueOfContents>'.$request->total_value.'</ValueOfContents>
                                    <Country>'.$country->country_name.'</Country>
                                    <Container>RECTANGULAR</Container>
                                    <Width>'. (float)$dimensions['width'][$j] .'</Width>
                                    <Length>'. (float)$dimensions['length'][$j] .'</Length>
                                    <Height>'. (float)$dimensions['height'][$j] .'</Height>
                                    <OriginZip>'.$request->from_zip.'</OriginZip>
                                    '.$zipRelData.'
                                </Package>';
                
                $packages = $packages.$package;
            }
            
            $xml        =       '<IntlRateV2Request USERID="'.$this->usps_username.'">
                                    <Revision>2</Revision>
                                    '.$packages.'
                                </IntlRateV2Request>';

            // URL
            $xmlencoded = urlencode($xml);
            if($this->usps_sandbox_enable){
                $apiURL = $this->usps_test_api_url.'?API=IntlRateV2&XML='.$xmlencoded; // (API URL and endpoint)
            }else{
                $apiURL = $this->usps_api_url.'?API=IntlRateV2&XML='.$xmlencoded; // (API URL and endpoint)
            }

        }else{ //Domestic
            for ($i=0; $i < $loopCount; $i++) { 
                $pno = $i + 1;
                $ordinal = strtoupper($this->ordinal($pno));
                $package   =  '<Package ID="'.$ordinal. '">
                                    <Service>ALL</Service>
                                    <FirstClassMailType>FLAT</FirstClassMailType>
                                    <ZipOrigination>'.$request->from_zip.'</ZipOrigination>
                                    <ZipDestination>'.$request->to_zip.'</ZipDestination>
                                    <Pounds>'. (float)$dimensions['weight'][$i] .'</Pounds>
                                    <Ounces>0</Ounces>
                                    <Container/>
                                    <Width>'. (float)$dimensions['width'][$i] .'</Width>
                                    <Length>'. (float)$dimensions['length'][$i] .'</Length>
                                    <Height>'. (float)$dimensions['height'][$i] .'</Height>
                                    <Machinable>true</Machinable>
                                    <DropOffTime>'.date('h:i', strtotime('+2 hours')).'</DropOffTime>
                                    <ShipDate>'.date('Y-m-d').'</ShipDate>
                                </Package>';
                
                $packages = $packages.$package;
            }
    
            
    
            $xml        =       '<RateV4Request USERID="'.$this->usps_username.'">
                                    <Revision>2</Revision>
                                    '.$packages.'
                                </RateV4Request>';
    
            // URL
            $xmlencoded = urlencode($xml);
            
            if($this->usps_sandbox_enable){
                $apiURL = $this->usps_test_api_url.'?API=RateV4&XML='.$xmlencoded; // (API URL and endpoint)
            }else{
                $apiURL = $this->usps_api_url.'?API=RateV4&XML='.$xmlencoded; // (API URL and endpoint)
            }
        }

       

        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => "$apiURL",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_HTTPHEADER => array('Content-Length :0')
        ));

        $curlresponse = curl_exec($curl);
        
        $statusCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);
        $simplexml  = simplexml_load_string($curlresponse);
        $json       = json_encode($simplexml);
        $responseArray      = json_decode($json,TRUE);

        //echo '<pre>';print_r($responseArray);die;

        $consignee_id = '';
        if($request->consignees_id){
            $consignee_id = $request->consignees_id;
        }

        /*
            Save Quote Request in DB - START
        */
        $latest_saved_quotation = \Session::get('latest_saved_quotation');
        $updateQuote = Quotation::find($latest_saved_quotation);
        $updateQuote->usps_response  = json_encode($responseArray);
        
        
        /*
            Save Quote Request in DB - END
        */

        $response['api_response'] = $curlresponse;
            
        if ($statusCode == 200) {
            $response['status'] = 'success';
            $quotations = '';
            $packageCount = $loopCount;
            if (auth()->id() != '') {
                $current_user_id = auth()->id(); 
            }else{
                $current_user_id = 'NULL';
            }
            
            

            if ($request->from_country != $request->to_country) { //HTML for International
                if($packageCount > 1){
                    $quotations .= '<div class="del-card"><div class="date-info"><figure class="deli-logo"><img src="https://logos-world.net/wp-content/uploads/2020/10/United-States-Postal-Service-Emblem.png"></figure></div>';
                
                    $quotations .= '<div class="del-action"><a href="javascript:void(0);" data-url="/shipping/'.$current_user_id.'/'.$latest_saved_quotation.'/'.base64_encode('usps').'/'.$consignee_id.'" class="cstm-btn do_ship">Details</a><a href="javascript:void(0);" class="del-toggle" data-tag="shipper_'.$packageCount.'"><i class="fas fa-chevron-down"></i></a></div></div><ul id="shipper_'.$packageCount.'" class="rate-listing">';

                    foreach ($responseArray['Package'] as $k => $Package) {
                        $quotations .= '<li style="text-align:center;"><div style="width:100%;">Package '.$Package['@attributes']['ID'].'</div></li>';
                        if(!isset($Package['Error'])){
                            foreach($Package['Service'] as $service){
                                
                                $commitment = $this->getStaticCommitment($service['SvcDescription']);

                                if ($commitment != '') {
                                    $quotations .= '<h3>ARRIVES ON</h3><p>'.$commitment.'</p>';
                                }
                                $quotations .= '<li><p>'.htmlspecialchars_decode($service['SvcDescription']).'</p><span class="price_in">$'.$service['Postage'].' <br><i>Delivery ETA - '.$commitment.'</i></span></li>';
                            }
                        }else{
                            $quotations .= '<li><p>'.$Package['Error']['Description'].'</p></li>';
                        }
                        
                    }
                }else{
                    
                    if(!isset($responseArray['Package']['Error'])){
                        foreach($responseArray['Package']['Service'] as $shippersCount=>$service){
                            
                            $quotations .= '<div class="del-card"><div class="date-info"><figure class="deli-logo"><img src="https://logos-world.net/wp-content/uploads/2020/10/United-States-Postal-Service-Emblem.png"></figure><div class="date_inn">';

                            
                            $commitment = $this->getStaticCommitment($service['SvcDescription']);
                            
                            if ($commitment != '') {
                                $quotations .= '<h3>ARRIVES ON</h3><p>'.$commitment.'</p>';
                            }
    
                            $quotations .= '</div></div>';
    
                            $quotations .= '<ul class="del-item-list"><li><div class="del-head"><div class="del-time-info"><h3>DELIVERED BY</h3><span class="time"></span><p>'.htmlspecialchars_decode($service['SvcDescription']).'</p></div>';

                            $quotations .= '<div class="del-action"><a href="javascript:void(0);" data-url="/shipping/'.$current_user_id.'/'.$latest_saved_quotation.'/'.base64_encode('usps').'/'.base64_encode(preg_replace('@<(\w+)\b.*?>.*?</\1>@si', '', htmlspecialchars_decode($service['SvcDescription']))).'/'.$consignee_id.'" class="cstm-btn do_ship">$'.$service['Postage'].'</a><a href="javascript:void(0);" class="del-toggle" data-tag="shipper_'.$shippersCount.'"><i class="fas fa-chevron-down" style="display:none;"></i></a></div></div><ul id="shipper_'.$shippersCount.'" class="rate-listing" style="display:none;">';

                            $quotations .= '</ul></li></ul></div><hr><br>';

                            $rates[] = [
                                'currency'                      => 'USD',
                                'total'                         => $service['Postage'],
                                'service_name'                  => preg_replace('@<(\w+)\b.*?>.*?</\1>@si', '', htmlspecialchars_decode($service['SvcDescription'])),
                                'estimatedDeliveryDateAndTime'  => $commitment,
                            ];
                        }
                    }else{
                        $quotations = '<div class="del-card"><figure class="deli-logo"><img src="https://logos-world.net/wp-content/uploads/2020/10/United-States-Postal-Service-Emblem.png"></figure><p>'.$responseArray['Package']['Error']['Description'].'</p></div>';
                    }
                }
                $quotations .= '</ul></li></ul></div><hr><br>';
            }else{ //HTML for Domestic
                if($packageCount > 1){
                    $quotations .= '<div class="del-card"><div class="date-info"><figure class="deli-logo"><img src="https://logos-world.net/wp-content/uploads/2020/10/United-States-Postal-Service-Emblem.png"></figure></div>';
                
                    $quotations .= '<div class="del-action"><a href="javascript:void(0);" data-url="/shipping/'.$current_user_id.'/'.$latest_saved_quotation.'/'.base64_encode('usps').'/'.$consignee_id.'" class="cstm-btn do_ship">Details</a><a href="javascript:void(0);" class="del-toggle" data-tag="shipper_'.$packageCount.'"><i class="fas fa-chevron-down"></i></a></div></div><ul id="shipper_'.$packageCount.'" class="rate-listing">';
                    if(isset($responseArray['Package'])){
                        foreach ($responseArray['Package'] as $k => $Package) {

                            $quotations .= '<li style="text-align:center;"><div style="width:100%;">Package '.$Package['@attributes']['ID'].'</div></li>';

                            if(!isset($Package['Error'])){
                                foreach($Package['Postage'] as $postage){
                                    $commitment = '';
                                    if(isset($postage['CommitmentDate'])){
                                        $commitment = date('D, M d', strtotime($postage['CommitmentDate']));
                                    }
                                    if ($commitment == '') {

                                        $commitment = $this->getStaticCommitment($postage['MailService']);

                                    }
                                    $quotations .= '<li><p>'.htmlspecialchars_decode($postage['MailService']).' <br><i>Delivery ETA - '.$commitment.'</i></p><span class="price_in">$'.$postage['Rate'].'</span></li>';
                                }
                            }else{
                                $quotations .= '<li><p>'.$Package['Error']['Description'].'</p></li>';
                            }
                            
                        }
                        $quotations .= '</ul></li></ul></div><hr><br>';
                    }else{
                        $quotations = '<div class="del-card"><figure class="deli-logo"><img src="https://logos-world.net/wp-content/uploads/2020/10/United-States-Postal-Service-Emblem.png"></figure><p>There is some error, please try again later or You can call the office on (305-515-2616)</p></div>';
                    }
                    
                }else{
                    
                    if(!isset($responseArray['Package']['Error'])){
                        foreach($responseArray['Package']['Postage'] as $shippersCount=>$postage){

                            
                            $commitment = '';
                            if(isset($postage['CommitmentDate'])){
                                $commitment = date('D, M d', strtotime($postage['CommitmentDate']));
                            }
                            
                            if ($commitment == '') {

                                $commitment = $this->getStaticCommitment($postage['MailService']);

                            }
                            $quotations .= '<div class="del-card"><div class="date-info"><figure class="deli-logo"><img src="https://logos-world.net/wp-content/uploads/2020/10/United-States-Postal-Service-Emblem.png"></figure><div class="date_inn">';

                            $quotations .= '<h3>ARRIVES ON</h3><p>'.$commitment.'</p>';
    
                            $quotations .= '</div></div>';
    
                            $quotations .= '<ul class="del-item-list"><li><div class="del-head"><div class="del-time-info"><h3>DELIVERED BY</h3><span class="time"></span><p>'.htmlspecialchars_decode($postage['MailService']).'</p></div>';
                            
                            $quotations .= '<div class="del-action"><a href="javascript:void(0);" data-url="/shipping/'.$current_user_id.'/'.$latest_saved_quotation.'/'.base64_encode('usps').'/'.base64_encode(preg_replace('@<(\w+)\b.*?>.*?</\1>@si', '', htmlspecialchars_decode($postage['MailService']))).'/'.$consignee_id.'" class="cstm-btn do_ship">$'.$postage['Rate'].'</a><a href="javascript:void(0);" class="del-toggle" data-tag="shipper_'.$shippersCount.'"><i class="fas fa-chevron-down" style="display:none;"></i></a></div></div><ul id="shipper_'.$shippersCount.'" class="rate-listing" style="display:none;">';

                            $quotations .= '</ul></li></ul></div><hr><br>';

                            $rates[] = [
                                'currency'                      => 'USD',
                                'total'                         => $postage['Rate'],
                                'service_name'                  => preg_replace('@<(\w+)\b.*?>.*?</\1>@si', '', htmlspecialchars_decode($postage['MailService'])),
                                'estimatedDeliveryDateAndTime'  => $commitment,
                            ];
                        }
                    }else{
                        $quotations = '<div class="del-card"><figure class="deli-logo"><img src="https://logos-world.net/wp-content/uploads/2020/10/United-States-Postal-Service-Emblem.png"></figure><p>'.$responseArray['Package']['Error']['Description'].'</p></div>';
                    }
                }
            }
            
            $response['html']   = $quotations;
        }else{
            
            $message = '<div class="del-card"><figure class="deli-logo"><img src="https://logos-world.net/wp-content/uploads/2020/10/United-States-Postal-Service-Emblem.png"></figure><p>There is some error, please try again later or You can call the office on (305-515-2616)</p></div>';
            
            $response['html']   = $message;
        }
        $updateQuote->usps_products = json_encode($rates);
        $updateQuote->save();
        return $response;
    }

    public function getStaticCommitment($service){
        if(strstr($service,"Retail Ground")){
            $commitment = date('D, M d', strtotime('+5 days'));
        }elseif(strstr($service,"Media Mail")){
            $commitment = date('D, M d', strtotime('+7 days'));
        }elseif(strstr($service,"Library Mail")){
            $commitment = date('D, M d', strtotime('+7 days'));
        }elseif(strstr($service,"First-Class")){
            $commitment = date('D, M d', strtotime('+7 days'));
        }else{
            $commitment = 'Delivery date and time <br>estimates are not available for this shipment.';
        }
        return $commitment;
    }
    
    public function getDHLrates($request){
        
        $response['status'] = 'error';

        $common_data = $this->common_data($request);

        $dimensions         = $common_data['dimensions'];

        // URL
        $apiURL = $this->dhl_api_url.'rates'; // (API URL and endpoint)

        $typeCode = '3BX';
        $consignee_id = '';
        if($request->consignees_id){
            $consignee_id = $request->consignees_id;
        }

        $rateArray = array();

        $rateArray['customerDetails']['shipperDetails']['postalCode']   = $request->from_zip;
        $rateArray['customerDetails']['shipperDetails']['cityName']     = $request->from_city;
        $rateArray['customerDetails']['shipperDetails']['countryCode']  = $request->from_country;
        $rateArray['customerDetails']['shipperDetails']['provinceCode'] = $request->from_country;
        $rateArray['customerDetails']['shipperDetails']['addressLine1'] = $common_data['from_address_line1'];
        $rateArray['customerDetails']['shipperDetails']['addressLine2'] = $common_data['from_address_line2'];
        $rateArray['customerDetails']['shipperDetails']['addressLine3'] = $request->from_state;
        $rateArray['customerDetails']['shipperDetails']['countyName']   = $request->from_country_name;

        $rateArray['customerDetails']['receiverDetails']['postalCode']      = ($request->to_zip)?$request->to_zip:"";
        $rateArray['customerDetails']['receiverDetails']['cityName']        = $request->to_city;
        $rateArray['customerDetails']['receiverDetails']['countryCode']     = $request->to_country;
        $rateArray['customerDetails']['receiverDetails']['provinceCode']    = $request->to_country;
        $rateArray['customerDetails']['receiverDetails']['addressLine1']    = $common_data['to_address_line1'];
        $rateArray['customerDetails']['receiverDetails']['addressLine2']    = $common_data['to_address_line2'];
        $rateArray['customerDetails']['receiverDetails']['addressLine3']    = $request->to_state;
        $rateArray['customerDetails']['receiverDetails']['countyName']      = $request->to_country_name;

        $rateArray['accounts'][0]['typeCode']   = 'shipper';
        $rateArray['accounts'][0]['number']     = '848430610';

        if(date('D') == 'Sat' || date('D') == 'Sun') { 
            $day_add = 1;
            if (date('D') == 'Sat') {
               $day_add = 2;
            }           
            $rateArray['plannedShippingDateAndTime'] = date("Y-m-d", strtotime("+".$day_add." day"))."T00:00:00";//date("Y-m-d")."T00:00:00";
        } else {
            $rateArray['plannedShippingDateAndTime'] = date("Y-m-d")."T00:00:00";//date("Y-m-d", strtotime("+1 day"))."T00:00:00";
        }

        
        $rateArray['unitOfMeasurement'] = 'imperial';

        if ($request->shipment_type && $request->shipment_type == 'contains_document') {
            //$rateArray['description'] = 'Documents';
            $typeCode = '2BP';
        }

        $rateArray['isCustomsDeclarable'] = false;

        $rateArray['monetaryAmount'][0]['typeCode'] = 'declaredValue';
        $rateArray['monetaryAmount'][0]['value']    = ($request->total_value)?(int)$request->total_value:10;
        $rateArray['monetaryAmount'][0]['currency'] = 'USD';

        $rateArray['requestAllValueAddedServices']  = false;
        $rateArray['returnStandardProductsOnly']    = false;
        $rateArray['nextBusinessDay']               = false;
        $rateArray['productTypeCode']               = 'all';

        $loopCount = $request->package_count;
        
        for ($i=0; $i < $loopCount; $i++) { 
            $rateArray['packages'][$i]['typeCode']      = $typeCode;
            $rateArray['packages'][$i]['weight']        = (float)$dimensions['weight'][$i];
            // if ($request->contains_document && $request->contains_document == 'on') {
            //     $rateArray['packages'][$i]['description']   = 'Piece content description';
            // }
            $rateArray['packages'][$i]['dimensions']['length']  = (float)$dimensions['length'][$i];        
            $rateArray['packages'][$i]['dimensions']['width']   = (float)$dimensions['width'][$i];
            $rateArray['packages'][$i]['dimensions']['height']  = (float)$dimensions['height'][$i];    
        }

        // Headers
        $headers = [
            'accept'        => 'application/json',
            'content-type'  => 'application/json',
            'authorization' => 'Basic '.base64_encode($this->dhl_api_key.':'.$this->dhl_api_secret) //(siteid:password)
        ];

        // echo json_encode($rateArray);
        // die();

        $http_response  = Http::withHeaders($headers)->post($apiURL, $rateArray);
        $statusCode     = $http_response->status();
                
        $responseBody   = json_decode($http_response->getBody(), true);

        /*
            Save Quote Request in DB - START
        */
        $quotations_data = new Quotation;
        $quotations_data->user_id       = auth()->id();
        $quotations_data->request       = json_encode($rateArray);
        $quotations_data->dhl_response  = $http_response->getBody();
        $quotations_data->save();
        $latest_saved_quotation         = $quotations_data->id;

        //This will use to update same quotation for other carrier functions
        \Session::forget('latest_saved_quotation');
        \Session::put('latest_saved_quotation', $latest_saved_quotation);
        
        /*
            Save Quote Request in DB - END
        */

        $response['api_response'] = $http_response->getBody();
            
        if ($statusCode == 200) {
            $response['status'] = 'success';
            $quotations = '';
            foreach ($responseBody['products'] as $shippersCount => $shipperData) {

                $estimatedDeliveryDate = date("D, M d", strtotime($shipperData['deliveryCapabilities']['estimatedDeliveryDateAndTime']));

                $estimatedDeliveryTime = date("g:i A", strtotime($shipperData['deliveryCapabilities']['estimatedDeliveryDateAndTime']));


                // $quotations .= '<div class="del-card"><div class="date-info"><h3>ARRIVES ON</h3><p>'.$estimatedDeliveryDate.'</p></div>';

                $quotations .= '<div class="del-card"><div class="date-info"><figure class="deli-logo"><img src="https://1000logos.net/wp-content/uploads/2018/08/DHL-Logo.png"></figure><div class="date_inn"> <h3>ARRIVES ON</h3><p>'.$estimatedDeliveryDate.'</p></div></div>';

                $quotations .= '<ul class="del-item-list"><li><div class="del-head"><div class="del-time-info"><h3>DELIVERED BY</h3><span class="time">'.$estimatedDeliveryTime.'</span><p>'.$shipperData['productName'].'</p></div>';

                if (auth()->id() != '') {
                    $current_user_id = auth()->id(); 
                }else{
                    $current_user_id = 'NULL';
                }

                $quotations .= '<div class="del-action"><a href="javascript:void(0);" data-url="/shipping/'.$current_user_id.'/'.$latest_saved_quotation.'/'.base64_encode('dhl').'/'.base64_encode($shipperData['productName']).'/'.$consignee_id.'" class="cstm-btn do_ship">$'.$shipperData['totalPrice'][0]['price'].'</a><a href="javascript:void(0);" class="del-toggle" data-tag="shipper_'.$shippersCount.'"><i class="fas fa-chevron-down"></i></a></div></div><ul id="shipper_'.$shippersCount.'" class="rate-listing">';

                foreach ($shipperData['detailedPriceBreakdown'][0]['breakdown'] as $breakdownCount => $breakdownValues) {
                    $quotations .= '<li><p>'.$breakdownValues['name'].'</p><span class="price_in">$'.$breakdownValues['price'].'</span></li>';
                }
                $quotations .= '</ul></li></ul></div><hr><br>';
            }
            
            $response['html']   = $quotations;    
        }else{

            if ( isset($responseBody['detail']) && strpos($responseBody['detail'], 'location is invalid') !== false && $statusCode == 400) {
                // $address_suggestions = $this->address_suggestions('', $rateArray['customerDetails']);
                
                $address_suggestions = $this->address_suggestions($responseBody['detail'], $rateArray['customerDetails']);
                if ($address_suggestions) {
                    $addresses_html = '<ul>';
                    foreach ($address_suggestions as $address_num => $addr) {
                        $addresses_html .= '<li><strong> > </strong>'.$addr['address'].', '.$addr['cityName'].', '.$addr['postalCode'].'</li>';
                    }
                    $addresses_html .= '</ul>';
                    $message = '<div class="del-card"><p>'.substr(strstr($responseBody['detail'], ':'), strlen(':')).'</p></div>';    
                    $message .= "<hr><div class='del-card'><figure class='deli-logo'><img src='https://1000logos.net/wp-content/uploads/2018/08/DHL-Logo.png'></figure><p>Or You can use some of the listed addresses from suggestions. </p></div><b>Suggestions : </b> <br>".$addresses_html;
                }else{
                    $message = '<div class="del-card"><figure class="deli-logo"><img src="https://1000logos.net/wp-content/uploads/2018/08/DHL-Logo.png"></figure><p>'.substr(strstr($responseBody['detail'], ':'), strlen(':')).'</p></div>';    
                }
            }elseif ($statusCode == 404) {
                // $message = '<div class="del-card"><figure class="deli-logo"><img src="https://1000logos.net/wp-content/uploads/2018/08/DHL-Logo.png"></figure><p>The requested product(s) not available for the requested pickup date.</p></div>';
                $message = '<div class="del-card"><figure class="deli-logo"><img src="https://1000logos.net/wp-content/uploads/2018/08/DHL-Logo.png"></figure><p>There is some error, please try again later or You can call the office on (305-515-2616)</p></div>';
            }else{
                // $message = '<div class="del-card"><figure class="deli-logo"><img src="https://1000logos.net/wp-content/uploads/2018/08/DHL-Logo.png"></figure><p>There is some error please check filled details again</p></div>';
                $message = '<div class="del-card"><figure class="deli-logo"><img src="https://1000logos.net/wp-content/uploads/2018/08/DHL-Logo.png"></figure><p>There is some error, please try again later or You can call the office on (305-515-2616)</p></div>';
            }
            $response['html']   = $message;
        }

        return $response;

    }

    public function getFedExBearer()
    {
        $postData = 'grant_type=client_credentials&client_id='.$this->fedex_client_id.'&client_secret='.$this->fedex_client_secret;
        $curl = curl_init();

        curl_setopt_array($curl, array(
          CURLOPT_URL => $this->fedex_api_url."oauth/token",
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_ENCODING => "",
          CURLOPT_MAXREDIRS => 10,
          CURLOPT_TIMEOUT => 30,
          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
          CURLOPT_CUSTOMREQUEST => "POST",
          CURLOPT_POSTFIELDS => $postData,
          CURLOPT_HTTPHEADER => array(
            "content-type: application/x-www-form-urlencoded",
          ),
        ));

        $response = curl_exec($curl);
        $err = curl_error($curl);

        curl_close($curl);

        $token_data = json_decode($response, true);

        if (isset($token_data['access_token'])) {
            return $token_data['access_token'];
        }
        return '';
    }


    public function getFedExrates($request)
    {
        $common_data            = $this->common_data($request);

        $request_dimensions     = $common_data['dimensions'];

        $latest_saved_quotation = \Session::get('latest_saved_quotation');
        $updateQuote            = Quotation::find($latest_saved_quotation);
        $FedExhtml              = '';
        $rates                  = [];
   
        $apiURL = $this->fedex_api_url.'rate/v1/rates/quotes'; // (API test url)

        $consignee_id = '';
        if($request->consignees_id){
            $consignee_id = $request->consignees_id;
        }

        // Headers
        $headers = [
            'content-type'          => 'application/json',
            'accept'                => 'application/json',
            'authorization'         => 'Bearer '.$this->getFedExBearer()
        ];
    
        $rateArray = array();

        $rateArray['accountNumber']['value']                                                        = $this->fedex_accountNumber;

        $rateArray['rateRequestControlParameters']['returnTransitTimes']                            = true;
        $rateArray['rateRequestControlParameters']['servicesNeededOnRateFailure']                   = true;
        $rateArray['rateRequestControlParameters']['rateSortOrder']                                 = 'COMMITASCENDING';
        $rateArray['rateRequestControlParameters']['variableOptions']                               = NULL;

        $rateArray['requestedShipment']['shipper']['accountNumber']                                 = $this->fedex_accountNumber;


        $rateArray['requestedShipment']['shipper']['address']['streetLines'][0]                     = $common_data['from_address_line1'];
        $rateArray['requestedShipment']['shipper']['address']['streetLines'][1]                     = $common_data['from_address_line2'];

        $rateArray['requestedShipment']['shipper']['address']['city']                               = $request->from_city;
        $rateArray['requestedShipment']['shipper']['address']['stateOrProvinceCode']                = (strlen($request->from_state) > 3)?'':$request->from_state;
        $rateArray['requestedShipment']['shipper']['address']['postalCode']                         = ($request->from_zip)?str_replace(" ", "", $request->from_zip):"";
        $rateArray['requestedShipment']['shipper']['address']['countryCode']                        = $request->from_country;
        $rateArray['requestedShipment']['shipper']['address']['residential']                        = false;

        $rateArray['requestedShipment']['recipient']['address']['streetLines'][0]                   = $common_data['to_address_line1'];
        $rateArray['requestedShipment']['recipient']['address']['streetLines'][1]                   = $common_data['to_address_line2'];
        $rateArray['requestedShipment']['recipient']['address']['city']                             = $request->to_city;
        $rateArray['requestedShipment']['recipient']['address']['stateOrProvinceCode']              = (strlen($request->to_state) > 3)?'':$request->to_state;
        $rateArray['requestedShipment']['recipient']['address']['postalCode']                       = ($request->to_zip)?str_replace(" ", "", $request->to_zip):"";
        $rateArray['requestedShipment']['recipient']['address']['countryCode']                      = $request->to_country;
        $rateArray['requestedShipment']['recipient']['address']['residential']                      = false;

        $rateArray['requestedShipment']['preferredCurrency']                                        = 'USD';
        
        $rateArray['requestedShipment']['rateRequestType'][0]                                       = 'ACCOUNT';
        $rateArray['requestedShipment']['rateRequestType'][1]                                       = 'LIST';
        // $rateArray['requestedShipment']['rateRequestType'][2] = 'INCENTIVE';
        // $rateArray['requestedShipment']['rateRequestType'][3] = 'PREFERRED';

        if(date('D') == 'Sat' || date('D') == 'Sun') { 
            $day_add = 1;
            if (date('D') == 'Sat') {
               $day_add = 2;
            }           
            $rateArray['requestedShipment']['shipDateStamp']                                        = date("Y-m-d", strtotime("+".$day_add." day"));
        } else {
            $rateArray['requestedShipment']['shipDateStamp']                                        = date("Y-m-d");
        }

        $rateArray['requestedShipment']['pickupType']                                               = 'DROPOFF_AT_FEDEX_LOCATION';

        $subPackagingType = 'PACKAGE';
        if ($request->shipment_type && $request->shipment_type == 'contains_document') {
            $subPackagingType = 'ENVELOPE';
        }

        $loopCount      = $request->package_count;
        $total_weight   = 0;
        for ($i=0; $i < $loopCount; $i++) { 
            $rateArray['requestedShipment']['requestedPackageLineItems'][$i]['groupPackageCount']                   = 1;
            $rateArray['requestedShipment']['requestedPackageLineItems'][$i]['physicalPackaging']                   = 'YOUR_PACKAGING';
            $rateArray['requestedShipment']['requestedPackageLineItems'][$i]['insuredValue']['currency']            = 'USD';
            $rateArray['requestedShipment']['requestedPackageLineItems'][$i]['insuredValue']['currencySymbol']      = NULL;
            $rateArray['requestedShipment']['requestedPackageLineItems'][$i]['insuredValue']['amount']              = 0;
            
            // $rateArray['requestedShipment']['requestedPackageLineItems'][$i]['subPackagingType']    = $subPackagingType;
            $rateArray['requestedShipment']['requestedPackageLineItems'][$i]['weight']['units']     = 'LB';
            $rateArray['requestedShipment']['requestedPackageLineItems'][$i]['weight']['value']     = $request_dimensions['weight'][$i];

            $rateArray['requestedShipment']['requestedPackageLineItems'][$i]['dimensions']['length'] = $request_dimensions['length'][$i];
            $rateArray['requestedShipment']['requestedPackageLineItems'][$i]['dimensions']['width']  = $request_dimensions['width'][$i];
            $rateArray['requestedShipment']['requestedPackageLineItems'][$i]['dimensions']['height'] = $request_dimensions['height'][$i];
            $rateArray['requestedShipment']['requestedPackageLineItems'][$i]['dimensions']['units']  = 'IN';
            
            $total_weight        = $total_weight+(float)$request_dimensions['weight'][$i];
        }

        $rateArray['requestedShipment']['smartPostInfoDetail']['ancillaryEndorsement']                              = 'ADDRESS_CORRECTION';
        $rateArray['requestedShipment']['smartPostInfoDetail']['specialServices']                                   = 'USPS_DELIVERY_CONFIRMATION';


        // $rateArray['requestedShipment']['customsClearanceDetail']['commercialInvoice']['shipmentPurpose']           = 'GIFT';
        
        // $rateArray['requestedShipment']['customsClearanceDetail']['freightOnValue']                                 = 'CARRIER_RISK';

        for ($i=0; $i < $loopCount; $i++) { 
            $rateArray['requestedShipment']['customsClearanceDetail']['commodities'][$i]['name']                         = 'DOCUMENTS';
            $rateArray['requestedShipment']['customsClearanceDetail']['commodities'][$i]['description']                  = 'DOCUMENTS';
            $rateArray['requestedShipment']['customsClearanceDetail']['commodities'][$i]['numberOfPieces']               = 1;
            $rateArray['requestedShipment']['customsClearanceDetail']['commodities'][$i]['countryOfManufacture']         = '';//$request->from_country;
            $rateArray['requestedShipment']['customsClearanceDetail']['commodities'][$i]['harmonizedCode']               = '';
            $rateArray['requestedShipment']['customsClearanceDetail']['commodities'][$i]['harmonizedCodeDescription']    = '';
            $rateArray['requestedShipment']['customsClearanceDetail']['commodities'][$i]['itemDescriptionForClearance']  = '';
            $rateArray['requestedShipment']['customsClearanceDetail']['commodities'][$i]['weight']['units']              = 'LB';
            $rateArray['requestedShipment']['customsClearanceDetail']['commodities'][$i]['weight']['value']              = $request_dimensions['weight'][$i];
            $rateArray['requestedShipment']['customsClearanceDetail']['commodities'][$i]['quantity']                     = 1;
            $rateArray['requestedShipment']['customsClearanceDetail']['commodities'][$i]['quantityUnits']                = '';
            $rateArray['requestedShipment']['customsClearanceDetail']['commodities'][$i]['unitPrice']['currency']        = 'USD';
            $rateArray['requestedShipment']['customsClearanceDetail']['commodities'][$i]['unitPrice']['amount']          = NULL;
            $rateArray['requestedShipment']['customsClearanceDetail']['commodities'][$i]['unitPrice']['currencySymbol']  = '';
            $rateArray['requestedShipment']['customsClearanceDetail']['commodities'][$i]['customsValue']['currency']     = 'USD';
            $rateArray['requestedShipment']['customsClearanceDetail']['commodities'][$i]['customsValue']['amount']       = 1;
        }
        
        $rateArray['requestedShipment']['documentShipment']     = false;
        $rateArray['requestedShipment']['packagingType']        = 'YOUR_PACKAGING';
        
        $rateArray['requestedShipment']['shippingChargesPayment']['payor']['responsibleParty']['accountNumber']['value']        = $this->fedex_accountNumber;
        $rateArray['requestedShipment']['shippingChargesPayment']['payor']['responsibleParty']['address']['countryCode']        = 'US';

        $rateArray['requestedShipment']['blockInsightVisibility']                                                               = false;
        $rateArray['requestedShipment']['edtRequestType']                                                                       = 'NONE';

        // $rateArray['requestedShipment']['totalPackageCount']    = $loopCount;
        // $rateArray['requestedShipment']['totalWeight']          = $total_weight;
        // $rateArray['requestedShipment']['groundShipment']       = false;

        $rateArray['carrierCodes'][0]               = 'FDXG';
        $rateArray['carrierCodes'][1]               = 'FDXE';

        $rateArray['returnLocalizedDateTime']       = true;

        // echo json_encode($rateArray);
        // die();

        $http_response  = Http::withHeaders($headers)->post($apiURL, $rateArray);
        $statusCode     = $http_response->status();
        
        $responseBody   = json_decode($http_response->getBody(), true);
        
        $updateQuote->fedex_response = $http_response->getBody();

        if ($statusCode == 200) {
            $FedExresponse['status'] = 'success';
            if (isset($responseBody['output']) && isset($responseBody['output']['rateReplyDetails'])) {
                foreach ($responseBody['output']['rateReplyDetails'] as $RatedShipmentkey => $RatedShipmentvalue) {
                    $rates[] = [
                        'currency'                      => 'USD',
                        'total'                         => $RatedShipmentvalue['ratedShipmentDetails'][0]['totalNetFedExCharge'],
                        'breakdown'                     => $RatedShipmentvalue['ratedShipmentDetails'][0]['shipmentRateDetail'],
                        'service_name'                  => $RatedShipmentvalue['serviceName'],
                        'service_type'                  => $RatedShipmentvalue['serviceType'],
                        'estimatedDeliveryDateAndTime'  => (isset($RatedShipmentvalue['operationalDetail']['deliveryDate']) && !empty($RatedShipmentvalue['operationalDetail']['deliveryDate']))?$RatedShipmentvalue['operationalDetail']['deliveryDate']:'',
                    ];
                }
                if ($rates) {
                    foreach ($rates as $shippersCount => $rate) {
                        $estimatedDeliveryDate = $estimatedDeliveryTime = '';
                        if (isset($rate['estimatedDeliveryDateAndTime']) && !empty($rate['estimatedDeliveryDateAndTime'])) {
                            $estimatedDeliveryDate = date("D, M d", strtotime($rate['estimatedDeliveryDateAndTime']));
                        }else{
                            $estimatedDeliveryDate = 'Delivery date and time <br>estimates are not available for this shipment.';
                        }

                        if (isset($rate['estimatedDeliveryDateAndTime']) && !empty($rate['estimatedDeliveryDateAndTime'])) {
                            $estimatedDeliveryTime = date("g:i A", strtotime($rate['estimatedDeliveryDateAndTime']));
                        }

                        $FedExhtml .= '<div class="del-card"><div class="date-info"><figure class="deli-logo"><img src="https://logos-world.net/wp-content/uploads/2020/04/FedEx-Logo-1994-present.jpg"></figure><div class="date_inn">'; 

                        if ($estimatedDeliveryDate != '') {
                            $FedExhtml .= '<h3>ARRIVES ON</h3><p>'.$estimatedDeliveryDate.'</p>';
                        }

                        $FedExhtml .= '</div></div>';

                        $FedExhtml .= '<ul class="del-item-list"><li><div class="del-head"><div class="del-time-info"><h3>DELIVERED BY</h3><span class="time">'.$estimatedDeliveryTime.'</span><p>'.$rate['service_name'].'</p></div>';

                        if (auth()->id() != '') {
                            $current_user_id = auth()->id(); 
                        }else{
                            $current_user_id = 'NULL';
                        }

                        $FedExhtml .= '<div class="del-action"><a href="javascript:void(0);" data-url="/shipping/'.$current_user_id.'/'.$latest_saved_quotation.'/'.base64_encode('fedex').'/'.base64_encode($rate['service_name']).'/'.$consignee_id.'" class="cstm-btn do_ship">$'.$rate['total'].'</a><a href="javascript:void(0);" class="del-toggle" data-tag="shipper_'.$shippersCount.'"><i class="fas fa-chevron-down" style="display:none;"></i></a></div></div><ul id="shipper_'.$shippersCount.'" class="rate-listing" style="display:none;">';

                        // foreach ($shipperData['detailedPriceBreakdown'][0]['breakdown'] as $breakdownCount => $breakdownValues) {
                        //     $quotations .= '<li><p>'.$breakdownValues['name'].'</p><span class="price_in">$'.$breakdownValues['price'].'</span></li>';
                        // }
                        $FedExhtml .= '</ul></li></ul></div><hr><br>';
                    }
                }else{
                    // $FedExhtml = '<div class="del-card"><figure class="deli-logo"><img src="https://logos-world.net/wp-content/uploads/2020/04/FedEx-Logo-1994-present.jpg"></figure><p>No shipment found for Date or Location</p></div>';    
                    $FedExhtml = '<div class="del-card"><figure class="deli-logo"><img src="https://logos-world.net/wp-content/uploads/2020/04/FedEx-Logo-1994-present.jpg"></figure><p>There is some error, please try again later or You can call the office on (305-515-2616)</p></div>';    
                }
            }else{
                $FedExhtml = '<div class="del-card"><figure class="deli-logo"><img src="https://logos-world.net/wp-content/uploads/2020/04/FedEx-Logo-1994-present.jpg"></figure><p>There is some error, please check values and try again</p></div>';    
            }
        }else{
            // if (isset($responseBody['errors'])) {
            //     $FedExhtml = '<div class="del-card"><figure class="deli-logo"><img src="https://logos-world.net/wp-content/uploads/2020/04/FedEx-Logo-1994-present.jpg"></figure><p>'.$responseBody['errors'][0]['message'].'</p></div>';    
            // }else{
            //     $FedExhtml = '<div class="del-card"><figure class="deli-logo"><img src="https://logos-world.net/wp-content/uploads/2020/04/FedEx-Logo-1994-present.jpg"></figure><p>There is some error, please check values and try again</p></div>';    
            // }
            $FedExhtml = '<div class="del-card"><figure class="deli-logo"><img src="https://logos-world.net/wp-content/uploads/2020/04/FedEx-Logo-1994-present.jpg"></figure><p>There is some error, please try again later or You can call the office on (305-515-2616)</p></div>';    
        }
        $updateQuote->fedex_products = json_encode($rates);
        $updateQuote->update();
        $FedExresponse['html'] = $FedExhtml;
        return $FedExresponse;

    }

    public function getUPSrates_2($request)
    {
        $common_data = $this->common_data($request);

        $request_dimensions         = $common_data['dimensions'];
		
		$latest_saved_quotation = \Session::get('latest_saved_quotation');
        $updateQuote = Quotation::find($latest_saved_quotation);
        $USPhtml= '';
        $rates  = [];

        $apiURL = $this->ups_api_url.'ship/v1801/rating/Shop?additionalinfo=timeintransit'; // (API URL with Endpoint)

        $consignee_id = '';
        if($request->consignees_id){
            $consignee_id = $request->consignees_id;
        }
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

        $updateQuote->ups_response = $http_response->getBody();
        
        if ($statusCode == 200) {
            $UPSresponse['status'] = 'success';
            if (isset($responseBody['RateResponse']) && isset($responseBody['RateResponse']['RatedShipment'])) {
                foreach ($responseBody['RateResponse']['RatedShipment'] as $RatedShipmentkey => $RatedShipmentvalue) {
                    $rates[] = [
                        'currency'              => $RatedShipmentvalue['TotalCharges']['CurrencyCode'],
                        'total'                 => isset($RatedShipmentvalue['NegotiatedRateCharges'])?$RatedShipmentvalue['NegotiatedRateCharges']['TotalCharge']['MonetaryValue']:$RatedShipmentvalue['TotalCharges']['MonetaryValue'],
                        'days_to_deliver'       => (isset($RatedShipmentvalue['TimeInTransit']['ServiceSummary']['EstimatedArrival']['TotalTransitDays']) && !empty($RatedShipmentvalue['TimeInTransit']['ServiceSummary']['EstimatedArrival']['TotalTransitDays']))?$RatedShipmentvalue['TimeInTransit']['ServiceSummary']['EstimatedArrival']['TotalTransitDays']:'',
                        'service_name'          => $RatedShipmentvalue['TimeInTransit']['ServiceSummary']['Service']['Description'],
                        'estimatedDeliveryDate' => $RatedShipmentvalue['TimeInTransit']['ServiceSummary']['EstimatedArrival']['Arrival']['Date'],
                        'estimatedDeliveryTime' => $RatedShipmentvalue['TimeInTransit']['ServiceSummary']['EstimatedArrival']['Arrival']['Time']
                    ];
                }
                $updateQuote->ups_products = json_encode($rates);
                if ($rates) {
                    foreach ($rates as $shippersCount => $rate) {
                        $UPSestimatedDeliveryDate = '';
                        if (isset($rate['days_to_deliver']) && !empty($rate['days_to_deliver'])) {
                            $days_to_add = $rate['days_to_deliver'];
                            $UPSestimatedDeliveryDate = date("Y-m-d", strtotime("+".$days_to_add." day"));
                        }

                        $estimatedDeliveryDate = $estimatedDeliveryTime = '';
                        if ($UPSestimatedDeliveryDate == '' && (isset($rate['estimatedDeliveryDate']) && !empty($rate['estimatedDeliveryDate']))) {
                            $estimatedDeliveryDate = date("D, M d", strtotime($rate['estimatedDeliveryDate']));
                        }

                        if ($UPSestimatedDeliveryDate != '') {
                            $estimatedDeliveryDate = date("D, M d", strtotime($UPSestimatedDeliveryDate));
                        }
                        if (isset($rate['estimatedDeliveryTime']) && !empty($rate['estimatedDeliveryTime'])) {
                            $estimatedDeliveryTime = date("g:i A", strtotime($rate['estimatedDeliveryTime']));
                        }
                        $USPhtml .= '<div class="del-card"><div class="date-info"><figure class="deli-logo"><img src="https://wwwapps.ups.com/assets/resources/images/UPS_logo.svg"></figure><div class="date_inn">'; 

                        if ($estimatedDeliveryDate != '') {
                            $USPhtml .= '<h3>ARRIVES ON</h3><p>'.$estimatedDeliveryDate.'</p>';
                        }

                        $USPhtml .= '</div></div>';

                        $USPhtml .= '<ul class="del-item-list"><li><div class="del-head"><div class="del-time-info"><h3>DELIVERED BY</h3><span class="time">'.$estimatedDeliveryTime.'</span><p>'.$rate['service_name'].'</p></div>';

                        if (auth()->id() != '') {
                            $current_user_id = auth()->id(); 
                        }else{
                            $current_user_id = 'NULL';
                        }

                        $USPhtml .= '<div class="del-action"><a href="javascript:void(0);" data-url="/shipping/'.$current_user_id.'/'.$latest_saved_quotation.'/'.base64_encode('ups').'/'.base64_encode($rate['service_name']).'/'.$consignee_id.'" class="cstm-btn do_ship">$'.$rate['total'].'</a><a href="javascript:void(0);" class="del-toggle" data-tag="shipper_'.$shippersCount.'"><i class="fas fa-chevron-down" style="display:none;"></i></a></div></div><ul id="shipper_'.$shippersCount.'" class="rate-listing" style="display:none;">';

                        // foreach ($shipperData['detailedPriceBreakdown'][0]['breakdown'] as $breakdownCount => $breakdownValues) {
                        //     $quotations .= '<li><p>'.$breakdownValues['name'].'</p><span class="price_in">$'.$breakdownValues['price'].'</span></li>';
                        // }
                        $USPhtml .= '</ul></li></ul></div><hr><br>';
                    }
                }else{
                    // $USPhtml = '<div class="del-card"><figure class="deli-logo"><img src="https://wwwapps.ups.com/assets/resources/images/UPS_logo.svg"></figure><p>No shipment found for Date or Location</p></div>';    
                    $USPhtml = '<div class="del-card"><figure class="deli-logo"><img src="https://wwwapps.ups.com/assets/resources/images/UPS_logo.svg"></figure><p>There is some error, please try again later or You can call the office on (305-515-2616)</p></div>';    
                }
            }            
        }else{
            // if (isset($responseBody['response']) && isset($responseBody['response']['errors'])) {
            //     $USPhtml = '<div class="del-card"><figure class="deli-logo"><img src="https://wwwapps.ups.com/assets/resources/images/UPS_logo.svg"></figure><p>'.$responseBody['response']['errors'][0]['message'].'</p></div>';    
            // }else{
            //     $USPhtml = '<div class="del-card"><figure class="deli-logo"><img src="https://wwwapps.ups.com/assets/resources/images/UPS_logo.svg"></figure><p>There is some error, please check values and try again</p></div>';    
            // }
            $USPhtml = '<div class="del-card"><figure class="deli-logo"><img src="https://wwwapps.ups.com/assets/resources/images/UPS_logo.svg"></figure><p>There is some error, please try again later or You can call the office on (305-515-2616)</p></div>';    

        }
        $updateQuote->update();
        $UPSresponse['html'] = $USPhtml;
        return $UPSresponse;
    }

    public function address_suggestions($api_response, $addresses)
    {
        $type = $countryCode = $cityName = $postalCode = '';
        if (strpos($api_response, 'destination') !== false) {
            $type = 'delivery';
        }else{
            $type = 'pickup';
        }

        $receiverDetails = $addresses['receiverDetails'];
        $shipperDetails  = $addresses['shipperDetails'];

        if ($type == 'delivery') {
            $countryCode = $receiverDetails['countryCode'];
            $cityName    = $receiverDetails['cityName'];
            if (isset($receiverDetails['postalCode']) && !empty($receiverDetails['postalCode'])) {
                $postalCode  = $receiverDetails['postalCode'];
            }
        }else{
            $countryCode = $shipperDetails['countryCode'];
            $cityName    = $shipperDetails['cityName'];
            if (isset($shipperDetails['postalCode']) && !empty($shipperDetails['postalCode'])) {
                $postalCode  = $shipperDetails['postalCode'];
            }
        }

        // https://api-mock.dhl.com/mydhlapi/address-validate?type=delivery&countryCode=CZ&postalCode=14800&cityName=Prague&strictValidation=true
        $apiURL = $this->dhl_api_url.'address-validate'; // (API URL and Endpoint) 
        // Headers
        $headers = [
            'accept'        => 'application/json',
            'content-type'  => 'application/json',
            'authorization' => 'Basic '.base64_encode($this->dhl_api_key.':'.$this->dhl_api_secret) //(siteid:password)
        ];

        $params = array();
        $params['type'] = $type;
        $params['countryCode'] = $countryCode;
        $params['cityName'] = $cityName;
        $params['strictValidation'] = 'false';
        if ($postalCode != '') {
            $params['postalCode'] = $postalCode;
        }

        $http_response = Http::withHeaders($headers)->get($apiURL, $params);
        
        /*
            Save Addresses Request in DB - START
        */
        $addresses_data = new Addresses;
        $addresses_data->user_id        = auth()->id();
        $addresses_data->request        = $apiURL."?".http_build_query($params);
        $addresses_data->response       = $http_response->getBody();
        $addresses_data->save();
        $latest_saved_addresses         = $addresses_data->id;

        /*
            Save Addresses Request in DB - END
        */
        $statusCode     = $http_response->status();
        $suggested_addresses = array();    
        if ($statusCode == 400 || $statusCode == 404 || $statusCode == 500) {
            return false;
        }else{
            $responseBody        = json_decode($http_response->getBody(), true);
            if ($responseBody['address']) {
                foreach ($responseBody['address'] as $address_num => $address) {
                    $suggested_addresses[$address_num]['countryCode'] = $address['countryCode'];
                    $suggested_addresses[$address_num]['postalCode']  = isset($address['postalCode'])?$address['postalCode']:'';
                    $suggested_addresses[$address_num]['cityName']    = $address['cityName'];        
                    $suggested_addresses[$address_num]['address']     = $address['serviceArea']['description'];
                    $suggested_addresses[$address_num]['state']       = $address['serviceArea']['code'];
                }
            }
        }
        return $suggested_addresses;
    }

    public function saveConsignee(Request $request){
        /*
            Save Consignee Request in DB - START
        */
        $consignee_data = new Consignee;
        $consignee_data->user_id       = auth()->id();
        $consignee_data->consignee_name       = $request->consignee_name;
        $consignee_data->consignee_phone  = $request->consignee_phone;
        $consignee_data->consignee_homephone  = $request->consignee_homephone;
        $consignee_data->consignee_address_country  = $request->to_country;
        $consignee_data->consignee_address_city  = $request->to_city;
        $consignee_data->consignee_address_state  = $request->to_state;
        $consignee_data->consignee_address_zip  = $request->to_zip;
        $consignee_data->consignee_address  = $request->to_address;
        $consignee_data->save();
        $latest_saved_consignee         = $consignee_data->id;
        $response['status'] = 'success';
        $response['consignee_id'] = $latest_saved_consignee;
        return response()->json($response);
    }

    public function updateConsignee(Request $request){
        /*
            Update Consignee Request in DB - START
        */
        $response['status'] = 'success';
        $consignee_id = $request->consignee_id;
        if($consignee_id == "")
        return response()->json($response);
        $consignee_data = Consignee::where('id', $consignee_id)->first();
        $consignee_data->consignee_name       = $request->to_name;
        $consignee_data->consignee_phone  = $request->to_phone_1;
        $consignee_data->consignee_homephone  = $request->to_phone_2;
        $consignee_data->update();
        $latest_saved_consignee         = $consignee_data->id;
        return response()->json($response);
    }

    public function remove_special_characters($string='')
    {
        $string = str_replace("-", " ", $string); // Replaces all spaces with hyphens.
        // $string = str_replace( array( '\'', '"', ',' , ';', '<', '>' ), ' ', $string);
        return preg_replace('/[^a-zA-Z0-9_ %\[\]\.\(\)%&-]/s', '', $string); // Removes special chars.   
        // die();
    }

}