<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer-master/src/PHPMailer.php';
require 'PHPMailer-master/src/SMTP.php';
require 'PHPMailer-master/src/Exception.php';

include 'config.php';

$from = $_POST['from'] ?? '';
$text = $_POST['text'] ?? '';

$name = 'SMS User';
$email = 'sms_user@j4me.com';

// Extracting questionnaire responses from the SMS text
preg_match('/Q1=(\d)/', $text, $q1Match);
preg_match('/Q2=(\d)/', $text, $q2Match);
preg_match('/Q3=(\d)/', $q3Match);

$product_rating = $q1Match[1] ?? 0;
$customer_care_rating = $q2Match[1] ?? 0;
$satisfaction_rating = $q3Match[1] ?? 0;

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    echo "Database connection failed: " . $conn->connect_error;
    exit;
}

$stmt = $conn->prepare("INSERT INTO feedback (name, email, phone, product_rating, customer_care_rating, satisfaction_rating, feedback_date) VALUES (?, ?, ?, ?, ?, ?, NOW())");
$stmt->bind_param("sssiii", $name, $email, $from, $product_rating, $customer_care_rating, $satisfaction_rating);

if ($stmt->execute()) {
    $average_rating = ($product_rating + $customer_care_rating + $satisfaction_rating) / 3;

    if ($average_rating < 3) {
        $classification = 'Detractor';

        $message = "Dear Customer Support Team,\n\n";
        $message .= "We have received negative feedback from a customer. Please review the details below and consider reaching out to resolve any concerns.\n\n";
        $message .= "Customer Information:\n";
        $message .= " - Name: $name\n";
        $message .= " - Email: $email\n";
        $message .= " - Phone: $from\n\n";
        $message .= "Feedback Summary:\n";
        $message .= " - Product Rating: $product_rating\n";
        $message .= " - Customer Care Rating: $customer_care_rating\n";
        $message .= " - Satisfaction Rating: $satisfaction_rating\n";
        $message .= " - Feedback Date: " . date("Y-m-d H:i:s") . "\n\n";
        $message .= "Best regards,\n";
        $message .= "Abashwili Ltd";

        $notification_stmt = $conn->prepare("INSERT INTO notifications (message, recipient_phone, sent_date) VALUES (?, ?, NOW())");
        $notification_stmt->bind_param("ss", $message, $from);
        $notification_stmt->execute();
        $notification_stmt->close();

        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host = 'mail.smtp2go.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'tendersoko';
            $mail->Password = 'Barcampivorycoast2024!';
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
            $mail->Port = 443;

            // Email sender and recipient details
            $mail->setFrom('notifications@tendersoko.com', '[Abashwili Ltd] Support');
            $mail->addAddress('ochieng123george@gmail.com');
            $mail->Subject = 'Customer Complaint - Detractor Alert';
            $mail->Body = $message;

            $mail->send();
            echo "SMS response recorded successfully in feedback table. Alert sent.";
        } catch (Exception $e) {
            echo "SMS response recorded, but email failed to send. Error: " . $mail->ErrorInfo;
        }
    } else {
        echo "SMS response recorded successfully in feedback table.";
    }
} else {
    echo "Failed to record SMS response: " . $stmt->error;
}

$stmt->close();
$conn->close();
?>
