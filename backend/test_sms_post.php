<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$postData = [
    'from' => '+254745058878', 
    'text' => 'Q1=4 Q2=3 Q3=5' 
];

$ch = curl_init('http://localhost/feedback-system/backend/receive_feedback_sms.php');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postData));

$response = curl_exec($ch);

if (curl_errno($ch)) {
    echo 'cURL error: ' . curl_error($ch);
} else {
  //  echo "Response from receive_feedback_sms.php: " . $response;
}

curl_close($ch);
?>
