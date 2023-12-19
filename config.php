<?php

/**
 * Payment configs like Private API key, success url etc.
 */
return [

    // General information
    'checkout_api_key' => 'your api key',
    'referer' => 'www.yourwebsite.com',
    'test' => false,

    // Api urls
    'offer_url' => 'https://api.superpayments.com/v2/offers',
    'payment_url' => 'https://api.superpayments.com/v2/payments',

    // Callback urls
    'success_url' => 'https://www.yourwebsite.com/success.html',
    'cancel_url' => 'https://www.yourwebsite.com/cancel.html',
    'failure_url' => 'https://www.yourwebsite.com/failure.html',
];