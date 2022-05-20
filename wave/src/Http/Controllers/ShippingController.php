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
use Carbon\Carbon;
use Wave\Pickup;
use TCG\Voyager\Facades\Voyager;
use UpsRate;
use App;
use Wave\Consignee;
use DB;

class ShippingController extends Controller
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

        
        $this->usps_shipping_sandbox_url            = config('carriers.usps_shipping_sandbox_url');
        $this->usps_shipping_sandbox_account_id     = config('carriers.usps_shipping_sandbox_account_id');
        $this->usps_shipping_sandbox_passphrase     = config('carriers.usps_shipping_sandbox_passphrase');
        $this->usps_shipping_sandbox_requesterid    = config('carriers.usps_shipping_sandbox_requesterid');
        

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
    public function index($user_id, $quote_id, $partner, $selected_shipper, $consignee_id=''){
    	if ($user_id == 'NULL' || $user_id == NULL || is_null($user_id)) {
            return redirect('get-quote');
        }
        $countries 	= Countries::orderBy('country_name', 'ASC')->get();
    	$param 		= base64_decode($partner)."_response";
        $partner    = base64_decode($partner);
        if ($user_id == '') {
            $user_id = auth()->id();
        }
        $consignee_data = array();

        if($consignee_id != ''){
            $consignee_data = Consignee::where('id', $consignee_id)->first();
        }

        $select_columns = array('request', 'shipped', 'shipped_trackingNumber', $param);
        if ($partner == 'ups' || $partner == 'fedex' || $partner == 'usps') {
            $select_columns[] = $partner.'_products'; 
        }

        $quotation_data = Quotation::select($select_columns)->where(['user_id' => $user_id, 'id' => $quote_id])->whereDate('created_at', Carbon::today())->first();
		
        if (!$quotation_data) {
    		return redirect()->route('wave.getQuotationForm');
    	}else{

            if ($quotation_data->shipped == 1) {
                $tracking_number = $quotation_data->shipped_trackingNumber;
                $trackingNumberParts = explode(",", $tracking_number);
                return view('theme::shipping.already_shipped', compact(['tracking_number', 'trackingNumberParts', 'partner']));
            }

    		$quotation_request  = json_decode($quotation_data->request, true);
            if ($partner == 'dhl') {
                $quotation_response = json_decode($quotation_data->$param, true);
            }elseif ($partner == 'ups' || $partner == 'fedex' || $partner == 'usps') {
                $partner_products   = $partner."_products";
                $quotation_response = json_decode($quotation_data->$partner_products, true);
            }

            $shipping_page_info = array();

            foreach ($quotation_request as $quotation_key => $quotation_value) {
                if ($quotation_key == 'customerDetails') {
                    $shipping_page_info['shipper_pin']           = $quotation_value['shipperDetails']['postalCode'];
                    $shipping_page_info['shipper_city']          = $quotation_value['shipperDetails']['cityName'];
                    $shipping_page_info['shipper_state']         = $quotation_value['shipperDetails']['addressLine3'];
                    $shipping_page_info['shipper_country']       = $quotation_value['shipperDetails']['countyName'];
                    $shipping_page_info['shipper_countryCode']   = $quotation_value['shipperDetails']['countryCode'];
                    $shipping_page_info['shipper_addressLine1']  = $quotation_value['shipperDetails']['addressLine1'];
                    $shipping_page_info['shipper_addressLine2']  = $quotation_value['shipperDetails']['addressLine2'];

                    $shipping_page_info['receiver_pin']          = $quotation_value['receiverDetails']['postalCode'];
                    $shipping_page_info['receiver_city']         = $quotation_value['receiverDetails']['cityName'];
                    $shipping_page_info['receiver_state']        = $quotation_value['receiverDetails']['addressLine3'];
                    $shipping_page_info['receiver_country']      = $quotation_value['receiverDetails']['countyName'];
                    $shipping_page_info['receiver_countryCode']  = $quotation_value['receiverDetails']['countryCode'];
                    $shipping_page_info['receiver_addressLine1'] = $quotation_value['receiverDetails']['addressLine1'];
                    $shipping_page_info['receiver_addressLine2'] = $quotation_value['receiverDetails']['addressLine2'];
                }

                if ($quotation_key == 'packages') {
                    $packages = array();
                    foreach ($quotation_value as $package_count => $package_data) {
                        $packages[$package_count]['weight'] = $package_data['weight'];
                        $packages[$package_count]['length'] = $package_data['dimensions']['length'];
                        $packages[$package_count]['width']  = $package_data['dimensions']['width'];
                        $packages[$package_count]['height'] = $package_data['dimensions']['height'];
                        $shipping_page_info['package_type'] = 'not_document';
                        if ($package_data['typeCode'] == '2BP') {
                            $shipping_page_info['package_type'] = 'document';
                        }
                    }
                    $shipping_page_info['packages']         = $packages;   
                    $shipping_page_info['total_packages']   = count($packages); 
                }

                if ($quotation_key == 'monetaryAmount') {
                    $shipping_page_info['package_value'] = $quotation_value[0]['value'];    
                }
            }

            $shippers = array();

            if ($partner == 'dhl') {
                $shippers = $this->getDHLproducts($quotation_response['products'], $selected_shipper);
            }elseif ($partner == 'ups' || $partner == 'fedex' || $partner == 'usps') {
                $shippers = $this->getCarrierproducts($quotation_response, $selected_shipper, $partner);
            }

            if (isset($shippers['selected_shipper']) && !empty($shippers['selected_shipper'])) {
                $shipping_page_info['selected_shipper']        = $shippers['selected_shipper'];
                unset($shippers['selected_shipper']);
            }
        
            $shipping_page_info['shippers']     = $shippers;
            $shipping_page_info['quote_id']     = $quote_id;
            $shipping_page_info['us_holidays']  = $this->us_holidays;   
            $shipping_page_info['partner']      = strtoupper($partner);
    	}
        
        // echo "<pre>";
        // print_r($consignee_data);
        // echo "</pre>";
        // die();

        return view('theme::shipping.index', compact('countries', 'shipping_page_info', 'consignee_data'));
    }

    public function getCarrierproducts($carrier_response, $selected_shipper, $partner='ups')
    {
        $shippers = array();
        foreach ($carrier_response as $shippingRate) {
            $UPSestimatedDeliveryDate = $UPSestimatedDeliveryTime = '';
            if ($partner == 'ups') {
                $UPSestimatedDeliveryDate = $UPSestimatedDeliveryTime = '';
                if (isset($shippingRate['days_to_deliver']) && !empty($shippingRate['days_to_deliver'])) {
                    $day_add = 0;
                    if(date('D') == 'Sat' || date('D') == 'Sun') { 
                        $day_add = 1;
                        if (date('D') == 'Sat') {
                           $day_add = 2;
                        }           
                    }
                    $days_to_add = $shippingRate['days_to_deliver'];
                    if ($day_add > 0) {
                        $days_to_add = $day_add+$days_to_add;
                    }
                    $UPSestimatedDeliveryDate = date("Y-m-d", strtotime("+".$days_to_add." day"));
                    $UPSestimatedDeliveryDate = date("D, M d", strtotime($UPSestimatedDeliveryDate));
                }
            }elseif ($partner == 'fedex') {
                if (isset($shippingRate['estimatedDeliveryDateAndTime']) && !empty($shippingRate['estimatedDeliveryDateAndTime'])) {
                    $UPSestimatedDeliveryDate = date("D, M d", strtotime($shippingRate['estimatedDeliveryDateAndTime']));
                    $UPSestimatedDeliveryTime = date("g:i A", strtotime($shippingRate['estimatedDeliveryDateAndTime']));
                }
            }elseif ($partner == 'usps') {
                if (isset($shippingRate['estimatedDeliveryDateAndTime']) && !empty($shippingRate['estimatedDeliveryDateAndTime'])) {
                    $UPSestimatedDeliveryDate = date("D, M d", strtotime($shippingRate['estimatedDeliveryDateAndTime']));
                    $UPSestimatedDeliveryTime = date("g:i A", strtotime($shippingRate['estimatedDeliveryDateAndTime']));
                }
            }
            $shippers[] = [
                'estimatedDeliveryDate' => $UPSestimatedDeliveryDate,
                'estimatedDeliveryTime' => $UPSestimatedDeliveryTime,
                'product_name'          => $shippingRate['service_name'],
                'product_type'          => (isset($shippingRate['service_type']) && !empty($shippingRate['service_type']))?$shippingRate['service_type']:$shippingRate['service_name'],
                'product_price'         => $shippingRate['total'],
            ];
            if (base64_decode($selected_shipper) == $shippingRate['service_name']) {
                $shippers['selected_shipper']        = base64_decode($selected_shipper);
            }
        }
        return $shippers;
    }

    public function getDHLproducts($carrier_response, $selected_shipper)
    {
        $shippers = array();
        foreach ($carrier_response as $shippersCount => $shipperData) {

            $shippers[$shippersCount]['estimatedDeliveryDate'] = date("D, M d", strtotime($shipperData['deliveryCapabilities']['estimatedDeliveryDateAndTime']));

            $shippers[$shippersCount]['estimatedDeliveryTime'] = date("g:i A", strtotime($shipperData['deliveryCapabilities']['estimatedDeliveryDateAndTime']));

            $shippers[$shippersCount]['product_name']          = $shipperData['productName'];
            $shippers[$shippersCount]['product_type']          = $shipperData['productName'];
            $shippers[$shippersCount]['product_code']          = $shipperData['productCode'];
            
            $shippers[$shippersCount]['product_price']         = $shipperData['totalPrice'][0]['price'];

            if (base64_decode($selected_shipper) == $shipperData['productName']) {
                $shippers['selected_shipper']        = base64_decode($selected_shipper);
            }
        }
        return $shippers;
    }

    public function ship(Request $request){

        if ($request->partner == 'DHL') {
            $ship_response = $this->ship_from_DHL($request);
        }elseif ($request->partner == 'UPS') {
            // $ship_response = $this->ship_from_UPS($request);
            $ship_response = $this->ship_from_UPS_2($request);
        }elseif ($request->partner == 'FEDEX') {
            $ship_response = $this->ship_from_FedEx($request);
        }elseif ($request->partner == 'USPS') {
            $ship_response = $this->ship_from_USPS($request);
        }
        return $ship_response;
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

    public function ship_from_FedEx($request)
    {
        $response['status'] = 'error';
        $apiURL             = $this->fedex_api_url.'ship/v1/shipments'; // (API test url)
        $invoice_num        = $this->generate_invoice_num("FEDEX", $request->quote_id);

        // Headers
        $headers = [
            'content-type'          => 'application/json',
            'accept'                => 'application/json',
            'authorization'         => 'Bearer '.$this->getFedExBearer()
        ];
    
        $loopCount      = $request->package_count;
        $r_dimensions   = $request->dimensions; 

        $shipment = array();
        
        $shipment['openShipmentAction']     = 'CONFIRM';
        $shipment['accountNumber']['value'] = $this->fedex_accountNumber;
        $shipment['labelResponseOptions']   = 'LABEL';

        $shipment['requestedShipment']['shipper']['address']['countryCode']         = $request->from_country;
        $shipment['requestedShipment']['shipper']['address']['postalCode']          = str_replace(" ", "", $request->from_zip);
        $shipment['requestedShipment']['shipper']['address']['city']                = $request->from_city;
        $shipment['requestedShipment']['shipper']['address']['stateOrProvinceCode'] = (strlen($request->from_state) > 3)?'':$request->from_state;
        $shipment['requestedShipment']['shipper']['address']['residential']         = ($request->from_apt && ($request->from_apt != ''))?true:false;
        $shipment['requestedShipment']['shipper']['address']['streetLines'][0]      = ($request->from_apt)?$request->from_apt.", ".$request->from_address:''.$request->from_address;
        $shipment['requestedShipment']['shipper']['address']['streetLines'][1]      = $request->from_state;

        $shipment['requestedShipment']['shipper']['contact']['companyName']         = $request->from_name;
        $shipment['requestedShipment']['shipper']['contact']['personName']          = $request->from_name;
        $shipment['requestedShipment']['shipper']['contact']['phoneNumber']         = $request->from_phone;
        $shipment['requestedShipment']['shipper']['contact']['emailAddress']        = ($request->from_email)?$request->from_email:'ziontech2010@yahoo.com';

        $shipment['requestedShipment']['recipients'][0]['contact']['companyName']   = '';
        $shipment['requestedShipment']['recipients'][0]['contact']['phoneNumber']   = $request->to_phone_1;
        $shipment['requestedShipment']['recipients'][0]['contact']['personName']    = $request->to_name;
        $shipment['requestedShipment']['recipients'][0]['contact']['emailAddress']  = '';

        $shipment['requestedShipment']['recipients'][0]['address']['city']                  = $request->to_city;
        $shipment['requestedShipment']['recipients'][0]['address']['countryCode']           = $request->to_country;
        $shipment['requestedShipment']['recipients'][0]['address']['postalCode']            = str_replace(" ", "", $request->to_zip);
        $shipment['requestedShipment']['recipients'][0]['address']['residential']           = false;
        $shipment['requestedShipment']['recipients'][0]['address']['stateOrProvinceCode']   = (strlen($request->to_state) > 3)?'':$request->to_state;
        $shipment['requestedShipment']['recipients'][0]['address']['streetLines'][0]        = $request->to_address;
        $shipment['requestedShipment']['recipients'][0]['address']['streetLines'][1]        = $request->to_state;
        $shipment['requestedShipment']['recipients'][0]['address']['streetLines'][2]        = '';

        if(date('D') == 'Sat' || date('D') == 'Sun') { 
            $day_add = 1;
            if (date('D') == 'Sat') {
               $day_add = 2;
            }           
            $shipment['requestedShipment']['shipTimestamp']                                 = date("M-d-Y", strtotime("+".$day_add." day"));
        } else {
            $shipment['requestedShipment']['shipTimestamp']                                 = date("M-d-Y", strtotime("+1 day"));
        }

        $shipment['requestedShipment']['pickupType']                                            = 'DROPOFF_AT_FEDEX_LOCATION';
        if ($request->pickup_required && $request->pickup_required == 'yes') {
            $shipment['requestedShipment']['pickupType']                                        = 'USE_SCHEDULED_PICKUP';
        }

        $shipment['requestedShipment']['serviceType']                                       = $request->delivery_option;
        $shipment['requestedShipment']['packagingType']                                     = 'YOUR_PACKAGING';
        
        $shipment['requestedShipment']['specialServicesRequested']['specialServiceTypes']   = array();
        
        $shipment['requestedShipment']['shippingChargesPayment']['paymentType']                                             = 'SENDER';
        $shipment['requestedShipment']['shippingChargesPayment']['payor']['responsibleParty']['accountNumber']['value']     = $this->fedex_accountNumber;

        if ($request->shipment_type && $request->shipment_type == 'contains_document') {
            $shipment['requestedShipment']['customsClearanceDetail']['documentContent']         = 'DOCUMENTS';
        }else{
            $shipment['requestedShipment']['customsClearanceDetail']['documentContent']         = 'NON_DOCUMENTS';
        }

        $shipment['requestedShipment']['customsClearanceDetail']['dutiesPayment']['paymentType']                                            = 'SENDER';
        $shipment['requestedShipment']['customsClearanceDetail']['dutiesPayment']['payor']['responsibleParty']['accountNumber']['value']    = $this->fedex_accountNumber;

        $shipment['requestedShipment']['customsClearanceDetail']['commercialInvoice']['shipmentPurpose']    = 'SOLD';
        $shipment['requestedShipment']['customsClearanceDetail']['commercialInvoice']['termsOfSale']        = 'DAP';
        $shipment['requestedShipment']['customsClearanceDetail']['commercialInvoice']['specialInstructions']= '';
        
        $shipment['requestedShipment']['customsClearanceDetail']['commodities'][0]['name']                  = ($request->package_description)?$request->package_description:'No Name';
        $shipment['requestedShipment']['customsClearanceDetail']['commodities'][0]['description']           = ($request->package_description)?$request->package_description:'No Description';
        $shipment['requestedShipment']['customsClearanceDetail']['commodities'][0]['countryOfManufacture']  = $request->from_country;
        $shipment['requestedShipment']['customsClearanceDetail']['commodities'][0]['numberOfPieces']        = $loopCount;
        $shipment['requestedShipment']['customsClearanceDetail']['commodities'][0]['weight']['units']       = 'LB';

        $total_weight = 0;
        for ($i=0; $i < $loopCount; $i++) { 
            $total_weight = $total_weight+$r_dimensions['weight'][$i];   
        }

        $shipment['requestedShipment']['customsClearanceDetail']['commodities'][0]['weight']['value']       = $total_weight;

        $shipment['requestedShipment']['customsClearanceDetail']['commodities'][0]['quantity']              = $loopCount;
        $shipment['requestedShipment']['customsClearanceDetail']['commodities'][0]['quantityUnits']         = 'PCS';
        
        $shipment['requestedShipment']['customsClearanceDetail']['commodities'][0]['unitPrice']['amount']   = $request->total_value;
        $shipment['requestedShipment']['customsClearanceDetail']['commodities'][0]['unitPrice']['currency'] = 'USD';

        $shipment['requestedShipment']['customsClearanceDetail']['commodities'][0]['customsValue']['amount']    = $request->total_value;
        $shipment['requestedShipment']['customsClearanceDetail']['commodities'][0]['customsValue']['currency']  = 'USD';

        $shipment['requestedShipment']['customsClearanceDetail']['commodities'][0]['harmonizedCode']            = NULL;

        $shipment['requestedShipment']['labelSpecification']['printerType']                                 = 'PDF';
        $shipment['requestedShipment']['labelSpecification']['paperType']                                   = 'LETTER';
        $shipment['requestedShipment']['labelSpecification']['autoPrint']                                   = false;
        $shipment['requestedShipment']['labelSpecification']['returnedDispositionDetail']                   = true;
        $shipment['requestedShipment']['labelSpecification']['emailDispositionDetail']['emailAddress']      = $request->from_email;
        $shipment['requestedShipment']['labelSpecification']['emailDispositionDetail']['recipientType']     = 'SHIPPER';
        $shipment['requestedShipment']['labelSpecification']['emailDispositionDetail']['type']              = 'EMAILED';

        $shipment['requestedShipment']['labelSpecification']['imageType']                                   = 'PNG';
        $shipment['requestedShipment']['labelSpecification']['labelStockType']                              = 'PAPER_LETTER';

        $shipment['requestedShipment']['labelStockType']                                                    = 'PAPER_LETTER';
        
        $shipment['requestedShipment']['shippingDocumentSpecification']['commercialInvoiceDetail']['documentFormat']['imageType']   = 'PDF';
        $shipment['requestedShipment']['shippingDocumentSpecification']['commercialInvoiceDetail']['documentFormat']['stockType']   = 'PAPER_LETTER';
        $shipment['requestedShipment']['shippingDocumentSpecification']['commercialInvoiceDetail']['documentFormat']['docType']     = 'PDF';

        $shipment['requestedShipment']['shippingDocumentSpecification']['commercialInvoiceDetail']['customerImageUsages']           = array();

        $shipment['requestedShipment']['shippingDocumentSpecification']['shippingDocumentTypes'][0]                                 = 'COMMERCIAL_INVOICE';

        for ($i=0; $i < $loopCount; $i++) { 
            $shipment['requestedShipment']['requestedPackageLineItems'][$i]['itemDescriptionForClearance']  = NULL;
            $shipment['requestedShipment']['requestedPackageLineItems'][$i]['groupPackageCount']            = 1;
            $shipment['requestedShipment']['requestedPackageLineItems'][$i]['insuredValue']['amount']       = '0';
            $shipment['requestedShipment']['requestedPackageLineItems'][$i]['insuredValue']['currency']     = 'USD';
            $shipment['requestedShipment']['requestedPackageLineItems'][$i]['weight']['units']              = 'LB';
            $shipment['requestedShipment']['requestedPackageLineItems'][$i]['weight']['value']              = $r_dimensions['weight'][$i];

            $shipment['requestedShipment']['requestedPackageLineItems'][$i]['customerReferences'][0]['customerReferenceType']   = 'INVOICE_NUMBER';
            $shipment['requestedShipment']['requestedPackageLineItems'][$i]['customerReferences'][0]['value']                   = $invoice_num;

            // $shipment['requestedShipment']['requestedPackageLineItems'][$i]['customerReferences'][1]['customerReferenceType']   = 'P_O_NUMBER';
            // $shipment['requestedShipment']['requestedPackageLineItems'][$i]['customerReferences'][1]['value']                   = '';

            // $shipment['requestedShipment']['requestedPackageLineItems'][$i]['customerReferences'][2]['customerReferenceType']   = 'DEPARTMENT_NUMBER';
            // $shipment['requestedShipment']['requestedPackageLineItems'][$i]['customerReferences'][2]['value']                   = '';

            $shipment['requestedShipment']['requestedPackageLineItems'][$i]['customerReferences'][1]['customerReferenceType']   = 'CUSTOMER_REFERENCE';
            $shipment['requestedShipment']['requestedPackageLineItems'][$i]['customerReferences'][1]['value']                   = $invoice_num;

            $shipment['requestedShipment']['requestedPackageLineItems'][$i]['dimensions']['length']                             = $r_dimensions['length'][$i];
            $shipment['requestedShipment']['requestedPackageLineItems'][$i]['dimensions']['width']                              = $r_dimensions['width'][$i];
            $shipment['requestedShipment']['requestedPackageLineItems'][$i]['dimensions']['height']                             = $r_dimensions['height'][$i];
            $shipment['requestedShipment']['requestedPackageLineItems'][$i]['dimensions']['units']                              = 'IN';

            $shipment['requestedShipment']['requestedPackageLineItems'][$i]['packageSpecialServices']['specialServiceTypes']    = array();
        }

        $shipment['requestedShipment']['emailNotificationDetail']['recipients'][0]['emailNotificationRecipientType']    = 'RECIPIENT';
        $shipment['requestedShipment']['emailNotificationDetail']['recipients'][0]['notificationFormatType']            = 'HTML';
        $shipment['requestedShipment']['emailNotificationDetail']['recipients'][0]['notificationEventType'][0]          = 'ON_TENDER';
        $shipment['requestedShipment']['emailNotificationDetail']['recipients'][0]['notificationEventType'][1]          = 'ON_SHIPMENT';
        $shipment['requestedShipment']['emailNotificationDetail']['recipients'][0]['notificationEventType'][2]          = 'ON_EXCEPTION';
        $shipment['requestedShipment']['emailNotificationDetail']['recipients'][0]['notificationEventType'][3]          = 'ON_ESTIMATED_DELIVERY';
        $shipment['requestedShipment']['emailNotificationDetail']['recipients'][0]['notificationEventType'][4]          = 'ON_DELIVERY';
        $shipment['requestedShipment']['emailNotificationDetail']['recipients'][0]['notificationType']                  = 'EMAIL';
        $shipment['requestedShipment']['emailNotificationDetail']['recipients'][0]['emailAddress']                      = $request->from_email;
        $shipment['requestedShipment']['emailNotificationDetail']['recipients'][0]['locale']                            = 'en';

        $shipment['requestedShipment']['emailNotificationDetail']['recipients'][1]['emailNotificationRecipientType']    = 'SHIPPER';
        $shipment['requestedShipment']['emailNotificationDetail']['recipients'][1]['notificationFormatType']            = 'HTML';
        $shipment['requestedShipment']['emailNotificationDetail']['recipients'][1]['notificationEventType'][0]          = 'ON_DELIVERY';
        $shipment['requestedShipment']['emailNotificationDetail']['recipients'][1]['notificationEventType'][1]          = 'ON_EXCEPTION';
        $shipment['requestedShipment']['emailNotificationDetail']['recipients'][1]['notificationEventType'][2]          = 'ON_SHIPMENT';
        $shipment['requestedShipment']['emailNotificationDetail']['recipients'][1]['notificationEventType'][3]          = 'ON_ESTIMATED_DELIVERY';
        $shipment['requestedShipment']['emailNotificationDetail']['recipients'][1]['notificationEventType'][4]          = 'ON_TENDER';
        $shipment['requestedShipment']['emailNotificationDetail']['recipients'][1]['notificationType']                  = 'EMAIL';
        $shipment['requestedShipment']['emailNotificationDetail']['recipients'][1]['emailAddress']                      = 'ziontech2010@yahoo.com';
        $shipment['requestedShipment']['emailNotificationDetail']['recipients'][1]['locale']                            = 'en';

        $http_response  = Http::withHeaders($headers)->post($apiURL, $shipment);

        $statusCode     = $http_response->status();

        $responseBody   = json_decode($http_response->getBody(), true);

        $master_tracking_numbers = $tracking_numbers = $invoice_n_label = array();
        if (isset($responseBody['output']['transactionShipments'][0]['masterTrackingNumber']) && !empty($responseBody['output']['transactionShipments'][0]['masterTrackingNumber'])) {
            $master_tracking_numbers[] = $responseBody['output']['transactionShipments'][0]['masterTrackingNumber'];
        }

        if (isset($responseBody['output']['transactionShipments'][0]['pieceResponses']) && !empty($responseBody['output']['transactionShipments'][0]['pieceResponses'])) {
            foreach ($responseBody['output']['transactionShipments'][0]['pieceResponses'] as $tracking_key => $tracking_data) {
                $tracking_numbers[]     = $tracking_data['trackingNumber'];
                $invoice_n_label[]      = $tracking_data['packageDocuments'][0]['encodedLabel'];
            }
        }

        /*
        Save Shipping Request in DB - START
        */
        $shipping_data_to_save                   = new Shipping;
        $shipping_data_to_save->user_id          = auth()->id();
        $shipping_data_to_save->request          = json_encode($shipment);
        $shipping_data_to_save->response         = $http_response->getBody();
        $shipping_data_to_save->tracking_number  = ($master_tracking_numbers)?implode(", ", $master_tracking_numbers):NULL;
        $shipping_data_to_save->shipped_from     = 'FEDEX';
        $shipping_data_to_save->invoice_num      = $invoice_num;
        $shipping_data_to_save->shipper_email    = $request->from_email;
        $shipping_data_to_save->shipper_phone    = $request->from_phone;
        $shipping_data_to_save->consignee_phone  = $request->to_phone_1;
        $shipping_data_to_save->other_charge     = rand(10,100);
        $shipping_data_to_save->save();
        $latest_saved_shipping                   = $shipping_data_to_save->id;
        /*
        Save Shipping Request in DB - END
        */

        if($tracking_numbers){
            $fedex_ref                          = 'ZSP'.rand(1111111,9999999);
            if ($request->pickup_required && $request->pickup_required == 'yes') {
                /*
                    Save Pickup Request in DB - START
                */
                    $pickup_data_to_save                    = new Pickup;
                    $pickup_data_to_save->user_id           = auth()->id();
                    $pickup_data_to_save->request           = json_encode($shipment);
                    $pickup_data_to_save->response          = $http_response->getBody();
                    $pickup_data_to_save->shipped_from      = 'FEDEX';
                    $pickup_data_to_save->reference_num     = $fedex_ref;
                    $pickup_data_to_save->save();
                    $latest_saved_pickup                    = $pickup_data_to_save->id;
                /*
                    Save Pickup Request in DB - END
                */
            }

            $response['status'] = 'success';
            $updateQuote = Quotation::find($request->quote_id);
            $updateQuote->shipped                = 1;
            $updateQuote->shipped_trackingNumber = implode(", ", $master_tracking_numbers);
            if ($request->pickup_required && $request->pickup_required == 'yes') {
                $updateQuote->pickup_scheduled       = 1;
                $updateQuote->pickup_id              = $latest_saved_pickup;
            }
            $updateQuote->update();

            // $pickup_id = array();

            // if ($request->pickup_required && $request->pickup_required == 'yes') {
            //     $pickup_id = $this->pickup_from_ups($request);
            // }

            $label_html = '';            
            foreach ($invoice_n_label as $label_count => $labels) {
                $filename = "label_".$tracking_numbers[$label_count].".PNG";
                \Storage::disk('public')->put("label/".$filename,base64_decode($labels));
                $invoice_n_label['label_'.$label_count] = url("label/".$filename);
            }

            $receiptFilename = "receipt_".trim($master_tracking_numbers[0]).".pdf";            
            $this->generateReceipt($receiptFilename, $invoice_num, $request, $shipment['requestedShipment']['shipTimestamp'], 'FeDex');


            $pdfFilename = "label_".trim($master_tracking_numbers[0]).".pdf";
            if (!file_exists(storage_path('app/public/label/').'/'.$pdfFilename)) {
                $this->generateFedExShipmentLabelPDF($tracking_numbers, $pdfFilename);
            }

            if (isset($responseBody['output']['transactionShipments'][0]['shipmentDocuments'][0]['encodedLabel'])) {
                $invFilename = "invoice_".trim($master_tracking_numbers[0]).".pdf";
                \Storage::disk('public')->put("invoice/".$invFilename,base64_decode($responseBody['output']['transactionShipments'][0]['shipmentDocuments'][0]['encodedLabel']));   
            }

            // $receiptFilename = "receipt_".trim($master_tracking_numbers[0]).".pdf";

            $buttons_class = 'col-lg-4';
            $invoice_class = '';
            if ($request->from_country == $request->to_country) {
                $buttons_class = 'col-lg-6';
                $invoice_class = 'd-none';
            }

            $label_html .= '<div class="'.$buttons_class.'" id="label_btn"><div class="btn-wrap text-center"><a target="_blank" href="'.url("label/$pdfFilename").'" class="cstm-btn quote-btn">View Label</a></div></div>';
            $html = '<div class="row"><div class="col-lg-12"><div class="form-card"><div class="form-head"><h3>VIEW LABELS DOCUMENTS AND RECEIPT</h3></div><div class="form-body"><div class="row" id="download_buttons">'.$label_html.'<div class="'.$buttons_class.' '.$invoice_class.'" id="invoice_btn"><div class="btn-wrap text-center"><a target="_blank" href="'.url("invoice/".$invFilename).'" class="cstm-btn quote-btn inactiveLink">View Invoice</a></div></div><div class="'.$buttons_class.'" id="receipt_btn"><div class="btn-wrap text-center"><a target="_blank" href="'.url("receipt/".$receiptFilename).'" class="cstm-btn quote-btn inactiveLink">View Receipt</a></div></div></div></div></div></div></div>';

            if ($request->pickup_required && $request->pickup_required == 'yes') {
                $html .= '<div class="row"><div class="col-lg-12"><div class="form-card"><div class="form-head"><h3>PICKUP COMFIRMATION</h3></div><div class="form-body"><div class="row"><div class="col-lg-12"><p>Pickup created successfully! <br>Please save this Pickup Reference number for reference > <b>'.$fedex_ref.'</b> <br>The pickup fee is: <b>20.00 USD</b></p></div></div></div></div></div></div>';
            }

            // if (isset($pickup_id['status']) && $pickup_id['status'] == 'success') {
            //     $html .= '<div class="row"><div class="col-lg-12"><div class="form-card"><div class="form-head"><h3>PICKUP COMFIRMATION</h3></div><div class="form-body"><div class="row"><div class="col-lg-12"><p> '.$pickup_id['message'].'</p></div></div></div></div></div></div>';
            // }elseif (isset($pickup_id['status']) && $pickup_id['status'] == 'error' && $pickup_id['message'] != '') {
            //     $html .= '<div class="row"><div class="col-lg-12"><div class="form-card"><div class="form-head"><h3>PICKUP COMFIRMATION</h3></div><div class="form-body"><div class="row"><div class="col-lg-12"><p>'.$pickup_id['message'].'</p></div><div class="col-lg-12"><p>Please contact agent to schecule pickup with tracking number.</p></div></div></div></div></div></div>';
            // }
            $response['html'] = $html;
        }else{
            $response['html'] = '<div class="row"><div class="col-lg-12"><div class="form-card"><div class="form-head"><h3>Package Information</h3></div><div class="form-body"><div class="row"><div class="col-lg-12"><b>There is some error, please try again later or You can call the office on (305-515-2616)</b></div></div></div></div></div></div>';
        }
        return json_encode($response);
    }

    public function getUSPSMailClass($delivery_option){
        if(strpos($delivery_option, 'Priority') !== false) {
            $mailclass = 'Priority';
        }
        if(strpos($delivery_option, 'Priority') !== false && strpos($delivery_option, 'Express') !== false) {
            $mailclass = 'PriorityExpress';
        }
        if(strpos($delivery_option, 'Priority') !== false && strpos($delivery_option, 'International') !== false) {
            $mailclass = 'PriorityMailInternational';
        }
        if(strpos($delivery_option, 'Priority') !== false && strpos($delivery_option, 'Express') !== false && strpos($delivery_option, 'International') !== false) {
            $mailclass = 'PriorityMailExpressInternational';
        }
        
        if($delivery_option == "USPS Retail Ground"){
            $mailclass = 'RetailGround';
        }
        if($delivery_option == "Media Mail Parcel"){
            $mailclass = 'MediaMail';
        }
        if($delivery_option == "Library Mail Parcel"){
            $mailclass = 'LibraryMail';
        }
        return $mailclass;
    }

    public function ship_from_USPS($request){
        $response['status'] = 'error';
        $apiURL             = $this->usps_shipping_sandbox_url; // (API test url)
        //$invoice_num        = $this->generate_invoice_num("USPS", $request->quote_id);
        $label_count        = 1;
        $tracking_numbers   = [];
        $label_html = '';
        $international = 0;

        $loopCount      = $request->package_count;
        $r_dimensions   = $request->dimensions;

        $total_weight = 0;
        for ($i=0; $i < $loopCount; $i++) { 
            $total_weight = $total_weight+$r_dimensions['weight'][$i];   
        }
        $total_weight_oz = $total_weight*16;
        //LabeRequest Test="Yes" for sandbox mode only
        $mailclass = $this->getUSPSMailClass($request->delivery_option);
        //echo $mailclass;die;
        $xml = '<soap:Envelope xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
                            xmlns:xsd="http://www.w3.org/2001/XMLSchema"
                            xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/">
                    <soap:Body>
                        <GetPostageLabel xmlns="www.envmgr.com/LabelService">
                            <LabelRequest Test="Yes"
                                          LabelSize="4x6">
                                <MailClass>'.$mailclass.'</MailClass>
                                <WeightOz>'.$total_weight_oz.'</WeightOz>
                                <RequesterID>'.$this->usps_shipping_sandbox_requesterid.'</RequesterID>
                                <AccountID>'.$this->usps_shipping_sandbox_account_id.'</AccountID>
                                <PassPhrase>'.$this->usps_shipping_sandbox_passphrase.'</PassPhrase>
                                <PartnerCustomerID>100</PartnerCustomerID>
                                <PartnerTransactionID>200</PartnerTransactionID>
                                <ToName>'.$request->to_name.'</ToName>
                                <ToAddress1>'.$request->to_address.'</ToAddress1>
                                <ToCity>'.$request->to_city.'</ToCity>
                                <ToState>'.$request->to_state.'</ToState>
                                <ToPostalCode>'.$request->to_zip.'</ToPostalCode>
                                <FromCompany>'.$request->from_name.'</FromCompany>
                                <FromName>'.$request->from_name.'</FromName>
                                <ReturnAddress1>'.$request->from_address.'</ReturnAddress1>
                                <FromCity>'.$request->from_city.'</FromCity>
                                <FromState>'.$request->from_state.'</FromState>
                                <FromPostalCode>'.$request->from_zip.'</FromPostalCode>
                            </LabelRequest>
                        </GetPostageLabel>
                    </soap:Body>
                </soap:Envelope>';

        if($request->from_country != $request->to_country){
            $xml = '<soap:Envelope xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
                            xmlns:xsd="http://www.w3.org/2001/XMLSchema"
                            xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/">
                    <soap:Body>
                        <GetPostageLabel xmlns="www.envmgr.com/LabelService">
                            <LabelRequest LabelType="International"
                                          Test="Yes"
                                          LabelSize="4x6">
                                <MailClass>'.$mailclass.'</MailClass>
                                <WeightOz>'.$total_weight_oz.'</WeightOz>
                                <RequesterID>'.$this->usps_shipping_sandbox_requesterid.'</RequesterID>
                                <AccountID>'.$this->usps_shipping_sandbox_account_id.'</AccountID>
                                <PassPhrase>'.$this->usps_shipping_sandbox_passphrase.'</PassPhrase>
                                <PartnerCustomerID>100</PartnerCustomerID>
                                <PartnerTransactionID>200</PartnerTransactionID>
                                <ToName>'.$request->to_name.'</ToName>
                                <ToAddress1>'.$request->to_address.'</ToAddress1>
                                <ToCity>'.$request->to_city.'</ToCity>
                                <ToState>'.$request->to_state.'</ToState>
                                <ToPostalCode>'.$request->to_zip.'</ToPostalCode>
                                <ToCountry>'.$request->to_country_name.'</ToCountry>
                                <FromCompany>'.$request->from_name.'</FromCompany>
                                <FromName>'.$request->from_name.'</FromName>
                                <ReturnAddress1>'.$request->from_address.'</ReturnAddress1>
                                <FromCity>'.$request->from_city.'</FromCity>
                                <FromState>'.$request->from_state.'</FromState>
                                <FromPostalCode>'.$request->from_zip.'</FromPostalCode>
                                <FromPhone>'.$request->from_phone.'</FromPhone>
                                <FromCountry>'.$request->from_country_name.'</FromCountry>
                                <Description>Package</Description>
                                <Value>'.$request->total_value.'</Value>
                                <CustomsInfo>
                                    <ContentsType>Other</ContentsType>
                                    <ContentsExplanation>Package</ContentsExplanation>
                                    <CustomsItems>
                                        <CustomsItem>
                                            <Description>Package</Description>
                                            <Quantity>'.$request->package_count.'</Quantity>
                                            <Weight>'.$total_weight_oz.'</Weight>
                                            <Value>'.$request->total_value.'</Value>
                                        </CustomsItem>
                                    </CustomsItems>
                                </CustomsInfo>
                            </LabelRequest>
                        </GetPostageLabel>
                    </soap:Body>
                </soap:Envelope>';

                $international = 1;
        }
        
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => $apiURL,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS =>$xml,
            CURLOPT_HTTPHEADER => array(
                'Content-Type: text/xml; charset=utf-8'
            ),
        ));

        $curlresponse = curl_exec($curl);

        curl_close($curl);
        $curlresponse = str_replace('xmlns="www.envmgr.com/LabelService"', '', $curlresponse);
        //echo $curlresponse;die;
        $curlresponse = str_replace('<soap:Envelope xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd="http://www.w3.org/2001/XMLSchema"><soap:Body>','', $curlresponse);
        $curlresponse = str_replace('</soap:Body></soap:Envelope>','', $curlresponse);

        $simplexml  = simplexml_load_string($curlresponse);
        $json       = json_encode($simplexml);
        $responseArray      = json_decode($json,TRUE);
        //echo '<pre>';print_r($responseArray);die;
        if(isset($responseArray['LabelRequestResponse']['Base64LabelImage']) || isset($responseArray['LabelRequestResponse']['Label']['Image'])){
            
            $TrackingNumber = $responseArray['LabelRequestResponse']['TrackingNumber'];
            $tracking_numbers[] =   $TrackingNumber;
            $req = $xml;

            $req = str_replace('xmlns="www.envmgr.com/LabelService"', '', $req);
            //echo $response;die;
            $req = str_replace('<soap:Envelope xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd="http://www.w3.org/2001/XMLSchema"><soap:Body>','', $req);
            $req = str_replace('</soap:Body></soap:Envelope>','', $req);

            $simplexml  = simplexml_load_string($req);
            $reqjson       = json_encode($simplexml);
            /*
            Save Shipping Request in DB - START
            */
            $shipping_data_to_save                   = new Shipping;
            $shipping_data_to_save->user_id          = auth()->id();
            $shipping_data_to_save->request          = $reqjson;
            $shipping_data_to_save->response         = $json;
            $shipping_data_to_save->tracking_number  = $TrackingNumber;
            $shipping_data_to_save->shipped_from     = 'USPS';
            //$shipping_data_to_save->invoice_num      = $invoice_num;
            $shipping_data_to_save->shipper_email    = $request->from_email;
            $shipping_data_to_save->shipper_phone    = $request->from_phone;
            $shipping_data_to_save->consignee_phone  = $request->to_phone_1;
            $shipping_data_to_save->other_charge     = rand(10,100);
            $shipping_data_to_save->save();
            $latest_saved_shipping                   = $shipping_data_to_save->id;
            /*
            Save Shipping Request in DB - END
            */

            $usps_ref                          = 'ZSP'.rand(1111111,9999999);
            if ($request->pickup_required && $request->pickup_required == 'yes') {
                /*
                    Save Pickup Request in DB - START
                */
                    $pickup_data_to_save                    = new Pickup;
                    $pickup_data_to_save->user_id           = auth()->id();
                    $pickup_data_to_save->request           = $reqjson;
                    $pickup_data_to_save->response          = $json;
                    $pickup_data_to_save->shipped_from      = 'USPS';
                    $pickup_data_to_save->reference_num     = $usps_ref;
                    $pickup_data_to_save->save();
                    $latest_saved_pickup                    = $pickup_data_to_save->id;
                /*
                    Save Pickup Request in DB - END
                */
            }

            $response['status'] = 'success';
            $updateQuote = Quotation::find($request->quote_id);
            $updateQuote->shipped                = 1;
            $updateQuote->shipped_trackingNumber = $TrackingNumber;
            if ($request->pickup_required && $request->pickup_required == 'yes') {
                $updateQuote->pickup_scheduled       = 1;
                $updateQuote->pickup_id              = $latest_saved_pickup;
            }
            $updateQuote->update();
            if(isset($responseArray['LabelRequestResponse']['Base64LabelImage'])){
                $label = base64_decode($responseArray['LabelRequestResponse']['Base64LabelImage']);
            }
            if(isset($responseArray['LabelRequestResponse']['Label']['Image'])){
                $label = base64_decode($responseArray['LabelRequestResponse']['Label']['Image']);
            }
            $filename = "label_".$TrackingNumber.".PNG";
            \Storage::disk('public')->put("label/".$filename,$label);
            $invoice_n_label['label_'.$label_count] = url("label/".$filename);

            $pdfFilename = "label_".trim($TrackingNumber).".pdf";
            if (!file_exists(storage_path('app/public/label/').'/'.$pdfFilename)) {
                $this->generateUSPSShipmentLabelPDF($tracking_numbers, $pdfFilename, $international);
            }

            
            // $receiptFilename = "receipt_".trim($master_tracking_numbers[0]).".pdf";
            $receiptFilename = "receipt_".trim($TrackingNumber).".pdf";           
            $this->generateReceipt($receiptFilename, '', $request, $responseArray['LabelRequestResponse']['TransactionDateTime'], 'USPS');



            $buttons_class = 'col-lg-4';
            $invoice_class = '';
            if ($request->from_country == $request->to_country) {
                $buttons_class = 'col-lg-6';
                $invoice_class = 'd-none';
            }

            $label_html .= '<div class="'.$buttons_class.'" id="label_btn"><div class="btn-wrap text-center"><a target="_blank" href="'.url("label/$pdfFilename").'" class="cstm-btn quote-btn">View Label</a></div></div>';
            $html = '<div class="row"><div class="col-lg-12"><div class="form-card"><div class="form-head"><h3>VIEW LABELS DOCUMENTS AND RECEIPT</h3></div><div class="form-body"><div class="row" id="download_buttons">'.$label_html.'<div class="'.$buttons_class.' '.$invoice_class.'" id="invoice_btn"></div><div class="'.$buttons_class.'" id="receipt_btn"><div class="btn-wrap text-center"><a target="_blank" href="'.url("receipt/".$receiptFilename).'" class="cstm-btn quote-btn inactiveLink">View Receipt</a></div></div></div></div></div></div></div>';

            if ($request->pickup_required && $request->pickup_required == 'yes') {
                $html .= '<div class="row"><div class="col-lg-12"><div class="form-card"><div class="form-head"><h3>PICKUP COMFIRMATION</h3></div><div class="form-body"><div class="row"><div class="col-lg-12"><p>Pickup created successfully! <br>Please save this Pickup Reference number for reference > <b>'.$usps_ref.'</b> <br>The pickup fee is: <b>20.00 USD</b></p></div></div></div></div></div></div>';
            }

            // if (isset($pickup_id['status']) && $pickup_id['status'] == 'success') {
            //     $html .= '<div class="row"><div class="col-lg-12"><div class="form-card"><div class="form-head"><h3>PICKUP COMFIRMATION</h3></div><div class="form-body"><div class="row"><div class="col-lg-12"><p> '.$pickup_id['message'].'</p></div></div></div></div></div></div>';
            // }elseif (isset($pickup_id['status']) && $pickup_id['status'] == 'error' && $pickup_id['message'] != '') {
            //     $html .= '<div class="row"><div class="col-lg-12"><div class="form-card"><div class="form-head"><h3>PICKUP COMFIRMATION</h3></div><div class="form-body"><div class="row"><div class="col-lg-12"><p>'.$pickup_id['message'].'</p></div><div class="col-lg-12"><p>Please contact agent to schecule pickup with tracking number.</p></div></div></div></div></div></div>';
            // }
            $response['html'] = $html;
            
        }else{
            $response['html'] = '<div class="row"><div class="col-lg-12"><div class="form-card"><div class="form-head"><h3>Package Information</h3></div><div class="form-body"><div class="row"><div class="col-lg-12"><b>There is some error, please try again later or You can call the office on (305-515-2616)</b><br>Error Detail - '. $responseArray['LabelRequestResponse']['ErrorMessage'] .'</div></div></div></div></div></div>';
        }
        return json_encode($response);
    }

    public function ship_from_UPS_2($request)
    {
        $response['status'] = 'error';
        $apiURL             = $this->ups_api_url.'ship/v1/shipments?additionaladdressvalidation=city'; // (API URL)
        $invoice_num        = $this->generate_invoice_num("UPS", $request->quote_id);
        
        // Headers
        $headers = [
            'accesslicensenumber'   => $this->ups_access_key,
            'username'              => $this->ups_user_id,
            'password'              => $this->ups_password,
            'content-type'          => 'application/json',
            'accept'                => 'application/json'
        ];

        $loopCount      = $request->package_count;
        $r_dimensions   = $request->dimensions; 

        $shipment       = array();

        $shipment['ShipmentRequest']['Shipment']['Description']                                     = $invoice_num;
        $shipment['ShipmentRequest']['Shipment']['Shipper']['Name']                                 = $request->from_name;
        $shipment['ShipmentRequest']['Shipment']['Shipper']['AttentionName']                        = $request->from_name;
        // $shipment['ShipmentRequest']['Shipment']['Shipper']['TaxIdentificationNumber']           = 'TaxID';
        $shipment['ShipmentRequest']['Shipment']['Shipper']['Phone']['Number']                      = $request->from_phone;
        $shipment['ShipmentRequest']['Shipment']['Shipper']['ShipperNumber']                        = $this->ups_shipper_number;
        $shipment['ShipmentRequest']['Shipment']['Shipper']['Address']['AddressLine']               = ($request->from_apt)?$request->from_apt.", ".$request->from_address:''.$request->from_address;
        $shipment['ShipmentRequest']['Shipment']['Shipper']['Address']['City']                      = $request->from_city;
        $shipment['ShipmentRequest']['Shipment']['Shipper']['Address']['StateProvinceCode']         = $request->from_state;
        $shipment['ShipmentRequest']['Shipment']['Shipper']['Address']['PostalCode']                = str_replace(" ", "", $request->from_zip);
        $shipment['ShipmentRequest']['Shipment']['Shipper']['Address']['CountryCode']               = $request->from_country;

        $shipment['ShipmentRequest']['Shipment']['ShipTo']['Name']                                  = $request->to_name;
        $shipment['ShipmentRequest']['Shipment']['ShipTo']['AttentionName']                         = $request->to_name;
        $shipment['ShipmentRequest']['Shipment']['ShipTo']['Phone']['Number']                       = $request->to_phone_1;
        $shipment['ShipmentRequest']['Shipment']['ShipTo']['Address']['AddressLine']                = $request->to_address;
        $shipment['ShipmentRequest']['Shipment']['ShipTo']['Address']['City']                       = $request->to_city;
        $shipment['ShipmentRequest']['Shipment']['ShipTo']['Address']['StateProvinceCode']          = $request->to_state;
        if ($request->to_zip) {
            $shipment['ShipmentRequest']['Shipment']['ShipTo']['Address']['PostalCode']             = str_replace(" ", "", $request->to_zip);
        }

        $shipment['ShipmentRequest']['Shipment']['ShipTo']['Address']['CountryCode']                = $request->to_country;

        $shipment['ShipmentRequest']['Shipment']['ShipFrom']['Name']                                = $request->from_name;
        $shipment['ShipmentRequest']['Shipment']['ShipFrom']['AttentionName']                       = $request->from_name;
        //$shipment['ShipmentRequest']['Shipment']['ShipFrom']['TaxIdentificationNumber']           = '456999';
        //$shipment['ShipmentRequest']['Shipment']['ShipFrom']['FaxNumber']                         = '1234567999';
        $shipment['ShipmentRequest']['Shipment']['ShipFrom']['Phone']['Number']                     = $request->from_phone;
        $shipment['ShipmentRequest']['Shipment']['ShipFrom']['Address']['AddressLine']              = ($request->from_apt)?$request->from_apt.", ".$request->from_address:''.$request->from_address;
        $shipment['ShipmentRequest']['Shipment']['ShipFrom']['Address']['City']                     = $request->from_city;
        $shipment['ShipmentRequest']['Shipment']['ShipFrom']['Address']['StateProvinceCode']        = $request->from_state;
        $shipment['ShipmentRequest']['Shipment']['ShipFrom']['Address']['PostalCode']               = $request->from_zip;
        $shipment['ShipmentRequest']['Shipment']['ShipFrom']['Address']['CountryCode']              = $request->from_country;

        $shipment['ShipmentRequest']['Shipment']['PaymentInformation']['ShipmentCharge']['Type']    = '01';
        $shipment['ShipmentRequest']['Shipment']['PaymentInformation']['ShipmentCharge']['BillShipper']['AccountNumber'] = $this->ups_shipper_number;

        if ($request->delivery_option == 'UPS Next Day Air') {
            $shipment['ShipmentRequest']['Shipment']['Service']['Code']         = '01';
            $shipment['ShipmentRequest']['Shipment']['Service']['Description']  = 'Next Day Air';
        }elseif ($request->delivery_option == 'UPS 2nd Day Air') {
            $shipment['ShipmentRequest']['Shipment']['Service']['Code']         = '02';
            $shipment['ShipmentRequest']['Shipment']['Service']['Description']  = '2nd Day Air';
        }elseif ($request->delivery_option == 'UPS Ground') {
            $shipment['ShipmentRequest']['Shipment']['Service']['Code']         = '03';
            $shipment['ShipmentRequest']['Shipment']['Service']['Description']  = 'Ground';
        }elseif ($request->delivery_option == 'UPS Worldwide Express') {
            $shipment['ShipmentRequest']['Shipment']['Service']['Code']         = '07';
            $shipment['ShipmentRequest']['Shipment']['Service']['Description']  = 'Express';
        }elseif ($request->delivery_option == 'UPS Worldwide Expedited') {
            $shipment['ShipmentRequest']['Shipment']['Service']['Code']         = '08';
            $shipment['ShipmentRequest']['Shipment']['Service']['Description']  = 'Expedited';
        }elseif ($request->delivery_option == 'UPS Standard') {
            $shipment['ShipmentRequest']['Shipment']['Service']['Code']         = '11';
            $shipment['ShipmentRequest']['Shipment']['Service']['Description']  = 'UPS Standard';
        }elseif ($request->delivery_option == 'UPS 3 Day Select') {
            $shipment['ShipmentRequest']['Shipment']['Service']['Code']         = '12';
            $shipment['ShipmentRequest']['Shipment']['Service']['Description']  = '3 Day Select';
        }elseif ($request->delivery_option == 'UPS Next Day Air Saver') {
            $shipment['ShipmentRequest']['Shipment']['Service']['Code']         = '13';
            $shipment['ShipmentRequest']['Shipment']['Service']['Description']  = 'Next Day Air Saver';
        }elseif ($request->delivery_option == 'UPS Next Day Air Early') {
            $shipment['ShipmentRequest']['Shipment']['Service']['Code']         = '14';
            $shipment['ShipmentRequest']['Shipment']['Service']['Description']  = 'UPS Next Day Air® Early';
        }elseif ($request->delivery_option == 'UPS Worldwide Express Plus') {
            $shipment['ShipmentRequest']['Shipment']['Service']['Code']         = '54';
            $shipment['ShipmentRequest']['Shipment']['Service']['Description']  = 'Express Plus';
        }elseif ($request->delivery_option == 'UPS 2nd Day Air A.M.') {
            $shipment['ShipmentRequest']['Shipment']['Service']['Code']         = '59';
            $shipment['ShipmentRequest']['Shipment']['Service']['Description']  = '2nd Day Air A.M.';
        }elseif ($request->delivery_option == 'UPS Worldwide Saver') {
            $shipment['ShipmentRequest']['Shipment']['Service']['Code']         = '65';
            $shipment['ShipmentRequest']['Shipment']['Service']['Description']  = 'UPS Saver';
        }elseif ($request->delivery_option == 'UPS Access Point Economy') {
            $shipment['ShipmentRequest']['Shipment']['Service']['Code']         = '17';
            $shipment['ShipmentRequest']['Shipment']['Service']['Description']  = 'UPS Worldwide Economy DDU';
        }elseif ($request->delivery_option == 'UPS Express 12:00') {
            $shipment['ShipmentRequest']['Shipment']['Service']['Code']         = '74';
            $shipment['ShipmentRequest']['Shipment']['Service']['Description']  = 'UPS Express®12:00';
        }elseif ($request->delivery_option == 'UPS Today Standard') {
            $shipment['ShipmentRequest']['Shipment']['Service']['Code']         = '82';
            $shipment['ShipmentRequest']['Shipment']['Service']['Description']  = 'UPS Today Standard';
        }elseif ($request->delivery_option == 'UPS Today Dedicated Courrier') {
            $shipment['ShipmentRequest']['Shipment']['Service']['Code']         = '83';
            $shipment['ShipmentRequest']['Shipment']['Service']['Description']  = 'UPS Today Dedicated Courier';
        }elseif ($request->delivery_option == 'UPS Today Express') {
            $shipment['ShipmentRequest']['Shipment']['Service']['Code']         = '85';
            $shipment['ShipmentRequest']['Shipment']['Service']['Description']  = 'UPS Today Express';
        }elseif ($request->delivery_option == 'UPS Today Express Saver') {
            $shipment['ShipmentRequest']['Shipment']['Service']['Code']         = '86';
            $shipment['ShipmentRequest']['Shipment']['Service']['Description']  = 'UPS Today Express Saver';
        }elseif ($request->delivery_option == 'UPS Worldwide Express Freight') {
            $shipment['ShipmentRequest']['Shipment']['Service']['Code']         = '96';
            $shipment['ShipmentRequest']['Shipment']['Service']['Description']  = 'UPS Worldwide Express Freight';
        }elseif ($request->delivery_option == 'UPS Worldwide Express Freight Midday') {
            $shipment['ShipmentRequest']['Shipment']['Service']['Code']         = '71';
            $shipment['ShipmentRequest']['Shipment']['Service']['Description']  = 'UPS Worldwide Express Freight Midday';
        }elseif ($request->delivery_option == 'UPS Second Day Air AM') {
            $shipment['ShipmentRequest']['Shipment']['Service']['Code']         = '59';
            $shipment['ShipmentRequest']['Shipment']['Service']['Description']  = '2nd Day Air A.M.';
        }elseif ($request->delivery_option == 'UPS Saver') {
            $shipment['ShipmentRequest']['Shipment']['Service']['Code']         = '65';
            $shipment['ShipmentRequest']['Shipment']['Service']['Description']  = 'UPS Saver';
        }elseif ($request->delivery_option == 'UPS Access Point Economy') {
            $shipment['ShipmentRequest']['Shipment']['Service']['Code']         = '70';
            $shipment['ShipmentRequest']['Shipment']['Service']['Description']  = 'UPS Access Point™ Economy';
        }else{
            $shipment['ShipmentRequest']['Shipment']['Service']['Code']         = '07';
            $shipment['ShipmentRequest']['Shipment']['Service']['Description']  = 'Express';
        }

        $shipment['ShipmentRequest']['Shipment']['InvoiceLineTotal']['CurrencyCode']    = 'USD';
        $shipment['ShipmentRequest']['Shipment']['InvoiceLineTotal']['MonetaryValue']   = (string)$request->total_value;

        $total_weight = 0;
        for ($i=0; $i < $loopCount; $i++) { 
            $shipment['ShipmentRequest']['Shipment']['Package'][$i]['Description']                                  = 'International Goods';
            $shipment['ShipmentRequest']['Shipment']['Package'][$i]['Packaging']['Code']                            = '02';
            $shipment['ShipmentRequest']['Shipment']['Package'][$i]['PackageWeight']['UnitOfMeasurement']['Code']   = 'LBS';
            $shipment['ShipmentRequest']['Shipment']['Package'][$i]['PackageWeight']['Weight']                      = $r_dimensions['weight'][$i];
            $shipment['ShipmentRequest']['Shipment']['Package'][$i]['PackageServiceOptions']                        = '';

            $total_weight = $total_weight+$r_dimensions['weight'][$i];   
        }

        $shipment['ShipmentRequest']['Shipment']['ItemizedChargesRequestedIndicator']                   = '';
        $shipment['ShipmentRequest']['Shipment']['RatingMethodRequestedIndicator']                      = '';
        $shipment['ShipmentRequest']['Shipment']['TaxInformationIndicator']                             = '';
        $shipment['ShipmentRequest']['Shipment']['ShipmentRatingOptions']['NegotiatedRatesIndicator']   = 'Bids or Account Based Rates';

        $shipment['ShipmentRequest']['Shipment']['ShipmentServiceOptions']['InternationalForms']['FormType']                                           = '01';
        $shipment['ShipmentRequest']['Shipment']['ShipmentServiceOptions']['InternationalForms']['Contacts']['SoldTo']['Name']                         = $request->to_name;
        $shipment['ShipmentRequest']['Shipment']['ShipmentServiceOptions']['InternationalForms']['Contacts']['SoldTo']['AttentionName']                = $request->to_name;
        $shipment['ShipmentRequest']['Shipment']['ShipmentServiceOptions']['InternationalForms']['Contacts']['SoldTo']['Phone']['Number']              = $request->to_phone_1;
        $shipment['ShipmentRequest']['Shipment']['ShipmentServiceOptions']['InternationalForms']['Contacts']['SoldTo']['Address']['AddressLine']       = $request->to_address;
        $shipment['ShipmentRequest']['Shipment']['ShipmentServiceOptions']['InternationalForms']['Contacts']['SoldTo']['Address']['City']              = $request->to_city;
        $shipment['ShipmentRequest']['Shipment']['ShipmentServiceOptions']['InternationalForms']['Contacts']['SoldTo']['Address']['StateProvinceCode'] = $request->to_state;
        if ($request->to_zip) {
            $shipment['ShipmentRequest']['Shipment']['ShipmentServiceOptions']['InternationalForms']['Contacts']['SoldTo']['Address']['PostalCode']    = str_replace(" ", "", $request->to_zip);
        }
        $shipment['ShipmentRequest']['Shipment']['ShipmentServiceOptions']['InternationalForms']['Contacts']['SoldTo']['Address']['CountryCode']       = $request->to_country;
        $shipment['ShipmentRequest']['Shipment']['ShipmentServiceOptions']['InternationalForms']['Product']['Description']                             = ($request->package_description)?$request->package_description:'No Description';
        $shipment['ShipmentRequest']['Shipment']['ShipmentServiceOptions']['InternationalForms']['Product']['Unit']['Number']                          = $loopCount;
        $shipment['ShipmentRequest']['Shipment']['ShipmentServiceOptions']['InternationalForms']['Product']['Unit']['UnitOfMeasurement']['Code']       = 'PCS';
        $shipment['ShipmentRequest']['Shipment']['ShipmentServiceOptions']['InternationalForms']['Product']['Unit']['Value']                           = (string)$request->total_value;
        $shipment['ShipmentRequest']['Shipment']['ShipmentServiceOptions']['InternationalForms']['Product']['CommodityCode']                           = 'ZIONPRO123';
        $shipment['ShipmentRequest']['Shipment']['ShipmentServiceOptions']['InternationalForms']['Product']['OriginCountryCode']                       = $request->from_country;
        $shipment['ShipmentRequest']['Shipment']['ShipmentServiceOptions']['InternationalForms']['Product']['ProducerInfo']                            = 'No [1]';
        $shipment['ShipmentRequest']['Shipment']['ShipmentServiceOptions']['InternationalForms']['Product']['NumberOfPackagesPerCommodity']            = '1';
        $shipment['ShipmentRequest']['Shipment']['ShipmentServiceOptions']['InternationalForms']['Product']['ProductWeight']['UnitOfMeasurement']['Code']   = 'LBS';
        $shipment['ShipmentRequest']['Shipment']['ShipmentServiceOptions']['InternationalForms']['Product']['ProductWeight']['UnitOfMeasurement']['Weight'] = $total_weight;

        $ExportType = 'F'; //F Stands for Foreign
        if ($request->from_country == $request->to_country) {
            $ExportType = 'D'; //D Stands for Domestic
        }

        $shipment['ShipmentRequest']['Shipment']['ShipmentServiceOptions']['InternationalForms']['Product']['ExportType']   = $ExportType;
        $shipment['ShipmentRequest']['Shipment']['ShipmentServiceOptions']['InternationalForms']['InvoiceNumber']           = $invoice_num;

        // if(date('D') == 'Sat' || date('D') == 'Sun') { 
        //     $day_add = 1;
        //     if (date('D') == 'Sat') {
        //        $day_add = 2;
        //     }           
        //     $invoice_date = date("Ymd", strtotime("+".$day_add." day"));
        // } else {
        //     $invoice_date = date("Ymd", strtotime("+1 day"));
        // }
        $invoice_date = date("Ymd");

        $shipment['ShipmentRequest']['Shipment']['ShipmentServiceOptions']['InternationalForms']['InvoiceDate']             = $invoice_date;
        $shipment['ShipmentRequest']['Shipment']['ShipmentServiceOptions']['InternationalForms']['ReasonForExport']         = 'GIFT';
        $shipment['ShipmentRequest']['Shipment']['ShipmentServiceOptions']['InternationalForms']['Comments']                = 'No';
        $shipment['ShipmentRequest']['Shipment']['ShipmentServiceOptions']['InternationalForms']['CurrencyCode']            = 'USD';

        $shipment['ShipmentRequest']['LabelSpecification']['LabelImageFormat']['Code'] = "PNG";

        $http_response  = Http::withHeaders($headers)->post($apiURL, $shipment);

        $statusCode     = $http_response->status();

        $responseBody   = json_decode($http_response->getBody(), true);

        $tracking_numbers = array();

        $invoice_n_label = array();

        foreach($responseBody as $shipmentUPSres){
            if (isset($shipmentUPSres['ShipmentResults']) && isset($shipmentUPSres['ShipmentResults']['PackageResults'])) {
                foreach ($shipmentUPSres['ShipmentResults']['PackageResults'] as $tracking_key => $tracking_data) {
                    if ($tracking_key == 'TrackingNumber') {
                        $tracking_numbers[] = $tracking_data;
                    }else{
                        if (isset($tracking_data['TrackingNumber']) && !empty($tracking_data['TrackingNumber'])) {
                            $tracking_numbers[] = $tracking_data['TrackingNumber'];
                        }
                    }
                    if ($tracking_key == 'ShippingLabel') {
                        $invoice_n_label[] = $tracking_data['GraphicImage'];
                    }else{
                        if (isset($tracking_data['ShippingLabel'])) {
                            $invoice_n_label[] = $tracking_data['ShippingLabel']['GraphicImage'];
                        }
                    }
                }
            }
        }

        /*
        Save Shipping Request in DB - START
        */
        $shipping_data_to_save                   = new Shipping;
        $shipping_data_to_save->user_id          = auth()->id();
        $shipping_data_to_save->request          = json_encode($shipment);
        $shipping_data_to_save->response         = $http_response->getBody();
        $shipping_data_to_save->tracking_number  = ($tracking_numbers)?implode(", ", $tracking_numbers):NULL;
        $shipping_data_to_save->shipped_from     = 'UPS';
        $shipping_data_to_save->invoice_num      = $invoice_num;
        $shipping_data_to_save->shipper_email    = $request->from_email;
        $shipping_data_to_save->shipper_phone    = $request->from_phone;
        $shipping_data_to_save->consignee_phone  = $request->to_phone_1;
        $shipping_data_to_save->other_charge     = rand(10,100);
        $shipping_data_to_save->save();
        $latest_saved_shipping                   = $shipping_data_to_save->id;
        /*
        Save Shipping Request in DB - END
        */
        if($tracking_numbers){
            $response['status'] = 'success';
            $updateQuote = Quotation::find($request->quote_id);
            $updateQuote->shipped                = 1;
            $updateQuote->shipped_trackingNumber = implode(", ", $tracking_numbers);
            $updateQuote->update();

            $pickup_id = array();

            if ($request->pickup_required && $request->pickup_required == 'yes') {
                $pickup_id = $this->pickup_from_ups($request);
            }

            $label_html = '';            
            foreach ($invoice_n_label as $label_count => $labels) {
                $filename = "label_".$tracking_numbers[$label_count].".PNG";
                \Storage::disk('public')->put("label/".$filename,base64_decode($labels));
                $invoice_n_label['label_'.$label_count] = url("label/".$filename);
            }
            $pdfFilename = "label_".trim($tracking_numbers[0]).".pdf";
            if (!file_exists(storage_path('app/public/label/').'/'.$pdfFilename)) {
                $this->generateUPSShipmentLabelPDF($tracking_numbers, $pdfFilename, $request->pickup_date, $request->pickup_start_time);
            }

            $receiptFilename = "receipt_".trim($tracking_numbers[0]).".pdf";            
            $this->generateReceipt($receiptFilename, $invoice_num, $request, $invoice_date, 'UPS');

            if (isset($responseBody['ShipmentResponse']['ShipmentResults']['Form'])) {
                $invFilename = "invoice_".trim($tracking_numbers[0]).".pdf";
                \Storage::disk('public')->put("invoice/".$invFilename,base64_decode($responseBody['ShipmentResponse']['ShipmentResults']['Form']['Image']['GraphicImage']));   
            }

            $buttons_class = 'col-lg-4';
            $invoice_class = '';
            if ($request->from_country == $request->to_country) {
                $buttons_class = 'col-lg-6';
                $invoice_class = 'd-none';
            }

            $label_html .= '<div class="'.$buttons_class.'" id="label_btn"><div class="btn-wrap text-center"><a target="_blank" href="'.url("label/$pdfFilename").'" class="cstm-btn quote-btn">View Label</a></div></div>';
            $html = '<div class="row"><div class="col-lg-12"><div class="form-card"><div class="form-head"><h3>VIEW LABELS DOCUMENTS AND RECEIPT</h3></div><div class="form-body"><div class="row" id="download_buttons">'.$label_html.'<div class="'.$buttons_class.' '.$invoice_class.'" id="invoice_btn"><div class="btn-wrap text-center"><a target="_blank" href="'.url("invoice/".$invFilename).'" class="cstm-btn quote-btn inactiveLink">View Invoice</a></div></div><div class="'.$buttons_class.'" id="receipt_btn"><div class="btn-wrap text-center"><a target="_blank" href="'.url("receipt/".$receiptFilename).'" class="cstm-btn quote-btn inactiveLink">View Receipt</a></div></div></div></div></div></div></div>';

            if (isset($pickup_id['status']) && $pickup_id['status'] == 'success') {
                $html .= '<div class="row"><div class="col-lg-12"><div class="form-card"><div class="form-head"><h3>PICKUP COMFIRMATION</h3></div><div class="form-body"><div class="row"><div class="col-lg-12"><p> '.$pickup_id['message'].'</p></div></div></div></div></div></div>';
            }elseif (isset($pickup_id['status']) && $pickup_id['status'] == 'error' && $pickup_id['message'] != '') {
                $html .= '<div class="row"><div class="col-lg-12"><div class="form-card"><div class="form-head"><h3>PICKUP COMFIRMATION</h3></div><div class="form-body"><div class="row"><div class="col-lg-12"><p>'.$pickup_id['message'].'</p></div><div class="col-lg-12"><p>Please contact agent to schecule pickup with tracking number.</p></div></div></div></div></div></div>';
            }

            $response['html'] = $html;
        }else{
            $response['html'] = '<div class="row"><div class="col-lg-12"><div class="form-card"><div class="form-head"><h3>Package Information</h3></div><div class="form-body"><div class="row"><div class="col-lg-12"><b>There is some error, please try again later or You can call the office on (305-515-2616)</b></div></div></div></div></div></div>';
        }
        return json_encode($response);
    }

    public function generate_invoice_num($carrier='DHL', $quote_id='')
    {
        if ($carrier == 'DHL') {
            $perfix = "ZD";
        }elseif ($carrier == 'UPS') {
            $perfix = "ZU";
        }elseif ($carrier == 'FEDEX') {
            $perfix = "ZF";
        }
        $pre            = auth()->id().$quote_id;
        $current_length = strlen((string)$pre);

        if ($current_length < 6) {
            $need_extra = 6 - $current_length;
        }else{
            return $pre;
        }
        $final_invoice     = rand(100000,999999);
        if ($need_extra      == 1) {
            $final_invoice = $pre.rand(1,9);
        }elseif ($need_extra == 2) {
            $final_invoice = $pre.rand(10,99);
        }elseif ($need_extra == 3) {
            $final_invoice = $pre.rand(100,999);
        }elseif ($need_extra == 4) {
            $final_invoice = $pre.rand(1000,9999);
        }elseif ($need_extra == 5) {
            $final_invoice = $pre.rand(10000,99999);
        }
        return $final_invoice;
    }

    public function ship_from_DHL($request)
    {
        $counttries_data = Countries::select('plt_supported', 'threshold')->where(['alpha_2_code' => $request->to_country])->first();

        $response['status'] = 'error';
        // URL
        $apiURL = $this->dhl_api_url.'shipments'; // (API URL and Endpoint)

        $invoice_num     = $this->generate_invoice_num("DHL", $request->quote_id);
        $typeCode        = '3BX';
        $productCode     = 'P';
        if ($request->shipment_type && $request->shipment_type == 'contains_document') {
            $typeCode    = '2BP';
            $productCode = 'D';
        }
        
        // if ($request->product_code && $request->product_code != '') {
        //     $productCode = $request->product_code;
        // }
        
        $productCode = 'P'; //Need to confirm from DHL Team

        $shipping_data   = $pickup_api_data = array();
    
        $shipping_data['productCode']       = $productCode;
        $shipping_data['getRateEstimates']  = true;

        if(date('D') == 'Sat' || date('D') == 'Sun') { 
            $day_add = 1;
            if (date('D') == 'Sat') {
               $day_add = 2;
            }           
            $shipping_data['plannedShippingDateAndTime'] = date("Y-m-d", strtotime("+".$day_add." day"))."T00:00:00 GMT+01:00";//date("Y-m-d")."T00:00:00";
        } else {
            $shipping_data['plannedShippingDateAndTime'] = date("Y-m-d", strtotime("+1 day"))."T00:00:00 GMT+01:00";//date("Y-m-d", strtotime("+1 day"))."T00:00:00";
        }

        $loopCount  = $request->package_count;
        
        $shipping_data['pickup']['isRequested'] = false;

        $shipping_data['accounts'][0]['number']     = '848430610';
        $shipping_data['accounts'][0]['typeCode']   = 'shipper';

        $shipping_data['outputImageProperties']['encodingFormat']                           = 'pdf';
        $shipping_data['outputImageProperties']['imageOptions'][0]['invoiceType']           = 'commercial';
        $shipping_data['outputImageProperties']['imageOptions'][0]['isRequested']           = true;
        $shipping_data['outputImageProperties']['imageOptions'][0]['typeCode']              = 'invoice';

        $shipping_data['outputImageProperties']['imageOptions'][1]['hideAccountNumber']     = false;
        $shipping_data['outputImageProperties']['imageOptions'][1]['isRequested']           = true;
        $shipping_data['outputImageProperties']['imageOptions'][1]['typeCode']              = 'waybillDoc';
        $shipping_data['outputImageProperties']['imageOptions'][1]['templateName']          = 'ARCH_8X4_A4_002';


        $shipping_data['outputImageProperties']['imageOptions'][2]['typeCode']              = 'label';
        $shipping_data['outputImageProperties']['imageOptions'][2]['templateName']          = 'ECOM26_84_A4_001';
        // $shipping_data['outputImageProperties']['imageOptions'][2]['fitLabelsToA4']         = true;


        if ($counttries_data) {
            if ($counttries_data->plt_supported == 'Y' && ($request->total_value <= $counttries_data->threshold || $counttries_data->threshold == 0.00 )) {
                $shipping_data['valueAddedServices'][0]['serviceCode']   = 'WY';                    
            }    
        }

        // $shipping_data['valueAddedServices'][0]['serviceCode']   = 'WY';

        $shipping_data['customerDetails']['shipperDetails']['postalAddress']['cityName']            = $request->from_city;
        $shipping_data['customerDetails']['shipperDetails']['postalAddress']['countryCode']         = $request->from_country;
        $shipping_data['customerDetails']['shipperDetails']['postalAddress']['postalCode']          = $request->from_zip;
        $shipping_data['customerDetails']['shipperDetails']['postalAddress']['addressLine1']        = ($request->from_apt)?$request->from_apt.", ".$request->from_address:''.$request->from_address;
        $shipping_data['customerDetails']['shipperDetails']['contactInformation']['phone']          = $request->from_phone;
        $shipping_data['customerDetails']['shipperDetails']['contactInformation']['companyName']    = $request->from_name;
        $shipping_data['customerDetails']['shipperDetails']['contactInformation']['fullName']       = $request->from_name;


        $shipping_data['customerDetails']['receiverDetails']['postalAddress']['cityName']           = $request->to_city;
        $shipping_data['customerDetails']['receiverDetails']['postalAddress']['countryCode']        = $request->to_country;
        $shipping_data['customerDetails']['receiverDetails']['postalAddress']['postalCode']         = ($request->to_zip)?$request->to_zip:"";
        $shipping_data['customerDetails']['receiverDetails']['postalAddress']['addressLine1']       = $request->to_address;
        $shipping_data['customerDetails']['receiverDetails']['postalAddress']['countyName']         = $request->to_country_name;

        $shipping_data['customerDetails']['receiverDetails']['contactInformation']['phone']         = $request->to_phone_1;
        $shipping_data['customerDetails']['receiverDetails']['contactInformation']['companyName']   = $request->to_name;
        $shipping_data['customerDetails']['receiverDetails']['contactInformation']['fullName']      = $request->to_name;
        // $shipping_data['customerDetails']['receiverDetails']['contactInformation']['email']         = $request->to_email;


        $shipping_data['content']['unitOfMeasurement']  = 'imperial';
        $shipping_data['content']['incoterm']           = 'DAP';

        if ($request->itn_number && $request->itn_number != '') {
            $shipping_data['content']['USFilingTypeValue']  = ($request->itn_number)??md5(uniqid(rand(), true));
        }else{
            if ($request->total_value > 2500 && $request->itn_number == '') {
                $response['html'] = '<div class="row"><div class="col-lg-12"><div class="form-card"><div class="form-head"><h3>Warning!</h3></div><div class="form-body"><div class="row"><div class="col-lg-12"><b>Packages exceeds more than $2500 must enter ITN Number. (Please contact admin or get ITN number from here > <a href="https://www.census.gov/foreign-trade/aes/aesdirect/transitiontoace.html" target="_blank">https://www.census.gov/foreign-trade/aes/aesdirect/transitiontoace.html</a>) </b></div></div></div></div></div></div>';
                return json_encode($response);
            }
            if ($request->to_country == 'CA' && $request->from_country == 'US') {
                $shipping_data['content']['USFilingTypeValue']  = '30.36';
            }else{
                $shipping_data['content']['USFilingTypeValue']  = '30.37(a)';
            }            
        }

        $shipping_data['content']['exportDeclaration']['lineItems'][0]['number'] = 1;
        $shipping_data['content']['exportDeclaration']['lineItems'][0]['commodityCodes'][0]['value']    = '33059040'; //need to confirm
        $shipping_data['content']['exportDeclaration']['lineItems'][0]['commodityCodes'][0]['typeCode'] = 'outbound'; //need to confirm

        $shipping_data['content']['exportDeclaration']['lineItems'][0]['priceCurrency']                 = 'USD';
        $shipping_data['content']['exportDeclaration']['lineItems'][0]['quantity']['unitOfMeasurement'] = 'PCS';
        $shipping_data['content']['exportDeclaration']['lineItems'][0]['quantity']['value']             = (int)$loopCount;

        $shipping_data['content']['exportDeclaration']['lineItems'][0]['price']                         = (float)$request->total_value;
        $shipping_data['content']['exportDeclaration']['lineItems'][0]['description']                   = ($request->package_description)?$request->package_description:'No Description';

        $shipping_data['content']['exportDeclaration']['lineItems'][0]['exportReasonType']              = 'permanent';
        $shipping_data['content']['exportDeclaration']['lineItems'][0]['manufacturerCountry']           = $request->from_country;



        if(date('D') == 'Sat' || date('D') == 'Sun') { 
            $day_add = 1;
            if (date('D') == 'Sat') {
               $day_add = 2;
            }           
            $invoice_date = date("Y-m-d", strtotime("+".$day_add." day"));
        } else {
            $invoice_date = date("Y-m-d", strtotime("+1 day"));
        }

        $shipping_data['content']['exportDeclaration']['invoice']['date']   = $invoice_date;
        $shipping_data['content']['exportDeclaration']['invoice']['number'] = $invoice_num; //need to create

        // $shipping_data['content']['exportDeclaration']['invoice']['signatureName']  = 'Therlande Louis J.'; //Need to confirm
        // $shipping_data['content']['exportDeclaration']['invoice']['signatureImage'] = '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'; //need real image

        // $shipping_data['content']['exportDeclaration']['invoice']['signatureTitle'] = 'Manager'; //Need to confirm

        $shipping_data['content']['isCustomsDeclarable']    = true;
        $shipping_data['content']['description']            = ($request->delivery_description)?$request->delivery_description:"No Delivery Description";

        $dimensions = $request->dimensions; 

        $total_weight = 0;

        for ($i=0; $i < $loopCount; $i++) { 
            $shipping_data['content']['packages'][$i]['typeCode']                           = $typeCode;
            $shipping_data['content']['packages'][$i]['weight']                             = (float)$dimensions['weight'][$i];
            $shipping_data['content']['packages'][$i]['dimensions']['length']               = (float)$dimensions['length'][$i];        
            $shipping_data['content']['packages'][$i]['dimensions']['width']                = (float)$dimensions['width'][$i];
            $shipping_data['content']['packages'][$i]['dimensions']['height']               = (float)$dimensions['height'][$i];    
            $shipping_data['content']['packages'][$i]['customerReferences'][0]['value']     = $invoice_num;    
            $shipping_data['content']['packages'][$i]['customerReferences'][0]['typeCode']  = 'CU';    
            $shipping_data['content']['packages'][$i]['description']                        = ($request->package_description)?$request->package_description:'No Description'; 
            $total_weight = $total_weight+$dimensions['weight'][$i];   
        }
        $shipping_data['content']['declaredValue']         = (float)$request->total_value;    
        $shipping_data['content']['declaredValueCurrency'] = "USD";    

        $shipping_data['content']['exportDeclaration']['lineItems'][0]['weight']['netValue']    = (float)$total_weight;
        $shipping_data['content']['exportDeclaration']['lineItems'][0]['weight']['grossValue']  = (float)$total_weight;


        // Headers
        $headers = [
            'accept'        => 'application/json',
            'content-type'  => 'application/json',
            'authorization' => 'Basic '.base64_encode($this->dhl_api_key.':'.$this->dhl_api_secret) //(siteid:password)
        ];

        // echo json_encode($shipping_data);
        // die();


        $http_response  = Http::withHeaders($headers)->post($apiURL, $shipping_data);
        $statusCode     = $http_response->status();

        $responseBody   = json_decode($http_response->getBody(), true);

        /*
            Save Shipping Request in DB - START
        */
            $shipping_data_to_save                   = new Shipping;
            $shipping_data_to_save->user_id          = auth()->id();
            $shipping_data_to_save->request          = json_encode($shipping_data);
            $shipping_data_to_save->response         = $http_response->getBody();
            $shipping_data_to_save->tracking_number  = (isset($responseBody['shipmentTrackingNumber']) && !empty($responseBody['shipmentTrackingNumber']))?$responseBody['shipmentTrackingNumber']:NULL;
            $shipping_data_to_save->invoice_num      = $invoice_num;
            $shipping_data_to_save->shipper_email    = $request->from_email;
            $shipping_data_to_save->shipper_phone    = $request->from_phone;
            $shipping_data_to_save->consignee_phone  = $request->to_phone_1;
            $shipping_data_to_save->other_charge     = rand(10,100);
            $shipping_data_to_save->shipped_from     = 'DHL';
            $shipping_data_to_save->save();
            $latest_saved_shipping                   = $shipping_data_to_save->id;
        /*
            Save Shipping Request in DB - END
        */
        

        if (isset($responseBody['shipmentTrackingNumber']) && !empty($responseBody['shipmentTrackingNumber'])) {
            $response['status'] = 'success';
            
            $updateQuote = Quotation::find($request->quote_id);
            $updateQuote->shipped                = 1;
            $updateQuote->shipped_trackingNumber = $responseBody['shipmentTrackingNumber'];
            $updateQuote->update();

            $invoice_n_label = array();

            $pickup_id = $this->schedule_pickup($request, $shipping_data);

            $receiptFilename = "receipt_".trim($responseBody['shipmentTrackingNumber']).".pdf";            
            $this->generateReceipt($receiptFilename, $invoice_num, $request, $invoice_date, 'DHL');

            foreach ($responseBody['documents'] as $document) {
                $filename = $document['typeCode']."_".$responseBody['shipmentTrackingNumber'].".".strtolower($document['imageFormat']);
                \Storage::disk('public')->put($document['typeCode']."/".$filename,base64_decode($document['content']));
                $invoice_n_label[$document['typeCode']] = url($document['typeCode']."/".$filename);
            }

            $buttons_class = 'col-lg-4';
            $invoice_class = '';
            if ($request->from_country == $request->to_country) {
                $buttons_class = 'col-lg-6';
                $invoice_class = 'd-none';
            }

            $html = '<div class="row"><div class="col-lg-12"><div class="form-card"><div class="form-head"><h3>VIEW LABELS DOCUMENTS AND RECEIPT</h3></div><div class="form-body"><div class="row" id="download_buttons"><div class="'.$buttons_class.'" id="label_btn"><div class="btn-wrap text-center"><a target="_blank" href="'.$invoice_n_label['label'].'" class="cstm-btn quote-btn">View Label</a></div></div><div class="'.$buttons_class.' '.$invoice_class.'" id="invoice_btn"><div class="btn-wrap text-center"><a target="_blank" href="'.$invoice_n_label['invoice'].'" class="cstm-btn quote-btn inactiveLink">View Invoice</a></div></div><div class="'.$buttons_class.'" id="receipt_btn"><div class="btn-wrap text-center"><a target="_blank" href="'.url("receipt/".$receiptFilename).'" class="cstm-btn quote-btn inactiveLink">View Receipt</a></div></div></div></div></div></div></div>';

            if (isset($pickup_id['status']) && $pickup_id['status'] == 'success') {
                $html .= '<div class="row"><div class="col-lg-12"><div class="form-card"><div class="form-head"><h3>PICKUP COMFIRMATION</h3></div><div class="form-body"><div class="row"><div class="col-lg-12"><p> '.$pickup_id['message'].'</p> And <b> 20 USD </b>to be paid to Pickup person</p></div></div></div></div></div></div>';
            }elseif (isset($pickup_id['status']) && $pickup_id['status'] == 'error' && $pickup_id['message'] != '') {
                $html .= '<div class="row"><div class="col-lg-12"><div class="form-card"><div class="form-head"><h3>PICKUP COMFIRMATION</h3></div><div class="form-body"><div class="row"><div class="col-lg-12"><p>'.$pickup_id['message'].'</p></div><div class="col-lg-12"><p>Please contact agent to schecule pickup with tracking number.</p></div></div></div></div></div></div>';
            }

            $response['html'] = $html;
        }
        if (!isset($response['html']) || empty($response['html'])) {
            $response['html'] = '<div class="row"><div class="col-lg-12"><div class="form-card"><div class="form-head"><h3>Package Information</h3></div><div class="form-body"><div class="row"><div class="col-lg-12"><b>There is some error, please try again later or You can call the office on (305-515-2616)</b></div></div></div></div></div></div>';
        }
        return json_encode($response);
    }

    public function pickup_from_ups($request)
    {
        $response['status']     = 'error';
        $response['message']    = 'There is some error, please try after some time!';
        
        $apiURL = $this->ups_api_url.'ship/v1607/pickups'; // (API URL)

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
        $pickup_api_data['PickupCreationRequest']['PickupAddress']['ContactName']   = $request->from_name;
        $pickup_api_data['PickupCreationRequest']['PickupAddress']['AddressLine']   = $request->from_address;
        $pickup_api_data['PickupCreationRequest']['PickupAddress']['Room']          = '';
        $pickup_api_data['PickupCreationRequest']['PickupAddress']['Floor']         = '';
        $pickup_api_data['PickupCreationRequest']['PickupAddress']['City']          = $request->from_city;
        $pickup_api_data['PickupCreationRequest']['PickupAddress']['StateProvince'] = $request->from_state;
        $pickup_api_data['PickupCreationRequest']['PickupAddress']['Urbanization']  = '';
        $pickup_api_data['PickupCreationRequest']['PickupAddress']['PostalCode']    = str_replace(" ", "", $request->from_zip);
        $pickup_api_data['PickupCreationRequest']['PickupAddress']['CountryCode']   = str_replace(" ", "", $request->from_country);
        $pickup_api_data['PickupCreationRequest']['PickupAddress']['ResidentialIndicator']      = 'N';
        $pickup_api_data['PickupCreationRequest']['PickupAddress']['PickupPoint']               = $request->pick_location;
        $pickup_api_data['PickupCreationRequest']['PickupAddress']['Phone']['Number']           = $request->from_phone;
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

        $pickup_api_data['PickupCreationRequest']['Notification']['ConfirmationEmailAddress']   = $request->from_email;
        $pickup_api_data['PickupCreationRequest']['Notification']['UndeliverableEmailAddress']  = $request->from_email;
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

            $latestPickup_data                  = Pickup::where('id', $latest_saved_pickup)->first();
            $latestPickup_data->reference_num   = $responseBody['PickupCreationResponse']['PRN'];
            $latestPickup_data->update();

            $response['status']     = 'success';
            $updateQuote = Quotation::find($request->quote_id);
            $updateQuote->pickup_scheduled  = 1;
            $updateQuote->pickup_id         = $latest_saved_pickup;
            $updateQuote->update();

            $response['message']    = 'Pickup created successfully! <br>Please save this Pickup Reference number for reference > <b>'.$responseBody['PickupCreationResponse']['PRN'].'</b> <br>The pickup fee is: <b>20.00 USD</b>';
        }elseif (isset($responseBody['response']['errors']) && !empty($responseBody['response']['errors'])) {
            $response['message'] = $responseBody['response']['errors'][0]['message'];
        }
        return $response;
    }

    public function schedule_pickup($request, $shipping_data)
    {   
        $response['status'] = 'error';
        $response['message'] = '';

        if ($request->pickup_required && $request->pickup_required == 'yes') {
            $pickup_api_data = array();
            $pickup_api_data['plannedPickupDateAndTime'] = $request->pickup_date."T".date("H:i:s", strtotime($request->pickup_start_time));
            $pickup_api_data['closeTime']       = date("H:i", strtotime($request->pickup_end_time));
            $pickup_api_data['location']        = $request->pick_location;
            $pickup_api_data['locationType']    = 'business';
            $pickup_api_data['accounts']        = $shipping_data['accounts'];

            $pickup_api_data['specialInstructions'][0]['value']     = ($request->pickup_instruction != '')?$request->pickup_instruction:'No intructions';
            $pickup_api_data['specialInstructions'][0]['typeCode']  = 'TBD';
            
            $pickup_api_data['customerDetails']         = $shipping_data['customerDetails'];
            $pickup_api_data['customerDetails']['bookingRequestorDetails']      = $shipping_data['customerDetails']['receiverDetails'];

            $pickup_api_data['customerDetails']['pickupDetails']['postalAddress']['postalCode']    = $request->from_zip;
            $pickup_api_data['customerDetails']['pickupDetails']['postalAddress']['cityName']      = $request->from_city;
            $pickup_api_data['customerDetails']['pickupDetails']['postalAddress']['countryCode']   = $request->from_country;
            $pickup_api_data['customerDetails']['pickupDetails']['postalAddress']['provinceCode']  = $request->from_country;
            $pickup_api_data['customerDetails']['pickupDetails']['postalAddress']['addressLine1']  = $request->from_address;
            $pickup_api_data['customerDetails']['pickupDetails']['postalAddress']['countyName']    = $request->from_country_name;


            $pickup_api_data['customerDetails']['pickupDetails']['contactInformation']['email']        = $request->from_email;
            $pickup_api_data['customerDetails']['pickupDetails']['contactInformation']['phone']         = $request->from_phone;
            $pickup_api_data['customerDetails']['pickupDetails']['contactInformation']['mobilePhone']  = $request->from_phone;
            $pickup_api_data['customerDetails']['pickupDetails']['contactInformation']['companyName']  = 'Zion Shipping';
            $pickup_api_data['customerDetails']['pickupDetails']['contactInformation']['fullName']     = $request->from_name;


            $pickup_api_data['shipmentDetails'][0]['productCode']       = 'D';
            $pickup_api_data['shipmentDetails'][0]['localProductCode']  = 'D';
            
            $pickup_api_data['shipmentDetails'][0]['accounts'][0]['typeCode']  = 'shipper';
            $pickup_api_data['shipmentDetails'][0]['accounts'][0]['number']  = '848430610';

            if (isset($shipping_data['valueAddedServices']) && !empty($shipping_data['valueAddedServices'])) {
                $pickup_api_data['shipmentDetails'][0]['valueAddedServices']  = $shipping_data['valueAddedServices'];
            }


            $pickup_api_data['shipmentDetails'][0]['isCustomsDeclarable']   = true;
            $pickup_api_data['shipmentDetails'][0]['declaredValue']         = $shipping_data['content']['declaredValue'];
            $pickup_api_data['shipmentDetails'][0]['declaredValueCurrency'] = $shipping_data['content']['declaredValueCurrency'];
            $pickup_api_data['shipmentDetails'][0]['unitOfMeasurement']     = 'imperial';
            
            $pickupPackages = array();
            $shipperPackages = $shipping_data['content']['packages'];

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

            $apiURL = $this->dhl_api_url.'pickups'; // (API URL and endpoint)
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
                $latestPickup_data                  = Pickup::where('id', $latest_saved_pickup)->first();
                $latestPickup_data->reference_num   = $responseBody['dispatchConfirmationNumbers'][0];
                $latestPickup_data->update();
                $response['status'] = 'success';
                $updateQuote = Quotation::find($request->quote_id);
                $updateQuote->pickup_scheduled  = 1;
                $updateQuote->pickup_id         = $latest_saved_pickup;
                $updateQuote->update();
                // $response['message'] = $responseBody['dispatchConfirmationNumbers'][0];
                $response['message']    = 'Pickup created successfully! <br>Please save this Pickup Reference number for reference > <b>'.$responseBody['dispatchConfirmationNumbers'][0].'</b> <br>The pickup fee is: <b>20.00 USD</b>';
            }elseif (isset($responseBody['detail']) && !empty($responseBody['detail'])) {
                $response['message'] = substr(strstr($responseBody['detail'], ':'), strlen(':'));
            }
        }
        return $response;
    }

    public function create_shipment_form(){
        $counttries_data = Countries::all();
        return Voyager::view('voyager::admin.shipping.create_shipment_form', compact('counttries_data'));
    }

    public function view_shipment(){
        $shipping_data = Shipping::leftjoin('countries as c', 'c.id', '=', 'shippings.consignee_country')
        ->select('shippings.*', 'c.country_name')->get();
        return Voyager::view('voyager::admin.shipping.view_shipment', compact('shipping_data'));
    }

    public function store_shipment(Request $request, Shipping $ship){
        $request->validate([
            'shipper_name' => 'required',
        ]);
        $data = $request->all();
        $data['user_id'] = Auth::user()->id;
        $data['tracking_number'] = rand(111111,999999);

        // echo "<pre>";
        // print_r($data);exit;

        $ship->create($data);
        return back()->withSuccess('Shipping created successfully');
    }

    public function edit_shipment(Shipping $ship, $id){
        $counttries_data = Countries::all();
        $edit_data = Shipping::leftjoin('countries as c', 'c.id', '=', 'shippings.consignee_country')
        ->select('shippings.*', 'c.country_name')->where('shippings.id', $id)->first();
        return Voyager::view('voyager::admin.shipping.create_shipment_form', compact('edit_data', 'counttries_data'));
    }

    public function update_shipment(Request $request, $id){
        $data = $request->all();
        $ship_data = Shipping::find($id);
        $ship_data->update($data);
        return redirect()->route('view_shipment')->withSuccess("Shipping updated successfully");
    }

    public function generateReceipt($pdfFilename, $invoice_num, $request, $date, $carrier)
    {
        $table_array    = array();
        $dimensions     = $request->dimensions; 
        $loopCount      = $request->package_count;
        $total_weight   = 0;
        $looped         = 0;
        $total_volume   = 0;
        for ($i=0; $i < $loopCount; $i++) { 
            $table_array[$i]['weight']                             = $dimensions['weight'][$i];
            $table_array[$i]['dimensions']['length']               = $dimensions['length'][$i];        
            $table_array[$i]['dimensions']['width']                = $dimensions['width'][$i];
            $table_array[$i]['dimensions']['height']               = $dimensions['height'][$i];    
            $table_array[$i]['description']                        = ($request->package_description)?$request->package_description:'No Description'; 
            $total_weight                                          = $total_weight+$dimensions['weight'][$i];
            $current_volume_calculation                            = ceil(($dimensions['length'][$i] * $dimensions['width'][$i] * $dimensions['height'][$i])/120);   
            $total_volume                                          = $total_volume + $current_volume_calculation; 
        }

        $table_html = '';
        foreach ($table_array as $tr_count => $tr_values) {
            $tr_num = $tr_count+1;
            $current_volume = ceil(($tr_values["dimensions"]["length"] * $tr_values["dimensions"]["width"] * $tr_values["dimensions"]["height"])/120);
            if ($tr_count == 0) {
                $p_description = $tr_values["description"];
            }else{
                $p_description = 'Above description is applied for all packages';
            }
            $table_html .= '<tr>
                <td class="no">'.$tr_num.'</td>
                <td class="text-left">
                    <h3>'.$p_description.'</h3>
                </td>
                <td class="unit">'.$tr_values["weight"].' lbs</td>
                <td class="qty">'.$current_volume.'</td>
                <td class="total">  '.$tr_values["dimensions"]["length"].' x '.$tr_values["dimensions"]["width"].' x '.$tr_values["dimensions"]["height"].'</td>
            </tr>';
        }

        $value_tax      = (7 / 100) * $request->deliveryEstimatePrice;
        $grand_total    = $request->deliveryEstimatePrice + $value_tax;
        $shipment_date  = date('F d, Y', strtotime($date));   
        $siteName       = setting("site.title", "Zion Shipping");
        $pdfHtml        = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
                            <html xmlns="http://www.w3.org/1999/xhtml" xmlns:v="urn:schemas-microsoft-com:vml" xmlns:o="urn:schemas-microsoft-com:office:office">
                                <head>
                                    <!--[if gte mso 9]>
                                    <xml>
                                        <o:OfficeDocumentSettings>
                                            <o:AllowPNG/>
                                            <o:PixelsPerInch>96</o:PixelsPerInch>
                                        </o:OfficeDocumentSettings>
                                    </xml>
                                    <![endif]-->
                                    <meta http-equiv="Content-type" content="text/html; charset=utf-8" />
                                    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1" />
                                    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
                                    <meta name="format-detection" content="date=no" />
                                    <meta name="format-detection" content="address=no" />
                                    <meta name="format-detection" content="telephone=no" />
                                    <meta name="x-apple-disable-message-reformatting" />
                                    <!--[if !mso]><!-->
                                    <link href="https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap" rel="stylesheet">
                                    <link href="https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css" rel="stylesheet" />
                                    <!--<![endif]-->
                                    <title>Invoice Template</title>
                                    <link href="'.url('themes/tailwind/css/pdfs/receipt-pdf.css').'" rel="stylesheet">
                                    <!--[if gte mso 9]> 
                                    <style type="text/css" media="all">
                                        sup { font-size: 100% !important; }
                                    </style>
                                    <![endif]-->
                                </head>
                                <body class="body" style="width:100%;">
                                    <div class="">
                                            <div class="card">
                                                <div class="card-body">
                                                    <div class="page-header text-blue-d2">
                                                        <div class="row contacts">
                                                            <div class="" style="margin-right: 440px; margin-top:-8%">
                                                                <h1 class="zion-name">Zion Shipping</h1>
                                                                <div class="my-2"> <span class="text-600 text-90">Address: Port-Au-Prince, Haiti Ouest <br>Delmas 40B, RUE Bohuo no. 6</div>
                                                            </div>
                                                            <div class="" style="margin-left: 460px; margin-top:-10%">
                                                                <div class="text-grey-m2">
                                                                    <div class="my-2"><i class="fa fa-phone text-blue-m2 text-xs mr-1"></i> <span class="text-600 text-90">Phone:</span> (123) 456-7890</div>
                                                                    <div class="my-2"><i class="fa fa-fax text-blue-m2 text-xs mr-1"></i> <span class="text-600 text-90">Fax:</span> (123) 456-7890</div>
                                                                    <div class="my-2"><i class="fa fa-envelope text-blue-m2 text-xs mr-1"></i> <span class="text-600 text-90">Email:</span> <span class="badge badge-warning badge-pill px-25">info@zionshipping.com</span></div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div id="invoice">
                                                        <div class="invoice overflow-auto">
                                                            <div style="min-width: 600px;">
                                                                <header class="notices" style="background-color:#e7f2ff; padding:15px 15px; height:115px;">
                                                                    <div class="row">
                                                                        <div style="width:80px; margin-right: 100px;">
                                                                            <a href="javascript:;">
                                                                                <img src="'.storage_path().'/'.'app'.'/public/carrier_logos/'.$carrier.'.png" width="80" alt="">
                                                                            </a>
                                                                        </div>
                                                                        <div class="invoice-header" style="margin-left: 100px;">
																			<div class="invoice-from" style=" width:200px; margin-right:150px; margin-top:-10%;">
																				<small>SHIPPER:</small>
																				<address class="m-t-5 m-b-5">
																					<strong class="text-inverse">'.$request->from_name.'</strong><br>
																					'.$request->from_address.' <br> '.$request->from_zip.' <br>'.$request->from_state.', '.$request->from_country_name.'<br>
																					Phone: '.$request->from_phone.'
																				</address>
																			</div>
																			<div class="invoice-to" style="margin-left: 150px;width:200px; margin-top:-60%;">
																				<small>CONSIGNEE:</small>
																				<address class="m-t-5 m-b-5">
																					<strong class="text-inverse">'.$request->to_name.' </strong><br>
																					'.$request->to_address.' <br>'.$request->to_zip.', <br>'.$request->to_state.' '.$request->to_country_name.'<br>
																					Phone: '.$request->to_phone_1.'
																				</address>
																			</div>
																			<div class="invoice-date" style="margin-left: 300px;width:200px; margin-top:-60%;">
																				<h1 class="account-id">Shipment no: '.$invoice_num.' </h1>
																				<h1 class="account-id">Account No: 6833 </h1>
																				<div class="company-details">
																					<div class="date">Shipment Date:    '.$shipment_date.'</div>
																					<div class="date"> Standard <span>By Air</span></div>
																					<div class="date">Home Delivery </div>
																				</div>
																			</div>
                                                                        </div>
                                                                    </div>
                                                                </header>
                                                                <main>
                                                                    <table style="width:150%;">
                                                                        <thead>
                                                                            <tr>
                                                                                <th>No Pieces</th>
                                                                                <th class="text-left">DESCRIPTION</th>
                                                                                <th class="text-right">WEIGHT</th>
                                                                                <th class="text-right">VOLUME</th>
                                                                                <th class="text-right">DIMENSION</th>
                                                                            </tr>
                                                                        </thead>
                                                                        <tbody>
                                                                            '.$table_html.'
                                                                            <tr>
                                                                                <td colspan="2" rowspan="6">
                                                                                    <h6 class="certify-text" style="margin-top:-20px">
                                                                                        I <span style="display:inline-block; border-bottom:1px solid #000000;  vertical-align:center; position:relative; height:1px; width:45%; bottom:-9px;">   </span> , hereby certify that this cargo does not contain any illegal, unauthorized, explosives, incendiaries, or hazardous materials. I consent to a search of this cargo. I am aware that:<br>
                                                                                        (1) Cargo containing hazardous materials (dangerous goods) for transportation by aircraft must be offered in accordance with Federal Hazardous Materials Regulations (49 CFR parts 171 through 180).<br>
                                                                                        (2) A violation can result in five years imprisonment and penalties of $250.000 or more (49 U.S.C.5124).<br>
                                                                                        Failure to comply with the above will result in further disciplinary actions. Zion Shipping will not be held responsible.<br>
                                                                                        (4) I understand that Zion Shipping will only refund the amount that was declared for the items in my package if items are lost or damaged. Undeclared items may subject to an additional fees depending on customs  and duties regulations. Any additional fees from undeclared items will be charged to the shipper.  If no declared value was given at the time that shipment was made, Zion Shipping will not provide no form of refund or credit.<br>
                                                                                        (5) Client has certified that all items in the packages have been declared. Items that were not listed or declared will not be considered for refund or credit. Undeclared items will subject to a charge back and this fee will have to pay before the delivery.<br>
                                                                                        If any items are lost or damaged, they must be reported within 24 hours from the delivery or pick -up time. Failure to do so will result in claim being denied. In this case, no refund or credit will be provided. <br>
                                                                                        (6) I also certify that all information I provided is accurate and complete. <br>
                                                                                        (7) THERE MAY BE AN ADDITIONAL CUSTOMS AND DUTIES FEE. '.$carrier.' CANNOT GIVE ANY ESTIMATE ABOUT THIS CHARGE BECAUSE IT IS UNDER HAITI CUSTOMS AUTHORITIES CONTROL.<br>
                                                                                        <hr style="color: #f4f4f4; margin-top:8px; margin-bottom:8px;">
																						
                                                                                        <span style="font-weight:600; font-size:10px; color:#000000;">Shipper’s signature : </span><span style="display:inline-block; border-bottom:1px solid #000000;  vertical-align:center; position:relative; height:1px; width:40%; bottom:-15px;">   </span>
                                                                                    </h6>
                                                                                </td>
                                                                                <td colspan="2" class="unit">Total Weight:</td>
                                                                                <td class="total">'.$total_weight.' lbs</td>
                                                                                <!--Total Weight-->
                                                                            </tr>
                                                                            <tr>
                                                                                <td colspan="2" class="unit">Total Volume:</td>
                                                                                <td class="total">'.$total_volume.'.00</td>
                                                                                <!--Total Volume-->
                                                                            </tr>
                                                                            <tr>
                                                                                <td colspan="2" class="unit">Total Value:</td>
                                                                                <td class="total">$'.$request->total_value.'</td>
                                                                                <!--Total Volume-->
                                                                            </tr>
                                                                            <tr>
                                                                                <td colspan="2" class="unit">Freight:</td>
                                                                                <td class="total">$'.$request->deliveryEstimatePrice.'</td>
                                                                                <!--Total Volume-->
                                                                            </tr>
                                                                            <tr>
                                                                                <td colspan="2" class="unit"></td>
                                                                                <td class="total"></td>
                                                                                <!--Total Volume-->
                                                                            </tr>
                                                                        </tbody>
                                                                        <tfoot>
                                                                            <tr>
                                                                                <td colspan="2">SUBTOTAL</td>
                                                                                <td>$'.$request->deliveryEstimatePrice.'</td>
                                                                            </tr>
                                                                            <tr>
                                                                                <td colspan="2"></td>
                                                                                <td colspan="2">TAX 7%</td>
                                                                                <td>$'.number_format((float)$value_tax, 2, '.', '').'</td>
                                                                            </tr>
                                                                            <tr>
                                                                                <td colspan="2"></td>
                                                                                <td colspan="2">GRAND TOTAL</td>
                                                                                <td>$'.number_format((float)$grand_total, 2, '.', '').'</td>
                                                                            </tr>
                                                                        </tfoot>
                                                                    </table> 
                                                                    <div class="thanks" style="margin-top:-120px; margin-bottom:10px;">Thank you!</div>
                                                                    <div class="thanks" style="font-size:12px; font-weight:600;margin-top:-10px; margin-bottom:10px;">Use Promo Code: ZION </div>
                                                                    <div class="thanks" style="margin-top:-3px; font-weight:500; margin-bottom:5px;">10% OFF ON YOUR NEXT REGULAR SHIPMENT</div>
                                                                    <div class="thanks" style="margin-top:-6px; line-height:6px; font-size:8px;">P.S: this coupon can be used by CHERLY TERVIL and can not be combined with any <br>other offer for flat rate shipments</div>
                                                                    <div class="notices" style="margin-top:20px; width:605px;">
                                                                        <div>EFFECTIVE IMMEDIATELY:</div>
                                                                        <div class="notice">ANY AMOUNT OVER $1000.00 MUST BE MADE WITH MONEY ORDER OR DIRECT WIRE TRANSFER. </div>
                                                                    </div>
                                                                </main>
                                                               <!-- <footer>Invoice was created on a computer and is valid without the signature and seal.</footer>-->
                                                            </div>
                                                            <!--DO NOT DELETE THIS div. IT is responsible for showing footer always at the bottom-->
                                                            <div></div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                </body>
                            </html>';
        $pdf = App::make('dompdf.wrapper');
        $pdf->loadHTML($pdfHtml);
        $pdf->save(storage_path('app/public/receipts/').'/'.$pdfFilename);
    }

    /**
     * 
     * Function for prepare PDF of shipment labels and render it in browser
     * 
     * @param int $quote_id
     */
    private function generateFedExShipmentLabelPDF($tracking_numbers = [], $pdfFilename)
    {
        if (!empty($tracking_numbers)) {
            $siteName = setting("site.title", "Zion Shipping");
            $pdfHtml = '<!DOCTYPE html>
            <html>
            <head>
                <meta charset="utf-8">
                <meta name="viewport" content="width=device-width, initial-scale=1">
                <title>'.$siteName.' - Shipment Labels</title>
            </head>
            <body style="margin:360px 75px 50px 25px; position: relative;">';

            $tempCount = 0;
            foreach ($tracking_numbers as $label) {
                $filename = '';
                $filename = "label_".trim($label).".PNG";
                $style = '';
                $width = 700;
                // $imgPadding = 'padding-left:20px;padding-right:0px;';
                $imgPadding = '';
                if ($tempCount != 0) {
                    $style = 'style="page-break-before: always;"';
                    $width = 700;
                    // $imgPadding = 'padding-left:120px;padding-right:0px;';
                    $imgPadding = '';
                } 
                $pdfHtml .= '<h4 '.$style.'></h4>
                <div style="text-align: center; position: absolute;top: 45%; width: 100%;">
                    <div>
                        <img src="'.url("label/$filename").'" height="400" width="'.$width.'" style="'.$imgPadding.'">
                    </div>
                </div>';
                $tempCount++;
            }
            $pdfHtml .= '</body>
            </html>';
            $pdf = App::make('dompdf.wrapper');
            $pdf->loadHTML($pdfHtml);
            $pdf->save(storage_path('app/public/label/').'/'.$pdfFilename);
        }
        return;
    }

    /**
     * 
     * Function for prepare PDF of shipment labels and render it in browser
     * 
     * @param int $quote_id
     */
    private function generateUSPSShipmentLabelPDF($tracking_numbers = [], $pdfFilename,$international)
    {
        if (!empty($tracking_numbers)) {
            $siteName = setting("site.title", "Zion Shipping");
            $pdfHtml = '<!DOCTYPE html>
            <html>
            <head>
                <meta charset="utf-8">
                <meta name="viewport" content="width=device-width, initial-scale=1">
                <title>'.$siteName.' - Shipment Labels</title>
            </head>
            <body style="position: relative;">';

            $tempCount = 0;
            foreach ($tracking_numbers as $label) {
                $filename = '';
                $filename = "label_".trim($label).".PNG";
                $style = '';
                $width = '700';
                // $imgPadding = 'padding-left:20px;padding-right:0px;';
                $imgPadding = '';
                if ($tempCount != 0) {
                    $style = 'style="page-break-before: always;"';
                    $width = 700;
                    // $imgPadding = 'padding-left:120px;padding-right:0px;';
                    $imgPadding = '';
                } 
                if($international){
                    $transform = '';
                }else{
                    $transform = 'transform: rotate(270deg);';
                }
                $pdfHtml .= '<h4 '.$style.'></h4>
                <div style="text-align: center; position: absolute;top: 0%; width: 100%;">
                    <div>
                        <img src="'.url("label/$filename").'" height="auto" width="'.$width.'" style="'.$imgPadding.';'.$transform.'">
                    </div>
                </div>';
                $tempCount++;
            }
            $pdfHtml .= '</body>
            </html>';
            $pdf = App::make('dompdf.wrapper');
            $pdf->loadHTML($pdfHtml);
            $pdf->save(storage_path('app/public/label/').'/'.$pdfFilename);
        }
        return;
    }

    /**
     * 
     * Function for prepare PDF of shipment labels and render it in browser
     * 
     * @param int $quote_id
     */
    private function generateUPSShipmentLabelPDF($tracking_numbers = [], $pdfFilename, $pickupDate, $pickupTime){
        if (!empty($tracking_numbers)) {
            $pickup_start_time  = date("g:i A", strtotime($pickupTime));
            $pickup_date        = date("l, F d, Y", strtotime($pickupDate));
            
            $siteName = setting("site.title", "Zion Shipping");
            $pdfHtml = '<!DOCTYPE html>
            <html>
            <head>
                <meta charset="utf-8">
                <meta name="viewport" content="width=device-width, initial-scale=1">
                <title>'.$siteName.' - Shipment Labels</title>
            </head>
            <body style="padding: 5px; position: relative;">';

            $tempCount = 0;
            foreach ($tracking_numbers as $label) {
                $filename = '';
                $filename = "label_".trim($label).".PNG";
                $style = '';
                $width = 700;
                $imgPadding = 'padding-left:20px;padding-right:0px;';
                if ($tempCount != 0) {
                    $style = 'style="page-break-before: always;"';
                    $width = 800;
                    $imgPadding = 'padding-left:120px;padding-right:0px;';
                } 
                $pdfHtml .= '<h4 '.$style.'>View/Print Label</h4>
                <div style="text-align: justify;width: 100%;">
                    <ol>
                        <li style="margin: 20px 0;">
                            <b>Ensure there are no other shipping or tracking labels attached to your package(s).</b> Remove all previous labels or barcodes on the package(s). Make sure the package(s) is well close and tape or wrapped.
                        </li>
                        <li style="margin: 20px 0;">
                            <b>Fold the printed label at the solid line below.</b> Place the label and all 3 customs Invoices (if any) in a Shipping Pouch. If you do not have a pouch, affix the folded label using clear plastic shipping tape over the entire label. Put the 3 customs invoices (if any) behind the label.
                        </li>
                        <li style="margin: 20px 0;">
                            <b>Get ready for pickup.</b> Make sure your package is ready to pickup. If anycase you miss the pickup, you will have to reschedule a new one at your own and you will get charge for the next schedule. If you miss a pickup and don’t want to pay to schedule a new one, you can drop of the package at the closest The UPS Store®, UPS Access Point™(TM) location, UPS Drop Box, UPS Customer Center, Staples® or Authorized Shipping Outlet.
                        </li>
                        <p>
                            Note that: <b>This shipment will pickup on…</b> ( '.$pickup_date.' after '.$pickup_start_time.')
                        </p>
                    </ol>
                </div>
                <div style="text-align: center; position: absolute;top: 45%; width: 100%;">
                    <b>FOLD HERE</b>
                    <hr style="border-top: 2px dashed #000; border-bottom: none;">
                    <div style="text-align: center;margin-top: 20px;">
                        <img src="'.url("label/$filename").'" height="400" width="'.$width.'" style="'.$imgPadding.'">
                    </div>
                </div>';
                $tempCount++;
            }
            $pdfHtml .= '</body>
            </html>';
            $pdf = App::make('dompdf.wrapper');
            $pdf->loadHTML($pdfHtml);
            $pdf->save(storage_path('app/public/label/').'/'.$pdfFilename);
        }
        return;
    }
}