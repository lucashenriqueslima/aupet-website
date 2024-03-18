<?php
error_reporting(0);
require_once '../../main/System/Core/Loader.php';
require '../../vendor/autoload.php';
require_once "../Classes/Sys.php";
use website\Classes\Sys;
$anexo = "";
$sistema = new Sys();
$sistema->DB_connect();
$anexo = array("file" => "", "field" => "");
$formulario = $sistema->formularioObjeto($_POST);
$options    = $sistema->formularioObjeto($formulario->options);
$validate   = $sistema->formularioObjeto($formulario->validate);
unset($formulario->options);
unset($formulario->validate);
if (!isset($_SESSION)) {
    session_start();
}
if (!isset($_SESSION["seo_session"])) {
    $_SESSION["seo_session"] = uniqid();
}
$validacao = validaFormulario($validate);

if (!$validacao->return) {
    echo json_encode($validacao);
} else {
    $resposta = new stdClass();
    $resposta->time = 2000;

    foreach ($formulario as $field => $data) {
        if ($field == 'checkbox1' || $field == 'checkbox2'){
            if ($formulario->$field == 'on') {
                $formulario->$field = 1;
            }
        }
    }

    try {
        if ($anexo["file"] != "") {
            $formulario->{$anexo['field']} = $anexo['file'];
        }
        $main_table = $options->table;
        $main_table2 = 'hbrd_cms_formularios';
        $df = $sistema->DB_fetch_array("SELECT * FROM {$main_table2} WHERE id = {$options->form}")->rows[0];
        $formulario_pai = [];

        if ($df['id_formulario_pai']) {
            $id_formulario_pai = $df['id_formulario_pai'];
            $formulario_pai = $sistema->DB_fetch_array("SELECT * FROM {$main_table2} WHERE id = {$id_formulario_pai}")->rows[0];
        }
        $campos = $sistema->DB_columns($options->table);
        $fields = $values = array();

        foreach ($campos as $campo) {
            if (array_key_exists($campo, (array)$_FILES) && (bool)$_FILES[$campo]["tmp_name"] && !$formulario->$campo) {
                $fields[] = $campo;
                $types = (bool)$validate->$campo["upload"] ? explode(",", $validate->$campo["upload"]) : ["png", "jpg","jpeg"];
                $upload = $sistema->uploadFile($campo, $types);
                if(!$upload->return) {
                    throw new Exception($upload->message);
                }
                $_FILES[$campo]['upload'] = $upload->file_uploaded;
                $values[] = "'{$_FILES[$campo]['upload']}'";
            } else if (array_key_exists($campo, (array)$formulario)) {
                $fields[] = $campo;
                if($campo == 'preco') {
                    $values[] = $sistema->formataMoedaBd($formulario->$campo);
                } else {
                    $values[] = ( array_key_exists("data", $validate->$campo) ) ? '"' . $sistema->datebr($formulario->$campo) . '"' : '"' . $formulario->$campo . '"';
                }
            }
        }
        $fields[] = "ip";
        $fields[] = "session";
        $values[] = '"'.$_SERVER['REMOTE_ADDR'].'"';
        $values[] = '"'.$_SESSION["seo_session"].'"';
        $insertedId = $sistema->DB_insert($options->table, implode(',', $fields), implode(',', $values))->insert_id;
        $resposta->type = "success";
        $resposta->message = 'Operação concluída com sucesso!';
        $resposta->conversion_script = $df['script'];

        if($df['id_formulario_pai'] != '') {
            $resposta->conversion_script .= $formulario_pai['script'];
        }
        $resposta->message = $df['mensagem'] ?: ($formulario_pai['mensagem'] ?: $resposta->message);

        if($df['mensagem'] != '') {
            $resposta->message = $df['mensagem'];
        }
        $confIntegracao = $sistema->DB_fetch_array("SELECT * FROM hbrd_cms_configuracoes_gerais")->rows[0];

        conversaoSeo();
        inserirListaEmails();
        dispararNotificacoes();
        integracaoHypnobox();
        integracaoRDStation();
        $sistema->mysqli->commit();
    } catch (\Exception $e) {
        if($sistema->mysqli) $sistema->mysqli->rollback();
        $resposta->type = 'error';
        $resposta->message = $e->getMessage() ? $e->getMessage() : 'Ocorreu um erro no processo, tente novamente em alguns minutos.';
    } finally {
        $sistema->mysqli->close();
    }
    echo json_encode($resposta);
}

function conversaoSeo(){
    global $sistema, $formulario, $options;

    if (isset($formulario->id_seo) AND ! empty($formulario->id_seo)) {
        if ($options->form == 6) {
            $sistema->DB_update("hbrd_cms_seo_acessos", "contato = 1 WHERE id_seo = $formulario->id_seo AND session = '{$_SESSION["seo_session"]}' ORDER BY id DESC LIMIT 1");
            $sistema->DB_update("hbrd_cms_seo_acessos_historico", "contato = 1 WHERE id_seo = $formulario->id_seo AND session = '{$_SESSION["seo_session"]}' ORDER BY id DESC LIMIT 1");
        } else if ($options->form == 9) {
            $sistema->DB_update("hbrd_cms_seo_acessos", "ligacao = 1 WHERE id_seo = $formulario->id_seo AND session = '{$_SESSION["seo_session"]}' ORDER BY id DESC LIMIT 1");
            $sistema->DB_update("hbrd_cms_seo_acessos_historico", "ligacao = 1 WHERE id_seo = $formulario->id_seo AND session = '{$_SESSION["seo_session"]}' ORDER BY id DESC LIMIT 1");
        } else if ($options->form == 7) {
            $sistema->DB_update("hbrd_cms_seo_acessos", "venda_terreno = 1 WHERE id_seo = $formulario->id_seo AND session = '{$_SESSION["seo_session"]}' ORDER BY id DESC LIMIT 1");
            $sistema->DB_update("hbrd_cms_seo_acessos_historico", "venda_terreno = 1 WHERE id_seo = $formulario->id_seo AND session = '{$_SESSION["seo_session"]}' ORDER BY id DESC LIMIT 1");
        } else if ($options->form == 8) {
            $sistema->DB_update("hbrd_cms_seo_acessos", "trabalhe_conosco = 1 WHERE id_seo = $formulario->id_seo AND session = '{$_SESSION["seo_session"]}' ORDER BY id DESC LIMIT 1");
            $sistema->DB_update("hbrd_cms_seo_acessos_historico", "trabalhe_conosco = 1 WHERE id_seo = $formulario->id_seo AND session = '{$_SESSION["seo_session"]}' ORDER BY id DESC LIMIT 1");
        }
        $sistema->mysqli->commit();
    }
}

function inserirListaEmails(){
    global $sistema, $formulario, $options, $formulario_pai;
    // CASO SETADO 'field_lista' ESTE CAMPO SERÁ PEGO PARA ARMAZENAR O CONTATO DO VISITANTE, NO CASO DE NÃO HAVER EMAIL NO FORMULÁRIO
    $email = isset($options->field_lista) ? $formulario->{$options->field_lista} : $formulario->email;
    $verifica = $sistema->DB_num_rows("SELECT * FROM hbrd_cms_email A INNER JOIN hbrd_cms_email_cms_listas B ON B.id_email = A.id AND A.email = '$email' AND B.id_lista = $options->list");
    if (!$verifica) {
        $verifica = $sistema->DB_fetch_array("SELECT * FROM hbrd_cms_email WHERE email = '$email'");
        if (!$verifica->num_rows) {
            $addEmail = $sistema->DB_insert('hbrd_cms_email', "nome,email", "'$formulario->nome','$email'");
            $idEmail = $addEmail->insert_id;
        } else {
            $idEmail = $verifica->rows[0]['id'];
        }
        $addListaHasEmail = $sistema->DB_insert('hbrd_cms_email_cms_listas', "id_lista,id_email", "$options->list,$idEmail");
    }
}

function dispararNotificacoes(){
    global $sistema, $formulario, $options, $validate, $formulario_pai, $confIntegracao;
    $queryFormPai = (bool)$formulario_pai ? "OR A.id = {$formulario_pai['id']}" : '';
    $emails = $sistema->DB_fetch_array("SELECT C.email, C.nome FROM hbrd_cms_formularios A INNER JOIN hbrd_cms_notificacoes B ON A.id = B.id_form INNER JOIN hbrd_main_usuarios C ON B.id_usuario = C.id WHERE A.id = 1 OR A.id = $options->form $queryFormPai GROUP BY B.id_usuario");

    if ($emails->num_rows) {
        $emailfrom = isset($formulario->email) ? $formulario->email : "";
        foreach ($emails->rows as $mail) {
            $to[] = array("email" => $mail['email'], "nome" => utf8_decode($mail['nome']));
        }
        $assunto = "Formulário de $options->form_name.";  //Assunto da mensagem de contato.

        if (isset($formulario->mensagem)) {
            $formulario->mensagem = trim(str_replace(array('\\r\\n', '\\n', '\\r'), '<br>', $formulario->mensagem));
        }
        $body = file_get_contents("../mailing_templates/form_scaffold.html");
        $body = str_replace("{FORMULARIO}", $options->form_name, $body);
        $body = str_replace("{EMPRESA}", $confIntegracao['nome'], $body);
        $body_fields = "";

        foreach ($validate as $field => $data) {
            if ($field == 'checkbox1' || $field == 'checkbox2'){
                if ($formulario->$field == 1) {
                    $formulario->$field = 'Sim';
                }
                elseif ($formulario->$field == 0) {
                    $formulario->$field = 'Não';
                }
            }
            if (!(bool)$formulario->$field || in_array($field, ['id_seo', 'termo'])) {
                continue;
            }
            $body_fields .= str_replace("{FIELD_VALUE}", $formulario->$field, str_replace("{FIELD_NAME}", formatarLabel($data['label'] == " " ? $field : $data['label']), file_get_contents("../mailing_templates/form_fields.html")));
        }
        $body = str_replace("{CONTENT}", $body_fields, $body);
        $files = [];
        foreach ($_FILES as $key => $row) {
            if(!$_FILES[$key]['tmp_name']) continue;
            $files[] = ['nome' => $key, 'arquivo' => $_FILES[$key]['upload']];
        }
        if((bool)count($files)) {
            $sistema->enviarEmail($to, $emailfrom, utf8_decode($assunto), utf8_decode($body), '', '', $files);
        } else {
            $sistema->enviarEmail($to, $emailfrom, utf8_decode($assunto), utf8_decode($body));
        }
    }
}

function validaFormulario($validade) {
    $resposta = new stdClass();
    $resposta->return = true;
    global $sistema, $formulario, $anexo, $options;

    foreach ($validade as $field => $data) {
        $custom = (isset($data["custom"])) ? $data["custom"] : "";
        if(!$data["required"]) {
            continue;
        }
        if (!(bool)$formulario->$field && !(bool)$_FILES[$field]["tmp_name"]) {
            $resposta->type = $data["type"];
            $resposta->message = isset($data["custom_msg"]) ? $data["custom_msg"] : "Preencha o campo $field";
            $resposta->field = $field;
            $resposta->return = false;
            return $resposta;
        } else if (isset($data["custom"]) AND ! $sistema->$custom($formulario->$field)) {
            $resposta->type = $data["type"];
            $resposta->message = isset($data["custom_msg"]) ? $data["custom_msg"] : "Preencha este campo corretamente";
            $resposta->field = $field;
            $resposta->return = false;
            return $resposta;
        } else if (isset($data["upload"])) {
            if (is_uploaded_file($_FILES[$field]["tmp_name"])) {
                $upload = $sistema->uploadFile($field, explode(",", $data["upload"]), '');
                if ($upload->return) {
                    $anexo["file"] = $upload->file_uploaded;
                    $anexo["field"] = $field;
                    $_FILES[$field]['upload'] = $upload->file_uploaded;
                }
            }
            if (isset($upload) && !$upload->return) {
                $resposta->type = "attention";
                $resposta->message = $upload->message;
                $resposta->field = $field;
                $resposta->return = false;
                return $resposta;
            }
        }
    }
    return $resposta;
}

function integracaoHypnobox(){
    global $sistema, $df, $formulario, $formulario_pai;
    if((isset($df['ws_hypnobox_id']) && $df['ws_hypnobox_id'] != '') || (isset($formulario_pai['ws_hypnobox_id']) && $formulario_pai['ws_hypnobox_id'] != '')) {
        $midia = "organico";
        $campanhas = $sistema->DB_fetch_array('SELECT * FROM hbrd_cms_seo_acessos_historico WHERE session = "'.$_SESSION["seo_session"].'"');
        if($campanhas->num_rows) {
            $campanhas = $campanhas->rows[0];
            $midia = '01,';
            /*
            if($campanhas['utm_campaign'] != '') $midia .= ',utm_campaign='.$campanhas['utm_campaign'];
            if($campanhas['utm_source'] != '') $midia .= ',utm_source='.$campanhas['utm_source'];
            if($campanhas['utm_medium'] != '') $midia .= ',utm_medium='.$campanhas['utm_medium'];
            if($campanhas['utm_term'] != '') $midia .= ',utm_term='.$campanhas['utm_term'];
            if($campanhas['utm_content'] != '') $midia .= ',utm_content='.$campanhas['utm_content'];
            */
            if($campanhas['utm_campaign'] != '') {
                $midia .= $campanhas['utm_campaign'];
            }
            if($campanhas['utm_source'] != '') {
                $midia .= '>'.$campanhas['utm_source'];
            }
            $midia = str_replace('01,','',$midia);
        }
        $data = new \StdClass();
        $data->nome = $formulario->nome;
        $data->email = $formulario->email;
        if(isset($formulario->assunto)) {
            $data->assunto = $formulario->assunto;
        }
        if(isset($formulario->mensagem)) {
            $data->mensagem = $formulario->mensagem;
        }
        if(isset($formulario->telefone) || isset($formulario->celular)) {
            if(isset($formulario->celular)){
                $main_phone = $formulario->celular;
            }else{
                $main_phone = $formulario->telefone;
            }
            $ddd = str_split($main_phone);
            $ddd = $ddd[1].$ddd[2];
            $fone = explode(') ',$main_phone);
            $fone = $fone[1];
            $data->ddd_residencial = $ddd;
            $data->tel_residencial = $fone;
        }
        if(isset($formulario->renda_mensal) and $formulario->renda_mensal!=""){
            $data->mensagem .= "<br />Renda Mensal: ".$formulario->renda_mensal;
        }
        if(isset($formulario->id_motivo) and $formulario->id_motivo!="" and $formulario->motivo!=""){
            $data->mensagem .= "<br />Motivo da Compra: ".$formulario->motivo;
        }
        $data->midia = $midia;
        if($df['ws_hypnobox_id'] != ''){
            $data->id_produto = $df['ws_hypnobox_id'];
            if($df['id_formulario_pai'] == 29){
                $data->assunto = "Formulário ".$df['nome'];
            }
            hypnoboxCall($data);
        }
        if($df['ws_hypnobox_id'] == '' && $formulario_pai['ws_hypnobox_id'] != ''){
            $data->id_produto = $formulario_pai['ws_hypnobox_id'];
            if($formulario_pai['id_formulario_pai'] == 29){
                $data->assunto = "Formulário ".$df['nome'];
            }
            $data->assunto = "Formulário ".$formulario_pai['nome'];
            hypnoboxCall($data);
        }
    }
}

function hypnoboxCall($data){
    global $sistema, $confIntegracao;
    $ch = curl_init();
    $send_post = 'id_produto='.$data->id_produto.'&nome='.$data->nome.'&email='.$data->email.'&midia='.$data->midia;
    if(isset($data->ddd_residencial)) {
        $send_post .= '&ddd_residencial='.$data->ddd_residencial.'&tel_residencial='.$data->tel_residencial;
    }
    if(isset($data->assunto)) {
        $send_post .= '&assunto='.$data->assunto;
    }
    if(isset($data->mensagem)) {
        $send_post .= '&mensagem='.$data->mensagem;
    }
    curl_setopt($ch, CURLOPT_URL, $confIntegracao['link_hypnobox']);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $send_post);
    $response = curl_exec($ch);
    $statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = '';
    if (curl_errno($ch)) {
        $error = curl_error($ch);
    }
    $sistema->inserirRelatorio('{hypnobox_send: {"data":'.$send_post.'}}');
    $sistema->inserirRelatorio('{hypnobox_result: {"response":'.$response.',"statusCode":"'.$statusCode.'","error": "'.$error.'"}}');
    curl_close($ch);
}

function integracaoRDStation(){
    global $sistema, $df, $formulario, $formulario_pai;
    if((isset($df['ws_rdstation_token']) && $df['ws_rdstation_token'] != '') || (isset($formulario_pai['ws_rdstation_token']) && $formulario_pai['ws_rdstation_token'] != '')){
        $midia = "organico";
        $campanhas = $sistema->DB_fetch_array('SELECT * FROM hbrd_cms_seo_acessos_historico WHERE session = "'.$_SESSION["seo_session"].'"');
        if($campanhas->num_rows){
            $campanhas = $campanhas->rows[0];
            /*
            if($campanhas['utm_campaign'] != '') $midia .= ',utm_campaign='.$campanhas['utm_campaign'];
            if($campanhas['utm_source'] != '') $midia .= ',utm_source='.$campanhas['utm_source'];
            if($campanhas['utm_medium'] != '') $midia .= ',utm_medium='.$campanhas['utm_medium'];
            if($campanhas['utm_term'] != '') $midia .= ',utm_term='.$campanhas['utm_term'];
            if($campanhas['utm_content'] != '') $midia .= ',utm_content='.$campanhas['utm_content'];
            */
            if ($campanhas['utm_source']) {
                $midia .= $campanhas['utm_source'];
            }
            if ($campanhas['utm_campaign']) {
                $midia .= $campanhas['utm_source'] ? '>' . $campanhas['utm_campaign'] : $campanhas['utm_campaign'];
            }
        }
        $data = new \StdClass();
        $data->nome = $formulario->nome;
        $data->email = $formulario->email;
        if(isset($formulario->assunto)) {
            $data->assunto = $formulario->assunto;
        }
        if(isset($formulario->mensagem)) {
            $data->mensagem = $formulario->mensagem;
        }
        if(isset($formulario->telefone) || isset($formulario->celular)) {
            if(isset($formulario->celular)) {
                $main_phone = $formulario->celular;
            } else {
                $main_phone = $formulario->telefone;
            }
            $ddd = str_split($main_phone);
            $ddd = $ddd[1].$ddd[2];
            $fone = explode(') ',$main_phone);
            $fone = $fone[1];
            $data->ddd_residencial = $ddd;
            $data->tel_residencial = $fone;
        }
        if(isset($formulario->renda_mensal) and $formulario->renda_mensal != "") {
            $data->mensagem .= "<br />Renda Mensal: ".$formulario->renda_mensal;
        }
        if(isset($formulario->id_motivo) and $formulario->id_motivo!="" and $formulario->motivo!=""){
            $data->mensagem .= "<br />Motivo da Compra: ".$formulario->motivo;
        }
        $data->identificador = $df['ws_rdstation_token'] ?: $formulario_pai['ws_rdstation_token'];
        $data->midia = $midia;
        if ($df['id_formulario_pai'] == 29) {
            $data->assunto = $df['nome'];
        }
        RDStationCall($data);
    }
}

function RDStationCall($data){
    global $sistema, $confIntegracao;
    $arrData = [
        'token_rdstation' => $confIntegracao['id_rdstation'], // seu token
        'identificador' => $data->identificador, // identificador
        'midia' => $data->midia,
        'nome' => $data->nome,
        'email' => $data->email,
        'telefone' => '('.$data->ddd_residencial.') '.$data->tel_residencial,
    ];
    if ($data->assunto) {
        $arrData['assunto'] = $data->assunto;
    }
    if ($data->mensagem) {
        $arrData['mensagem'] = $data->mensagem;
    }
    if (isset($_POST["id_empreendimento"])) {
        $empreendimento = $sistema->DB_fetch_array("SELECT * FROM hbrd_cms_empreendimentos where id = {$_POST["id_empreendimento"]}")->rows[0];
        $arrData['empreendimento'] = $empreendimento['titulo'];
    }
    if(!empty($_COOKIE["rdtrk"])) {
        $arrData["client_id"] = json_decode($_COOKIE["rdtrk"])->{'id'};
    }
    if(!empty($_COOKIE["__trf_src"])) {
        $arrData["traffic_source"] = $_COOKIE["__trf_src"];
    }
    $data_string = json_encode($arrData);
    $ch = curl_init($confIntegracao['link_rdstation']);
    // $send_post = 'id_produto='.$data->id_produto.'&nome='.$data->nome.'&email='.$data->email.'&identificador='.$data->midia;
    // if(isset($data->ddd_residencial)) $send_post .= '&telefone=('.$data->ddd_residencial.') '.$data->tel_residencial;
    // if(isset($data->assunto)) $send_post .= '&assunto='.$data->assunto;
    // if(isset($data->mensagem)) $send_post .= '&mensagem='.$data->mensagem;
    //curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json',
            'Content-Length: ' . strlen($data_string))
    );
    $response = curl_exec($ch);
    $statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = '';
    if (curl_errno($ch)) {
        $error = curl_error($ch);
    }
    $sistema->inserirRelatorio('{rdstation_send: {"data":'.$data_string.'}}');
    $sistema->inserirRelatorio('{rdstation_result: {"response":'.$response.',"statusCode":"'.$statusCode.'","error": "'.$error.'"}}');
    curl_close($ch);
}

function formatarLabel($label) {
    $substituicoes = array('Seu ', ' *', 'Deixe uma ', 'Deixe um ');
    $label = str_replace($substituicoes,'', $label);
    $label = ucwords($label);
    return $label;
}