<?php

namespace App\Core;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class Mailer {
    public static function send($to, $subject, $body) {
        $mail = new PHPMailer(true);

        try {
            // Server settings
            if (MAIL_DRIVER === 'smtp') {
                $mail->isSMTP();
                $mail->Host       = MAIL_HOST;
                $mail->SMTPAuth   = true;
                $mail->Username   = MAIL_USER;
                $mail->Password   = MAIL_PASS;
                // Port 465 requires SMTPS, 587 requires STARTTLS
                $mail->SMTPSecure = (MAIL_PORT == 465) ? PHPMailer::ENCRYPTION_SMTPS : PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port       = MAIL_PORT;
            } else {
                $mail->isMail();
            }

            // Recipients
            $mail->setFrom(MAIL_FROM_ADDRESS, MAIL_FROM_NAME);
            $mail->addAddress($to);

            // Content
            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body    = $body;
            $mail->CharSet = 'UTF-8';

            $mail->send();
            return true;
        } catch (Exception $e) {
            error_log("Mailer Error: {$mail->ErrorInfo}");
            return false;
        }
    }
}
