<?php
require 'vendor/autoload.php';

use AfricasTalking\SDK\AfricasTalking;

$username = 'sandbox';
$apiKey   = 'atsk_16f7d8baf8a789aea8d6b61e8e4bb57b9fd874ee4d2c23d0048bfa2611789f07a1a2ba9a';
$AT       = new AfricasTalking($username, $apiKey);
$sms      = $AT->sms();

function getNextQuestion($currentQuestion) {
    $questions = [
        1 => "Rate our customer care (1-5):",
        2 => "Are you happy with the product? (1-5):"
    ];

    return isset($questions[$currentQuestion]) ? $questions[$currentQuestion] : null;
}

function processResponse($phoneNumber, $response) {
    global $sms;

    // Retrieving current survey state
    $state = getSurveyState($phoneNumber);
    if (!$state) {
        echo "No survey in progress for this user.";
        return;
    }

    $currentQuestion = $state['current_question'];
    $responses       = json_decode($state['responses'], true) ?? [];

    // Saving the response to the current question
    $responses[$currentQuestion] = $response;
    saveSurveyState($phoneNumber, $currentQuestion + 1, json_encode($responses));

    // Sending the next question or finish the survey
    $nextQuestion = getNextQuestion($currentQuestion);
    if ($nextQuestion) {
        $sms->send([
            'to'      => $phoneNumber,
            'message' => $nextQuestion,
            'enqueue' => true,
        ]);
    } else {
        // Survey completed, save responses to final feedback table
        saveFinalFeedback($phoneNumber, $responses);
        $sms->send([
            'to'      => $phoneNumber,
            'message' => "Thank you for completing the survey!",
            'enqueue' => true,
        ]);
    }
}

function getSurveyState($phoneNumber) {
    // Fetching survey state from the database
    $servername = "localhost";
    $username = "root";
    $password = "";
    $dbname = "company_feedback";

    $conn = new mysqli($servername, $username, $password, $dbname);

    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    $sql = "SELECT * FROM survey_states WHERE phone='$phoneNumber'";
    $result = $conn->query($sql);

    $state = $result->fetch_assoc();
    $conn->close();

    return $state;
}

function saveFinalFeedback($phoneNumber, $responses) {
    // Save final feedback responses to the feedback table
    $servername = "localhost";
    $username = "root";
    $password = "";
    $dbname = "company_feedback";

    $conn = new mysqli($servername, $username, $password, $dbname);

    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Assuming responses are in order [product_rating, customer_care_rating, satisfaction_rating]
    $productRating       = $responses[1];
    $customerCareRating  = $responses[2];
    $satisfactionRating  = $responses[3];
    
    $sql = "INSERT INTO feedback (phone, product_rating, customer_care_rating, satisfaction_rating, feedback_date)
            VALUES ('$phoneNumber', '$productRating', '$customerCareRating', '$satisfactionRating', NOW())";
    
    if ($conn->query($sql) === TRUE) {
        echo "Final feedback saved!";
    } else {
        echo "Error saving feedback: " . $conn->error;
    }

    $conn->close();
}
