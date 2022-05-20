<?php

namespace Wave\Http\Controllers;

use Auth;
use Illuminate\Http\Request;
use Validator;
use Wave\Quotation;
use Wave\Countries;
use TCG\Voyager\Http\Controllers\Controller;
use Illuminate\Support\Facades\Http;
use Wave\Shipping;
use Wave\Tracking;
use Carbon\Carbon;

class TrackingController extends Controller
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

    public function __construct() {
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
	public function index($tracking_number='1258500983', $track_from='', Request $request)
    {
    	if (strlen($tracking_number) == 10) {
    		$track_from = 'DHL';
    	}elseif (strlen($tracking_number) == 12) {
    		$track_from = 'FEDEX';
    	}else{
    		$track_from = 'UPS';
    	}

    	$response['status'] 	= 'error';
    	$response['message'] 	= 'Tracking number not found in our database!';

    	if (!$tracking_number) {
    		if($request->ajax()){
            	return response()->json($response);
			}else{
				return redirect()->route('wave.home');
			}
		}
    	$shippingData = Shipping::select('id', 'request', 'shipped_from')->where('tracking_number', $tracking_number)->first();

    	if (!$shippingData) {
    		if ($track_from == '') {
    			if($request->ajax()){
	            	return response()->json($response);
				}else{
					return redirect()->route('wave.home')->with(['message' => $response['message'], 'message_type' => 'warning']);
	    		}	
    		}else{
    			if ($track_from == 'DHL') {
	    			$tracking_data = $this->getDHLtracking($tracking_number, $request, array());
	    		}elseif ($track_from == 'UPS') {
	    			$tracking_data = $this->getUPStracking($tracking_number, $request, array());
	    		}elseif ($track_from == 'FEDEX') {
	    			$tracking_data = $this->getFedExtracking($tracking_number, $request, array());
	    		}	
    		}
    	}else{
    		if ($shippingData->shipped_from == 'DHL') {
				$tracking_data = $this->getDHLtracking($tracking_number, $request, $shippingData);
	    	}elseif ($shippingData->shipped_from == 'UPS') {
				$tracking_data = $this->getUPStracking($tracking_number, $request, $shippingData);
	    	}elseif ($track_from == 'FEDEX') {
    			$tracking_data = $this->getFedExtracking($tracking_number, $request, $shippingData);
    		}	
    	}
    	
    	if (isset($tracking_data['status']) && $tracking_data['status'] == 'error') {
    		return redirect()->route('wave.home')->with(['message' => $tracking_data['message'], 'message_type' => 'warning']);
    	}
    	if($request->ajax()){
    		die($tracking_data);
    	}else{
    		return view('theme::tracking.index', compact('tracking_data'));
    	}

	}

    public function save_tracking_in_DB($db_request='', $db_response, $track_from)
    {
    	$tracking_data = new Tracking;
        $tracking_data->user_id       = auth()->id();
        $tracking_data->request       = $db_request;
        $tracking_data->response      = $db_response;
        $tracking_data->shipped_from  = $track_from;
        $tracking_data->save();
        return $tracking_data->id;
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

    public function getFedExtracking($tracking_number='775933830568', $request, $shippingData)
    {
    	$response['status'] 	= 'error';
    	$response['message'] 	= 'Tracking number not found in our database!';

    	$apiURL = $this->fedex_api_url.'track/v1/trackingnumbers'; // (API Live url)

        // Headers
        $headers = [
            'content-type'          => 'application/json',
            'accept'                => 'application/json',
            'authorization'         => 'Bearer '.$this->getFedExBearer()
        ];

        $tracking_api = array();
        $tracking_api['trackingInfo'][0]['trackingNumberInfo']['trackingNumber'] 	= $tracking_number;
        $tracking_api['includeDetailedScans'] 										= true;

        $http_response  = Http::withHeaders($headers)->post($apiURL, $tracking_api);
        $statusCode     = $http_response->status();

        $responseBody   = json_decode($http_response->getBody(), true);

        /*
            Save Track Request in DB - START
        */
        $latest_saved_tracking = $this->save_tracking_in_DB(json_encode($tracking_api), $http_response->getBody(), 'FEDEX');
	    /*
            Save Track Request in DB - END
        */

        $trackResults 	= $responseBody['output']['completeTrackResults'][0]['trackResults'][0];

        if ($statusCode == 200) {
        	if (isset($trackResults['error']['message'])) {
        		$response['message'] = $trackResults['error']['message'];
	            if($request->ajax()){
					return json_encode($response);
				}else{
					return $response;		
		        }
        	}else{
        		$response['status'] = 'success';
				
				if ($shippingData) {
					$shipping_request 	= json_decode($shippingData->request, true);
					$customerDetails 	= $shipping_request['customerDetails'];
					$shipperDetails  	= $customerDetails['shipperDetails']['postalAddress'];
					$receiverDetails 	= $customerDetails['receiverDetails']['postalAddress'];
				}else{
					$shipperDetails 	= $trackResults['shipperInformation']['address'];
					$receiverDetails 	= $trackResults['recipientInformation']['address'];
				}
				
				
				$tracking_data = array();
				$tracking_data['order_number'] 		= ($shippingData->id)??rand(10,100);
				$tracking_data['shipped_from'] 		= ($shippingData->shipped_from)??'FEDEX';
				// $tracking_data['shipped_date'] 		= date("F d, Y", strtotime($responseBody['shipments'][0]['shipmentTimestamp']));
				// $tracking_data['shipped_time'] 		= date("g:i A", strtotime($responseBody['shipments'][0]['shipmentTimestamp']));
				$tracking_data['tracking_number'] 	= $tracking_number;
				if ($shippingData) {
					$tracking_data['shipping_address'] 	= $shipperDetails['addressLine1']." ".$shipperDetails['cityName']." ".$shipperDetails['postalCode'];
				}else{
					$tracking_postal = (isset($shipperDetails['postalCode']) && !empty($shipperDetails['postalCode']))?$shipperDetails['postalCode']:'';
					$tracking_state  = (isset($shipperDetails['stateOrProvinceCode']) && !empty($shipperDetails['stateOrProvinceCode']))?$shipperDetails['stateOrProvinceCode']:'';
					$tracking_data['shipping_address'] 	= $shipperDetails['city']." ".$tracking_postal." ".$tracking_state;
				}
				$tracking_data['shipping_country'] 	= $shipperDetails['countryCode'];
				
				if ($shippingData) {
					$tracking_data['receiver_address'] 	= $receiverDetails['addressLine1']." ".$receiverDetails['cityName']." ".$receiverDetails['postalCode'];
				}else{
					$tracking_postal = (isset($receiverDetails['postalCode']) && !empty($receiverDetails['postalCode']))?$receiverDetails['postalCode']:'';
					$tracking_state  = (isset($receiverDetails['stateOrProvinceCode']) && !empty($receiverDetails['stateOrProvinceCode']))?$receiverDetails['stateOrProvinceCode']:'';
					$tracking_data['receiver_address'] 	= $receiverDetails['city']." ".$tracking_postal." ".$tracking_state;

				}
				$tracking_data['receiver_country'] 	= $receiverDetails['countryCode'];

				$tracking_data['totalWeight'] 		= (isset($trackResults['packageDetails']['weightAndDimensions']['weight'][0]['value']))?$trackResults['packageDetails']['weightAndDimensions']['weight'][0]['value']:'Weight not calculated yet';

				$tracking_data['estimatedDeliveryDate'] = 'Delivery date will update soon';

				$tracking_progress = array();
				$progress = $trackResults['scanEvents'];

				if ($progress) {
					$progress = array_reverse($progress);
					$tracking_data['latest_title'] 				= $trackResults['latestStatusDetail']['statusByLocale'];
					foreach ($progress as $ship_step => $ship_progress) {
						$getTime_only_withTimeZone 	= explode("T", $ship_progress['date']);
						$getTime_only 				= explode("-", $getTime_only_withTimeZone[1]);
						if ($ship_step == 0) {
							$tracking_data['shipped_date'] 		= date("F d, Y", strtotime($ship_progress['date']));
							$tracking_data['shipped_time'] 		= date("g:i A", strtotime($getTime_only[0]));
						}
						if ($ship_progress['derivedStatus'] != 'Initiated') {
							$tracking_progress[$ship_step]['title'] 	= $ship_progress['eventDescription'];						
							$tracking_progress[$ship_step]['step_date'] = date("F d, Y", strtotime($ship_progress['date']));
							$tracking_progress[$ship_step]['step_time'] = date("g:i A", strtotime($getTime_only[0]));
						}
						$tracking_data['latest_date'] 				= date("l, F d, Y", strtotime($ship_progress['date']));
						$tracking_data['latest_time'] 				= date("g:i A", strtotime($getTime_only[0]));
					}
				}else{
					$tracking_data['latest_title'] 				= 'Order Confirmed';
					$tracking_data['latest_date'] 				= date("l, F d, Y", strtotime($trackResults['dateAndTimes'][0]['dateTime']));
					$tracking_data['latest_time'] 				= date("g:i A", strtotime($trackResults['dateAndTimes'][0]['dateTime']));
				}
				if($request->ajax()){
	            	$tracking_data['tracking_progress_html'] 	= $this->get_progress_html($tracking_progress, $tracking_data);
					$response['status'] 		= 'success';
					$response['message'] 		= '';
					$response['tracking_data'] 	= $tracking_data;
					return json_encode($response);
				}else{
					$tracking_data['tracking_progress'] 		= $tracking_progress;
	    		}
        	}
        }else{
        	if($request->ajax()){
            	return json_encode($response);
			}else{
				if (isset($responseBody['errors']) && isset($responseBody['errors'][0]['message'])) {
	        		$response['message'] = $responseBody['errors'][0]['message'];
					return $response;		
	        	}
			}
        	
        }
        return $tracking_data;

    }

    public function getDHLtracking($tracking_number='1258500983', $request, $shippingData)
    {
    	$response['status'] 	= 'error';
    	$response['message'] 	= 'Tracking number not found in our database!';

    	$apiURL = $this->dhl_api_url.'shipments/'.$tracking_number.'/tracking'; // (API URL and Endpoint)
	    // Headers
	    $headers = [
	        'accept'        => 'application/json',
	        'content-type'  => 'application/json',
	        'authorization' => 'Basic '.base64_encode($this->dhl_api_key.':'.$this->dhl_api_secret) //(siteid:password)
	    ];

	    $http_response = Http::withHeaders($headers)->get($apiURL, [
		    'trackingView' => 'all-checkpoints',
		    'levelOfDetail' => 'all',
		]);

	    /*
            Save Track Request in DB - START
        */
        $db_request = $apiURL."?trackingView=all-checkpoints&levelOfDetail=all";
	    $latest_saved_tracking = $this->save_tracking_in_DB($db_request, $http_response->getBody(), 'DHL');
	    /*
            Save Track Request in DB - END
        */
        
		$statusCode     = $http_response->status();

		$responseBody 	= json_decode($http_response->getBody(), true);

		if ($statusCode == 400 || $statusCode == 404) {
			if($request->ajax()){
            	return json_encode($response);
			}else{
				$response['message'] = $responseBody['detail'];
				return $response;
			}
		}else{
			$response['status'] = 'success';
			$responseBody   = json_decode($http_response->getBody(), true);
			
			if ($shippingData) {
				$shipping_request = json_decode($shippingData->request, true);
				$customerDetails = $shipping_request['customerDetails'];
			}else{
				$customerDetails = $responseBody['shipments'][0];
			}
			$shipperDetails  = $customerDetails['shipperDetails']['postalAddress'];
			$receiverDetails = $customerDetails['receiverDetails']['postalAddress'];
		
			$tracking_data = array();
			$tracking_data['order_number'] 		= ($shippingData->id)??rand(10,100);
			$tracking_data['shipped_from'] 		= ($shippingData->shipped_from)??'DHL';
			$tracking_data['shipped_date'] 		= date("F d, Y", strtotime($responseBody['shipments'][0]['shipmentTimestamp']));
			$tracking_data['shipped_time'] 		= date("g:i A", strtotime($responseBody['shipments'][0]['shipmentTimestamp']));
			$tracking_data['tracking_number'] 	= $tracking_number;
			if ($shippingData) {
				$tracking_data['shipping_address'] 	= $shipperDetails['addressLine1']." ".$shipperDetails['cityName']." ".$shipperDetails['postalCode'];
			}else{
				$tracking_data['shipping_address'] 	= $customerDetails['shipperDetails']['serviceArea'][0]['description'];
			}
			$tracking_data['shipping_country'] 	= $shipperDetails['countryCode'];
			
			if ($shippingData) {
				$tracking_data['receiver_address'] 	= $receiverDetails['addressLine1']." ".$receiverDetails['cityName']." ".$receiverDetails['postalCode'];
			}else{
				$tracking_data['receiver_address'] 	= $customerDetails['receiverDetails']['serviceArea'][0]['description'];
			}
			
			$tracking_data['receiver_country'] 	= $receiverDetails['countryCode'];
			
			$tracking_data['totalWeight'] 		= isset($responseBody['shipments'][0]['totalWeight'])?$responseBody['shipments'][0]['totalWeight']:'Weight not calculated yet';
			$tracking_data['estimatedDeliveryDate'] = (isset($responseBody['shipments'][0]['estimatedDeliveryDate']) && !empty($responseBody['shipments'][0]['estimatedDeliveryDate']))?date("d/m/Y", strtotime($responseBody['shipments'][0]['estimatedDeliveryDate'])):'Delivery date will update soon';

			$tracking_progress = array();
			$progress = $responseBody['shipments'][0]['events'];

			if ($progress) {
				foreach ($progress as $ship_step => $ship_progress) {
					$tracking_progress[$ship_step]['title'] 	= $ship_progress['description'];
					$tracking_progress[$ship_step]['step_date'] = date("F d, Y", strtotime($ship_progress['date']));
					$tracking_progress[$ship_step]['step_time'] = date("g:i A", strtotime($ship_progress['time']));
					$tracking_data['latest_title'] 				= $ship_progress['description'];
					$tracking_data['latest_date'] 				= date("l, F d, Y", strtotime($ship_progress['date']));
					$tracking_data['latest_time'] 				= date("g:i A", strtotime($ship_progress['time']));
				}
			}else{
				$tracking_data['latest_title'] 				= 'Order Confirmed';
				$tracking_data['latest_date'] 				= date("l, F d, Y", strtotime($responseBody['shipments'][0]['shipmentTimestamp']));
				$tracking_data['latest_time'] 				= date("g:i A", strtotime($responseBody['shipments'][0]['shipmentTimestamp']));
			}

			if($request->ajax()){
            	$tracking_data['tracking_progress_html'] 	= $this->get_progress_html($tracking_progress, $tracking_data);
				$response['status'] 		= 'success';
				$response['message'] 		= '';
				$response['tracking_data'] 	= $tracking_data;
				return json_encode($response);
			}else{
				$tracking_data['tracking_progress'] 		= $tracking_progress;
    		}
		}
    	return $tracking_data;
    }

    public function getUPStracking($tracking_number='1Z715F5W0391373262', $request, $shippingData)
    {
    	$response['status'] 	= 'error';
    	$response['message'] 	= 'Tracking number not found in our database!';
    	$tracking_data = array();
           
        $db_request = $tracking_number;
	    
        try {
        	$response['status'] = 'success';
			$UPS_tracking = new \Ups\Tracking($this->ups_access_key, $this->ups_user_id, $this->ups_password, false);
			$responseBody = $UPS_tracking->track($tracking_number);
			/*
	            Save Track Request in DB - START
	        */
	        $latest_saved_tracking = $this->save_tracking_in_DB($db_request, json_encode($responseBody), 'UPS');
	    	/*
	            Save Track Request in DB - END
	        */

			if ($shippingData) {
				$shipping_request = json_decode($shippingData->request, true);
				$customerDetails = $shipping_request['customerDetails'];
				$shipperDetails  = $customerDetails['shipperDetails']['postalAddress'];
				$receiverDetails = $customerDetails['receiverDetails']['postalAddress'];
			}else{
				$customerDetails = $responseBody;
				$shipperDetails  = $customerDetails->Shipper->Address;
				$receiverDetails = $customerDetails->ShipTo->Address;
			}
			
			$tracking_data['order_number'] 		= ($shippingData->id)??rand(10,100);
			$tracking_data['shipped_from'] 		= ($shippingData->shipped_from)??'UPS';
			$tracking_data['shipped_date'] 		= date("F d, Y", strtotime($responseBody->PickupDate));
			$tracking_data['shipped_time'] 		= date("g:i A", strtotime('10:00:00'));
			$tracking_data['tracking_number'] 	= $tracking_number;
			if ($shippingData) {
				$tracking_data['shipping_address'] 	= $shipperDetails['addressLine1']." ".$shipperDetails['cityName']." ".$shipperDetails['postalCode'];
				$tracking_data['shipping_country'] 	= $shipperDetails['countryCode'];
			}else{
				$tracking_data['shipping_address'] 	= $shipperDetails->AddressLine1." ".$shipperDetails->City." ".$shipperDetails->StateProvinceCode." ".$shipperDetails->PostalCode;
				$tracking_data['shipping_country'] 	= $shipperDetails->CountryCode;
			}
			
			
			if ($shippingData) {
				$tracking_data['receiver_address'] 	= $receiverDetails['addressLine1']." ".$receiverDetails['cityName']." ".$receiverDetails['postalCode'];
				$tracking_data['receiver_country'] 	= $receiverDetails['countryCode'];
			}else{
				$tracking_data['receiver_address'] 	= $receiverDetails->City." ".$receiverDetails->StateProvinceCode." ".$receiverDetails->PostalCode;
				$tracking_data['receiver_country'] 	= $receiverDetails->CountryCode;
			}
			
			
			$tracking_data['totalWeight'] 			= ($responseBody->ShipmentWeight)?$responseBody->ShipmentWeight->Weight:'Weight not calculated yet';
			if (property_exists($responseBody, 'ScheduledDeliveryDate')) {
				$tracking_data['estimatedDeliveryDate'] = property_exists($responseBody, 'ScheduledDeliveryDate')?date("d/m/Y", strtotime($responseBody->ScheduledDeliveryDate)):'Delivery date will update soon';
			}elseif (property_exists($responseBody->Package, 'RescheduledDeliveryDate')) {
				$tracking_data['estimatedDeliveryDate'] = date("d/m/Y", strtotime($responseBody->Package->RescheduledDeliveryDate));
			}else{
				$tracking_data['estimatedDeliveryDate'] = 'Delivery date will update soon';
			}
			
			$tracking_progress = array();
			
			if ($responseBody->Package) {
				if (is_array($responseBody->Package->Activity)) {
					foreach ($responseBody->Package->Activity as $step => $activity) {
			            $tracking_progress[$step]['title'] 		= $activity->Status->StatusType->Description;
						$tracking_progress[$step]['step_date'] 	= date("F d, Y", strtotime($activity->Date));
						$tracking_progress[$step]['step_time'] 	= date("g:i A", strtotime($activity->Time));
						if ($step == 0) {
							$tracking_data['latest_title'] 				= $activity->Status->StatusType->Description;
							$tracking_data['latest_date'] 				= date("l, F d, Y", strtotime($activity->Date));
							$tracking_data['latest_time'] 				= date("g:i A", strtotime($activity->Time));
						}
			        }
			    }else{
			    	$activity = $responseBody->Package->Activity;
					$tracking_progress[0]['title'] 		= $activity->Status->StatusType->Description;
					$tracking_progress[0]['step_date'] 	= date("F d, Y", strtotime($activity->Date));
					$tracking_progress[0]['step_time'] 	= date("g:i A", strtotime($activity->Time));
					$tracking_data['latest_title'] 				= $activity->Status->StatusType->Description;
					$tracking_data['latest_date'] 				= date("l, F d, Y", strtotime($activity->Date));
					$tracking_data['latest_time'] 				= date("g:i A", strtotime($activity->Time));
				}
			}else{
				$tracking_data['latest_title'] 				= 'Order Confirmed';
				$tracking_data['latest_date'] 				= date("l, F d, Y", strtotime($responseBody->PickupDate));
				$tracking_data['latest_time'] 				= date("g:i A", strtotime('10:00:00'));
			}

			if ($tracking_data['estimatedDeliveryDate'] == 'Delivery date will update soon' && (strpos($tracking_data['latest_title'], 'Delivered') !== false || strpos($tracking_data['latest_title'], 'delivered') !== false)) {
				$tracking_data['estimatedDeliveryDate'] = date("d/m/Y", strtotime($tracking_data['latest_date']));
			}

			$tracking_progress = array_reverse($tracking_progress);

			if($request->ajax()){
		    	$tracking_data['tracking_progress_html'] 	= $this->get_progress_html($tracking_progress, $tracking_data);
				$response['status'] 		= 'success';
				$response['message'] 		= '';
				$response['tracking_data'] 	= $tracking_data;
				return json_encode($response);
			}else{
				$tracking_data['tracking_progress'] 		= $tracking_progress;
			}
        } catch (\Exception $e) {
    		$response['message'] 	= 'Error: '.$e->getMessage();
    		/*
	            Save Track Request in DB - START
	        */
	        $latest_saved_tracking = $this->save_tracking_in_DB($db_request, $response['message'], 'UPS');
	    	/*
	            Save Track Request in DB - END
	        */
    		return $response;
		}
        return $tracking_data;
    }

    public function get_progress_html($events = array(), $tracking_data = array())
    {
    	$html = '';
		$pickup_found = $transit_found = $hold_found = $customs_found = $delay_found = $arrived_found = $awaiting_found = $delivery_found = $delivered_found = false;
		$html .= '<div class="transition-page"><div class="truck-icon">';
				
		if (strpos($tracking_data['latest_title'], 'Pick') !== false || strpos($tracking_data['latest_title'], 'pick') !== false){
			$html .= '<i class="fas fa-people-carry"></i>';
		}elseif(strpos($tracking_data['latest_title'], 'Transit') !== false || strpos($tracking_data['latest_title'], 'transit') !== false || strpos($tracking_data['latest_title'], 'Processed') !== false || strpos($tracking_data['latest_title'], 'processed') !== false || strpos($tracking_data['latest_title'], 'departed') !== false || strpos($tracking_data['latest_title'], 'Departed') !== false || strpos($tracking_data['latest_title'], 'Shipper') !== false || strpos($tracking_data['latest_title'], 'shipper') !== false || strpos($tracking_data['latest_title'], 'Created') !== false || strpos($tracking_data['latest_title'], 'created') !== false || strpos($tracking_data['latest_title'], 'Label') !== false || strpos($tracking_data['latest_title'], 'label') !== false || strpos($tracking_data['latest_title'], 'Origin') !== false || strpos($tracking_data['latest_title'], 'origin') !== false || strpos($tracking_data['latest_title'], 'transferred') !== false || strpos($tracking_data['latest_title'], 'Transferred') !== false || strpos($tracking_data['latest_title'], 'clearance') !== false || strpos($tracking_data['latest_title'], 'Clearance') !== false || strpos($tracking_data['latest_title'], 'Drop-Off') !== false || strpos($tracking_data['latest_title'], 'scheduled') !== false || strpos($tracking_data['latest_title'], 'Scheduled') !== false || strpos($tracking_data['latest_title'], 'At destination sort facility') !== false || strpos($tracking_data['latest_title'], 'International shipment release - Import') !== false || strpos($tracking_data['latest_title'], 'At local FedEx facility') !== false || strpos($tracking_data['latest_title'], 'In FedEx possession') !== false){
			$html .= '<i class="fas fa-truck"></i>';
		}elseif(strpos($tracking_data['latest_title'], 'Hold') !== false || strpos($tracking_data['latest_title'], 'hold') !== false || strpos($tracking_data['latest_title'], 'processing') !== false || strpos($tracking_data['latest_title'], 'Processing') !== false){
			$html .= '<i class="fas fa-hourglass-start"></i>';
		}elseif(strpos($tracking_data['latest_title'], 'Customs') !== false || strpos($tracking_data['latest_title'], 'customs') !== false){
			$html .= '<i class="fas fa-box"></i>';
		}elseif(strpos($tracking_data['latest_title'], 'Delay') !== false || strpos($tracking_data['latest_title'], 'delay') !== false){
			$html .= '<i class="fas fa-stopwatch"></i>';
		}elseif(strpos($tracking_data['latest_title'], 'Arrived') !== false || strpos($tracking_data['latest_title'], 'arrived') !== false){
			$html .= '<i class="fas fa-street-view"></i>';
		}elseif(strpos($tracking_data['latest_title'], 'Delivery') !== false || strpos($tracking_data['latest_title'], 'delivery') !== false){
			$html .= '<i class="fas fa-motorcycle"></i>';
		}elseif(strpos($tracking_data['latest_title'], 'Delivered') !== false || strpos($tracking_data['latest_title'], 'delivered') !== false){
			$html .= '<i class="fas fa-check"></i>';
		}elseif(strpos($tracking_data['latest_title'], 'Awaiting') !== false || strpos($tracking_data['latest_title'], 'awaiting') !== false || strpos($tracking_data['latest_title'], 'Claim') !== false || strpos($tracking_data['latest_title'], 'claim') !== false){
			$html .= '<i class="fas fa-truck-pickup"></i>';
		}else{
			$html .= '<i class="fas fa-thumbs-up"></i>';
		}
		$html .= '</div>';
		$html .= '<h2 class="tracking-heaing">'.$tracking_data['latest_title'].'</h2>';
		$html .= '<p class="tracking-para"><span class="latest_date">'.$tracking_data['latest_date'].'</span> at <span class="latest_time">'.$tracking_data['latest_time'].'</span></p>';
		$html .= '<hr>';
		$html .= '<div class="row rounded top mt-5 mb-5"><div class="col-md-6 py-3"><div class="d-flex flex-column align-items start"><b>Shipping Address</b>';
		$html .= '<p class="text-justify pt-2 shipping_address">'.$tracking_data['shipping_address'].',</p>';
		$html .= '<p class="text-justify shipping_country">'.$tracking_data['shipping_country'].'</p>';
		$html .= '</div></div><div class="col-md-6 py-3"><div class="d-flex flex-column align-items "><b>Receiver Address</b>';
		$html .= '<p class="text-justify pt-2 receiver_address">'.$tracking_data['receiver_address'].',</p>';
		$html .= '<p class="text-receiver_country">'.$tracking_data['receiver_country'].'</p>';
		$html .= '</div></div></div><hr class=" mt-5 mb-5"><div class="row mt-5"><div class="col-md-12">';
		
		if(count($events) == 0){
			$html .= '<div class="tracking-item tracking-active"><div class="tracking-icon status-complete">';	
		}else{
			$html .= '<div class="tracking-item"><div class="tracking-icon status-complete">';
		}

		$html .= '<i class="fas fa-thumbs-up"></i></div>';
		$html .= '<div class="tracking-date shipped_date">'.$tracking_data['shipped_date'].'<span class="shipped_time">'.$tracking_data['shipped_time'].'</span></div><div class="tracking-content">Order Confirmed<span>Seller Confirmed your order</span></div></div>';
		if ($events) {
			foreach($events as $tracking_progress){
				if((strpos($tracking_progress['title'], 'Pick') !== false) && (strpos($tracking_data['latest_title'], 'Pick') !== false)){
					$html .= '<div class="tracking-item tracking-active"><div class="tracking-icon status-complete">';
				}elseif((strpos($tracking_progress['title'], 'Transit') !== false || strpos($tracking_progress['title'], 'transit') !== false || strpos($tracking_progress['title'], 'Processed') !== false || strpos($tracking_progress['title'], 'processed') !== false || strpos($tracking_progress['title'], 'departed') !== false || strpos($tracking_progress['title'], 'Departed') !== false || strpos($tracking_progress['title'], 'label') !== false || strpos($tracking_progress['title'], 'Label') !== false || strpos($tracking_progress['title'], 'created') !== false || strpos($tracking_progress['title'], 'Created') !== false || strpos($tracking_progress['title'], 'Shipper') !== false || strpos($tracking_progress['title'], 'shipper') !== false || strpos($tracking_progress['title'], 'Origin') !== false || strpos($tracking_progress['title'], 'origin') !== false || strpos($tracking_progress['title'], 'transferred') !== false || strpos($tracking_progress['title'], 'Transferred') !== false || strpos($tracking_progress['title'], 'clearance') !== false || strpos($tracking_progress['title'], 'Clearance') !== false || strpos($tracking_progress['title'], 'Drop-Off') !== false || strpos($tracking_progress['title'], 'scheduled') !== false || strpos($tracking_progress['title'], 'Scheduled') !== false || strpos($tracking_progress['title'], 'At destination sort facility') !== false || strpos($tracking_progress['title'], 'International shipment release - Import') !== false || strpos($tracking_progress['title'], 'At local FedEx facility') !== false || strpos($tracking_progress['title'], 'In FedEx possession') !== false) 
						&& 
						(strpos($tracking_data['latest_title'], 'Transit') !== false || strpos($tracking_data['latest_title'], 'transit') !== false || strpos($tracking_data['latest_title'], 'Processed') !== false || strpos($tracking_data['latest_title'], 'processed') !== false || strpos($tracking_data['latest_title'], 'departed') !== false || strpos($tracking_data['latest_title'], 'Departed') !== false || strpos($tracking_data['latest_title'], 'Shipper') !== false || strpos($tracking_data['latest_title'], 'shipper') !== false || strpos($tracking_data['latest_title'], 'Created') !== false || strpos($tracking_data['latest_title'], 'created') !== false || strpos($tracking_data['latest_title'], 'Label') !== false || strpos($tracking_data['latest_title'], 'label') !== false || strpos($tracking_data['latest_title'], 'Origin') !== false || strpos($tracking_data['latest_title'], 'origin') !== false || strpos($tracking_data['latest_title'], 'transferred') !== false || strpos($tracking_data['latest_title'], 'Transferred') !== false || strpos($tracking_data['latest_title'], 'clearance') !== false || strpos($tracking_data['latest_title'], 'Clearance') !== false || strpos($tracking_data['latest_title'], 'Drop-Off') !== false || strpos($tracking_data['latest_title'], 'Scheduled') !== false || strpos($tracking_data['latest_title'], 'scheduled') !== false || strpos($tracking_data['latest_title'], 'At destination sort facility') !== false || strpos($tracking_data['latest_title'], 'International shipment release - Import') !== false || strpos($tracking_data['latest_title'], 'At local FedEx facility') !== false || strpos($tracking_data['latest_title'], 'In FedEx possession') !== false)){
					$html .= '<div class="tracking-item tracking-active"><div class="tracking-icon status-complete">';
				}elseif((strpos($tracking_progress['title'], 'hold') !== false || strpos($tracking_progress['title'], 'Hold') !== false || strpos($tracking_progress['title'], 'Processing') !== false || strpos($tracking_progress['title'], 'processing') !== false) 
						&& 
						(strpos($tracking_data['latest_title'], 'hold') !== false || strpos($tracking_data['latest_title'], 'Hold') !== false || strpos($tracking_data['latest_title'], 'Processing') !== false || strpos($tracking_data['latest_title'], 'processing') !== false)){
					$html .= '<div class="tracking-item tracking-active"><div class="tracking-icon status-complete">';
				}elseif((strpos($tracking_progress['title'], 'Customs') !== false || strpos($tracking_progress['title'], 'customs') !== false) 
						&& 
						(strpos($tracking_data['latest_title'], 'Customs') !== false || strpos($tracking_data['latest_title'], 'customs') !== false)){
					$html .= '<div class="tracking-item tracking-active"><div class="tracking-icon status-complete">';
				}elseif((strpos($tracking_progress['title'], 'Delay') !== false || strpos($tracking_progress['title'], 'delay') !== false) 
						&& 
						(strpos($tracking_data['latest_title'], 'Delay') !== false || strpos($tracking_data['latest_title'], 'delay') !== false)){
					$html .= '<div class="tracking-item tracking-active"><div class="tracking-icon status-complete">';
				}elseif((strpos($tracking_progress['title'], 'Arrived') !== false || strpos($tracking_progress['title'], 'arrived') !== false) 
						&& 
						(strpos($tracking_data['latest_title'], 'Arrived') !== false || strpos($tracking_data['latest_title'], 'arrived') !== false)){
					$html .= '<div class="tracking-item tracking-active"><div class="tracking-icon status-complete">';
				}elseif((strpos($tracking_progress['title'], 'Awaiting') !== false || strpos($tracking_progress['title'], 'awaiting') !== false || strpos($tracking_progress['title'], 'Claim') !== false || strpos($tracking_progress['title'], 'claim') !== false) 
						&& 
						(strpos($tracking_data['latest_title'], 'Awaiting') !== false || strpos($tracking_data['latest_title'], 'awaiting') !== false || strpos($tracking_data['latest_title'], 'Claim') !== false || strpos($tracking_data['latest_title'], 'claim') !== false)){
					$html .= '<div class="tracking-item tracking-active"><div class="tracking-icon status-complete">';
				}elseif((strpos($tracking_progress['title'], 'Out for Delivery') !== false || strpos($tracking_progress['title'], 'Out') !== false || strpos($tracking_progress['title'], 'out') !== false || strpos($tracking_progress['title'], 'Delivery') !== false || strpos($tracking_progress['title'], 'delivery') !== false) 
						&& 
						(strpos($tracking_data['latest_title'], 'Out for Delivery') !== false || strpos($tracking_data['latest_title'], 'Out') !== false || strpos($tracking_data['latest_title'], 'out') !== false || strpos($tracking_data['latest_title'], 'Delivery') !== false || strpos($tracking_data['latest_title'], 'delivery') !== false)){
					$html .= '<div class="tracking-item tracking-active"><div class="tracking-icon status-complete">';
				}elseif((strpos($tracking_progress['title'], 'Delivered') !== false || strpos($tracking_progress['title'], 'delivered') !== false) 
						&& 
						(strpos($tracking_data['latest_title'], 'Delivered') !== false || strpos($tracking_data['latest_title'], 'delivered') !== false)){
					$html .= '<div class="tracking-item tracking-active"><div class="tracking-icon status-complete">';
				}else{
					$html .= '<div class="tracking-item"><div class="tracking-icon status-complete">';
				}
				if (strpos($tracking_progress['title'], 'Pick') !== false || strpos($tracking_progress['title'], 'pick') !== false){
					$pickup_found = true;
					$html .= '<i class="fas fa-people-carry"></i>';
				}elseif (strpos($tracking_progress['title'], 'Transit') !== false || strpos($tracking_progress['title'], 'transit') !== false || strpos($tracking_progress['title'], 'Processed') !== false || strpos($tracking_progress['title'], 'processed') !== false || strpos($tracking_progress['title'], 'departed') !== false || strpos($tracking_progress['title'], 'Departed') !== false || strpos($tracking_progress['title'], 'Shipper') !== false || strpos($tracking_progress['title'], 'shipper') !== false || strpos($tracking_progress['title'], 'Created') !== false || strpos($tracking_progress['title'], 'created') !== false || strpos($tracking_progress['title'], 'Label') !== false || strpos($tracking_progress['title'], 'label') !== false || strpos($tracking_progress['title'], 'Origin') !== false || strpos($tracking_progress['title'], 'origin') !== false || strpos($tracking_progress['title'], 'transferred') !== false || strpos($tracking_progress['title'], 'Transferred') !== false || strpos($tracking_progress['title'], 'clearance') !== false || strpos($tracking_progress['title'], 'Clearance') !== false || strpos($tracking_progress['title'], 'Drop-Off') !== false || strpos($tracking_progress['title'], 'scheduled') !== false || strpos($tracking_progress['title'], 'Scheduled') !== false || strpos($tracking_progress['title'], 'At destination sort facility') !== false || strpos($tracking_progress['title'], 'International shipment release - Import') !== false || strpos($tracking_progress['title'], 'At local FedEx facility') !== false || strpos($tracking_progress['title'], 'In FedEx possession') !== false){
					$transit_found = true;
					$html .= '<i class="fas fa-truck"></i>';
				}elseif (strpos($tracking_progress['title'], 'hold') !== false || strpos($tracking_progress['title'], 'Hold') !== false || strpos($tracking_progress['title'], 'Processing') !== false || strpos($tracking_progress['title'], 'processing') !== false){
					$hold_found = true;
					$html .= '<i class="fas fa-hourglass-start"></i>';
				}elseif (strpos($tracking_progress['title'], 'Customs') !== false || strpos($tracking_progress['title'], 'customs') !== false){
					$customs_found = true;
					$html .= '<i class="fas fa-box"></i>';
				}elseif (strpos($tracking_progress['title'], 'Delay') !== false || strpos($tracking_progress['title'], 'delay') !== false){
					$delay_found = true;
					$html .= '<i class="fas fa-stopwatch"></i>';
				}elseif (strpos($tracking_progress['title'], 'Arrived') !== false || strpos($tracking_progress['title'], 'arrived') !== false){
					$arrived_found = true;
					$html .= '<i class="fas fa-street-view"></i>';
				}elseif (strpos($tracking_progress['title'], 'Awaiting') !== false || strpos($tracking_progress['title'], 'awaiting') !== false || strpos($tracking_progress['title'], 'Claim') !== false || strpos($tracking_progress['title'], 'claim') !== false){
					$awaiting_found = true;
					$html .= '<i class="fas fa-truck-pickup"></i>';
				}elseif (strpos($tracking_progress['title'], 'Out for Delivery') !== false || strpos($tracking_progress['title'], 'Out') !== false || strpos($tracking_progress['title'], 'out') !== false || strpos($tracking_progress['title'], 'Delivery') !== false || strpos($tracking_progress['title'], 'delivery') !== false){
					$delivery_found = true;
					$html .= '<i class="fas fa-motorcycle"></i>';
				}elseif (strpos($tracking_progress['title'], 'Delivered') !== false || strpos($tracking_progress['title'], 'delivered') !== false){
					$delivered_found = true;
					$html .= '<i class="fas fa-check"></i>';
				}
				$html .= '</div>';
				if (strpos($tracking_progress['title'], 'Pick') !== false || strpos($tracking_progress['title'], 'pick') !== false){
					$html .= '<div class="tracking-date step_date">'.$tracking_progress['step_date'].'<span class="step_time">'.$tracking_progress['step_time'].'</span></div><div class="tracking-content">Order Picked up<span class="title">'.$tracking_progress['title'].'</span></div>';
				}elseif (strpos($tracking_progress['title'], 'Transit') !== false || strpos($tracking_progress['title'], 'transit') !== false || strpos($tracking_progress['title'], 'Processed') !== false || strpos($tracking_progress['title'], 'processed') !== false || strpos($tracking_progress['title'], 'departed') !== false || strpos($tracking_progress['title'], 'Departed') !== false || strpos($tracking_progress['title'], 'Shipper') !== false || strpos($tracking_progress['title'], 'shipper') !== false || strpos($tracking_progress['title'], 'Created') !== false || strpos($tracking_progress['title'], 'created') !== false || strpos($tracking_progress['title'], 'Label') !== false || strpos($tracking_progress['title'], 'label') !== false || strpos($tracking_progress['title'], 'Origin') !== false || strpos($tracking_progress['title'], 'origin') !== false || strpos($tracking_progress['title'], 'transferred') !== false || strpos($tracking_progress['title'], 'Transferred') !== false || strpos($tracking_progress['title'], 'clearance') !== false || strpos($tracking_progress['title'], 'Clearance') !== false || strpos($tracking_progress['title'], 'Drop-Off') !== false || strpos($tracking_progress['title'], 'scheduled') !== false || strpos($tracking_progress['title'], 'Scheduled') !== false || strpos($tracking_progress['title'], 'At destination sort facility') !== false || strpos($tracking_progress['title'], 'International shipment release - Import') !== false || strpos($tracking_progress['title'], 'At local FedEx facility') !== false || strpos($tracking_progress['title'], 'In FedEx possession') !== false){
					$html .= '<div class="tracking-date step_date">'.$tracking_progress['step_date'].'<span class="step_time">'.$tracking_progress['step_time'].'</span></div><div class="tracking-content">In Transit<span class="title">'.$tracking_progress['title'].'</span></div>';
				}elseif (strpos($tracking_progress['title'], 'hold') !== false || strpos($tracking_progress['title'], 'Hold') !== false || strpos($tracking_progress['title'], 'Processing') !== false || strpos($tracking_progress['title'], 'processing') !== false){
					$html .= '<div class="tracking-date step_date">'.$tracking_progress['step_date'].'<span class="step_time">'.$tracking_progress['step_time'].'</span></div><div class="tracking-content">On Hold<span class="title">'.$tracking_progress['title'].'</span></div>';
				}elseif (strpos($tracking_progress['title'], 'Customs') !== false || strpos($tracking_progress['title'], 'customs') !== false){
					$html .= '<div class="tracking-date step_date">'.$tracking_progress['step_date'].'<span class="step_time">'.$tracking_progress['step_time'].'</span></div><div class="tracking-content">Customs<span class="title">'.$tracking_progress['title'].'</span></div>';
				}elseif (strpos($tracking_progress['title'], 'Delay') !== false || strpos($tracking_progress['title'], 'delay') !== false){
					$html .= '<div class="tracking-date step_date">'.$tracking_progress['step_date'].'<span class="step_time">'.$tracking_progress['step_time'].'</span></div><div class="tracking-content">Delay <span class="title">'.$tracking_progress['title'].'</span></div>';
				}elseif (strpos($tracking_progress['title'], 'Arrived') !== false || strpos($tracking_progress['title'], 'arrived') !== false){
					$html .= '<div class="tracking-date step_date">'.$tracking_progress['step_date'].'<span class="step_time">'.$tracking_progress['step_time'].'</span></div><div class="tracking-content">Arrived<span class="title">'.$tracking_progress['title'].'</span></div>';
				}elseif (strpos($tracking_progress['title'], 'Awaiting') !== false || strpos($tracking_progress['title'], 'awaiting') !== false || strpos($tracking_progress['title'], 'Claim') !== false || strpos($tracking_progress['title'], 'claim') !== false){
					$html .= '<div class="tracking-date step_date">'.$tracking_progress['step_date'].'<span class="step_time">'.$tracking_progress['step_time'].'</span></div><div class="tracking-content">Awaiting to pickup<span class="title">'.$tracking_progress['title'].'</span></div>';
				}elseif (strpos($tracking_progress['title'], 'Out for Delivery') !== false || strpos($tracking_progress['title'], 'Out') !== false || strpos($tracking_progress['title'], 'out') !== false || strpos($tracking_progress['title'], 'Delivery') !== false || strpos($tracking_progress['title'], 'delivery') !== false){
					$html .= '<div class="tracking-date step_date">'.$tracking_progress['step_date'].'<span class="step_time">'.$tracking_progress['step_time'].'</span></div><div class="tracking-content">Out for Delivery<span class="title">'.$tracking_progress['title'].'</span></div>';
				}elseif (strpos($tracking_progress['title'], 'Delivered') !== false || strpos($tracking_progress['title'], 'delivered') !== false){
					$html .= '<div class="tracking-date step_date">'.$tracking_progress['step_date'].'<span class="step_time">'.$tracking_progress['step_time'].'</span></div><div class="tracking-content">Delivered<span class="title">'.$tracking_progress['title'].'</span></div>';
				}
				$html .= '</div>';
			}
		}
		if(strpos($tracking_data['latest_title'], 'Delivered') !== false || strpos($tracking_data['latest_title'], 'delivered') !== false){
			$pickup_found = $transit_found = $hold_found = $customs_found = $delay_found = $arrived_found = $awaiting_found = $delivery_found = $delivered_found = true;
		}
		if(!$pickup_found){
			$html .= '<div class="tracking-item tracking-pending"><div class="tracking-icon status-pending"><i class="fas fa-people-carry"></i></div><div class="tracking-date step_date"><span>--</span></div><div class="tracking-content">Order Picked up<span>--</span></div></div>';
		}
		if(!$transit_found){
			$html .= '<div class="tracking-item tracking-pending"><div class="tracking-icon status-pending"><i class="fas fa-truck"></i></div><div class="tracking-date"><span>--</span></div><div class="tracking-content">In Transit<span>--</span></div></div>';
		}
		if(!$hold_found){
			$html .= '<div class="tracking-item tracking-pending"><div class="tracking-icon status-pending"><i class="fas fa-hourglass-start"></i></div><div class="tracking-date"><span>--</span></div><div class="tracking-content">On Hold<span>--</span></div></div>';
		}
		if(!$customs_found){
			$html .= '<div class="tracking-item tracking-pending"><div class="tracking-icon status-pending"><i class="fas fa-box"></i></div><div class="tracking-date"><span>--</span></div><div class="tracking-content">Customs<span>--</span></div></div>';
		}
		if(!$delay_found){
			$html .= '<div class="tracking-item tracking-pending"><div class="tracking-icon status-pending"><i class="fas fa-stopwatch"></i></div><div class="tracking-date"><span>--</span></div><div class="tracking-content">Delay <span>--</span></div></div>';
		}
		if(!$arrived_found){
			$html .= '<div class="tracking-item tracking-pending"><div class="tracking-icon status-pending"><i class="fas fa-street-view"></i></div><div class="tracking-date"><span>--</span></div><div class="tracking-content">Arrived<span>--</span></div></div>';
		}
		if(!$awaiting_found){
			$html .= '<div class="tracking-item tracking-pending"><div class="tracking-icon status-pending"><i class="fas fa-truck-pickup"></i></div><div class="tracking-date"><span>--</span></span></div><div class="tracking-content">Awaiting to pickup<span>--</span></div></div>';
		}
		if(!$delivery_found){
			$html .= '<div class="tracking-item tracking-pending"><div class="tracking-icon status-pending"><i class="fas fa-motorcycle"></i></div><div class="tracking-date"><span>--</span></div><div class="tracking-content">Out for Delivery<span>--</span></div></div>';
		}
		if(!$delivered_found){
			$html .= '<div class="tracking-item tracking-pending"><div class="tracking-icon status-pending"><i class="fas fa-check"></i></div><div class="tracking-date"><span>--</span></div><div class="tracking-content">Delivered<span>--</span></div></div>';
		}
		$html .= '</div></div></div>';
    	return $html;
    }

    public function validate_tracking(Request $request)
    {
        $response['status']                 = 'error';
        $response['package_information']    = 'Please enter tracking number!';
        if ($request->shipped_trackingNumber) {
            $response['status']     = 'success';
            $response['package_information'] = '';
        	/*
				For future USE
        	*/
            // $shippData = Quotation::select('request')->where('shipped_trackingNumber', $request->shipped_trackingNumber)->first();
            // if ($shippData) {
            //     $response['status']     = 'success';
            //     $response['package_information'] = '';
            // }else{
            //     $response['package_information'] = 'No record found with this tracking number!';
            // }
        }
        return response()->json($response);
    }
}
