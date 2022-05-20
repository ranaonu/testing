<?php

return [
    /*
    |--------------------------------------------------------------------------
    | UPS Credentials
    |--------------------------------------------------------------------------
    |
    | This option specifies the UPS credentials for your account.
    | You can put it here but I strongly recommend to put thoses settings into your
    | .env & .env.example file.
    |
    */
    'access_key' => env('UPS_ACCESS_KEY', '5D92AEA77E052575'),
    'user_id'    => env('UPS_USER_ID', 'ziontech2010'),
    'password'   => env('UPS_PASSWORD', 'Developer2021'),
    'sandbox'    => env('UPS_SANDBOX', true), // Set it to false when your ready to use your app in production.
];
