<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Carriers Credentials
    |--------------------------------------------------------------------------
    |
    | This option specifies the Carriers credentials for your account.
    | You can put it here but I strongly recommend to put thoses settings into your
    | .env & .env.example file.
    |
    */
    'dhl_sandbox_url'               => env('dhl_sandbox_url', 'https://express.api.dhl.com/mydhlapi/test/'),
    'dhl_live_url'                  => env('dhl_live_url', 'https://express.api.dhl.com/mydhlapi/'),
    'dhl_api_key'                   => env('dhl_api_key', ''),
    'dhl_api_secret'                => env('dhl_api_secret', ''),
    'dhl_sandbox_enable'            => env('dhl_sandbox_enable', true),
    'ups_sandbox_url'               => env('ups_sandbox_url', 'https://wwwcie.ups.com/'),
    'ups_live_url'                  => env('ups_live_url', 'https://onlinetools.ups.com/'),
    'ups_access_key'                => env('ups_access_key', ''),
    'ups_user_id'                   => env('ups_user_id', ''),
    'ups_password'                  => env('ups_password', ''),
    'ups_sandbox_enable'            => env('ups_sandbox_enable', true),
    'ups_shipper_number'            => env('ups_shipper_number', ''),
    'fedex_sandbox_url'             => env('fedex_sandbox_url', 'https://apis-sandbox.fedex.com/'),
    'fedex_live_url'                => env('fedex_live_url', 'https://apis.fedex.com/'),
    'fedex_sandbox_client_id'       => env('fedex_sandbox_client_id', ''),
    'fedex_sandbox_client_secret'   => env('fedex_sandbox_client_secret', ''),
    'fedex_live_client_id'          => env('fedex_live_client_id', ''),
    'fedex_live_client_secret'      => env('fedex_live_client_secret', ''),
    'fedex_sandbox_accountNumber'   => env('fedex_sandbox_accountNumber', ''),
    'fedex_live_accountNumber'      => env('fedex_live_accountNumber', ''),
    'fedex_sandbox_enable'          => env('fedex_sandbox_enable', true),
    'usps_api_url'                  => env('usps_api_url', 'https://secure.shippingapis.com/ShippingApi.dll'),
    'usps_test_api_url'             => env('usps_test_api_url', 'https://production.shippingapis.com/ShippingAPITest.dll'),
    'usps_username'                 => env('usps_username', ''),
    'usps_sandbox_enable'           => env('usps_sandbox_enable', true),
    'usps_stg_api_url'              => env('usps_stg_api_url', 'https://stg-secure.shippingapis.com/ShippingApi.dll'),
    'usps_shipping_sandbox_url'     => env('usps_shipping_sandbox_url', 'https://elstestserver2.endicia.com/LabelService/EwsLabelService.asmx?wsdl'),
    'usps_shipping_sandbox_account_id'     => env('usps_shipping_sandbox_account_id', '3004020'),
    'usps_shipping_sandbox_passphrase'     => env('usps_shipping_sandbox_passphrase', 'April2022!'),
    'usps_shipping_sandbox_requesterid'     => env('usps_shipping_sandbox_requesterid', '8346cc40-5367-4358-b04f-257e7675d9fb'),
    // 
];
