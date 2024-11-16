<?php
require 'vendor/autoload.php';

use AfricasTalking\SDK\AfricasTalking;

$username = 'sandbox';
$apiKey   = 'atsk_16f7d8baf8a789aea8d6b61e8e4bb57b9fd874ee4d2c23d0048bfa2611789f07a1a2ba9a';
$AT       = new AfricasTalking($username, $apiKey);
$sms      = $AT->sms();

function startSurvey($phoneNumber) {
    global $sms;
    $question1 = "Rate your experience with our product (1-5):";
    
    try {
        $sms->send([
            'to'      => $phoneNumber,
            'message' => $question1,
            'enqueue' => true,
        ]);

        // Save survey state to track the user's responses
        saveSurveyState($phoneNumber, 1, null); 
        echo "Survey started!";
    } catch (Exception $e) {
        echo "Error starting survey: " . $e->getMessage();
    }
}

function saveSurveyState($phoneNumber, $currentQuestion, $responses) {
    $servername = "localhost";
    $username = "root"; 
    $password = ""; 
    $dbname = "company_feedback";

    $conn = new mysqli($servername, $username, $password, $dbname);

    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Inserting 
    $sql = "INSERT INTO survey_states (phone, current_question, responses) 
            VALUES ('$phoneNumber', $currentQuestion, '$responses')
            ON DUPLICATE KEY UPDATE current_question=$currentQuestion, responses='$responses'";
    
    if ($conn->query($sql) === TRUE) {
        echo "Survey state saved!";
    } else {
        echo "Error saving survey state: " . $conn->error;
    }

    $conn->close();
}

startSurvey('+254745058878'); 
