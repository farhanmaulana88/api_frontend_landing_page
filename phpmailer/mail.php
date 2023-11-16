<?php
include "classes/class.phpmailer.php";
$mail = new PHPMailer;
$mail->IsSMTP();
$mail->SMTPSecure = 'ssl';
$mail->Host = "smtp.gmail.com"; //host masing2 provider email
$mail->SMTPDebug = 2;
$mail->Port = 465;
$mail->SMTPAuth = true;
$mail->Username = "support@sanders.co.id"; //user email
$mail->Password = "jnncnerkiwjeyuug"; //password email 
$mail->SetFrom("support@sanders.co.id", "Tester"); //set email pengirim
$mail->Subject = "Testing"; //subyek email
$mail->AddAddress($_GET['to'], "nama email tujuan");  //tujuan email
$mail->MsgHTML("Testing...");
if ($mail->Send()) echo "Message has been sent";
else echo "Failed to sending message";