<?php

namespace System\Base;

require_once __DIR__ . '/General.php';

use main\classes\{Util};
use PHPMailer;

class BadRequest extends \Exception {
}
class SistemaBase extends \GeneralBase {
    function __construct() {
        $this->table_pessoa = "hbrd_app_pessoa";
        parent::__construct();
        $this->module_link = $this->getPath();
        $this->retorno = $this->getPath();
        $config = [
            'endpoint' => 'https://nyc3.digitaloceanspaces.com',
            'region' => 'nyc3',
            'version' =>  'latest',
            'credentials' => [
                'key' => $_ENV['SPACES_ACCESS_KEY'],
                'secret' => $_ENV['SPACES_ACCESS_SECRET']
            ]
        ];
        $sdk = new \Aws\Sdk($config);
        $this->s3Client = $sdk->createS3();
    }
    protected function putObjectAws($file, $table = null, $key = null) {
        if (!$file) throw new BadRequest("Arquivo não encontrado");
        if ((bool)$key) $this->deleteObjectAWS($key, $table);
        //ver o tipo
        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $type = $finfo->buffer($file);
        $ext = explode('/', $type)[1];
        $hash = Util::createHash(22);
        $keyName = strtolower('aupet/' . $hash . '.' . $ext);
        $this->s3Client->putObject([
            'ACL' => 'public-read',
            'ContentType' => $type,
            'Bucket' => 'aupetheinsten',
            'Key' => $keyName,
            'Body' => $file,
        ]);
        return 'https://aupetheinsten.nyc3.digitaloceanspaces.com/' . $keyName;
    }
    protected function deleteObjectAWS($key, $table = null) {
        $this->s3Client->deleteObject(['Bucket' => 'aupetheinsten', 'Key' => $key]);
    }
    public function setAction() {
        try {
            $this->DB_connect();
            if ((bool)$this->model) $this->model->mysqli = &$this->mysqli;
            $this->getAllPermissions();
            $action = $this->getParameter(__classe__);
            $action = explode("?", $action);
            $newAction = $action[0] . "Action";
            $this->getPOST();
            if (method_exists($this, $newAction)) $echo = $this->$newAction();
            else if ($newAction == "Action" && method_exists($this, 'indexAction')) $echo = $this->indexAction();
            else header("HTTP/1.0 404 Not Found");
            if ($echo) echo $echo;
            $this->mysqli->commit();
        } catch (BadRequest $e) {
            header("HTTP/1.1 400 Bad Request");
            $this->errorHander($e, true);
        } catch (\Throwable $e) {
            header("HTTP/1.0 500 Internal Server Error");
            $this->sentryExce($e);
            $this->errorHander($e);
        } finally {
            $this->mysqli->close();
        }
    }
    protected function getPOST() {
        if (!$_POST) {
            $json = file_get_contents('php://input');
            $_POST = json_decode($json, true);
            if ($json && json_last_error() != JSON_ERROR_NONE) {
                // $this->sentryClient->extra_context(['json' => $json]);
                throw new BadRequest(json_last_error_msg() . '. Tente novamente!');
            }
        }
    }
    public function errorHander($e, $showMessagem = false) {
        if ($this->mysqli) $this->mysqli->rollback();
        if ($_SERVER["HTTP_SEC_FETCH_MODE"] == 'navigate') {
            header('Content-type: text/html');
            $this->showLog($e);
            // if($_ENV['LOCAL_ENV'] == 'development') $this->showLog($e);
            // else require_once $this->main_layouts."500.phtml";
        } else {
            $resposta = new \stdClass();
            $resposta->type = "error";
            $msg = $e->getMessage();
            if (strpos($msg, "email_UNIQUE") !== false) {
                header("HTTP/1.1 400 Bad Request");
                $resposta->message = 'Este email ja esta sendo usado';
            } else $resposta->message = $e->getMessage();
            // if($_ENV['LOCAL_ENV'] == 'development') $resposta->message = $e->getMessage();
            // else $resposta->message = $showMessagem ? $e->getMessage() : 'Houve um problema com sua requisição. Tente novamente!';
            header('Content-type: application/json');
            echo json_encode($resposta);
        }
    }
    protected function getService() {
        return __service__;
    }
    protected function getModule() {
        return __classe__;
    }
    protected function getPath() {
        return __service__ . '/' . __classe__;
    }
    protected function handlerHttp($tipo, $url, $options) {
        $client = new \GuzzleHttp\Client(['verify' => false]);
        try {
            $response = $client->request($tipo, $url, $options);
            $body = (string)$response->getBody();
            $code = (string)$response->getStatusCode();
            if ($this->SGAerros($body) || $code != '200') $this->handlerHttpError($response);
            return $body;
        } catch (\GuzzleHttp\Exception\ClientException $e) {
            $this->handlerHttpError($e);
        } catch (\GuzzleHttp\Exception\ServerException $e) {
            $this->handlerHttpError($e);
        } catch (\GuzzleHttp\Exception\BadResponseException $e) {
            $this->handlerHttpError($e);
        } catch (\GuzzleHttp\Exception\ConnectException $e) {
            $this->handlerHttpError($e);
        }
    }
    private function SGAerros($body) {
        $erros = ['Erro ao executar: fachMode', 'Error:', 'Verique seu token de acesso', 'codigo_erro'];
        foreach ($erros as $erro) {
            if (strpos(strtolower($body), strtolower($erro)) !== false) return true;
        }
        $this->sentryClient->extra_context(['SGA_context' => $body]);
        return false;
    }
    private function handlerHttpError($e) {
        // $this->sentryExce($e);
        $response = $e->getResponse();
        $responseBodyAsString = json_decode($response->getBody()->getContents());
        if (is_string($responseBodyAsString)) $res = $responseBodyAsString;
        else if ($responseBodyAsString["message"]) $res = $responseBodyAsString["message"];
        else if ($responseBodyAsString["error_description"]) $res = $responseBodyAsString["error_description"];
        else if (is_string($responseBodyAsString["error"])) $res = $responseBodyAsString["error"];
        else if (is_string($responseBodyAsString["error"][0])) $res = $responseBodyAsString["error"][0];
        else if (is_string($responseBodyAsString["error"]["error"][0])) $res = $responseBodyAsString["error"]["error"][0];
        throw new BadRequest($res);
    }
    protected function sentryExce($e) {
        if (strpos($_SERVER["SERVER_NAME"], 'aupetheinsten') !== false) {
            $this->sentryClient->extra_context(array('POST' => $_POST, 'GET' => $_GET, 'REQUEST' => $_REQUEST));
            $this->sentryClient->captureException($e);
        }
    }
    public function trataArray($array, $option = "echo") {
        if (is_array($array)) {
            $new_array = array();
            foreach ($array as $key => $value) {
                $new_array[$key] = (string)$value;
            }
        } else if (is_object($array)) {
            $new_array = new \stdClass();
            foreach ($array as $key => $value) {
                $new_array->$key = (string)$value;
            }
        }
        return $new_array;
    }
    protected function getheaderLeadzapp() {
        $integracao = $this->DB_fetch_array("SELECT leadszapp_token, leadszapp_status FROM hbrd_adm_integration")->rows[0];
        if (!$integracao['leadszapp_token']) throw new BadRequest("Configuração do WhastApp incorreta, para continuar desative a integração ou insira um token.");
        return ['headers' => ['Content-Type' => 'application/json', 'X-API-KEY' => $integracao['leadszapp_token']]];
    }
    public function sendLeadszApp($pessoa, $text) {
        $integracao = $this->DB_fetch_array("SELECT * FROM hbrd_adm_integration")->rows[0];
        if ($integracao['leadszapp_status'] != '1' || !(bool)$pessoa || !(bool)$text) return;
        $options = $this->getheaderLeadzapp();
        $pessoa = (array)$pessoa;
        $options['body'] = Util::json_encode(["bot" => $integracao['leadszapp_bot'], "contact" => ["first_name" => $pessoa['nome'], "last_name" => "", "mobile_phone" => "55" . preg_replace("/[^0-9]/", '', $pessoa['telefone'])], "messages" => [$text]]);
        // if ($_ENV['LOCAL_ENV'] == 'development') return;
        try {
            $this->handlerHttp('POST', "https://hub.leadszapp.com/api/v1/whatsapp/send", $options);
        } catch (\Throwable $e) {
            throw new BadRequest("Houve um problema com a integração com o WhatsApp");
        }
    }
    function enviarEmail($to = "", $from = "", $assunto = "", $mensagem = "", $bcc = "", $arquivo = "") {
        if ($_ENV['LOCAL_ENV'] == 'development') {
            $to = [["email" => $_ENV['TESTE_EMAIL'], "nome" => "teste email ileva"]];
        }
        $query = $this->DB_fetch_array("SELECT * from hbrd_main_smtp");
        $smtp = $query->rows[0];
        $mail = new PHPMailer();
        if ($smtp['autenticado']) {
            $mail->isSMTP();
            $mail->Host = $smtp['email_host'];
            $mail->Port = $smtp['email_port'];
            $mail->SMTPAuth = true;
            $mail->Username = $smtp['email_user'];
            $mail->Password = $smtp['email_password'];
            //$mail->SMTPSecure = 'ssl';
        }
        if (is_array($arquivo)) {
            $nome_arquivo = $arquivo['nome'];
            $arquivo = $arquivo['arquivo'];
            $mail->AddAttachment($this->upload_folder . $arquivo, $nome_arquivo);
        }
        if ($from == "") {
            $mail->addReplyTo($smtp['email_padrao'], utf8_decode($this->_empresa['nome']));
        } else {
            $mail->addReplyTo($from['email'], utf8_decode($from['nome']));
        }
        $mail->setFrom($smtp['email_padrao'], utf8_decode($from['nome']));
        if ($bcc != "") {
            if (is_array($bcc)) {

                foreach ($bcc as $email) {
                    $email['nome'] = utf8_decode($email['nome']);
                    $mail->addBCC($email['email'], utf8_decode($from['nome']));
                }
            }
        }
        if (is_array($to)) {
            foreach ($to as $email) {
                //hack para replyTo funcionar quando o remetente for igual ao destinatário
                if ($email['email'] == $smtp['email_padrao']) {
                    if ($from != "")
                        $mail->setFrom($from);
                }
                //------------------------------------------------------------------------
                $email['nome'] = utf8_decode($email['nome']);
                $mail->AddAddress($email['email'], utf8_decode($this->_empresa['nome']));
            }
            $mail->Subject = $assunto;
            //Read an HTML message body from an external file, convert referenced images to embedded,
            //convert HTML into a basic plain-text alternative body
            $mail->msgHTML($mensagem, dirname(__FILE__));
            return $mail->send();
        } else {
            return 0;
        }
    }

    protected function enviarNotificacao($id_pessoa, $mensagem, $titulo = '', $url = '') {
        if (!(bool)$id_pessoa) return;
        $registration_ids = $this->DB_fetch_array("SELECT token FROM hbrd_app_pessoa_pushtoken WHERE id_pessoa = $id_pessoa")->rows;
        $pessoa = $this->DB_fetch_array("SELECT * FROM hbrd_app_pessoa WHERE id = $id_pessoa")->rows[0];
        if (!(bool)$registration_ids) return;
        $registration_ids = array_map(function ($x) {
            return $x['token'];
        }, $registration_ids);
        $options = ['headers' => ['Content-Type' => 'application/json', 'Authorization' => "key=AAAABIetYCo:APA91bF737bAtdDU21dQwllzr6Cn6DSM-P17n7GdsbJQ27QgBK9bi48IOdCncC7nW4p36JIOSBYWgOzBzoz4ZE-R2XID-KCDagJx4EmItd24onMCPW127t8qmiPnYNk8TF3uSayx4dVq"]];
        $titulo = ((bool)$titulo) ? $titulo : "Olá {$pessoa['nome']},";
        $options['body'] = Util::json_encode([
            "data" => ["title" => $titulo, "body" => $mensagem, "url" => $url],
            "registration_ids" => $registration_ids
        ]);
        // if ($_ENV['LOCAL_ENV'] == 'development') return;
        $this->handlerHttp('POST', 'https://fcm.googleapis.com/fcm/send', $options);
    }
    public function allowedServiceLogin($service) {
        if (isset($_SESSION['admin_id'])) {
            //$perm = $this->DB_fetch_array("SELECT A.*, IF(C.id_servico IS NULL, 0 , 1) permitido FROM hbrd_main_servicos A LEFT JOIN hbrd_main_usuarios_main_servicos C ON A.id = C.id_servico  AND C.id_usuario = '{$_SESSION['admin_id']}' WHERE A.identificador = '$service' GROUP BY A.id ORDER BY A.nome")->rows[0]['permitido'];
            $perm = ((bool)$_SESSION['admin_perm_serv_ind']) ?
                $this->DB_fetch_array("SELECT A.*, IF(C.id_servico IS NULL, 0 , 1) permitido FROM hbrd_main_servicos A LEFT JOIN hbrd_main_usuarios_main_servicos C ON A.id = C.id_servico  AND C.id_usuario = '{$_SESSION['admin_id']}' WHERE A.identificador = '$service' GROUP BY A.id ORDER BY A.nome")->rows[0]['permitido'] :

                $this->DB_fetch_array("SELECT A.*, IF(B.id_servico IS NULL, 0 , 1) permitido FROM hbrd_main_servicos A LEFT JOIN hbrd_main_grupos_main_servicos B ON A.id = B.id_servico AND B.id_grupo = '{$_SESSION['admin_grupo']}' WHERE A.identificador = '$service' GROUP BY A.id ORDER BY A.nome")->rows[0]['permitido'];

            return $perm ? true : false;
            /* if ($this->DB_fetch_array("SELECT * FROM hbrd_main_servicos A INNER JOIN hbrd_main_usuarios_main_servicos B ON B.id_servico = A.id AND B.id_usuario = {$_SESSION['admin_id']} AND A.identificador = '$service'")->num_rows) {
              return true;
              } else {
              return false;
              } */
        } else {
            return false;
        }
    }
    public function getRoute() {
        if (!isset($_SESSION['admin_logado']) && strpos($_SERVER["REQUEST_URI"], '/login') === false)
            return header("Location: " . $this->system_path . "main/login" . ($_SERVER["REQUEST_URI"] == '/' ? "" : "?return=" . $_SERVER["REQUEST_URI"]));
        $protocol = ($_SERVER['HTTPS'] == 'on' || $_SERVER["HTTP_X_SCHEME"] == "https") ?  "https://" : "http://";
        $url = $protocol . $_SERVER['HTTP_HOST'] . $_SERVER["REQUEST_URI"];
        $resposta = "";
        $new_request = "";
        $separador = "";
        $base = explode("/", $this->system_path);
        $request = explode("/", explode('?', $url)[0]);
        foreach ($request as $request) {
            if (!in_array($request, $base)) {
                $new_request .= $separador . $request;
                $separador = "/";
            }
        }
        $caminhos = explode("/", $new_request);
        $module = explode("?", $caminhos[0])[0];
        $caminho = "environment/$module";
        if ($module === '') {
            $resposta = "environment/main/module/dashboard/Controller/Dashboard.php";
        } else {
            $caminho = "environment/$module/module/" . str_replace('-', '', $caminhos[1]) . "/Controller/" . str_replace('-', '', $caminhos[1]) . ".php";
            $resposta = $this->file_exists_ci($caminho);
            if (!$resposta) {
                header("HTTP/1.0 404 Not Found");
                header('Content-type: text/html');
                require_once $this->main_layouts . "404.phtml";
                exit;
            } else if ($caminhos[1] != 'login' && !isset($_SESSION['admin_id'])) {
                header("Location: /sistema/main/login");
                exit;
            }
        }
        return $resposta;
    }
}
