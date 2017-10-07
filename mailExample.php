<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

//Load composer's autoloader
require 'vendor/autoload.php';

class mailController{
    public function sendMail($msgs){
       $content = '';
       foreach($msgs as $msg){
           $content .= $msg."\r\n";
       }
       $mail = new PHPMailer;

       $mail->isSMTP();                                 // Set mailer to use SMTP
       $mail->Host = "smtp.gmail.com";                  // Specify main and backup SMTP servers
       $mail->SMTPAuth = true;                          // Enable SMTP authentication
       $mail->Username = 'yourgmail@gmail.com';         // SMTP username
       $mail->Password = 'yourpassword';                // SMTP password
       $mail->SMTPSecure = 'ssl';                       // Enable TLS encryption, `ssl` also accepted
       $mail->Port = 465;
       $mail->SMTPDebug = 1;

       $mail->CharSet      = "utf-8";
       $mail->From = '';
       $mail->addAddress("", "");
       $mail->isHTML(true);

       $mail->WordWrap = 70;
       $mail->Subject = '今日0050參考';
       $mail->Body    = $content;
       $mail->AltBody = '';

       if( !$mail->send() ) {
           echo "Error:".$mail->ErrorInfo;
       }else{
           echo "Success!";
       }
    }
}
