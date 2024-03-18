<?php

/*
 * 	MOD: 			MAIL
 * 	DESCRIPTION:	Controla todo envio de emails utilizando funão mail();
 * 	VERSION: 		1.0
 * 	DATE:			11/03/2014
 * 	BY: 			J2B DIGITAL
 */

function enviarEmailParaCliente($cliente = "", $assunto = "", $mensagem = "") {

    //------- Configuração do Sistema.

    $email_principal = $this->email_principal;
    $email_copia = $this->email_copias;

    //-------------------------------

    if (PHP_OS == "Linux")
        $quebra_linha = "\n"; //Se for Linux
    elseif (PHP_OS == "WINNT")
        $quebra_linha = "\r\n"; // Se for Windows

    $headers = "MIME-Version: 1.1" . $quebra_linha;
    $headers .= "Content-type: text/html; charset=iso-8859-1" . $quebra_linha;
    $headers .= "From: " . $email_principal . $quebra_linha;
    $headers .= "Return-Path: " . $email_principal . $quebra_linha;
    $headers .= "Reply-To:" . $email_principal . $quebra_linha;
    // ------------------------------

    return mail($cliente, $assunto, $mensagem, $headers, "-r" . $email_principal); // Envia o e-mail.
    //return 1;
}

function enviarEmailInterno($from = "", $assunto = "", $mensagem = "") {

    //------- Configuração do Sistema.

    $email_principal = $this->email_principal;
    $email_copia = $this->email_copias;

    //-------------------------------

    if (PHP_OS == "Linux")
        $quebra_linha = "\n"; //Se for Linux
    elseif (PHP_OS == "WINNT")
        $quebra_linha = "\r\n"; // Se for Windows

    $headers = "MIME-Version: 1.1" . $quebra_linha;
    $headers .= "Content-type: text/html; charset=iso-8859-1" . $quebra_linha;
    $headers .= "From: " . $email_principal . $quebra_linha;
    $headers .= "Return-Path: " . $email_principal . $quebra_linha;
    if ($from == "") {
        $headers .= "Reply-To:" . $email_principal . $quebra_linha;
    } else {
        $headers .= "Reply-To:" . $from . $quebra_linha;
    }
    // ------------------------------

    return mail($email_principal, $assunto, $mensagem, $headers, "-r" . $email_principal); // Envia o e-mail.
    //return 1;
}

?>