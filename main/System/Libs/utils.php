<?php

/*
 * 	MOD: 			UTILIDADE
 * 	DESCRIPTION:	funcões de utilidades diversa
 * 	VERSION: 		1.0
 * 	DATE:			11/03/2014
 * 	BY: 			J2B DIGITAL
 */

function validaEmail($email) {
    $mail_correcto = 0;
    //verifico umas coisas
    if ((strlen($email) >= 6) && (substr_count($email, "@") == 1) && (substr($email, 0, 1) != "@") && (substr($email, strlen($email) - 1, 1) != "@")) {
        if ((!strstr($email, "'")) && (!strstr($email, "\"")) && (!strstr($email, "\\")) && (!strstr($email, "\$")) && (!strstr($email, " "))) {
            //vejo se tem caracter .
            if (substr_count($email, ".") >= 1) {
                //obtenho a terminação do dominio
                $term_dom = substr(strrchr($email, '.'), 1);
                //verifico que a terminação do dominio seja correcta
                if (strlen($term_dom) > 1 && strlen($term_dom) < 5 && (!strstr($term_dom, "@"))) {
                    //verifico que o de antes do dominio seja correcto
                    $antes_dom = substr($email, 0, strlen($email) - strlen($term_dom) - 1);
                    $caracter_ult = substr($antes_dom, strlen($antes_dom) - 1, 1);
                    if ($caracter_ult != "@" && $caracter_ult != ".") {
                        $mail_correcto = 1;
                    }
                }
            }
        }
    }
    if ($mail_correcto)
        return 1;
    else
        return 0;
}

function checkdate($data) {
    // VERIFICA DE DATA É VÁLIDA (formato de entrada de data brasileiro)
    $data = explode("/", $data);
    return checkdate($data[1], $data[0], $data[2]);
}

function checktime($time) {
    // VERIFICA DE HORA É VÁLIDA
    $time = explode(":", $time);
    if ($time[0] < 24 && $time[0] >= 0) {
        if ($time[1] < 60 && $time[1] >= 0) {
            if (isset($time[2])) {
                if ($time[2] < 60 && $time[2] >= 0) {
                    return true;
                } else {
                    return false;
                }
            } else {
                return true;
            }
        } else {
            return false;
        }
    } else {
        return false;
    }
}

function formataDataDeMascara($data) {

    $data = explode('/', $data);
    $data = $data[2] . '-' . $data[1] . '-' . $data[0];

    return $data;
}

function formataDataDeBanco($data) {
    $data = explode('-', $data);
    $data = $data[2] . '/' . $data[1] . '/' . $data[0];

    return $data;
}

?>