<?php
    use PHPMailer\PHPMailer\PHPMailer;
    use PHPMailer\PHPMailer\Exception;

    require_once './controlDB.php';
    //require 'vendor/autoload.php';

    if ($_SERVER['REQUEST_METHOD'] == 'POST')
    {
        $email = $_POST['email'];
        $resetPassCode = hash('sha256', random_bytes(32));
        $expiry = date('Y-m-d H:i:s', strtotime('+30 minutes'));

        $stmt = $conn->prepare("UPDATE users SET resetPassCode = :resetPassCode, resetPassExpiry = :expiry WHERE email = :email");
        $stmt->bind_param("sss", $resetPassCode, $expiry, $email);
        
        if ($stmt->execute()) {
            enviarCorreoReset($email, $resetPassCode);
            echo "Se ha enviado un correo con instrucciones para restablecer tu contrase�a.";
        } else {
            echo "Error al generar el enlace de recuperaci�n.";
        }
        $stmt->close();
    }

    function enviarCorreoReset($email, $resetPassCode) {
        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'mario.baetam@educem.net';
            $mail->Password = 'vqdb nrrt vdrj cdqv';
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 587;
    
            $mail->setFrom('mario.baetam@educem.net', 'TecView');
            $mail->addAddress($email);
    
            $mail->isHTML(true);
            $mail->Subject = 'Recupera tu contrase�a';
            $mail->Body = '<h1>Recuperaci�n de contrase�a</h1><p>Haz clic en el siguiente enlace para restablecer tu contrase�a:</p>' .
                          '<a href="https://tecview.com/resetPassword.php?code=' . $resetPassCode . '&mail=' . urlencode($email) . '">Restablecer contrase�a</a>';
    
            $mail->send();
        } catch (Exception $e) {
            echo "Error al enviar correo: {$mail->ErrorInfo}";
        }
    }