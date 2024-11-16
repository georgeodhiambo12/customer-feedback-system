<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer-master/src/Exception.php';
require 'PHPMailer-master/src/PHPMailer.php';
require 'PHPMailer-master/src/SMTP.php';

$mail = new PHPMailer();
$mail->isSMTP();
$mail->Host = 'smtp.gmail.com';
$mail->SMTPAuth = true;
$mail->Username = 'georgesmalling12@gmail.com';
$mail->Password = '@Smalling97'; 
$mail->SMTPSecure = 'tls';
$mail->Port = 587;

$mail->SMTPDebug = 2;
$mail->Debugoutput = 'html';

$mail->setFrom('no-reply@gmail.com', 'George');
$mail->addAddress('ochieng123george@gmail.com');
$mail->Subject = 'Test Email from PHPMailer';
$mail->Body = 'This is a test email sent from localhost using PHPMailer with Gmail SMTP.';

if (!$mail->send()) {
    echo 'Mail sending failed: ' . $mail->ErrorInfo;
} else {
    echo 'Mail sent successfully.';
}
?>
