<?php

session_start();

require_once '../sistema/System/Core/Loader.php';

use System\Libs\EmailMarketingCliente;

$mkt = new EmailMarketingCliente();

use System\Core\Bootstrap;

require_once "../_system.php";

$sistema = new _sys();

$sistema->DB_connect();

$table1 = "tb_espaco_formulario_reserva";
$table2 = "tb_espaco_formulario_reserva_condicoes_gerais";
$table3 = "tb_espaco_formulario_reserva_forma_pagamento";

$formulario = $sistema->formularioObjeto($_POST);

$validacao = validaFormulario($formulario);

$crop_sizes = array();

if (!isset($_SESSION["seo_session"])) {
    $_SESSION["seo_session"] = uniqid();
}

if (!$validacao->return) {
    echo json_encode($validacao);
} else {

    $resposta = new stdClass();
    $resposta->time = 4000;

    $data = $sistema->formularioObjeto($_POST, $table1);

    if (isset($data->nascimento) && $data->nascimento != "")
        $data->nascimento = $sistema->formataDataDeMascara($data->nascimento);
    else
        $data->nascimento = "NULL";
    if (isset($data->adquirente_nascimento) && $data->adquirente_nascimento != "")
        $data->adquirente_nascimento = $sistema->formataDataDeMascara($data->adquirente_nascimento);
    else
        $data->adquirente_nascimento = "NULL";

    if (isset($data->renda) && $data->renda != "")
        $data->renda = $sistema->formataMoedaBd($data->renda);
    else
        $data->renda = "NULL";

    if (isset($data->adquirente_renda) && $data->adquirente_renda != "")
        $data->adquirente_renda = $sistema->formataMoedaBd($data->adquirente_renda);
    else
        $data->adquirente_renda = "NULL";


    unset($data->data);
    
    $data->ip = $_SERVER['REMOTE_ADDR'];
    $data->session = $_SESSION["seo_session"];

    foreach ($data as $key => $value) {
        $fields[] = $key;
        if ($value == "NULL")
            $values[] = "$value";
        else
            $values[] = "'$value'";
    }

    $query = $sistema->DB_insert($table1, implode(',', $fields), implode(',', $values));
    $idReserva = $query->insert_id;

    unset($fields, $values);

    if ($query->query) {
        
        $formulario->id_lista = 138;
        $formulario->origem = "lista";

        $mkt->getEmailMarketing("salvar-email", $formulario);

        $verifica = $sistema->DB_num_rows("SELECT * FROM tb_emails_emails A INNER JOIN tb_listas_listas_has_tb_emails_emails B ON B.id_email = A.id AND A.email = '$formulario->email' AND B.id_lista = 4");
        if (!$verifica) {
            $verifica = $sistema->DB_fetch_array("SELECT * FROM tb_emails_emails WHERE email = '$formulario->email'");
            if (!$verifica->num_rows) {
                $addEmail = $sistema->DB_insert('tb_emails_emails', "nome,email", "'$formulario->proponente','$formulario->email'");
                $idEmail = $addEmail->insert_id;
            } else {
                $idEmail = $verifica->rows[0]['id'];
            }
            $addListaHasEmail = $sistema->DB_insert('tb_listas_listas_has_tb_emails_emails', "id_lista,id_email", "4,$idEmail");
        }

        // PREPARA EMAIL -------------------
        $emails = $sistema->DB_fetch_array("SELECT C.email, C.nome FROM tb_admin_forms A INNER JOIN tb_admin_email_notification B ON A.id = B.id_form INNER JOIN tb_admin_users C ON B.id_user = C.id WHERE A.id = 1 OR A.id = 4 GROUP BY B.id_user");

        if ($emails->num_rows) {

            if ($emails->num_rows) {
                foreach ($emails->rows as $mail) {
                    $to[] = array("email" => $mail['email'], "nome" => utf8_decode($mail['nome']));
                }
            }

            $assunto = "Formulário de Reserva.";  //Assunto da mensagem de contato.

            $arquivo = $sistema->trataParameter($formulario->proponente) . '-' . date("Y-m-d");
            $arquivo = str_replace("+", "-", $arquivo);
            $arquivo = "formulario-reserva-" . $arquivo;

            $link = "<a href = '" . $sistema->root_path . "pdf/dompdf.php?base_path=pagina/&navegador&output_file=$arquivo&input_file=pagina/reserva.php&id=$idReserva' > " . $sistema->root_path . "pdf/dompdf.php?base_path = pagina/&navegador&output_file = $arquivo&input_file = pagina/reserva.php&id=$idReserva</a>";

            $body = file_get_contents("../mailing_templates/form_reserva.html");
            $body = str_replace("{PROPONENTE}", $formulario->proponente, $body);
            $body = str_replace("{EMAIL}", $formulario->email, $body);
            $body = str_replace("{TELEFONE}", $formulario->telefone_residencial, $body);
            $body = str_replace("{CELULAR}", $formulario->celular, $body);
            $body = str_replace("{PDF}", $link, $body);


            $sistema->enviarEmail($to, $formulario->email, utf8_decode($assunto), utf8_decode($body));
        }

        $data = $sistema->formularioObjeto($_POST, $table2);
        $data->id_reserva = $idReserva;

        if (isset($data->corretor_vencimento) && $data->corretor_vencimento != "")
            $data->corretor_vencimento = $sistema->formataDataDeMascara($data->corretor_vencimento
            );
        else
            $data->corretor_vencimento = "NULL";

        if (isset($data->gerente_vencimento) && $data->gerente_vencimento != "")
            $data->gerente_vencimento = $sistema->formataDataDeMascara($data->gerente_vencimento
            );
        else
            $data->gerente_vencimento = "NULL";

        if (isset($data->diretor_vencimento) && $data->diretor_vencimento != "")
            $data->diretor_vencimento = $sistema->formataDataDeMascara($data->diretor_vencimento
            );
        else
            $data->diretor_vencimento = "NULL";

        if (isset($data->premio_vencimento) && $data->premio_vencimento != "")
            $data->premio_vencimento = $sistema->formataDataDeMascara($data->premio_vencimento
            );
        else
            $data->premio_vencimento = "NULL";

        if (isset($data->imobiliaria_vencimento) && $data->imobiliaria_vencimento != "")
            $data->imobiliaria_vencimento = $sistema->formataDataDeMascara($data->imobiliaria_vencimento
            );
        else
            $data->imobiliaria_vencimento = "NULL";

        foreach ($data as $key => $value) {
            $fields[] = $key;
            if ($value == "NULL")
                $values[] = "$value"
                ;
            else
                $values[] = "'$value'";
        }

        $sistema->DB_insert($table2, implode(',', $fields), implode(',', $values));

        unset($fields, $values);

        if (isset($_POST['n_parcelas'])) {
            for ($i = 0; $i < count($_POST['n_parcelas']); $i++) {
                if ($_POST['n_parcelas'][$i] != "") {
                    if ($_POST['data_primeiro_vencimento'][$i] != "")
                        $_POST['data_primeiro_vencimento'][$i] = "'{$sistema->formataDataDeMascara($_POST['data_primeiro_vencimento'][$i])}'"
                        ;
                    else
                        $_POST['data_primeiro_vencimento'][$i] = "NULL";

                    if ($_POST['valor_unitario_nesta_data'][$i] != "")
                        $_POST['valor_unitario_nesta_data'][$i] = "'{$sistema->formataMoedaBd($_POST['valor_unitario_nesta_data'][$i])}'"
                        ;
                    else
                        $_POST['valor_unitario_nesta_data'][$i] = "NULL";

                    if ($_POST['valor_total_serie_parcelas'][$i] != "")
                        $_POST['valor_total_serie_parcelas'][$i] = "'{$sistema->formataMoedaBd($_POST['valor_total_serie_parcelas'][$i])}'"
                        ;
                    else
                        $_POST['valor_total_serie_parcelas'][$i] = "NULL";

                    if ($_POST['atualizacao_monetaria'][$i] != "")
                        $_POST['atualizacao_monetaria'][$i] = "'{$sistema->formataMoedaBd($_POST['atualizacao_monetaria'][$i])}'"
                        ;
                    else
                        $_POST['atualizacao_monetaria'][$i] = "NULL";

                    $insertData[] = "('" . $idReserva . "', '" . $_POST['n_parcelas'][$i] . "', '" . $_POST['periodicidade'][$i] . "', {$_POST['data_primeiro_vencimento'][$i]}, {$_POST['valor_unitario_nesta_data'][$i]}, {$_POST['valor_total_serie_parcelas'][$i]}, {$_POST['atualizacao_monetaria'][$i]}, '" . $_POST['forma_pagamento'][$i] . "', '" . $_POST['observacoes'][$i] . "' ) ";
                }
            }
        }

        //ARRAY COM CAMPOS A SEREM ALTERADOS
        $fields = array('id_reserva', 'n_parcelas', 'periodicidade', 'data_primeiro_vencimento', 'valor_unitario_nesta_data', 'valor_total_serie_parcelas', 'atualizacao_monetaria', 'forma_pagamento', 'observacoes');

        //GRAVA DADOS
        if (isset($insertData))
            $query = $sistema->mysqli->query("INSERT INTO $table3(" . implode(',', $fields) . ") VALUES " . implode(',', $insertData));


        $resposta->type = "success";
        $resposta->time = 4000;
        $resposta->message = "Reserva enviada com sucesso, em breve entraremos em contato!";
        $sistema->inserirRelatorio("Reserva: [ " . $formulario->email . "] Id: [" . $idReserva . "]");
    } else {
        $resposta->type = "error";
        $resposta->message = "Aconteceu um erro no sistema, favor tente novamente mais tarde!";
    }

    echo json_encode($resposta);
}

$sistema->DB_disconnect
();

function validaFormulario($form) {

    $resposta = new stdClass();
    $resposta->return = true;

    //$sistema = new sistema();
    global $sistema, $main_table;

    if ($form->proponente == "") {
        $resposta->type = "validation";
        $resposta->message = "Preencha este campo corretamente";
        $resposta->field = "proponente";
        $resposta->return = false;
        return $resposta;
    } else if ($form->email == "") {
        $resposta->type = "validation";
        $resposta->message = "Preencha este campo corretamente";
        $resposta->field = "email";
        $resposta->return = false;
        return $resposta;
    } else if ($sistema->validaEmail($form->email) == 0) {
        $resposta->type = "validation";
        $resposta->message = "Preencha este campo com um E-mail válido";
        $resposta->field = "email";
        $resposta->return = false;
        return $resposta;
    } else if ($form->nacionalidade == "") {
        $resposta->type = "validation";
        $resposta->message = "Preencha este campo corretamente";
        $resposta->field = "nacionalidade";
        $resposta->return = false;
        return $resposta;
    } else if ($form->nascimento == "") {
        $resposta->type = "validation";
        $resposta->message = "Preencha este campo corretamente";
        $resposta->field = "nascimento";
        $resposta->return = false;
        return $resposta;
    } else if ($sistema->checkdate($form->nascimento) != 1) {
        $resposta->type = "validation";
        $resposta->message = "Data inválida";
        $resposta->field = "nascimento";
        $resposta->return = false;
        return $resposta;
    } else if ($form->rg == "") {
        $resposta->type = "validation";
        $resposta->message = "Preencha este campo corretamente";
        $resposta->field = "rg";
        $resposta->return = false;
        return $resposta;
    } else if ($form->orgao_emissor == "") {
        $resposta->type = "validation";
        $resposta->message = "Preencha este campo corretamente";
        $resposta->field = "orgao_emissor";
        $resposta->return = false;
        return $resposta;
    } else if ($form->cpf == "") {
        $resposta->type = "validation";
        $resposta->message = "Preencha este campo corretamente";
        $resposta->field = "cpf";
        $resposta->return = false;
        return $resposta;
    } else if ($form->estado_civil == "") {
        $resposta->type = "validation";
        $resposta->message = "Preencha este campo corretamente";
        $resposta->field = "estado_civil";
        $resposta->return = false;
        return $resposta;
    } else if ($form->regime_comunhao == "") {
        $resposta->type = "validation";
        $resposta->message = "Preencha este campo corretamente";
        $resposta->field = "regime_comunhao";
        $resposta->return = false;
        return $resposta;
    } else if ($form->cep == "") {
        $resposta->type = "validation";
        $resposta->message = "Preencha este campo corretamente";
        $resposta->field = "cep";
        $resposta->return = false;
        return $resposta;
    } else if ($form->endereco_residencial == "") {
        $resposta->type = "validation";
        $resposta->message = "Preencha este campo corretamente";
        $resposta->field = "endereco_residencial";
        $resposta->return = false;
        return $resposta;
    } else if ($form->telefone_residencial == "") {
        $resposta->type = "validation";
        $resposta->message = "Preencha este campo corretamente";
        $resposta->field = "telefone_residencial";
        $resposta->return = false;
        return $resposta;
    } else if ($form->celular == "") {
        $resposta->type = "validation";
        $resposta->message = "Preencha este campo corretamente";
        $resposta->field = "celular";
        $resposta->return = false;
        return $resposta;
    } else if ($form->profissao == "") {
        $resposta->type = "validation";
        $resposta->message = "Preencha este campo corretamente";
        $resposta->field = "profissao";
        $resposta->return = false;
        return $resposta;
    } else if ($form->cargo == "") {
        $resposta->type = "validation";
        $resposta->message = "Preencha este campo corretamente";
        $resposta->field = "cargo";
        $resposta->return = false;
        return $resposta;
    } else if ($form->empresa == "") {
        $resposta->type = "validation";
        $resposta->message = "Preencha este campo corretamente";
        $resposta->field = "empresa";
        $resposta->return = false;
        return $resposta;
    } else if ($form->renda == "") {
        $resposta->type = "validation";
        $resposta->message = "Preencha este campo corretamente";
        $resposta->field = "renda";
        $resposta->return = false;
        return $resposta;
    } else if ($form->adquirente != "Solteiro") {
        if ($form->adquirente_proponente == "") {
            $resposta->type = "validation";
            $resposta->message = "Preencha este campo corretamente";
            $resposta->field = "adquirente_proponente";
            $resposta->return = false;
            return $resposta;
        } else if ($form->adquirente_email == "") {
            $resposta->type = "validation";
            $resposta->message = "Preencha este campo corretamente";
            $resposta->field = "adquirente_email";
            $resposta->return = false;
            return $resposta;
        } else if ($sistema->validaEmail($form->adquirente_email) == 0) {
            $resposta->type = "validation";
            $resposta->message = "Preencha este campo com um E-mail válido";
            $resposta->field = "adquirente_email";
            $resposta->return = false;
            return $resposta;
        } else if ($form->adquirente_nacionalidade == "") {
            $resposta->type = "validation";
            $resposta->message = "Preencha este campo corretamente";
            $resposta->field = "adquirente_nacionalidade";
            $resposta->return = false;
            return $resposta;
        } else if ($form->adquirente_nascimento == "") {
            $resposta->type = "validation";
            $resposta->message = "Preencha este campo corretamente";
            $resposta->field = "adquirente_nascimento";
            $resposta->return = false;
            return $resposta;
        } else if ($sistema->checkdate($form->adquirente_nascimento) != 1) {
            $resposta->type = "validation";
            $resposta->message = "Data inválida";
            $resposta->field = "adquirente_nascimento";
            $resposta->return = false;
            return $resposta;
        } else if ($form->adquirente_rg == "") {
            $resposta->type = "validation";
            $resposta->message = "Preencha este campo corretamente";
            $resposta->field = "adquirente_rg";
            $resposta->return = false;
            return $resposta;
        } else if ($form->adquirente_orgao_emissor == "") {
            $resposta->type = "validation";
            $resposta->message = "Preencha este campo corretamente";
            $resposta->field = "adquirente_orgao_emissor";
            $resposta->return = false;
            return $resposta;
        } else if ($form->adquirente_cpf == "") {
            $resposta->type = "validation";
            $resposta->message = "Preencha este campo corretamente";
            $resposta->field = "adquirente_cpf";
            $resposta->return = false;
            return $resposta;
        } else if ($form->adquirente_estado_civil == "") {
            $resposta->type = "validation";
            $resposta->message = "Preencha este campo corretamente";
            $resposta->field = "adquirente_estado_civil";
            $resposta->return = false;
            return $resposta;
        } else if ($form->adquirente_regime_comunhao == "") {
            $resposta->type = "validation";
            $resposta->message = "Preencha este campo corretamente";
            $resposta->field = "adquirente_regime_comunhao";
            $resposta->return = false;
            return $resposta;
        } else if ($form->adquirente_cep == "") {
            $resposta->type = "validation";
            $resposta->message = "Preencha este campo corretamente";
            $resposta->field = "adquirente_cep";
            $resposta->return = false;
            return $resposta;
        } else if ($form->adquirente_endereco_residencial == "") {
            $resposta->type = "validation";
            $resposta->message = "Preencha este campo corretamente";
            $resposta->field = "adquirente_endereco_residencial";
            $resposta->return = false;
            return $resposta;
        } else if ($form->adquirente_telefone_residencial == "") {
            $resposta->type = "validation";
            $resposta->message = "Preencha este campo corretamente";
            $resposta->field = "adquirente_telefone_residencial";
            $resposta->return = false;
            return $resposta;
        } else if ($form->adquirente_celular == "") {
            $resposta->type = "validation";
            $resposta->message = "Preencha este campo corretamente";
            $resposta->field = "adquirente_celular";
            $resposta->return = false;
            return $resposta;
        } else if ($form->adquirente_profissao == "") {
            $resposta->type = "validation";
            $resposta->message = "Preencha este campo corretamente";
            $resposta->field = "adquirente_profissao";
            $resposta->return = false;
            return $resposta;
        } else if ($form->adquirente_cargo == "") {
            $resposta->type = "validation";
            $resposta->message = "Preencha este campo corretamente";
            $resposta->field = "adquirente_cargo";
            $resposta->return = false;
            return $resposta;
        } else if ($form->adquirente_empresa == "") {
            $resposta->type = "validation";
            $resposta->message = "Preencha este campo corretamente";
            $resposta->field = "adquirente_empresa";
            $resposta->return = false;
            return $resposta;
        } else if ($form->adquirente_renda == "") {
            $resposta->type = "validation";
            $resposta->message = "Preencha este campo corretamente";
            $resposta->field = "adquirente_renda";
            $resposta->return = false;
            return $resposta;
        } else {
            return validaFormularioContinuacao($form);
        }
    } else {
        return validaFormularioContinuacao($form);
    }
}

function validaFormularioContinuacao(
$form) {
    $resposta = new stdClass();
    $resposta->return = true;

    //$sistema = new sistema();
    global $sistema, $main_table;

    if ($form->banco == "") {
        $resposta->type = "validation";
        $resposta->message = "Preencha este campo corretamente";
        $resposta->field = "banco";
        $resposta->return = false;
        return $resposta;
    } else if ($form->agencia == "") {
        $resposta->type = "validation";
        $resposta->message = "Preencha este campo corretamente";
        $resposta->field = "agencia";
        $resposta->return = false;
        return $resposta;
    } else if ($form->conta == "") {
        $resposta->type = "validation";
        $resposta->message = "Preencha este campo corretamente";
        $resposta->field = "conta";
        $resposta->return = false;
        return $resposta;
    } else if ($form->usar_financiamento_bancario == "") {
        $resposta->type = "validation";
        $resposta->message = "Preencha este campo corretamente";
        $resposta->field = "usar_financiamento_bancario";
        $resposta->return = false;
        return $resposta;
    } else if ($form->usar_fgts_na_compra_imovel == "") {
        $resposta->type = "validation";
        $resposta->message = "Preencha este campo corretamente";
        $resposta->field = "usar_fgts_na_compra_imovel";
        $resposta->return = false;
        return $resposta;
    } else if ($form->incorporador_construtor == "") {
        $resposta->type = "validation";
        $resposta->message = "Preencha este campo corretamente";
        $resposta->field = "incorporador_construtor";
        $resposta->return = false;
        return $resposta;
    } else if ($form->und_autonoma == "") {
        $resposta->type = "validation";
        $resposta->message = "Preencha este campo corretamente";
        $resposta->field = "und_autonoma";
        $resposta->return = false;
        return $resposta;
    } else if ($form->torre == "") {
        $resposta->type = "validation";
        $resposta->message = "Preencha este campo corretamente";
        $resposta->field = "torre";
        $resposta->return = false;
        return $resposta;
    } else if ($form->boxs == "") {
        $resposta->type = "validation";
        $resposta->message = "Preencha este campo corretamente";
        $resposta->field = "boxs";
        $resposta->return = false;
        return $resposta;
    } else if ($form->escaninho == "") {
        $resposta->type = "validation";
        $resposta->message = "Preencha este campo corretamente";
        $resposta->field = "escaninho";
        $resposta->return = false;
        return $resposta;
    } else if ($form->corretor_vencimento == "") {
        $resposta->type = "validation";
        $resposta->message = "Preencha este campo corretamente";
        $resposta->field = "corretor_vencimento";
        $resposta->return = false;
        return $resposta;
    } else if ($form->corretor_honorarios == "") {
        $resposta->type = "validation";
        $resposta->message = "Preencha este campo corretamente";
        $resposta->field = "corretor_honorarios";
        $resposta->return = false;
        return $resposta;
    } else if ($form->corretor_valor_honorarios == "") {
        $resposta->type = "validation";
        $resposta->message = "Preencha este campo corretamente";
        $resposta->field = "corretor_valor_honorarios";
        $resposta->return = false;
        return $resposta;
    } else if ($form->corretor_forma_pagamento == "") {
        $resposta->type = "validation";
        $resposta->message = "Preencha este campo corretamente";
        $resposta->field = "corretor_forma_pagamento";
        $resposta->return = false;
        return $resposta;
    } else if ($form->corretor_observacoes == "") {
        $resposta->type = "validation";
        $resposta->message = "Preencha este campo corretamente";
        $resposta->field = "corretor_observacoes";
        $resposta->return = false;
        return $resposta;
    } else if ($form->gerente_vencimento == "") {
        $resposta->type = "validation";
        $resposta->message = "Preencha este campo corretamente";
        $resposta->field = "gerente_vencimento";
        $resposta->return = false;
        return $resposta;
    } else if ($form->gerente_honorarios == "") {
        $resposta->type = "validation";
        $resposta->message = "Preencha este campo corretamente";
        $resposta->field = "gerente_honorarios";
        $resposta->return = false;
        return $resposta;
    } else if ($form->gerente_valor_honorarios == "") {
        $resposta->type = "validation";
        $resposta->message = "Preencha este campo corretamente";
        $resposta->field = "gerente_valor_honorarios";
        $resposta->return = false;
        return $resposta;
    } else if ($form->gerente_forma_pagamento == "") {
        $resposta->type = "validation";
        $resposta->message = "Preencha este campo corretamente";
        $resposta->field = "gerente_forma_pagamento";
        $resposta->return = false;
        return $resposta;
    } else if ($form->gerente_observacoes == "") {
        $resposta->type = "validation";
        $resposta->message = "Preencha este campo corretamente";
        $resposta->field = "gerente_observacoes";
        $resposta->return = false;
        return $resposta;
    } else if ($form->diretor_vencimento == "") {
        $resposta->type = "validation";
        $resposta->message = "Preencha este campo corretamente";
        $resposta->field = "diretor_vencimento";
        $resposta->return = false;
        return $resposta;
    } else if ($form->diretor_honorarios == "") {
        $resposta->type = "validation";
        $resposta->message = "Preencha este campo corretamente";
        $resposta->field = "diretor_honorarios";
        $resposta->return = false;
        return $resposta;
    } else if ($form->diretor_valor_honorarios == "") {
        $resposta->type = "validation";
        $resposta->message = "Preencha este campo corretamente";
        $resposta->field = "diretor_valor_honorarios";
        $resposta->return = false;
        return $resposta;
    } else if ($form->diretor_forma_pagamento == "") {
        $resposta->type = "validation";
        $resposta->message = "Preencha este campo corretamente";
        $resposta->field = "diretor_forma_pagamento";
        $resposta->return = false;
        return $resposta;
    } else if ($form->diretor_observacoes == "") {
        $resposta->type = "validation";
        $resposta->message = "Preencha este campo corretamente";
        $resposta->field = "diretor_observacoes";
        $resposta->return = false;
        return $resposta;
    } else if ($form->premio_vencimento == "") {
        $resposta->type = "validation";
        $resposta->message = "Preencha este campo corretamente";
        $resposta->field = "premio_vencimento";
        $resposta->return = false;
        return $resposta;
    } else if ($form->premio_honorarios == "") {
        $resposta->type = "validation";
        $resposta->message = "Preencha este campo corretamente";
        $resposta->field = "premio_honorarios";
        $resposta->return = false;
        return $resposta;
    } else if ($form->premio_valor_honorarios == "") {
        $resposta->type = "validation";
        $resposta->message = "Preencha este campo corretamente";
        $resposta->field = "premio_valor_honorarios";
        $resposta->return = false;
        return $resposta;
    } else if ($form->premio_forma_pagamento == "") {
        $resposta->type = "validation";
        $resposta->message = "Preencha este campo corretamente";
        $resposta->field = "premio_forma_pagamento";
        $resposta->return = false;
        return $resposta;
    } else if ($form->premio_observacoes == "") {
        $resposta->type = "validation";
        $resposta->message = "Preencha este campo corretamente";
        $resposta->field = "premio_observacoes";
        $resposta->return = false;
        return $resposta;
    } else if ($form->imobiliaria_vencimento == "") {
        $resposta->type = "validation";
        $resposta->message = "Preencha este campo corretamente";
        $resposta->field = "imobiliaria_vencimento";
        $resposta->return = false;
        return $resposta;
    } else if ($form->imobiliaria_honorarios == "") {
        $resposta->type = "validation";
        $resposta->message = "Preencha este campo corretamente";
        $resposta->field = "imobiliaria_honorarios";
        $resposta->return = false;
        return $resposta;
    } else if ($form->imobiliaria_valor_honorarios == "") {
        $resposta->type = "validation";
        $resposta->message = "Preencha este campo corretamente";
        $resposta->field = "imobiliaria_valor_honorarios";
        $resposta->return = false;
        return $resposta;
    } else if ($form->imobiliaria_forma_pagamento == "") {
        $resposta->type = "validation";
        $resposta->message = "Preencha este campo corretamente";
        $resposta->field = "imobiliaria_forma_pagamento";
        $resposta->return = false;
        return $resposta;
    } else if ($form->imobiliaria_observacoes == "") {
        $resposta->type = "validation";
        $resposta->message = "Preencha este campo corretamente";
        $resposta->field = "imobiliaria_observacoes";
        $resposta->return = false;
        return $resposta;
    } else if ($form->total_servicos == "") {
        $resposta->type = "validation";
        $resposta->message = "Preencha este campo corretamente";
        $resposta->field = "total_servicos";
        $resposta->return = false;
        return $resposta;
    } else if ($form->corretor == "") {
        $resposta->type = "validation";
        $resposta->message = "Preencha este campo corretamente";
        $resposta->field = "corretor";
        $resposta->return = false;
        return $resposta;
    } else if ($form->gerente == "") {
        $resposta->type = "validation";
        $resposta->message = "Preencha este campo corretamente";
        $resposta->field = "gerente";
        $resposta->return = false;
        return $resposta;
    } else if ($form->local == "") {
        $resposta->type = "validation";
        $resposta->message = "Preencha este campo corretamente";
        $resposta->field = "local";
        $resposta->return = false;
        return $resposta;
    } else if ($form->data == "") {
        $resposta->type = "validation";
        $resposta->message = "Preencha este campo corretamente";
        $resposta->field = "data";
        $resposta->return = false;
        return $resposta;
    } else if (strtolower($form->codigo) != strtolower($_SESSION["securimage_code_value"]["default"])){
        $resposta->type = "validation";
        $resposta->message = "Código inválido";
        $resposta->field = "codigo";
        $resposta->return = false;
        return $resposta;
    } else {
        return $resposta;
    }
}

?>