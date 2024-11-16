<?php
require 'vendor/autoload.php';

use AfricasTalking\SDK\AfricasTalking;

// AfricasTalking credentials
$username = 'sandbox';  
$apiKey   = 'atsk_16f7d8baf8a789aea8d6b61e8e4bb57b9fd874ee4d2c23d0048bfa2611789f07a1a2ba9a';

$africasTalking = new AfricasTalking($username, $apiKey);
$sms = $africasTalking->sms();

$recipients = [
    '+2547745058878',
    '+2547737219344'
];

// Here goes the message in the survey
$message = "Customer Survey:\nQ1: Rate your experience with our product (1-5)\nQ2: Rate our customer care (1-5)\nQ3: Are you happy with the product? (1-5)\nReply with: Q1=4 Q2=3 Q3=5";

// Sending the  SMS to each recipient
foreach ($recipients as $phoneNumber) {
    try {
        $result = $sms->send([
            'to'      => $phoneNumber,
            'message' => $message
        ]);
        
        // Displaying the results as in the Api
        echo "Survey sent to $phoneNumber: " . json_encode($result) . "<br>";
        
    } catch (Exception $e) {
        echo "Error sending SMS to $phoneNumber: " . $e->getMessage() . "<br>";
    }
}
?>
