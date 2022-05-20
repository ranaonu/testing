<?php

namespace Wave\Http\Controllers;

use Auth;
use Illuminate\Http\Request;
use Validator;
use Wave\Quotation;
use Wave\Countries;
use Wave\Pickup;
use TCG\Voyager\Http\Controllers\Controller;
use Illuminate\Support\Facades\Http;
use Wave\Shipping;
use Carbon\Carbon;

class PickupController extends Controller
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

    private $us_holidays;

    private $usps_api_url;
    private $usps_test_api_url;
    private $usps_username;
    private $usps_sandbox_enable;

    public function __construct() {
        $this->us_holidays          = config("holidays.list");

        $this->dhl_sandbox_enable   = config('carriers.dhl_sandbox_enable');
        $this->dhl_sandbox_url      = config('carriers.dhl_sandbox_url');
        $this->dhl_live_url         = config('carriers.dhl_live_url');
        $this->dhl_api_key          = config('carriers.dhl_api_key');
        $this->dhl_api_secret       = config('carriers.dhl_api_secret');
        
        $this->ups_sandbox_enable   = config('carriers.ups_sandbox_enable');
        $this->ups_sandbox_url      = config('carriers.ups_sandbox_url');
        $this->ups_live_url         = config('carriers.ups_live_url');
        $this->ups_access_key       = config('carriers.ups_access_key');
        $this->ups_user_id          = config('carriers.ups_user_id');
        $this->ups_password         = config('carriers.ups_password');
        $this->ups_password         = config('carriers.ups_password');
        $this->ups_shipper_number   = config('carriers.ups_shipper_number');

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
        $this->usps_stg_api_url        = config('carriers.usps_stg_api_url');
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

    //
    public function index()
    {
    	$countries 	= Countries::all();
        $shipping_page_info = array();
        $shipping_page_info['us_holidays']  = $this->us_holidays;  
        return view('theme::pickup.index', compact('countries', 'shipping_page_info'));
    }

    public function agentSchedule(Request $request)
    {
        $response['status']     = 'error';
        $response['message']    = 'There is some error, please try after some time!';
        if ($request->pickup_from == 'dhl' && $request->pickup_tracking_number) {
            $dhl_pickup = $this->pickup_from_dhl($request);
            if (isset($dhl_pickup['message']) && !empty($dhl_pickup['message'])) {
                $response['status']     = $dhl_pickup['status'];
                $response['message']    = $dhl_pickup['message'];
            }
        }elseif ($request->pickup_from == 'ups') {
            $ups_pickup = $this->pickup_from_ups($request);
            if (isset($ups_pickup['message']) && !empty($ups_pickup['message'])) {
                $response['status']     = $ups_pickup['status'];
                $response['message']    = $ups_pickup['message'];
            }
        }elseif ($request->pickup_from == 'FedEx') {
            $fedex_pickup = $this->pickup_from_fedex($request);
            if (isset($fedex_pickup['message']) && !empty($fedex_pickup['message'])) {
                $response['status']     = $fedex_pickup['status'];
                $response['message']    = $fedex_pickup['message'];
            }
        }elseif ($request->pickup_from == 'usps') {
            $usps_pickup = $this->pickup_from_usps($request);
            if (isset($usps_pickup['message']) && !empty($usps_pickup['message'])) {
                $response['status']     = $usps_pickup['status'];
                $response['message']    = $usps_pickup['message'];
            }
        } else{
            $response['message']    = 'There is some error, please check all fields and values or contact admin.';
        }
        return response()->json($response);
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

    public function pickup_from_fedex($request)
    {
        $response['status']     = 'error';
        $response['message']    = 'There is some error, please try after some time!';
        
        $apiURL = $this->fedex_api_url.'pickup/v1/pickups'; // (API test url)

        // Headers
        $headers = [
            'content-type'          => 'application/json',
            'accept'                => 'application/json',
            'authorization'         => 'Bearer '.$this->getFedExBearer()
        ];

        $pickup_start_time  = date("H:i:s", strtotime($request->pickup_start_time));
        $pickup_end_time    = date("H:i:s", strtotime($request->pickup_end_time));
        $pickup_date        = date("Y-m-d", strtotime($request->pickup_date));
        $loopCount          = $request->package_count;
        $dimensions         = $request->dimensions;
        $total_weight       = 0;
        for ($i=0; $i < $loopCount; $i++) { 
            $total_weight = $total_weight+$dimensions['weight'][$i];
        }

        $pickup_api_data = array();
        $pickup_api_data['associatedAccountNumber']['value']                                    = $this->fedex_accountNumber;
   
        $pickup_api_data['originDetail']['pickupLocation']['contact']['personName']             = $request->pickup_name;
        $pickup_api_data['originDetail']['pickupLocation']['contact']['phoneNumber']            = $request->pickup_phone;
        $pickup_api_data['originDetail']['pickupLocation']['address']['streetLines'][0]         = $request->pickup_address;
        $pickup_api_data['originDetail']['pickupLocation']['address']['streetLines'][1]         = $request->pickup_state;

        $pickup_api_data['originDetail']['pickupLocation']['address']['city']                   = $request->pickup_city;
        $pickup_api_data['originDetail']['pickupLocation']['address']['stateOrProvinceCode']    = $request->pickup_state;
        $pickup_api_data['originDetail']['pickupLocation']['address']['postalCode']             = str_replace(" ", "", $request->pickup_zip);
        $pickup_api_data['originDetail']['pickupLocation']['address']['countryCode']            = $request->pickup_country;

        if ($request->pick_location == 'Front Door') {
            $packageLocation = 'FRONT';
        }elseif ($request->pick_location == 'Back Door') {
            $packageLocation = 'REAR';
        }elseif ($request->pick_location == 'Reception' || $request->pick_location == 'Loading Dock') {
            $packageLocation = 'SIDE';
        }elseif ($request->pick_location == 'Other') {
            $packageLocation = 'NONE';
        }else{
            $packageLocation = 'FRONT';
        }

        $pickup_api_data['originDetail']['packageLocation']                                 =  $packageLocation;
        $pickup_api_data['originDetail']['readyDateTimestamp']                              =  $pickup_date."T".$pickup_start_time."Z";
        $pickup_api_data['originDetail']['customerCloseTime']                               =  $pickup_end_time;
        
        $pickup_api_data['totalWeight']['units']                                            =  'LB';

        $pickup_api_data['totalWeight']['value']                                            =  $total_weight;
        $pickup_api_data['packageCount']                                                    =  $loopCount;
        $pickup_api_data['remarks']                                                         =  ($request->pickup_instruction != '')?$request->pickup_instruction:'No intructions';

        $pickup_api_data['trackingNumber']                                                  =  $request->pickup_tracking_number;

        if ($request->fedex_picup_options == 'ground_pickup') {
            $pickup_api_data['carrierCode']                                                     =  'FDXG';
        }else{
            $pickup_api_data['carrierCode']                                                     =  'FDXE';
        }


        $http_response  = Http::withHeaders($headers)->post($apiURL, $pickup_api_data);
        $statusCode     = $http_response->status();

        $responseBody   = json_decode($http_response->getBody(), true);

        /*
            Save Pickup Request in DB - START
        */
            $pickup_data_to_save                   = new Pickup;
            $pickup_data_to_save->user_id          = auth()->id();
            $pickup_data_to_save->request          = json_encode($pickup_api_data);
            $pickup_data_to_save->response         = $http_response->getBody();
            $pickup_data_to_save->shipped_from     = 'FEDEX';
            $pickup_data_to_save->save();
            $latest_saved_pickup                   = $pickup_data_to_save->id;
        /*
            Save Pickup Request in DB - END
        */

        if ($statusCode == 200) {
            if (isset($responseBody['output']) && isset($responseBody['output']['pickupConfirmationCode'])) {
                $fedex_ref                          = 'ZSP'.rand(1111111,9999999);
                $latestPickup_data                  = Pickup::where('id', $latest_saved_pickup)->first();
                $latestPickup_data->reference_num   = $fedex_ref;
                $latestPickup_data->update();

                $updateQuote                    = Quotation::where('shipped_trackingNumber', $request->pickup_tracking_number)->first();
                $updateQuote->pickup_scheduled  = 1;
                $updateQuote->pickup_id         = $latest_saved_pickup;
                $updateQuote->update();
                $response['status']     = 'success';
                $response['message']    = 'Pickup created successfully! <br>Please save this Pickup Reference number for reference > <b>'.$fedex_ref.'</b> And <br>The pickup fee is: <b>20.00 USD</b>';
            }else{
                $response['message'] = "There is some error, while scheduling pickup, please contact admin or try again later.";
            }
        }else{
            $response['message'] = $responseBody['errors'][0]['message'];
        }
        return $response;
    }

    public function pickup_from_ups($request)
    {
        $response['status']     = 'error';
        $response['message']    = 'There is some error, please try after some time!';
        
        $apiURL = $this->ups_api_url.'ship/v1607/pickups'; // (API test url)

        // Headers
        $headers = [
            'accesslicensenumber'   => $this->ups_access_key,
            'username'              => $this->ups_user_id,
            'password'              => $this->ups_password,
            'content-type'          => 'application/json',
            'accept'                => 'application/json'
        ];

        $pickup_start_time  = date("H:i:s", strtotime($request->pickup_start_time));
        $pickup_end_time    = date("H:i:s", strtotime($request->pickup_end_time));
        $pickup_date        = date("Ymd", strtotime($request->pickup_date));

        $pickup_api_data = array();

        $pickup_api_data['PickupCreationRequest']['RatePickupIndicator'] = 'Y';
        $pickup_api_data['PickupCreationRequest']['Shipper']['Account']['AccountNumber'] = $this->ups_shipper_number;
        $pickup_api_data['PickupCreationRequest']['Shipper']['Account']['AccountCountryCode'] = 'US';


        $pickup_api_data['PickupCreationRequest']['PickupDateInfo']['CloseTime']    = '1440';//(string)$this->get_in_minutes($pickup_end_time);
        $pickup_api_data['PickupCreationRequest']['PickupDateInfo']['ReadyTime']    = '0500';//(string)$this->get_in_minutes($pickup_start_time);
        $pickup_api_data['PickupCreationRequest']['PickupDateInfo']['PickupDate']   = $pickup_date;

        $pickup_api_data['PickupCreationRequest']['PickupAddress']['CompanyName']   = 'Zion Shipping';
        $pickup_api_data['PickupCreationRequest']['PickupAddress']['ContactName']   = $request->pickup_name;
        $pickup_api_data['PickupCreationRequest']['PickupAddress']['AddressLine']   = $request->pickup_address;
        $pickup_api_data['PickupCreationRequest']['PickupAddress']['Room']          = '';
        $pickup_api_data['PickupCreationRequest']['PickupAddress']['Floor']         = '';
        $pickup_api_data['PickupCreationRequest']['PickupAddress']['City']          = $request->pickup_city;
        $pickup_api_data['PickupCreationRequest']['PickupAddress']['StateProvince'] = $request->pickup_state;
        $pickup_api_data['PickupCreationRequest']['PickupAddress']['Urbanization']  = '';
        $pickup_api_data['PickupCreationRequest']['PickupAddress']['PostalCode']    = str_replace(" ", "", $request->pickup_zip);
        $pickup_api_data['PickupCreationRequest']['PickupAddress']['CountryCode']   = $request->pickup_country;
        $pickup_api_data['PickupCreationRequest']['PickupAddress']['ResidentialIndicator']      = 'N';
        $pickup_api_data['PickupCreationRequest']['PickupAddress']['PickupPoint']               = $request->pick_location;
        $pickup_api_data['PickupCreationRequest']['PickupAddress']['Phone']['Number']           = $request->pickup_phone;
        $pickup_api_data['PickupCreationRequest']['PickupAddress']['Phone']['Extension']        = '';

        $pickup_api_data['PickupCreationRequest']['AlternateAddressIndicator']      = 'Y';

        $loopCount      = $request->package_count;
        $dimensions     = $request->dimensions;
        $total_weight   = 0;

        for ($i=0; $i < $loopCount; $i++) { 
            $pickup_api_data['PickupCreationRequest']['PickupPiece'][$i]['ServiceCode']             = '001';
            $pickup_api_data['PickupCreationRequest']['PickupPiece'][$i]['Quantity']                = '1';
            $pickup_api_data['PickupCreationRequest']['PickupPiece'][$i]['DestinationCountryCode']  = 'US';
            $pickup_api_data['PickupCreationRequest']['PickupPiece'][$i]['ContainerCode']           = '01';
            $total_weight = $total_weight+$dimensions['weight'][$i];
        }

        $pickup_api_data['PickupCreationRequest']['TotalWeight']['Weight']              = (string)$total_weight;
        $pickup_api_data['PickupCreationRequest']['TotalWeight']['UnitOfMeasurement']   = 'LBS';
        
        $pickup_api_data['PickupCreationRequest']['OverweightIndicator']                = 'N';

        $pickup_api_data['PickupCreationRequest']['PaymentMethod']                      = '01';
        $pickup_api_data['PickupCreationRequest']['SpecialInstruction']                 = ($request->pickup_instruction != '')?$request->pickup_instruction:'No intructions';

        // $pickup_api_data['PickupCreationRequest']['ReferenceNumber']                    = rand(111111,999999);

        $pickup_api_data['PickupCreationRequest']['Notification']['ConfirmationEmailAddress']   = $request->pickup_email;
        $pickup_api_data['PickupCreationRequest']['Notification']['UndeliverableEmailAddress']  = $request->pickup_email;
        $pickup_api_data['PickupCreationRequest']['Notification']['ShippingLabelsAvailable']    = 'Y';

        $http_response  = Http::withHeaders($headers)->post($apiURL, $pickup_api_data);
        $statusCode     = $http_response->status();

        $responseBody   = json_decode($http_response->getBody(), true);

        /*
            Save Pickup Request in DB - START
        */
            $pickup_data_to_save                   = new Pickup;
            $pickup_data_to_save->user_id          = auth()->id();
            $pickup_data_to_save->request          = json_encode($pickup_api_data);
            $pickup_data_to_save->response         = $http_response->getBody();
            $pickup_data_to_save->shipped_from     = 'UPS';
            $pickup_data_to_save->save();
            $latest_saved_pickup                   = $pickup_data_to_save->id;
        /*
            Save Pickup Request in DB - END
        */
        if (isset($responseBody['PickupCreationResponse']['PRN']) && !empty($responseBody['PickupCreationResponse']['PRN'])) {
            $latestPickup_data = Pickup::where('id', $latest_saved_pickup)->first();
            $latestPickup_data->reference_num = $responseBody['PickupCreationResponse']['PRN'];
            $latestPickup_data->update();


            $response['status']     = 'success';
            $response['message']    = 'Pickup created successfully! <br>Please save this Pickup Reference number for reference > <b>'.$responseBody['PickupCreationResponse']['PRN'].'</b> <br>The pickup fee is: <b>20.00 USD</b>';
        }elseif (isset($responseBody['response']['errors']) && !empty($responseBody['response']['errors'])) {
            $response['message'] = $responseBody['response']['errors'][0]['message'];
        }
        return $response;
    }

    public function zipLookup($pickup_address,$pickup_city,$pickup_state){

        $xml = '<ZipCodeLookupRequest USERID="'.$this->usps_username.'">
                    <Address ID="0">
                        <Address1></Address1>
                        <Address2>'.$pickup_address.'</Address2>
                        <City>'.$pickup_city.'</City>
                        <State>'.$pickup_state.'</State>
                    </Address>
                </ZipCodeLookupRequest>';

        $xmlencoded = urlencode($xml);

        if($this->usps_sandbox_enable){
            $apiURL = $this->usps_test_api_url.'?API=ZipCodeLookup&XML='.$xmlencoded; // (API URL and endpoint)
        }else{
            $apiURL = $this->usps_api_url.'?API=ZipCodeLookup&XML='.$xmlencoded; // (API URL and endpoint)
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

        curl_close($curl);

        $simplexml  = simplexml_load_string($curlresponse);
        $json       = json_encode($simplexml);
        $responseArray      = json_decode($json,TRUE);
        return $responseArray;
    }

    public function uspsPickupAvailability($pickup_apt,$pickup_address,$pickup_city,$pickup_state,$pickup_zip,$pickup_date){
        $pickup_date        = date("Y-m-d", strtotime($pickup_date));
        $xml = '<CarrierPickupAvailabilityRequest USERID="'.$this->usps_username.'">
                    <FirmName></FirmName>
                    <SuiteOrApt>'.$pickup_apt.'</SuiteOrApt>
                    <Address2>'.$pickup_address.'</Address2>
                    <Urbanization></Urbanization>
                    <City>'.$pickup_city.'</City>
                    <State>'.$pickup_state.'</State>
                    <ZIP5>'.str_replace(" ", "", $pickup_zip).'</ZIP5>
                    <ZIP4></ZIP4>
                    <Date>'.$pickup_date.'T12:00:00z</Date>
                </CarrierPickupAvailabilityRequest>';
        //echo $xml;die;
        $xmlencoded = urlencode($xml);

        if($this->usps_sandbox_enable){
            $apiURL = $this->usps_test_api_url.'?API=CarrierPickupAvailability&XML='.$xmlencoded; // (API URL and endpoint)
        }else{
            $apiURL = $this->usps_api_url.'?API=CarrierPickupAvailability&XML='.$xmlencoded; // (API URL and endpoint)
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

        curl_close($curl);

        $simplexml  = simplexml_load_string($curlresponse);
        $json       = json_encode($simplexml);
        $responseArray      = json_decode($json,TRUE);
        return $responseArray;
    }

    public function pickup_from_usps($request)
    {
        $response['status']     = 'error';
        $response['message']    = 'There is some error, please try after some time!';
        
        $pickup_date        = date("m/d/Y", strtotime($request->pickup_date));
        $pickup_name = $request->pickup_name;
        $parts = explode(" ", $pickup_name);
        if(count($parts) > 1) {
            $last_name = array_pop($parts);
            $first_name = implode(" ", $parts);
        }else{
            $first_name = $pickup_name;
            $last_name = "";
        }

        //$pickup_zip = $this->zipLookup($request->pickup_address, $request->pickup_city, $request->pickup_state);
        
        $availability = $this->uspsPickupAvailability($request->pickup_apt, $request->pickup_address, $request->pickup_city, $request->pickup_state,$request->pickup_zip,$request->pickup_date);
        if(isset($availability['Date'])){
            if($availability['Date'] != $pickup_date){
                $response['status']     = 'error';
                $response['message']    = 'Pickup facility not available at selected date!';
                return $response;
            }
        }

        $loopCount      = $request->package_count;
        $dimensions     = $request->dimensions;
        $total_weight   = 0;
        
        for ($i=0; $i < $loopCount; $i++) { 
            $total_weight = $total_weight+$dimensions['weight'][$i];
        }

        $xml = '<CarrierPickupScheduleRequest USERID="'.$this->usps_username.'">
                    <FirstName>'.$first_name.'</FirstName>
                    <LastName>'.$last_name.'</LastName>
                    <FirmName></FirmName>
                    <SuiteOrApt>'.$request->pickup_apt.'</SuiteOrApt>
                    <Address2>'.$request->pickup_address.'</Address2>
                    <Urbanization></Urbanization>
                    <City>'.$request->pickup_city.'</City>
                    <State>'.$request->pickup_state.'</State>
                    <ZIP5>'.str_replace(" ", "", $request->pickup_zip).'</ZIP5>
                    <ZIP4></ZIP4>
                    <Phone>'.$request->pickup_phone.'</Phone>
                    <Extension></Extension>
                    <Package>
                        <ServiceType>'.$request->usps_picup_options.'</ServiceType>
                        <Count>'.$loopCount.'</Count>
                    </Package>
                    <EstimatedWeight>'.round($total_weight).'</EstimatedWeight>
                    <PackageLocation>'.$request->pick_location.'</PackageLocation>
                    <SpecialInstructions>'.$request->pickup_instruction.'</SpecialInstructions>
                </CarrierPickupScheduleRequest>';

        $xmlencoded = urlencode($xml);

        if($this->usps_sandbox_enable){
            $apiURL = $this->usps_stg_api_url.'?API=CarrierPickupSchedule&XML='.$xmlencoded; // (API URL and endpoint)
        }else{
            $apiURL = $this->usps_api_url.'?API=CarrierPickupSchedule&XML='.$xmlencoded; // (API URL and endpoint)
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

        curl_close($curl);
        
        $simplexml  = simplexml_load_string($curlresponse);
        $json       = json_encode($simplexml);
        $responseArray      = json_decode($json,TRUE);
        
        /*
            Save Pickup Request in DB - START
        */
        $pickup_data_to_save                   = new Pickup;
        $pickup_data_to_save->user_id          = auth()->id();
        $pickup_data_to_save->request          = $json;
        $pickup_data_to_save->response         = json_encode($responseArray);
        $pickup_data_to_save->shipped_from     = 'USPS';
        $pickup_data_to_save->save();
        $latest_saved_pickup                   = $pickup_data_to_save->id;
        /*
            Save Pickup Request in DB - END
        */
        if (isset($responseArray['ConfirmationNumber']) && !empty($responseArray['ConfirmationNumber'])) {
            $latestPickup_data = Pickup::where('id', $latest_saved_pickup)->first();
            $latestPickup_data->reference_num = $responseArray['ConfirmationNumber'];
            $latestPickup_data->update();


            $response['status']     = 'success';
            $response['message']    = 'Pickup created successfully! <br>Please save this Pickup Confirmation Number for reference > <b>'.$responseArray['ConfirmationNumber'];
        }else{
            if(isset($responseArray['Error'])){
                $response['message'] = $responseArray['Error']['Description'];
            }else{
                $response['message'] = $responseArray['Description'];
            }
        }
        return $response;
    }

    public function get_in_minutes($time)
    {
        $time = explode(':', $time);
        return ($time[0]*60) + ($time[1]) + ($time[2]/60);
    }


    public function pickup_from_dhl($request)
    {
        $response['status']     = 'error';
        $response['message']    = 'There is some error, please try after some time!';
        $shippData = Shipping::select('request')->where('tracking_number', $request->pickup_tracking_number)->first();
        if ($shippData) {
            $requested_data = json_decode($shippData->request, true);
            
            $pickup_api_data = array();
            $pickup_api_data['plannedPickupDateAndTime'] = $request->pickup_date."T".date("H:i:s", strtotime($request->pickup_start_time));
            $pickup_api_data['closeTime']       = date("H:i", strtotime($request->pickup_end_time));
            $pickup_api_data['location']        = $request->pick_location;
            $pickup_api_data['locationType']    = 'business';
            $pickup_api_data['accounts']        = $requested_data['accounts'];
            
            $pickup_api_data['specialInstructions'][0]['value']     = ($request->pickup_instruction != '')?$request->pickup_instruction:'No intructions';
            $pickup_api_data['specialInstructions'][0]['typeCode']  = 'TBD';
            
            $pickup_api_data['customerDetails']         = $requested_data['customerDetails'];
            $pickup_api_data['customerDetails']['bookingRequestorDetails']      = $requested_data['customerDetails']['receiverDetails'];

            $pickup_api_data['customerDetails']['pickupDetails']['postalAddress']['postalCode']    = $request->pickup_zip;
            $pickup_api_data['customerDetails']['pickupDetails']['postalAddress']['cityName']      = $request->pickup_city;
            $pickup_api_data['customerDetails']['pickupDetails']['postalAddress']['countryCode']   = $request->pickup_country;
            $pickup_api_data['customerDetails']['pickupDetails']['postalAddress']['provinceCode']  = $request->pickup_country;
            $pickup_api_data['customerDetails']['pickupDetails']['postalAddress']['addressLine1']  = $request->pickup_address;
            $pickup_api_data['customerDetails']['pickupDetails']['postalAddress']['countyName']    = $request->pickup_country_name;

            $pickup_api_data['customerDetails']['pickupDetails']['contactInformation']['email']        = $request->pickup_email;
            $pickup_api_data['customerDetails']['pickupDetails']['contactInformation']['phone']        = $request->pickup_phone;
            $pickup_api_data['customerDetails']['pickupDetails']['contactInformation']['mobilePhone']  = $request->pickup_phone;
            $pickup_api_data['customerDetails']['pickupDetails']['contactInformation']['companyName']  = 'Zion Shipping';
            $pickup_api_data['customerDetails']['pickupDetails']['contactInformation']['fullName']     = $request->pickup_name;


            $pickup_api_data['shipmentDetails'][0]['productCode']       = 'D';
            $pickup_api_data['shipmentDetails'][0]['localProductCode']  = 'D';
            
            $pickup_api_data['shipmentDetails'][0]['accounts'][0]['typeCode']  = 'shipper';
            $pickup_api_data['shipmentDetails'][0]['accounts'][0]['number']  = '848430610';
            
            if (isset($requested_data['valueAddedServices']) && !empty($requested_data['valueAddedServices'])) {
                $pickup_api_data['shipmentDetails'][0]['valueAddedServices']  = $requested_data['valueAddedServices'];
            }

            $pickup_api_data['shipmentDetails'][0]['isCustomsDeclarable']   = true;
            $pickup_api_data['shipmentDetails'][0]['declaredValue']         = $requested_data['content']['declaredValue'];
            $pickup_api_data['shipmentDetails'][0]['declaredValueCurrency'] = $requested_data['content']['declaredValueCurrency'];
            $pickup_api_data['shipmentDetails'][0]['unitOfMeasurement']     = 'imperial';
            
            $pickup_api_data['shipmentDetails'][0]['shipmentTrackingNumber']= $request->pickup_tracking_number;
            

            $pickupPackages = array();
            $shipperPackages = $requested_data['content']['packages'];

            foreach ($shipperPackages as $sp_key => $sp_value) {
                if (isset($sp_value['customerReferences'])) {
                    unset($sp_value['customerReferences']);
                }
                if (isset($sp_value['description'])) {
                    unset($sp_value['description']);
                }
                $pickupPackages[$sp_key] = $sp_value;
            }

            $pickup_api_data['shipmentDetails'][0]['packages']              = $pickupPackages;

            // echo json_encode($pickup_api_data);
            // die();
            $apiURL = $this->dhl_api_url.'pickups'; // (API URL and Endpoint)
            // Headers
            $headers = [
                'accept'        => 'application/json',
                'content-type'  => 'application/json',
                'authorization' => 'Basic '.base64_encode($this->dhl_api_key.':'.$this->dhl_api_secret) //(siteid:password)
            ];

            $http_response  = Http::withHeaders($headers)->post($apiURL, $pickup_api_data);
            $statusCode     = $http_response->status();

            $responseBody   = json_decode($http_response->getBody(), true);

            /*
                Save Pickup Request in DB - START
            */
                $pickup_data_to_save                   = new Pickup;
                $pickup_data_to_save->user_id          = auth()->id();
                $pickup_data_to_save->request          = json_encode($pickup_api_data);
                $pickup_data_to_save->response         = $http_response->getBody();
                $pickup_data_to_save->shipped_from     = 'DHL';
                $pickup_data_to_save->save();
                $latest_saved_pickup                   = $pickup_data_to_save->id;
            /*
                Save Pickup Request in DB - END
            */
            if (isset($responseBody['dispatchConfirmationNumbers']) && !empty($responseBody['dispatchConfirmationNumbers'])) {
                
                $latestPickup_data = Pickup::where('id', $latest_saved_pickup)->first();
                $latestPickup_data->reference_num = $responseBody['dispatchConfirmationNumbers'][0];
                $latestPickup_data->update();

                $updateQuote = Quotation::where('shipped_trackingNumber', $request->pickup_tracking_number)->first();
                $updateQuote->pickup_scheduled  = 1;
                $updateQuote->pickup_id         = $latest_saved_pickup;
                $updateQuote->update();

                $response['status']     = 'success';
                $response['message']    = 'Pickup created successfully! <br>Please save this Pickup Reference number for reference > <b>'.$responseBody['dispatchConfirmationNumbers'][0].'</b> <br>The pickup fee is: <b>20.00 USD</b>';
            }elseif (isset($responseBody['detail']) && !empty($responseBody['detail'])) {
                $response['message'] = substr(strstr($responseBody['detail'], ':'), strlen(':'));
            }
        }else{
            $response['message']    = 'Please check or renter your tracking number!';
        }
        return $response;
    }

    public function shipData(Request $request)
    {
        // \DB::enableQueryLog();

        $response['status']                 = 'error';
        $response['package_information']    = 'Please enter tracking number!';
        if ($request->shipped_trackingNumber) {
            $shippData = Quotation::select('request', 'pickup_scheduled')->where('shipped_trackingNumber', $request->shipped_trackingNumber)->first();
            // dd(\DB::getQueryLog());
            if ($shippData) {
                // $shipRequested = json_decode($shippData->request, true);
                // $response['status']     = 'success';
                // $packageData = array();
                // $packageData['package_count']   = count($shipRequested['packages']);
                // $packageData['package_type']    = 'not_contains_document';
                // $packageData['total_value']     = $shipRequested['monetaryAmount'][0]['value'];
                // foreach ($shipRequested['packages'] as $package_num => $package_data) {
                //     if ($package_data['typeCode'] == '2BP') {
                //         $packageData['package_type']    = 'contains_document';
                //     }
                //     $packageData['packages'][$package_num]['weight']    = $package_data['weight'];
                //     $packageData['packages'][$package_num]['length']    = $package_data['dimensions']['length'];
                //     $packageData['packages'][$package_num]['width']     = $package_data['dimensions']['width'];
                //     $packageData['packages'][$package_num]['height']    = $package_data['dimensions']['height'];
                // }
                // $response['package_information'] = json_encode($packageData);
                if ($shippData->pickup_scheduled == 1 || $shippData->pickup_scheduled == '1') {
                    $response['package_information'] = 'Pick Up already scheduled for this tracking number!';
                }else{
                    $response['status']     = 'success';
                    $response['package_information'] = '';
                }
            }else{
                $response['package_information'] = 'No record found with this tracking number!';
            }
        }
        return response()->json($response);
    }
}
