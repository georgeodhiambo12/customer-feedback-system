<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer-master/src/PHPMailer.php';
require 'PHPMailer-master/src/SMTP.php';
require 'PHPMailer-master/src/Exception.php';

include 'config.php';

header('Content-Type: application/json');

// Retrieving the raw POST data
$rawData = file_get_contents("php://input");
$data = json_decode($rawData, true);

if (json_last_error() !== JSON_ERROR_NONE) {
    echo json_encode(["status" => "error", "message" => "Invalid JSON format"]);
    exit;
}

$name = $data['name'] ?? '';
$email = $data['email'] ?? '';
$phone = $data['phone'] ?? '';
$product_rating = $data['product_rating'] ?? 0;
$customer_care_rating = $data['customer_care_rating'] ?? 0;
$satisfaction_rating = $data['satisfaction_rating'] ?? 0;

if (!$name || !$email || !$phone) {
    echo json_encode(["status" => "error", "message" => "All fields are required"]);
    exit;
}

// Validating the phone number format
if (!preg_match('/^07[0-9]{8}$/', $phone)) {
    echo json_encode(["status" => "error", "message" => "Please enter a valid phone number in the format 07xxxxxxxx"]);
    exit;
}

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    echo json_encode(["status" => "error", "message" => "Database connection failed: " . $conn->connect_error]);
    exit;
}

// Inserting the feedback into database
$stmt = $conn->prepare("INSERT INTO feedback (name, email, phone, product_rating, customer_care_rating, satisfaction_rating, feedback_date) VALUES (?, ?, ?, ?, ?, ?, NOW())");
$stmt->bind_param("sssiii", $name, $email, $phone, $product_rating, $customer_care_rating, $satisfaction_rating);

if ($stmt->execute()) {
    $average_rating = ($product_rating + $customer_care_rating + $satisfaction_rating) / 3;

    $classification = '';
    if ($average_rating < 3) {
        $classification = 'Detractor';

        // Preparing the notification message that got to be sent to the Customer care
        $message = "Dear Customer Support Team,\n\n";
        $message .= "We have received negative feedback from a customer. Please review the details below and consider reaching out to resolve any concerns they may have.\n\n";
        $message .= "Customer Information:\n";
        $message .= " - Name: $name\n";
        $message .= " - Email: $email\n";
        $message .= " - Phone: $phone\n\n";
        $message .= "Feedback Summary:\n";
        $message .= " - Product Rating: $product_rating\n";
        $message .= " - Customer Care Rating: $customer_care_rating\n";
        $message .= " - Satisfaction Rating: $satisfaction_rating\n";
        $message .= " - Feedback Date: " . date("Y-m-d H:i:s") . "\n\n";
        $message .= "Best regards,\n";
        $message .= "Abashwili Ltd";

        // Insert notification into database
        $notification_stmt = $conn->prepare("INSERT INTO notifications (message, recipient_phone, sent_date) VALUES (?, ?, NOW())");
        $notification_stmt->bind_param("ss", $message, $phone);
        $notification_stmt->execute();
        $notification_stmt->close();

        //Sending the email
        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host = 'mail.smtp2go.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'tendersoko'; 
            $mail->Password = 'Barcampivorycoast2024!';
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
            $mail->Port = 443;

            $mail->setFrom('notifications@tendersoko.com', '[Abashwili Ltd] Support');
            $mail->addAddress('ochieng123george@gmail.com'); 
            $mail->Subject = 'Customer Complaint - Detractor Alert';
            $mail->Body = $message;

            $mail->send();
            echo json_encode(["status" => "success", "message" => "Feedback submitted and alert sent", "classification" => $classification]);
        } catch (Exception $e) {
            echo json_encode(["status" => "error", "message" => "Feedback submitted, but email failed to send. Error: " . $mail->ErrorInfo]);
        }
    } elseif ($average_rating == 3) {
        $classification = 'Passive';
        echo json_encode(["status" => "success", "message" => "Feedback submitted", "classification" => $classification]);
    } else {
        $classification = 'Promoter';
        echo json_encode(["status" => "success", "message" => "Feedback submitted", "classification" => $classification]);
    }
} else {
    echo json_encode(["status" => "error", "message" => "Failed to submit feedback"]);
}

$stmt->close();
$conn->close();
?>
