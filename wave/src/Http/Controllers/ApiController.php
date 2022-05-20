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
use Wave\ZionShippingsPayments;
use Carbon\Carbon;
use Wave\Pickup;
use TCG\Voyager\Facades\Voyager;
use UpsRate;
use App;
use Wave\Consignee;
use DB;

class ApiController extends Controller
{
	/**
     * 
     * Function for Zion Phone Payment System and return required information
     * 
     * @param int $tracking_number
     */
    public function shipping_details($tracking_number='')
    {
        // DB::enableQueryLog();
        $track_from = 'DHL';
        if (strlen($tracking_number) == 10) {
            $track_from = 'DHL';
        }elseif (strlen($tracking_number) == 12) {
            $track_from = 'FEDEX';
        }elseif (strlen($tracking_number) >= 18 && strlen($tracking_number) <= 22) {
            $track_from = 'UPS';
        }elseif (strlen($tracking_number) == 8) {
            $track_from = 'ZION';
        }

        $response['status'] = 400;
        if ($tracking_number == '') {
            $response['message'] = 'Please share tracking number.';
        }

        $shippingQuery = Shipping::select('request', 'response', 'shipper_email', 'shipper_phone', 'consignee_phone', 'shipped_from', 'invoice_num', 'other_charge');
        if ($track_from == 'UPS') {
            $shippingData = $shippingQuery->where('tracking_number' , 'like', '%' . $tracking_number . '%')->where('shipped_from', $track_from)->first();
        }else{
            $shippingData = $shippingQuery->where(['tracking_number' => $tracking_number, 'shipped_from' => $track_from])->first();
        }
        
        // dd(DB::getQueryLog());
        if (!$shippingData) {
            $response['status']     = 404;
            $response['message']    = 'No record found with this tracking number.';
        }else{
            $shipping_request           = json_decode($shippingData->request, true);
            $shipping_response          = json_decode($shippingData->response, true);
            if ($shippingData->shipped_from == 'UPS') {
                if (isset($shipping_response['ShipmentResponse']['ShipmentResults']['NegotiatedRateCharges']['TotalCharge']['MonetaryValue']) && !empty($shipping_response['ShipmentResponse']['ShipmentResults']['NegotiatedRateCharges']['TotalCharge']['MonetaryValue'])) {
                    $response['status']          = 200;
                    $response['shipped_by']      = $shipping_request['ShipmentRequest']['Shipment']['Shipper']['Name'];
                    $response['shipped_for']     = $shipping_request['ShipmentRequest']['Shipment']['ShipTo']['Name'];
                    $response['shipper_email']   = $shippingData->shipper_email;
                    $response['shipper_phone']   = $shippingData->shipper_phone;
                    $response['consignee_phone'] = $shippingData->consignee_phone;
                    $response['shipping_cost']   = $shipping_response['ShipmentResponse']['ShipmentResults']['NegotiatedRateCharges']['TotalCharge']['MonetaryValue']." USD";
                    $response['other_charge']    = $shippingData->other_charge;
                    $response['shipped_from']    = $shippingData->shipped_from;
                    $response['invoice_num']     = $shippingData->invoice_num;
                }else{
                    $response['status']     = 404;
                    $response['message']    = 'No record found with this tracking number.';
                }
            }elseif ($shippingData->shipped_from == 'DHL') {
                if (isset($shipping_response['shipmentCharges'][0]['price']) && !empty($shipping_response['shipmentCharges'][0]['price'])) {
                    $response['status']          = 200;
                    $response['shipped_by']      = $shipping_request['customerDetails']['shipperDetails']['contactInformation']['fullName'];
                    $response['shipped_for']     = $shipping_request['customerDetails']['receiverDetails']['contactInformation']['fullName'];
                    $response['shipper_email']   = $shippingData->shipper_email;
                    $response['shipper_phone']   = $shippingData->shipper_phone;
                    $response['consignee_phone'] = $shippingData->consignee_phone;
                    $response['shipping_cost']   = $shipping_response['shipmentCharges'][0]['price']." USD";
                    $response['other_charge']    = $shippingData->other_charge;
                    $response['shipped_from']    = $shippingData->shipped_from;
                    $response['invoice_num']     = $shippingData->invoice_num;
                }else{
                    $response['status']     = 404;
                    $response['message']    = 'No record found with this tracking number.';
                }
            }elseif ($shippingData->shipped_from == 'ZION') {
                $payments = ZionShippingsPayments::where('invoice', $shippingData->invoice_num)->first();
                if (isset($shipping_response['shipmentCharges'][0]['price']) && !empty($shipping_response['shipmentCharges'][0]['price'])) {
                    $response['status']          = 200;
                    $response['shipped_by']      = $shipping_request['customerDetails']['shipperDetails']['contactInformation']['fullName'];
                    $response['shipped_for']     = $shipping_request['customerDetails']['receiverDetails']['contactInformation']['fullName'];
                    $response['shipper_email']   = $shippingData->shipper_email;
                    $response['shipper_phone']   = $shippingData->shipper_phone;
                    $response['consignee_phone'] = $shippingData->consignee_phone;
                    $response['shipping_cost']   = $shipping_response['shipmentCharges'][0]['price']." USD";
                    $response['other_charge']    = $shippingData->other_charge;
                    $response['shipped_from']    = $shippingData->shipped_from;
                    $response['invoice_num']     = $shippingData->invoice_num;
                    if ($payments) {
                        $response['shipping_payment']     = ($payments->shipping)?$payments->shipping:'pending';
                        $response['customs_payment']      = ($payments->customs)?$payments->customs:'pending';
                    }else{
                        $response['shipping_payment']     = 'pending';
                        $response['customs_payment']      = 'pending';
                    }
                }else{
                    $response['status']     = 404;
                    $response['message']    = 'No record found with this tracking number.';
                }
            }elseif ($shippingData->shipped_from == 'FEDEX') {
                if (isset($shipping_response['output']['transactionShipments'][0]['pieceResponses'][0]['netRateAmount']) && !empty($shipping_response['output']['transactionShipments'][0]['pieceResponses'][0]['netRateAmount'])) {
                    $response['status']          = 200;
                    $response['shipped_by']      = $shipping_request['requestedShipment']['shipper']['contact']['personName'];
                    $response['shipped_for']     = $shipping_request['requestedShipment']['recipients'][0]['contact']['personName'];
                    $response['shipper_email']   = $shippingData->shipper_email;
                    $response['shipper_phone']   = $shippingData->shipper_phone;
                    $response['consignee_phone'] = $shippingData->consignee_phone;
                    $response['shipping_cost']   = $shipping_response['output']['transactionShipments'][0]['pieceResponses'][0]['netRateAmount']." USD";
                    $response['other_charge']    = $shippingData->other_charge;
                    $response['shipped_from']    = $shippingData->shipped_from;
                    $response['invoice_num']     = $shippingData->invoice_num;
                }else{
                    $response['status']     = 404;
                    $response['message']    = 'No record found with this tracking number.';
                }
            }
        }
        return response()->json($response);
    }

    /**
     * 
     * Function for Zion Phone Payment System and return consignee address
     * 
     * @param int $phone_or_account
     */
    public function get_address($phone_or_account='')
	{
		$response['status'] = 400;
		if ($phone_or_account == '') {
            $response['message'] = 'Please share phone or account number.';
        }else{
			$consignee_data 	= Consignee::where('consignee_phone', $phone_or_account)->first();
				
			if (!$consignee_data) {
				$response['status'] 	= 404;
				$response['message'] 	= 'Invalid phone or account number!';
			}else{
				$response['status'] 			= 200;
				$complete_address				= $consignee_data->consignee_address." ".$consignee_data->consignee_address_city." ".$consignee_data->consignee_address_state." ".str_replace(" ", "", $consignee_data->consignee_address_zip)." ".$consignee_data->consignee_address_country;			
				$response['complete_address'] 	= $complete_address;
			}
        }
		return response()->json($response);
	}

	/**
     * 
     * Function for Zion Phone Payment System to schedule pickup over phone after success payment
     * 
     * @param int $phone_or_account
     		  date $pickup_date = 'mm-dd-yy'
     		  $pickup_time = 'morning or afternoon'
     */
	public function schedule_pickup(Request $request)
	{
		$response['status'] 					= 400;

		if (!$request->phone_or_account || $request->phone_or_account == '' || !$request->pickup_date || $request->pickup_date == '' || !$request->pickup_time || $request->pickup_time == '') {
			$response['message'] 				= 'Invalid details!';
			return response()->json($response);
		}

		$consignee_data 						= Consignee::where('consignee_phone', $request->phone_or_account)->first();
		if (!$consignee_data) {
			$response['status'] 	= 404;
			$response['message'] 	= 'Invalid phone or account number!';
			return response()->json($response);
		}

		$pickupData 							= array();
		$pickupData['pickup_name'] 				= $consignee_data->consignee_name;
		$pickupData['pickup_country'] 			= $consignee_data->consignee_address_country;
		$pickupData['pickup_address'] 			= $consignee_data->consignee_address;
		$pickupData['pickup_zip'] 				= $consignee_data->consignee_address_zip;
		$pickupData['pickup_city'] 				= $consignee_data->consignee_address_city;
		$pickupData['pickup_state'] 			= $consignee_data->consignee_address_state;
		$pickupData['pickup_phone'] 			= $consignee_data->consignee_phone;
		$pickupData['pickup_email'] 			= '';
		$pickupData['pick_location'] 			= 'Office';
		$pickupData['pickup_instruction'] 		= '';
		$pickupData['package_count'] 			= 1;
		$pickupData['dimensions'][0]['weight']	= 1;
		$pickupData['dimensions'][0]['length']	= 1;
		$pickupData['dimensions'][0]['width']	= 1;
		$pickupData['dimensions'][0]['height']	= 1;
		$pickupData['total_value'] 				= 10;
		$pickupData['pickup_date'] 				= date("Y-m-d", strtotime(str_replace('-', '/', $request->pickup_date)));

		$pickup_for_user 						= $consignee_data->id;

		if ($request->pickup_time == 'morning') {
			$pickupData['pickup_start_time'] 		= '09:00 AM';
			$pickupData['pickup_end_time'] 			= '12:00 PM';
		}else{
			$pickupData['pickup_start_time'] 		= '12:00 PM';
			$pickupData['pickup_end_time'] 			= '05:00 PM';
		}

		$startTimeStamp  = strtotime(date('Y-m-d'));
		$endTimeStamp 	 = strtotime($pickupData['pickup_date']);
		$timeDiff 		 = abs($endTimeStamp - $startTimeStamp);
		$numberDays 	 = $timeDiff/86400;  // 86400 seconds in one day
		$numberDays 	 = intval($numberDays);
		
		$special_message = '';
		/*
			Check If Pickup Date is Today, previous date or weekend	
		*/
		if ($numberDays == 0 || strtotime($pickupData['pickup_date']) < strtotime('now') || (date('N', strtotime($pickupData['pickup_date'])) >= 6)) {
			$special_message = "You have enetered a wrong date, our pickup partner will call you on next working day!";
		}

		/*
            Save Pickup Request in DB - START
        */
        $pickup_reference 					   = rand(111111, 999999);

        $pickup_data_to_save                   = new Pickup;
        $pickup_data_to_save->user_id          = $pickup_for_user;
        $pickup_data_to_save->request          = json_encode($pickupData);
        $pickup_data_to_save->response         = 'Scheduled Pickup from API for consignee - '.$pickup_for_user;
        $pickup_data_to_save->shipped_from     = 'ZION';
        $pickup_data_to_save->reference_num    = $pickup_reference;
		$pickup_data_to_save->save();
        $latest_saved_pickup                   = $pickup_data_to_save->id;
        /*
            Save Pickup Request in DB - END
        */
		$response['status'] 					= 200;
		$response['message'] 					= 'Pickup created successfully! Please save this reference number '.$pickup_reference;
		if ($special_message != '') {
			$response['special_message'] 		= $special_message;
				
		}
		return response()->json($response);
	}


    /**
     * 
     * Function for Zion Phone Payment System to update payments for zion shippings only
     * 
     * @param int $phone_or_account
              date $pickup_date = 'mm-dd-yy'
              $pickup_time = 'morning or afternoon'
     */
    public function update_payment(Request $request)
    {
        // DB::enableQueryLog();
        $response['status'] = 400;
        if ($request->payment_for != 'customs' && $request->payment_for != 'shipping') {
            $response['message']    = 'Phone payments are accepted only for shipping and customs';
        }elseif ($request->payment_status != 'paid' && $request->payment_status != 'failed') {
            $response['message']    = 'Invalid payment status!';
        }else{
            $shippingData = Shipping::select('shipped_from', 'invoice_num')->where('invoice_num' , $request->invoice)->first();
            // dd(DB::getQueryLog());
            if (!$shippingData) {
                $response['status']     = 404;
                $response['message']    = 'No record found with this tracking number.';
            }elseif ($shippingData->shipped_from != 'ZION') {
                $response['message']    = 'Phone Payments are only accepted for ZION SHIPPINGS.';
            }else{
                $payments       = ZionShippingsPayments::where('invoice', $request->invoice)->first();
                $payment_for    = $request->payment_for;
                if (!$payments) {
                    /*
                    Save New ZION Payments - START
                    */
                    $payments_data_to_save                   = new ZionShippingsPayments;
                    $payments_data_to_save->invoice          = $request->invoice;
                    $payments_data_to_save->$payment_for     = $request->payment_status;
                    $payments_data_to_save->save();
                    /*
                    Save New ZION Payments - END
                    */
                    $response['status']     = 200;
                    $response['message']    = 'Payment Status Updated Successfully!';
                }elseif ($payments->$payment_for == 'paid') {
                    $response['message']    = 'Payment is already paid for '.$payment_for;
                }else{
                    /*
                    Update ZION Payments - START
                    */
                    $payments->$payment_for     = $request->payment_status;
                    $payments->update();
                    // dd(DB::getQueryLog());
                    /*
                    Update New ZION Payments - END
                    */
                    $response['status']     = 200;
                    $response['message']    = 'Payment Status Updated Successfully!';
                }
            }
        }
        return response()->json($response);
    }
}
