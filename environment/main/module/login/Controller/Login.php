<?php
use System\Base\{SistemaBase, BadRequest};
use app\classes\{Crypto, Util};
class __classe__ extends SistemaBase {
    function __construct() {
        parent::__construct();
        $this->table = "hbrd_main_usuarios";
    }
    public function indexAction() {
        if (isset($_SESSION['admin_logado']) && $_SESSION['admin_logado'] === true) {
            header("Location: $this->system_path");
            exit();
        } else if (isset($_COOKIE['c_admin_logado']) && isset($_COOKIE['c_admin_id']) && $_COOKIE['c_admin_id'] != "") {
            $verificar = $this->DB_fetch_array("SELECT * FROM $this->table WHERE id = {$_COOKIE['c_admin_id']} AND id_grupo = {$_COOKIE['c_admin_grupo']} AND session = '{$_COOKIE['c_admin_login_session']}' AND session != ''");
            if ($verificar->num_rows) {
                $_SESSION['admin_logado'] = true;
                $_SESSION['admin_nome'] = $_COOKIE['c_admin_nome'];
                $_SESSION['admin_foto'] = $_COOKIE['c_admin_foto'];
                $_SESSION['admin_id'] = $_COOKIE['c_admin_id'];
                $_SESSION['admin_nivel'] = $_COOKIE['c_admin_nivel'];
                $_SESSION['admin_grupo'] = $_COOKIE['c_admin_grupo'];
                $_SESSION['admin_service'] = explode(",", $_COOKIE['c_admin_service']);
                $_SESSION['ws-folder'] = $_COOKIE['c_ws_folder'];
                $_SESSION['ws-cliente'] = $_COOKIE['c_ws_cliente_id'];
                $this->inserirRelatorio("[Login]", __service__);
                if (!isset($_SESSION["login_session"])) {
                        $_SESSION["login_session"] = uniqid();
                }
                header("Location: //$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]");
                exit();
            } else {
                $this->inserirRelatorio("Tentativa frustrada de logar no sistema, ip: " . $_SERVER['REMOTE_ADDR'], __service__);
                $this->renderView($this->getService(), $this->getModule(), "index");
                exit();
            }
            exit();
        } else {
            $this->renderView($this->getService(), $this->getModule(), "index");
        }
    }
    public function entrarAction() {
        $formulario = $this->formularioObjeto($_POST);
        $validacao = $this->validaFormulario($formulario);
        if (!$validacao->return) {
            echo json_encode($validacao);
        } else {
            $resposta = new \stdClass();
            if ($formulario->recuperar_senha == 1) {
                $dados = $this->DB_fetch_array("SELECT A.* FROM $this->table A WHERE A.usuario='{$formulario->username}' OR A.email = '{$formulario->username}'");
                if ($dados->num_rows) {
                    $code = $this->criptPass($dados->rows[0]['email'] . time());
                    $url = $this->system_path . "main/login/?code=$code";
                    $body = "Seu nome de usuário é: <b>{$dados->rows[0]['usuario']}</b>.<p>Para gerar uma nova senha, clique <a href='$url'>aqui</a>!<br><br>" . $this->_empresa['nome'];
                    if ($this->enviarEmail(array(array("nome" => $dados->rows[0]['nome'], "email" => $dados->rows[0]['email'])), "", utf8_decode("Recuperação de Senha - " . $this->_empresa['nome'] . ""), utf8_decode($body))) {
                        $resposta->time = 5000;
                        $resposta->action = "";
                        $resposta->type = "success";
                        $resposta->message = "{$dados->rows[0]['nome']}, lhe enviamos um e-mail com instruções para gerar uma nova senha!";
                        $this->DB_update($this->table, ["code" => $code]," WHERE id=" . $dados->rows[0]['id']);
                    } else {
                        $resposta->type = "error";
                        $resposta->message = "Ocorreu um erro, tente novamente mais tarde!";
                    }
                } else {
                    $resposta->type = "error";
                    $resposta->message = "Usuário inexistente!";
                }
                echo json_encode($resposta);
                exit;
            }
            if ($formulario->recuperar_senha == 2) {
                $verifica = $this->DB_fetch_array("SELECT * FROM $this->table WHERE code = '{$_POST['code']}'");
                if ($verifica->num_rows) {
                    $this->DB_update($this->table, ["senha" =>  $this->embaralhar($formulario->password) ] ," WHERE code = '{$_POST['code']}'");
                    $resposta->type = "success";
                    $resposta->action = "main/login";
                    $resposta->message = "Senha alterada com sucesso!";
                } else {
                    $resposta->type = "error";
                    $resposta->message = "Código inválido!";
                }
                echo json_encode($resposta);
                exit;
            }
            $dados = $this->DB_fetch_array("SELECT A.* FROM $this->table A WHERE A.usuario='{$formulario->username}' OR A.email = '{$formulario->username}'");
            if ($dados->num_rows) {
                if ($this->desembaralhar($dados->rows[0]['senha']) == $formulario->password) {
                    if ($dados->rows[0]["stats"] == 1) {
                        $_SESSION['ws-folder'] = 24;
                        $_SESSION['ws-cliente-id'] = 24;
                        $_SESSION['admin_logado'] = true;
                        $_SESSION['admin_nome'] = $dados->rows[0]["nome"];
                        $_SESSION['admin_foto'] = $dados->rows[0]["avatar"];
                        $_SESSION['admin_id'] = $dados->rows[0]["id"];
                        $_SESSION['admin_nivel'] = $dados->rows[0]["id_nivel"];
                        $_SESSION['admin_grupo'] = $dados->rows[0]["id_grupo"];
                        $_SESSION['admin_service'] = array();
                        $resposta->type = "success";
                        $resposta->message = "Seja bem vindo " . $dados->rows[0]["nome"];
                        $resposta->username = $dados->rows[0]["nome"];
                        $resposta->logintime = date("H:i");
                        $resposta->loginip = $_SERVER['REMOTE_ADDR'];
                        $resposta->action = "environment";
                        $this->inserirRelatorio("[Login]", __service__);
                        if (!isset($_SESSION["login_session"])) {
                            $_SESSION["login_session"] = uniqid();
                        }
                    } else {
                        $_SESSION['admin_logado'] = false;
                        $resposta->type = "attention";
                        $resposta->message = "Este usuário encontra-se bloqueado";
                        $this->inserirRelatorio("Usuário bloqueado [" . $formulario->username . "] tentou logar no sistema", __service__);
                    }
                } else {
                    unset($_SESSION['admin_logado']);
                    $resposta->type = "error";
                    $resposta->message = "Usuário e senha incorretos!";
                    $this->inserirRelatorio("Tentou logar no sistema [Usuário: " . $formulario->username . "]", __service__);
                }
            } else {
                unset($_SESSION['admin_logado']);
                $resposta->type = "error";
                $resposta->message = "Usuário não encontrado!";
                $this->inserirRelatorio("Tentou logar no sistema [Usuário: " . $formulario->username . "]", __service__);
            }
            echo json_encode($resposta);
        }
    }
    public function sairAction() {
        setcookie("session_token", null, 0, "/");
        session_destroy();
        header("Location: $this->system_path");
    }
    public function validaFormulario($form) {
        $resposta = new \stdClass();
        $resposta->return = true;
        if (isset($form->username) && $form->username == "" && $form->recuperar_senha != 2) {
            $resposta->type = "validation";
            $resposta->message = "Preencha este campo";
            $resposta->field = "username";
            $resposta->return = false;
            return $resposta;
        } else if (($form->password == "" && $form->recuperar_senha == 2) || ($form->password == "" && !$form->recuperar_senha)) {
            $resposta->type = "validation";
            $resposta->message = "Preencha este campo";
            $resposta->field = "password";
            $resposta->return = false;
            return $resposta;
        } else {
            return $resposta;
        }
    }
    protected function entrarambienteAction() {
        if (isset($_SESSION['admin_logado']) && $_SESSION['admin_logado'] !== true) {
            header("Location: $this->system_path");
            exit();
        } else {
            $resposta = new \stdClass();
            $resposta->action = '';
            unset($_SESSION['firsturl']);
            $_SESSION["admin_service"]["current"] = array_search($_POST["ambiente"], $_SESSION['admin_service']);
            $time = time() + 43200;
            $dt = new DateTime("@$time"); 
            $_SESSION['expired'] = $dt->format('Y-m-d H:i:s');
            $hash = $this->embaralhar(json_encode($_SESSION));
            setcookie("session_token", $hash,time()+43200,'/');
            echo json_encode($resposta);
        }
    }
    protected function environmentAction(){
        $this->renderView($this->getService(), $this->getModule(), "environment");
    }
    protected function alterarSenhaAppAction() {
        $des = Crypto::decrypt($_GET['code']);
        $obj = json_decode($des, true);
        if(!(bool)$obj || $obj['vencimento'] > date("Y-M-d") || !(bool)$obj['id_pessoa']) {
            echo "<h1>Não encontrado</h1>";
        } else {
            $this->pessoa = $this->DB_fetch_array("SELECT * FROM hbrd_app_pessoa WHERE id = {$obj['id_pessoa']}")->rows[0];
            $this->renderView($this->getService(), $this->getModule(), "alterar_senha_app");
        }
    }
    protected function senhaAppAction() {
        $des = Crypto::decrypt($_POST['code']);
        $obj = json_decode($des, true);
        $pessoa = $this->DB_fetch_array("SELECT * FROM hbrd_app_pessoa WHERE id = {$obj['id_pessoa']}")->rows[0];
        if(!(bool)$_POST['senha1']) throw new BadRequest("Solicitação incorreta");
        $pessoa['salt'] = Util::createSalt();
        $pessoa['senha'] = Util::createSenhaHash($_POST['senha1'], $pessoa['salt']);
        $this->DBUpdate("hbrd_app_pessoa",["salt"=> $pessoa['salt'], "senha"=> $pessoa['senha']], "WHERE id = ". $pessoa['id']);
    }
}
define("__service__", strtolower(str_replace(dirname(dirname(dirname(dirname(__DIR__)))) . DIRECTORY_SEPARATOR, "", dirname(dirname(dirname(__DIR__))))));
define("__classe__", strtolower(str_replace(".php", "", basename(__FILE__))));
$class = new __classe__();
$class->setAction();