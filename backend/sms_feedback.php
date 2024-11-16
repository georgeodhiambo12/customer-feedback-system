<?php
require 'vendor/autoload.php';

use AfricasTalking\SDK\AfricasTalking;

$username = 'sandbox'; 
$apiKey = 'atsk_16f7d8baf8a789aea8d6b61e8e4bb57b9fd874ee4d2c23d0048bfa2611789f07a1a2ba9a'; 

$AT = new AfricasTalking($username, $apiKey);

$sms = $AT->sms();

// Phone number to send the questions
$phoneNumber = "+254745058878"; 

// Questionnaire message
$message = "Please rate the following by replying with 'Q1=X Q2=Y Q3=Z' (1-5 scale):\n" .
           "Q1: Rate your experience with our product\n" .
           "Q2: Rate our customer care\n" .
           "Q3: Are you happy with the product?";

// Send SMS
try {
    $response = $sms->send([
        'to' => $phoneNumber,
        'message' => $message
    ]);

    echo "SMS API Response:\n";
    print_r($response);

    echo "\nSMS sent successfully!";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
