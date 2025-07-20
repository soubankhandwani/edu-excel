<?php
// Import necessary classes and functions
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;
require_once "../vendor/autoload.php";

// Your hCaptcha secret key
$secret = '0x7a67FcbC37Bd8ccdcA2491107abFBE1b48D5F176';

// Verify hCaptcha response
$response = $_POST['h-captcha-response'];
$verify_url = 'https://hcaptcha.com/siteverify';

$data = array(
    'secret' => $secret,
    'response' => $response
);

$options = array(
    'http' => array(
        'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
        'method'  => 'POST',
        'content' => http_build_query($data)
    )
);

$context  = stream_context_create($options);
$verify_response = file_get_contents($verify_url, false, $context);
$captcha_success = json_decode($verify_response);

if ($captcha_success->success == false) {
    echo json_encode(array('message' => "Captcha verification failed. Please try again.", 'status' => 400));
    exit;
}

// Read the HTML templates
$output = file_get_contents('../mail_templates/contact.html'); 
if ($output === false) {
    echo "Error reading the 'contact.html' file: " . error_get_last()['message'];
    exit;
}

$output2 = file_get_contents('../mail_templates/thankyou.html'); 
if ($output2 === false) {
    echo "Error reading the 'thankyou.html' file: " . error_get_last()['message'];
    exit;
}

// Replace placeholders in the template
$output = str_replace('%firstname%', $_POST['Name'], $output);
$output = str_replace('%school%', $_POST['School'], $output); 
$output = str_replace('%useremail%', $_POST['Email'], $output); 
$output = str_replace('%phone%', $_POST['Phone'], $output); 
$output = str_replace('%board%', $_POST['Board'], $output); 
$output = str_replace('%grade%', $_POST['Grade'], $output);
$output = str_replace('%message%', $_POST['Message'], $output);

// Setup PHPMailer
$mail = new PHPMailer(); 
$mail->isSMTP();
$mail->Host = 'mail.edu-excel.com';
$mail->SMTPAuth = true;
$mail->Username = 'enquiry@edu-excel.com';
$mail->Password = 'Enquiry@123@#';
$mail->SMTPSecure = 'tls';
$mail->Port = 587;
$mail->From = "enquiry@edu-excel.com"; 
$mail->FromName = "Edu Excel"; 
$mail->addAddress($_POST['Email']);

$mail->isHTML(true);
$mail->CharSet = "utf-8"; 
$mail->Subject = "Enquiry Email"; 
$mail->MsgHTML($output2);

if (!$mail->send()) {
    header('Content-Type: application/json');
    echo json_encode(array('message' => "Failed to send mail. Please contact site admin.", 'status' => 500));
} else { 
    $mail2 = new PHPMailer(); 
    $mail2->isSMTP();
    $mail2->Host = 'mail.edu-excel.com';
    $mail2->SMTPAuth = true;
    $mail2->Username = 'enquiry@edu-excel.com';
    $mail2->Password = 'Enquiry@123@#';
    $mail2->SMTPSecure = 'tls';
    $mail2->Port = 587;
    $mail2->From = "enquiry@edu-excel.com"; 
    $mail2->FromName = "Edu Excel"; 
    $mail2->addAddress("edu.excel.internationals@gmail.com");

    $mail2->isHTML(true);
    $mail2->CharSet = "utf-8"; 
    $mail2->Subject = "Enquiry Email"; 
    $mail2->MsgHTML($output);

    if (!$mail2->send()) {
        header('Content-Type: application/json');
        echo json_encode(array('message' => "Failed to send mail. Please contact site admin.", 'status' => 500));
    } else { 
        header('Content-Type: application/json');
        echo json_encode(array('message' => "Message has been sent successfully", 'status' => 200));
    }
}
?>
