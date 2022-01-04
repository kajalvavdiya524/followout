<?php

return [
    'support_email' => 'info@followout.tv',
    'sales_rep_notification_email' => 'info@followout.tv',

    // Release followee role from user after given amount of days
    'release_followee_role_after' => 60,

    // Date and time formats
    'time_format' => 'h:i A',
    'date_format' => 'm/d/Y',
    'datetime_format' => 'h:i A m/d/Y',
    'date_format_date_string' => 'l, F jS, Y',
    'date_format_date_time_string' => 'h:i A, F jS, Y',
    'date_format_time_at_date_string' => 'F jS, Y \a\t h:i A',
    'date_format_time_at_date_string_short' => 'M j Y \a\t h:i a',
    'date_format_date_time_string_long' => 'h:i A, l, F jS, Y',

    'followout_ios_app_url' => 'https://itunes.apple.com/us/app/followout/id1254455001?ls=1&mt=8',
    'followout_llc_user_id' => '59b48b4463d83d0b93739d25',

    'chargebee_setup_fee' => env('CHARGEBEE_SETUP_FEE_ID', 'FOLLOWOUTS-PRO-SETUP-FEE'),
    'chargebee_monthly_promo_coupon' => env('CHARGEBEE_MONTHLY_PROMO_CODE', 'PROMO-FOLLOWOUTS-PRO'),
    'chargebee_annual_promo_coupon' => env('CHARGEBEE_ANNUAL_PROMO_CODE', 'PROMO-FOLLOWOUTS-PRO'),
];
