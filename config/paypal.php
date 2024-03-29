<?php

return [
    'client_id' => env('PAYPAL_CLIENT_ID'),
    'secret' => env('PAYPAL_SECRET'),

    /**
     * SDK configuration
     */
    'settings' => [
        /**
         * Available option 'sandbox' or 'live'
         */
        'mode' => env('PAYPAL_MODE', 'sandbox'),

        /**
         * Specify the max request time in seconds
         */
        'http.ConnectionTimeOut' => 60,

        /**
         * Whether want to log to a file
         */
        'log.LogEnabled' => false,

        /**
         * Specify the file that want to write on
         */
        'log.FileName' => storage_path() . '/logs/paypal.log',

        /**
         * Available option 'FINE', 'INFO', 'WARN' or 'ERROR'
         *
         * Logging is most verbose in the 'FINE' level and decreases as you
         * proceed towards ERROR
         */
        'log.LogLevel' => 'FINE'
    ],
];
