<?php

require dirname(dirname(__DIR__)).'/vendor/autoload.php';
require_once '../../main/System/Core/Loader.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;
use System\Core\Bootstrap;

try {
    $sistema = new Bootstrap();
    $mail = new PHPMailer(true);
    $mail->isSMTP();
    $mail->Host = 'smtp.mandrillapp.com';
    $mail->SMTPAuth = true;
    $mail->Username = 'j2bdigital@gmail.com';
    $mail->Password = 'BYka3Vr1hCFqjY5W34Ly4g';
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;         // Enable TLS encryption; `PHPMailer::ENCRYPTION_SMTPS` encouraged
    $mail->Port = 587;
    $mail->setFrom($_POST['email'], $_POST['nome']);
    $mail->addReplyTo($_POST['email'], $_POST['nome']);
    $sistema->DB_connect();
    $emails = $sistema->DB_fetch_array("SELECT nome, email FROM  hbrd_cms_notificacoes A INNER JOIN hbrd_main_usuarios B ON A.id_user = B.id WHERE email IS NOT NULL")->rows;
    if ($emails) {
        foreach ($emails as $row) {
            $mail->addAddress($row['email'], $row['nome']);
        }
    

        //$mail->addAddress('financeiro@hibridaweb.com.br');
        //$mail->addAddress('Novaindicacaogyn@gmail.com');
        $mail->isHTML(true);
        $mail->Subject = 'Formulário de contato Aupet';
        $msg = "<div style='background: gainsboro;'>";
        foreach ($_POST as $key => $value) {
            $msg .= "<b>$key</b>:  ".utf8_decode($value)."<br>";
        }
        $msg .= '</div>';
        $mail->Body = $msg;
        $mail->send();    
    }
    
    $formulario = $sistema->formularioObjeto($_POST);

    if(strlen($formulario->telefone) < 14){
        header("HTTP/1.0 500 Internal Server Error");
        echo 'O Núm. de Telefone não foi preenchido corretamente.';
        return;
    }
    if(!(bool)$formulario->id_assunto) unset($formulario->id_assunto);
    $sistema->DB_insert('hbrd_desk_contato', $formulario);
    echo 'Message has been sent';
} catch (\Exception $e) {
    header("HTTP/1.0 500 Internal Server Error");
    echo 'error';
} catch (Exception $e) {
    header("HTTP/1.0 500 Internal Server Error");
    echo 'error';
} 