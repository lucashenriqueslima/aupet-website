<?php
//namespace System\Core;

require_once __DIR__ . '/Connector.php';

require_once dirname(__DIR__) . '/Libs/MobileDetect.php';
require_once dirname(__DIR__) . '/Libs/SimpleImage.php';
require_once dirname(__DIR__) . '/Libs/SimpleImage3.php';
require_once dirname(__DIR__) . '/Libs/MailChimp.php';
require_once dirname(__DIR__) . '/Libs/SMTP.php';
require_once dirname(__DIR__) . '/Libs/PHPMailer.php';
require_once dirname(__DIR__) . '/Libs/sentry-php-master/lib/Raven/Autoloader.php';
Raven_Autoloader::register();

class GeneralBase extends \ConnectorBase
{

    //debug
    public $show_log_errors = false;
    public $show_log_on_console = false;
    public $permissions = [];
    public $is_mobile = 0;
    private $_dominios = array();
    //constantes genericas para controllers e etc.
    public $_request = "";
    public $_get = "";
    public $_post = "";
    public $_empresa;
    public $_sem_dados = "";
    public $_seo_table = "";
    public $_modulos = array(); #array com nome dos módulos
    public $model = "";
    public $sentryClient;

    function __construct()
    {
        $this->sentryClient = new Raven_Client('https://68992a0391fc4dd48562a1200c7bef31@o553045.ingest.sentry.io/5679733');
        parent::__construct();
        // ----------------------------------------------------------------------
        //  CAMINHO MÓDULOS
        // ----------------------------------------------------------------------
        $this->getCaminhoModulos();
        // -----------------------------------------------------------------------
        // DETECTA SE O DISPOSITIVO É MOBILE
        // --------------------------------------------------------  -------------
        $this->detect = new MobileDetect();

        if ($this->detect->isMobile()) {
            $this->is_mobile = 1;
        }
        // -----------------------------------------------------------------------
        /*
         * CHAMA FUNÇÃO QUE TRATA ENTRADA DE DADOS COM ANTI INJECTION
         */
        if ($_POST) {
            $_POST = $this->segurancaForms($_POST);
        }
        if ($_GET) {
            $_GET = $this->segurancaForms($_GET);
        }
        if ($_REQUEST) {
            $_REQUEST = $this->segurancaForms($_REQUEST);
        }
        $this->_request = $_REQUEST;
        $this->_get = $_GET;
        $this->_post = $_POST;
        // -----------------------------------------------------------------------
        $this->dbMain();
        $this->_sem_dados = "Sem dados!";
        // $this->_seo_table = $this->_tb_prefix . "cms_seo";
        $this->_seo_table = "hbrd_cms_paginas";
        $this->atual_data_eua = date('Y-m-d');
        $this->atual_data_time_eua = date('Y-m-d H:i:s');
        $this->atual_data = date('d/m/Y');
        $this->atual_data_time = date('d/m/Y H:i:s');
    }

    public function segurancaForms($inp)
    {
        if (is_array($inp))
            return array_map(__METHOD__, $inp);

        if (!empty($inp) && is_string($inp)) {
            return str_replace(array('\\0', '\\n', '\\r', "\'", '\"', '\\Z', "\0", "\n", "\r", "'", '"', "\x1a"), array("\0", "\n", "\r", "'", '"', "\x1a", '\\0', '\\n', '\\r', "\'", '\"', '\\Z'), $inp);
            //return str_replace(array("\0", "\n", "\r", "'", '"', "\x1a"), array('\\0', '\\n', '\\r', "\\'", '\\"', '\\Z'), $inp);
        }
        return $inp;
    }
    /*
     * RETORNA ARRAY COM OS MÓDULOS EXISTENTES
     */
    private function getCaminhoModulos()
    {
        $pathModulos = dirname(dirname(dirname(__DIR__))) . DIRECTORY_SEPARATOR . 'environment' . DIRECTORY_SEPARATOR;

        $dir = dir($pathModulos);
        $this->_modulos[] = "core";
        while ($arquivo = $dir->read()) {
            if (is_dir($pathModulos . $arquivo) and $arquivo != '..' and $arquivo != '.' and $arquivo != '.metadata' and $arquivo != 'core') {
                $this->_modulos[] = $arquivo;
            }
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

    function LOG_errors($message)
    {

        $data = date("d-m-Y");
        $hora = date("H:i:s");

        //Nome do arquivo:
        $arquivo = dirname(dirname(__DIR__)) . "/System/log/erros.txt";


        //Texto a ser impresso no log:
        $texto = "[$data][$hora] : $message";

        $manipular = fopen("$arquivo", "a+b");
        fwrite($manipular, $texto);
        fclose($manipular);

        if ($this->show_log_errors) {
            echo $message;
            exit();
        }

        if ($this->show_log_on_console) {
            $this->LOG_console($message);
        }
    }

    function LOG_console($message)
    {
        echo '<script type="text/javascript">console.log("' . $message . '")</script>';
    }

    function topPermissionRequest()
    { //DEPRECATING
        if ($_SESSION['admin_grupo'] == 1) {
            return array('ler' => 1, 'gravar' => 1, 'editar' => 1, 'excluir' => 1);
        } else {
            return array('ler' => 0, 'gravar' => 0, 'editar' => 0, 'excluir' => 0);
        }
    }

    function permissionRequest($ref)
    { //DEPRECATING
        $pqp = "SELECT IFNULL(C.ler,B.ler) ler, IFNULL(C.gravar,B.gravar) gravar, IFNULL(C.editar,B.editar) editar, IFNULL(C.excluir,B.excluir) excluir FROM hbrd_main_funcoes A INNER JOIN hbrd_main_function_permission B ON A.id=B.id_funcao LEFT JOIN hbrd_main_usuarios_permissoes C ON C.id_funcao = A.id AND C.id_usuario = {$_SESSION['admin_id']} WHERE A.identificador = '$ref' AND B.id_grupo = {$_SESSION['admin_grupo']}";
        // var_dump($pqp);
        $query = $this->DB_fetch_array($pqp);
        if ($query->query) {
            return $query->rows[0];
        } else {
            return "error";
        }
    }

    function getAllPermissions() {
        if (isset($_SESSION['admin_logado']) && $_SESSION['admin_logado'] === true) {
            $query = $this->DB_fetch_array("SELECT A.id, A.nome, A.identificador, D.identificador servico, IFNULL(C.ler,B.ler) ler, IFNULL(C.gravar,B.gravar) gravar, IFNULL(C.editar,B.editar) editar, IFNULL(C.excluir,B.excluir) excluir FROM hbrd_main_funcoes A LEFT JOIN hbrd_main_permissoes B ON A.id=B.id_funcao AND B.id_grupo = '{$_SESSION['admin_grupo']}' LEFT JOIN hbrd_main_usuarios_permissoes C ON C.id_funcao = A.id AND C.id_usuario = '{$_SESSION['admin_id']}' LEFT JOIN hbrd_main_servicos D ON D.id = A.id_servico ORDER BY A.nome");
            if ($query->num_rows) {
                foreach ($query->rows as $permission) {
                    $this->permissions[$permission['identificador']] = $permission;
                }
            }
            $_SESSION['admin_perm_serv_ind'] = false;
            $query = $this->DB_fetch_array("SELECT * FROM hbrd_main_servicos A LEFT JOIN hbrd_main_usuarios_main_servicos C ON A.id = C.id_servico  AND C.id_usuario = '{$_SESSION['admin_id']}' WHERE A.identificador != 'main' GROUP BY A.id ORDER BY C.id_usuario DESC");
            if ($query->rows[0]['id_usuario'])
                $_SESSION['admin_perm_serv_ind'] = true;
            if ($this->allowedMain()) {
                if (isset($_SESSION["admin_service"]) AND ! in_array("main", $_SESSION["admin_service"]))
                    $_SESSION["admin_service"][] = "main";
                if (isset($_COOKIE['c_admin_service']) AND strpos($_COOKIE['c_admin_service'], "main") === false)
                    setcookie("c_admin_service", "main", time() + (86400 * 30), "/");
            }else {
                if (isset($_SESSION["admin_service"]) AND ( $key = array_search("main", $_SESSION["admin_service"])) !== false) {
                    unset($_SESSION["admin_service"][$key]);
                }
                if (isset($_COOKIE['c_admin_service']) AND strpos($_COOKIE['c_admin_service'], "main") !== false) {
                    setcookie("c_admin_service", str_replace(array(",main", "main,", "main"), "", $_COOKIE['c_admin_service']), time() + (86400 * 30), "/");
                }
            }
            $query = ($_SESSION['admin_perm_serv_ind']) ? $this->DB_fetch_array("SELECT A.*, IF(C.id_servico IS NULL, 0 , 1) permitido FROM hbrd_main_servicos A LEFT JOIN hbrd_main_usuarios_main_servicos C ON A.id = C.id_servico  AND C.id_usuario = '{$_SESSION['admin_id']}' WHERE A.identificador != 'main' GROUP BY A.id ORDER BY A.nome") : $this->DB_fetch_array("SELECT A.*, IF(B.id_servico IS NULL, 0 , 1) permitido FROM hbrd_main_servicos A LEFT JOIN hbrd_main_grupos_main_servicos B ON A.id = B.id_servico AND B.id_grupo = '{$_SESSION['admin_grupo']}' WHERE A.identificador != 'main' GROUP BY A.id ORDER BY A.nome");
            $this->services = array();
            if ($query->num_rows) {
                foreach ($query->rows as $row) {
                    if ($row['permitido']) {
                        $service = $row["identificador"];
                        if (isset($_SESSION["admin_service"]) AND is_array($_SESSION["admin_service"]) AND ! in_array($service, $_SESSION["admin_service"])) {
                            $_SESSION["admin_service"][] = $service;
                        }
                        $this->services[array_search($service, $_SESSION["admin_service"])] = $row["nome"];
                        if (isset($_COOKIE['c_admin_service']) AND strpos($_COOKIE['c_admin_service'], $service) === false) {
                            if ($_COOKIE['c_admin_service'] == "" OR $_COOKIE['c_admin_service'] == "login")
                                setcookie("c_admin_service", "$service", time() + (86400 * 30), "/");
                            else
                                setcookie("c_admin_service", "{$_COOKIE['c_admin_service']},$service", time() + (86400 * 30), "/");
                        }
                    }
                }
                if (!isset($_SESSION["admin_service"]["current"]))
                    $_SESSION["admin_service"]["current"] = 0;
            }
        }
    }

    function getLevelsAccess()
    {
        $strg = "SELECT A.nome ,A.nivel, A.acesso, B.ler, B.gravar, B.editar, B.excluir FROM hbrd_main_level A INNER JOIN hbrd_main_level_permission B ON A.id = B.id_nivel INNER JOIN hbrd_main_funcoes C ON B.id_funcao = C.id INNER JOIN hbrd_main_usuarios D ON A.id = D.id_nivel WHERE D.id = '{$_SESSION['admin_id']}' AND C.identificador = '$this->permissao_ref'";
        $query = $this->DB_fetch_array($strg);
        if ($query->num_rows) {
            $this->level = $query->rows[0];
        } else {
            $this->level["nivel"] = $this->DB_fetch_array("SELECT max(nivel) nivel FROM hbrd_main_level")->rows[0]["nivel"] + 1;
            $this->level["ler"] = 0;
            $this->level["gravar"] = 0;
            $this->level["excluir"] = 0;
            $this->level["editar"] = 0;
        }
    }

    function masterLevel()
    {
        if (!isset($this->level)) $this->getLevelsAccess();
        return ($this->level["nivel"] == 1) ? true : false;
    }

    function allowedMain()
    {
        return 1;
        if (isset($_SESSION['admin_id'])) {
            if ($this->DB_fetch_array("SELECT * FROM hbrd_main_servicos A INNER JOIN hbrd_main_usuarios_main_servicos B ON B.id_servico = A.id AND B.id_usuario = {$_SESSION['admin_id']} AND A.identificador = 'main'")->num_rows) {
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    public function allowedService($service) {
        if ($service === 'main') {
            return 1;
        }
        return (isset($_SESSION['admin_service']) and is_array($_SESSION['admin_service']) and in_array($service, $_SESSION['admin_service']) and ($service == "main" or $_SESSION['admin_service']["current"] == array_search($service, $_SESSION['admin_service']))) ? true : false;
    }

    function topPermission()
    {
        if ($_SESSION['admin_grupo'] == 1) {
            return 1;
        } else {
            return 0;
        }
    }

    public function getFileName($table, $field, $where)
    {
        $query = $this->DB_fetch_array("SELECT $field FROM $table WHERE $where");
        if ($query->num_rows)
            return $query->rows[0][$field];
    }

    function getActualUploadFolder()
    {
        $folder = date("Y") . "_" . date("m") . "/";
        if (!file_exists($this->upload_folder . $folder)) {
            mkdir($this->upload_folder . $folder, 0777, true);
        }
        return $folder;
    }

    function getActualFileFormatFolder($format)
    {
        $format = strtolower($format);
        if ($format == "jpg" || $format == "jpeg" || $format == "gif" || $format == "png") {
            $folder = $this->getActualUploadFolder();
            $folder = $folder . "images/";
            if (!file_exists($this->upload_folder . $folder)) {
                mkdir($this->upload_folder . $folder, 0777, true);
                mkdir($this->upload_folder . $folder . "original/", 0777, true);
                mkdir($this->upload_folder . $folder . "resized/", 0777, true);
            }
            return $folder . "original/";
        } else if ($format == "doc" || $format == "docx" || $format == "pdf") {
            $folder = $this->getActualUploadFolder();
            $folder = $folder . "docs/";
            if (!file_exists($this->upload_folder . $folder)) {
                mkdir($this->upload_folder . $folder, 0777, true);
            }
            return $folder;
        } else {
            $folder = $this->getActualUploadFolder();
            $folder = $folder . "medias/";
            if (!file_exists($this->upload_folder . $folder)) {
                mkdir($this->upload_folder . $folder, 0777, true);
            }
            return $folder;
        }
    }

    //DUPLICAR IMAGENS DO SERVIDOR
    function duplicateFile($field, $accepted_formats, $sizes = "")
    {
        $return = new \stdClass();
        $file = explode("/", $field);
        $file_original = end($file);
        $file_array = explode(".", end($file));
        $format = end($file_array);
        array_pop($file_array);
        $filename = implode(".", $file_array);
        $folder = $this->getActualFileFormatFolder($format);
        $file_uploaded = $folder . $file_original;
        //VERIFICA SE EXISTE UM ARQUIVO COM O MESMO NOME
        $copy = 0;
        while (file_exists($this->upload_folder . $file_uploaded)) {
            $copy++;
            $file_uploaded = $folder . $filename . "($copy)." . $format;
        }
        if (!copy($this->upload_folder . $field, $this->upload_folder . $file_uploaded)) {
            $return->return = false;
            $return->message = "Não foi possivel copiar o arquivo enviado. Tente outro arquivo!";
        } else {
            $return->return = true;
            $return->filename = $filename;
            $return->folder = $folder;
            $return->file_uploaded = $file_uploaded;
            if ($sizes != "") {
                foreach ($sizes as $size) {
                    $img = new SimpleImage($this->upload_folder . $file_uploaded);
                    $folder_resized = str_replace("original/", "resized/", $folder);
                    if ($copy > 0) {
                        $saveto = $this->upload_folder . $folder_resized . $filename . "($copy)[" . $size['width'] . "x" . $size['height'] . "]." . $format;
                    } else {
                        $saveto = $this->upload_folder . $folder_resized . $filename . "[" . $size['width'] . "x" . $size['height'] . "]." . $format;
                    }
                    if (isset($size["best_fit"])) {
                        $img->best_fit($size['width'], $size['height'])->save($saveto);
                    } else {
                        $orientation = $img->get_orientation();
                        if ($orientation == "portrait") {
                            $img->fit_to_width($size['width']);
                            // Crop a portion of the image from x1, y1 to x2, y2
                            //echo "0, {$size['height']}, {$size['width']}, {$size['height']}";
                            $img->crop(0, 0, $size['width'], $size['height']);
                            $img->save($saveto);
                        } else {
                            $img->adaptive_resize($size['width'], $size['height'])->save($saveto);
                        }
                    }
                }
            }
        }
        return $return;
    }

    function existFile($folder, $width, $height)
    {
        if (empty($width) and empty($height) and file_exists($this->upload_folder . $folder)) {
            return true;
        } else if (!empty($width) and !empty($height)) {
            $folder_resized = str_replace("original/", "resized/", $folder);
            $file_array = explode(".", $folder_resized);
            $format = end($file_array);
            array_pop($file_array);
            $folder_resized = implode(".", $file_array);
            $folder_resized = $this->upload_folder . $folder_resized . "[" . $width . "x" . $height . "]." . $format;

            return (file_exists($folder_resized)) ? true : false;
        } else {
            return false;
        }
    }

    function uploadFile($field, $accepted_formats, $sizes = "")
    {
        $return = new stdClass();

        if ($_FILES[$field]["name"] != "") {
            $file = $this->removeAcentos($_FILES[$field]["name"]);
            $file = strtolower($file);
            $file = str_replace(" ", "-", $file);
            $file_array = explode(".", $file);
            $format = end($file_array);
            array_pop($file_array);
            $filename = implode(".", $file_array);
            $flag = true;

            foreach ($accepted_formats as $val) {
                if ($format == $val) {
                    $flag = true;
                    break;
                }
            }

            if ($flag) {
                $folder = $this->getActualFileFormatFolder($format);
                $file_uploaded = $folder . $file;
                $flag = false;
                //VERIFICA SE EXISTE UM ARQUIVO COM O MESMO NOME
                $copy = 0;

                while (file_exists($this->upload_folder . $file_uploaded)) {
                    $copy++;
                    $file_uploaded = $folder . $filename . "($copy)." . $format;
                }

                if (!move_uploaded_file($_FILES[$field]["tmp_name"], $this->upload_folder . $file_uploaded)) {
                    $return->return = false;
                    $return->message = "Não foi possivel copiar o arquivo enviado. Tente outro arquivo!";
                } else {
                    $return->return = true;
                    $return->filename = $filename;
                    $return->folder = $folder;
                    $return->file_uploaded = $file_uploaded;
                    if ($sizes != "") {
                        foreach ($sizes as $size) {
                            $img = new SimpleImage($this->upload_folder . $file_uploaded);
                            $folder_resized = str_replace("original/", "resized/", $folder);
                            if ($copy > 0) {
                                $saveto = $this->upload_folder . $folder_resized . $filename . "($copy)[" . $size['width'] . "x" . $size['height'] . "]." . $format;
                            } else {
                                $saveto = $this->upload_folder . $folder_resized . $filename . "[" . $size['width'] . "x" . $size['height'] . "]." . $format;
                            }
                            if (isset($size["best_fit"])) {
                                $img->best_fit($size['width'], $size['height'])->save($saveto);
                            } else {
                                $orientation = $img->get_orientation();
                                if ($orientation == "portrait") {
                                    $img->fit_to_width($size['width']);
                                    // Crop a portion of the image from x1, y1 to x2, y2
                                    //echo "0, {$size['height']}, {$size['width']}, {$size['height']}";
                                    $img->crop(0, 0, $size['width'], $size['height']);
                                    $img->save($saveto);
                                } else {
                                    $img->adaptive_resize($size['width'], $size['height'])->save($saveto);
                                }
                            }
                        }
                    }
                }
            } else {
                $return->return = false;
                $return->message = "Formato inesperado";
            }
        } else {
            $return->return = false;
            $return->message = "Não foi possivel reconhecer o arquivo enviado. Tente outro arquivo!";
        }

        return $return;
    }

    function uploadResized($folder, $size = "")
    {
        $file_array = explode(".", $folder);
        $format = end($file_array);
        array_pop($file_array);
        $folder_resized = implode(".", $file_array);
        if ($size != "") {
            $img = new SimpleImage($this->upload_folder . $folder);
            $folder_resized = str_replace("original/", "resized/", $folder_resized);
            $saveto = $this->upload_folder . $folder_resized . "[" . $size['width'] . "x" . $size['height'] . "]." . $format;
            if (isset($size["best_fit"])) {
                $img->best_fit($size['width'], $size['height'])->save($saveto);
            } else {
                $orientation = $img->get_orientation();
                if ($orientation == "portrait") {
                    $img->fit_to_width($size['width']);
                    // Crop a portion of the image from x1, y1 to x2, y2
                    //echo "0, {$size['height']}, {$size['width']}, {$size['height']}";
                    $img->crop(0, 0, $size['width'], $size['height']);
                    $img->save($saveto);
                } else {
                    $img->adaptive_resize($size['width'], $size['height'])->save($saveto);
                }
            }
        }
    }

    function uploadFiles($field, $accepted_formats, $sizes = "", $i)
    {
        $return = new \stdClass();
        if ($_FILES[$field]["name"] != "") {
            $file = $this->removeAcentos($_FILES[$field]["name"][$i]);
            $file = strtolower($file);
            $file = str_replace(" ", "-", $file);
            $file_array = explode(".", $file);
            $format = end($file_array);
            array_pop($file_array);
            $filename = implode(".", $file_array);
            $flag = false;
            foreach ($accepted_formats as $val) {
                if ($format == $val) {
                    $flag = true;
                    break;
                }
            }

            if ($flag) {
                $folder = $this->getActualFileFormatFolder($format);
                $file_uploaded = $folder . $file;
                $flag = false;
                //VERIFICA SE EXISTE UM ARQUIVO COM O MESMO NOME
                $copy = 0;
                while (file_exists($this->upload_folder . $file_uploaded)) {
                    $copy++;
                    $file_uploaded = $folder . $filename . "($copy)." . $format;
                }
                if (!move_uploaded_file($_FILES[$field]["tmp_name"][$i], $this->upload_folder . $file_uploaded)) {
                    $return->return = false;
                    $return->message = "Não foi possivel copiar o arquivo enviado. Tente outro arquivo!";
                } else {
                    chmod($this->upload_folder . $file_uploaded, 0644);
                    $return->return = true;
                    $return->filename = $filename;
                    $return->folder = $folder;
                    $return->file_uploaded = $file_uploaded;
                    if ($sizes != "") {
                        foreach ($sizes as $size) {
                            $img = new SimpleImage($this->upload_folder . $file_uploaded);
                            $folder_resized = str_replace("original/", "resized/", $folder);
                            if ($copy > 0) {
                                $saveto = $this->upload_folder . $folder_resized . $filename . "($copy)[" . $size['width'] . "x" . $size['height'] . "]." . $format;
                            } else {
                                $saveto = $this->upload_folder . $folder_resized . $filename . "[" . $size['width'] . "x" . $size['height'] . "]." . $format;
                            }
                            if (isset($size["best_fit"])) {
                                $img->best_fit($size['width'], $size['height'])->save($saveto);
                            } else {
                                $orientation = $img->get_orientation();
                                if ($orientation == "portrait") {
                                    $img->fit_to_width($size['width']);
                                    // Crop a portion of the image from x1, y1 to x2, y2
                                    //echo "0, {$size['height']}, {$size['width']}, {$size['height']}";
                                    $img->crop(0, 0, $size['width'], $size['height']);
                                    $img->save($saveto);
                                } else {
                                    $img->adaptive_resize($size['width'], $size['height'])->save($saveto);
                                }
                            }
                        }
                    }
                }
            } else {
                $return->return = false;
                $return->message = "Formato inesperado";
            }
        } else {
            $return->return = false;
            $return->message = "Não foi possivel reconhecer o arquivo enviado. Tente outro arquivo!";
        }
        return $return;
    }

    function deleteFile($table, $field = null, $condition, $sizes = "", $file = null)
    {
        if ($field != null and $file == null) {
            $query = $this->DB_fetch_array("SELECT $field FROM $table WHERE $condition");
            $file = $query->rows[0][$field];
        } else if ($file != null) {
            $file = $file;
        }
        if ($file != "") {
            $split = explode(".", $file);
            $format = end($split);
            array_pop($split);
            $pathfile = implode(".", $split);
            if (($format == "jpg" || $format == "jpeg" || $format == "gif" || $format == "png") && $sizes != "") {
                foreach ($sizes as $size) {
                    $file_resized = str_replace("original/", "resized/", $pathfile);
                    if (file_exists($this->upload_folder . $file_resized . "[" . $size['width'] . "x" . $size['height'] . "]." . $format)) {
                        unlink($this->upload_folder . $file_resized . "[" . $size['width'] . "x" . $size['height'] . "]." . $format);
                    }
                }
            }
            if (file_exists($this->upload_folder . $file)) {
                unlink($this->upload_folder . $file);
            }
        }
    }

    function deleteFileResized($table, $field, $condition, $sizes = "")
    {
        $query = $this->DB_fetch_array("SELECT $field FROM $table WHERE $condition");
        $file = $query->rows[0][$field];
        if ($file != "") {
            $split = explode(".", $file);
            $format = end($split);
            array_pop($split);
            $pathfile = implode(".", $split);
            if (($format == "jpg" || $format == "jpeg" || $format == "gif" || $format == "png") && $sizes != "") {
                foreach ($sizes as $size) {
                    $file_resized = str_replace("original/", "resized/", $pathfile);
                    if (file_exists($this->upload_folder . $file_resized . "[" . $size['width'] . "x" . $size['height'] . "]." . $format)) {
                        unlink($this->upload_folder . $file_resized . "[" . $size['width'] . "x" . $size['height'] . "]." . $format);
                    }
                }
            }
        }
    }

    function getImageFileSized($filepath, $w, $h, $upload_path = true) {
        $file = str_replace("original/", "resized/", $filepath);
        $file = explode(".", $file);
        $format = end($file);
        $add = "[" . $w . "x" . $h . "]." . $format;
        array_pop($file);
        $file = implode(".", $file);
        if ($upload_path) {
            return $this->upload_path . $file . $add;
        }
        return $this->upload_folder . $file . $add;
    }

    function replaceRelativePath($content)
    {
        return str_replace("../files/", $this->root_path . "files/", $content);
    }

    function enviarEmail($to = "", $from = "", $assunto = "", $mensagem = "", $bcc = "", $arquivo = "")
    {
        // if ($_SERVER["SERVER_NAME"] == "localhost") return;
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

    function enviarEmail2($to = "", $from = "", $assunto = "", $mensagem = "", $bcc = "", $arquivo = "")
    {
        // if ($_SERVER["SERVER_NAME"] == "localhost") return;
        $query = $this->DB_fetch_array("SELECT * from hbrd_main_smtp");
        $company = $this->DB_fetch_array("SELECT * FROM hbrd_adm_company", 'string')->rows[0];
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
        if ($arquivo != '') {
            $mail->AddAttachment($arquivo);
        }
        if ($from == "") {
            $mail->addReplyTo($smtp['email_padrao'], utf8_decode($this->_empresa['nome']));
        } else {
            $mail->addReplyTo($from);
        }
        $mail->setFrom($company['email'], utf8_decode($from));
        if ($bcc != "") {
            if (is_array($bcc)) {

                foreach ($bcc as $email) {
                    $email['nome'] = utf8_decode($email['nome']);
                    $mail->addBCC($email['email'], utf8_decode($from));
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

    function inserirRelatorio($atividade, $service = null) {
        // if ($_SESSION['admin_id'] == 1) return;
        if (isset($_SESSION["admin_nome"])) {
            $nome = $_SESSION["admin_nome"];
        } else {
            $nome = "anônimo";
        }
        if (isset($_SESSION['admin_id'])) {
            $idUsuario = $_SESSION['admin_id'];
        } else {
            $idUsuario = "NULL";
        }
        $atividade = str_replace("'", "`", $atividade);
        try {
            $this->DB_exec("INSERT INTO hbrd_main_log (usuario, atividade, id_usuario, date) VALUES ('$nome','$atividade', $idUsuario,NOW())");
        } catch (\Throwable $e) {
            if ($_SERVER["SERVER_NAME"] != 'localhost' && $e->getMessage() != 'cancel') {
                $this->sentryClient->extra_context(array('POST' => $_POST, 'GET' => $_GET, 'REQUEST' => $_REQUEST));
                $this->sentryClient->captureException($e);
            }
        }
    }

    function inserirBug($erro, $service = null, $classe = null, $exception = null)
    {
        $this->LOG_errors("QUERY ERROR:($exception) ERRO:(" . $erro . ")");
        $erro = addslashes($erro);
        $this->DB_insert($this->_tb_prefix . 'main_bug', 'erro,service,module,usuario', "'$erro','$service','$classe','{$_SESSION['admin_id']} - {$_SESSION['admin_nome']}'");
    }

    function ordenarRegistros($array, $table)
    {
        $indice = 0;
        for ($i = 0; $i < count($array); $i++) {
            $indice = ($i + 1);
            echo $table . " ordem = $indice WHERE id = " . $array[$i];
            $this->DB_update($table, ["ordem" => $indice], " WHERE id = " . $array[$i]);
        }
        $this->inserirRelatorio("Reordenou tabela: [" . $table . "]");
    }

    function inserirMailing($email, $nome, $origem = "")
    {
        if (!$this->DB_num_rows("SELECT * FROM tb_mailings_mailings WHERE email = '$email'")) {
            $this->DB_insert("tb_mailings_mailings", "data, nome, email, origem", "NOW(), '$nome', '$email', '$origem'");
        }
        $MailChimp = new MailChimp('3fb97268eb8ed082cafc765b94aa101e-us8');
        $result = $MailChimp->call('lists/subscribe', array(
            'id' => '16514f64c7',
            'email' => array('email' => $email),
            'merge_vars' => array('FNAME' => $nome, 'LNAME' => ''),
            'double_optin' => false,
            'update_existing' => true,
            'replace_interests' => false,
            'send_welcome' => false,
        ));
        return $result;
    }

    function validaUrlAmiga($url, $id = 0, $table = 'hbrd_cms_paginas')
    {
        if ($id == 0) {
            $query = $this->DB_fetch_array("SELECT * FROM {$table} WHERE seo_url = '$url'");
            if ($query->num_rows) {
                return false;
            } else {
                return true;
            }
        } else {
            $query = $this->DB_fetch_array("SELECT * FROM {$table} WHERE seo_url = '$url' AND id<>" . $id);
            if ($query->num_rows) {
                return false;
            } else {
                return true;
            }
        }
    }

    function formataUrlAmiga($url)
    {
        //limpa espaços no inicio e fim da string
        $url = trim($url);
        //substitui acentos da string
        $url = $this->removeAcentos($url);
        $url = strtolower($url);
        //substitui espacos,barras,etc por ifen
        $url = str_replace(array(" ", "/"), "-", $url);
        //retira virgulas e pontos da string
        $url = str_replace(array(",", ".", ":", "'", '"', "?", "!", "@", "#", "$", "%", "&", "*", "=", "+", "´", "`", ";", "/", "“", "”"), "", $url);
        $url = stripslashes($url);
        return $url;
    }

    function validaEmail($email)
    {
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
        if ($mail_correcto && $this->verifyDomainOfEmail($email) && $this->verifyBeforeDomain($email))
            return 1;
        else
            return 0;
    }

    function checkdate($data)
    {
        // VERIFICA DE DATA É VÁLIDA (formato de entrada de data brasileiro)
        $data = explode("/", $data);
        return checkdate($data[1], $data[0], $data[2]);
    }

    function checaData($date)
    {
        $vencimento = explode("-", $date);
        $ano = $vencimento[0];
        $mes = $vencimento[1];
        $dia = $vencimento[2];
        if (!$this->checkdate("$dia/$mes/$ano")) {
            $dia = $dia - 1;
            $vencimento = "$ano-$mes-$dia";
            return $this->checaData($vencimento);
        } else {
            return "$ano-$mes-$dia";
        }
    }

    function checktime($time)
    {
        if (stristr($time, ":")) {
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
        } else {
            if ($time < 24 && $time > -1) {
                return true;
            } else {
                return false;
            }
        }
    }

    function formataDataDeMascara($data)
    {
        if (!$data || !$data == '0000-00-00 00:00:00') return 'NULL';
        $data = explode(' ', $data)[0];
        $data = explode('/', $data);
        $data = $data[2] . '-' . $data[1] . '-' . $data[0];
        return $data;
    }

    function formataDataDeBanco($data) {
        if (!$data || !$data == '0000-00-00 00:00:00') return '';
        $data = explode(' ', $data)[0];
        $data = explode('-', $data);
        $data = $data[2] . '/' . $data[1] . '/' . $data[0];
        return $data;
    }

    function removeAcentos($value)
    {
        $from = "áàãâéêíóôõúüçÁÀÃÂÉÊÍÓÔÕÚÜÇ";
        $to = "aaaaeeiooouucAAAAEEIOOOUUC";
        $keys = array();
        $values = array();
        preg_match_all('/./u', $from, $keys);
        preg_match_all('/./u', $to, $values);
        $mapping = array_combine($keys[0], $values[0]);
        $value = strtr($value, $mapping);
        return $value;
    }

    function validaCPF($cpf)
    {
        // determina um valor inicial para o digito $d1 e $d2
        // pra manter o respeito ;)
        $d1 = 0;
        $d2 = 0;
        // remove tudo que não seja número
        $cpf = preg_replace("/[^0-9]/", "", $cpf);
        // lista de cpf inválidos que serão ignorados
        $ignore_list = array(
            '00000000000',
            '01234567890',
            '11111111111',
            '22222222222',
            '33333333333',
            '44444444444',
            '55555555555',
            '66666666666',
            '77777777777',
            '88888888888',
            '99999999999'
        );
        // se o tamanho da string for dirente de 11 ou estiver
        // na lista de cpf ignorados já retorna false
        if (strlen($cpf) != 11 || in_array($cpf, $ignore_list)) {
            return false;
        } else {
            // inicia o processo para achar o primeiro
            // número verificador usando os primeiros 9 dígitos
            for ($i = 0; $i < 9; $i++) {
                // inicialmente $d1 vale zero e é somando.
                // O loop passa por todos os 9 dígitos iniciais
                $d1 += $cpf[$i] * (10 - $i);
            }
            // acha o resto da divisão da soma acima por 11
            $r1 = $d1 % 11;
            // se $r1 maior que 1 retorna 11 menos $r1 se não
            // retona o valor zero para $d1
            $d1 = ($r1 > 1) ? (11 - $r1) : 0;
            // inicia o processo para achar o segundo
            // número verificador usando os primeiros 9 dígitos
            for ($i = 0; $i < 9; $i++) {
                // inicialmente $d2 vale zero e é somando.
                // O loop passa por todos os 9 dígitos iniciais
                $d2 += $cpf[$i] * (11 - $i);
            }
            // $r2 será o resto da soma do cpf mais $d1 vezes 2
            // dividido por 11
            $r2 = ($d2 + ($d1 * 2)) % 11;
            // se $r2 mair que 1 retorna 11 menos $r2 se não
            // retorna o valor zeroa para $d2
            $d2 = ($r2 > 1) ? (11 - $r2) : 0;
            // retona true se os dois últimos dígitos do cpf
            // forem igual a concatenação de $d1 e $d2 e se não
            // deve retornar false.
            return (substr($cpf, -2) == $d1 . $d2) ? true : false;
        }
    }

    function validaCNPJ($cnpj)
    {
        $cnpj = str_pad(str_replace(array('.', '-', '/'), '', $cnpj), 14, '0', STR_PAD_LEFT);
        if (strlen($cnpj) != 14) {
            return false;
        } else if ($cnpj == "00000000000000") {
            return false;
        } else {
            for ($t = 12; $t < 14; $t++) {
                for ($d = 0, $p = $t - 7, $c = 0; $c < $t; $c++) {
                    $d += $cnpj{
                    $c} * $p;
                    $p = ($p < 3) ? 9 : --$p;
                }
                $d = ((10 * $d) % 11) % 10;
                if ($cnpj{
                $c} != $d) {
                    return false;
                }
            }
            return true;
        }
    }

    function removeImageFromString($string)
    {
        return preg_replace("/<img[^>]+\>/i", "", $string);
    }

    function getCidades($id)
    {
        $dados = $this->DB_fetch_array("SELECT * FROM hbrd_main_util_city WHERE id_estado='" . $id . "' ORDER BY cidade");
        return $dados->rows;
    }

    function formataMoedaBd($valor = null)
    {
        if ($valor != null) {
            $valor = str_replace(".", "", $valor);
            $valor = str_replace(",", ".", $valor);
        }
        return $valor;
    }

    function formataMoedaDeMascara($valor = null)
    {
        if ($valor != null) {
            $valor = str_replace(".", "", $valor);
            $valor = str_replace(",", ".", $valor);
        }
        return $valor;
    }

    function formataMoedaDeBanco($valor = null)
    {
        return ($valor && !is_nan($valor)) ? number_format($valor, 2, ',', '.') : "0,00";
    }

    function formataMoeda($valor = null)
    {
        if ($valor != null) {
            $valor = number_format($valor, 2, ',', '');
        }
        return $valor;
    }

    function formataMoedaShow($valor = null)
    {
        if ($valor != null) {
            $valor = number_format($valor, 2, ',', '.');
        }
        return $valor;
    }

    // FUNÇÃO PROCURA TAG DENTRO DA STRING, SE ENCONTRAR INSERE UM ARQUIVO
    function insertFileInString($string = null, $array = null)
    {
        if ($string != null && $array != null) {
            foreach ($array as $key => $value) {
                if (stristr($string, "$key")) {
                    ob_start();
                    include("$value");
                    $obj = ob_get_clean();
                    $string = str_replace("$key", $obj, $string);
                }
            }
            ob_end_flush();
        }
        return $this->replaceRelativePath($string);
    }

    // FUNÇÃO RETORNA SENHA CRIPTOGRAFADA
    public function criptPass($senha = null)
    {
        $passCript = $this->criptBase;
        if ($senha != null) {
            $senha = sha1($senha . $passCript);
        }
        return $senha;
    }

    // FUNÇÃO REMOVE VALORRES NULOS DE UM ARRAY E RETORNA O ARRAY INDEXADO
    public function recompoeArray($array = null)
    {
        if ($array != null) {
            $array = array_filter($array);
            $array = array_values($array);
        }
        return $array;
    }

    // CRIA PARAMETROS DA URL DANDO EXPLODE EM "/"
    public function getParameter($parametro = null, $objeto = false)
    {
        //trata caracteres especiais da url
        $uri = urldecode($_SERVER["REQUEST_URI"]);
        //$uri = strtolower($uri);
        //cria array com palavras entre barras
        $uri = explode("/", $uri);
        $array = array();
        $count = 1;
        for ($i = 0; $i < count($uri); $i++) {
            $id = $i + 1;
            $count++;
            if (isset($uri[$id]) && $uri[$i]) {
                //$uri[$id] = str_replace(" ", "+", $uri[$id]);
                $array[$uri[$i]] = $this->segurancaForms($uri[$id]);
            }
            //declara url amigável, sendo ela sempre o último do array
            if (isset($uri[$id]) && $count === count($uri))
                $array["url_amiga"] = $this->segurancaForms($uri[$id]);
        }
        //caso seja solicitado um objeto
        if ($objeto == true) {
            $objeto = (object) $array;
            //caso seja solicitado um parametro
            if ($parametro != null) {
                if (isset($objeto->$parametro))
                    return $objeto->$parametro;
                else
                    return null;
            }
            //caso não seja solicitado um parametro
            else
                return $objeto;
        }
        //caso seja solicitado um array
        else {
            //caso seja solicitado um parametro
            if ($parametro != null) {
                if (isset($array[$parametro]))
                    return $array[$parametro];
                else
                    return null;
            }
            //caso não seja solicitado um parametro
            else
                return $array;
        }
    }

    // TRATA STRING DO GET PARAMETER PARA FORMATAR URL, EXEMPLO: "Aparecida de Goiânia" FICARÁ: "aparecida+de+goiânia"
    public function trataParameter($parametro = null)
    {
        if ($parametro != null) {
            $parametro = strtr($parametro, "ÀÁÂÃÄÅÆÇÈÉÊËÌÍÎÏÐÑÒÓÔÕÖ×ØÙÜÚÞß", "àáâãäåæçèéêëìíîïðñòóôõö÷øùüúþÿ");
            if (function_exists('mb_convert_case'))
                $parametro = mb_convert_case($parametro, MB_CASE_LOWER, "UTF-8");
            else
                $parametro = strtolower($parametro);
            $parametro = str_replace(" ", "+", $parametro);
        }
        return $parametro;
    }

    // PASSA TUDO PARA MAIÚSCULO, INCLUSIVE CARACTERES ACENTUADOS
    public function tudoMaiusculo($string = null)
    {
        if ($string != null) {
            $string = strtoupper(strtr($string, "áéíóúâêôãõàèìòùç", "ÁÉÍÓÚÂÊÔÃÕÀÈÌÒÙÇ"));
        }
        return $string;
    }

    // CRIA RESUMO COM LIMITES DA CARACTERES
    public function intro($texto, $limite)
    {
        $red = '';
        $texto = trim($texto);

        if (strlen($texto) > $limite) {
            $red = '...';
        }
        $texto = strip_tags($texto, '<(.*?)>');
        $texto = trim($texto);
        $texto = substr($texto, 0, $limite);
        $texto = trim($texto) . $red;

        return $texto;
    }

    // RETORNA CAMPOS DA TABELA INFORMADA
    function DB_columns($table = null)
    {
        /* if(strpos($table, $this->_tb_prefix) !== 0)
          $table = $this->_tb_prefix.$table; */
        $array = array();
        if ($table != null) {
            $query = $this->DB_fetch_array("DESCRIBE $table");
            if ($query->num_rows) {
                $campos = $query->rows;
                foreach ($campos as $campos) {
                    $array[] = $campos["Field"];
                }
            }
        }
        return $array;
    }

    // CRIA ARRAY PARA ALTERAÇÃO NO BANCO DE DADOS
    function comparaCampos($table = null, $data = null)
    {
        $array = array();

        if ($table != null && $data != null) {
            $colunas = $this->DB_columns($table);

            foreach ($data as $key => $value) {
                if (in_array($key, $colunas)) {
                    $array[$key] = $value;
                }
            }
        }
        return $array;
    }

    public function formularioArray($array, $table){
        return $this->arrayTableData($array, $table);
    }

    public function arrayTableData($array, $table) {
        $formulario = [];
        $colunas = $this->DB_columns($table);
        foreach ($array as $key => $value) {
            if (in_array($key, $colunas)) {
                $formulario[$key] = $value;
            }
        }
        return $formulario;
    }

    function formularioObjeto($array = null, $table = null)
    {
        $formulario = new \stdClass();
        if ($table != null) {
            $colunas = $this->DB_columns($table);
        }
        if ($array != null) {
            foreach ($array as $key => $value) {
                if ($table != null) {
                    if (in_array($key, $colunas)) {
                        $formulario->$key = $value;
                    }
                } else {
                    $formulario->$key = $value;
                }
            }
        }
        return $formulario;
    }

    function arrayTable($array, $table)
    {
        $formulario = [];
        $colunas = $this->DB_columns($table);
        foreach ($array as $key => $value) {
            if (in_array($key, $colunas)) {
                $formulario[$key] = $value;
            }
        }
        return $formulario;
    }

    // RESTA IDS PARA COMEÇAR A USAR O SISTEMA
    function DB_tablesIdsReset($table = null)
    {
        if ($table != null) {
            $query = $this->DB_fetch_array("SELECT t.table_name FROM INFORMATION_SCHEMA.TABLES t WHERE t.table_schema = '$table'");

            $reset_auto_increment_tables = "";
            if ($query->num_rows) {
                foreach ($query->rows as $bd) {
                    $reset_auto_increment_tables = " ALTER TABLE {$bd['table_name']} AUTO_INCREMENT = 1 ";
                    $query = $this->mysqli->query($reset_auto_increment_tables);
                }
            }
        }
    }

    // CONVERTE DATETIME USA PARA BRASIL EX: 22/08/2014 17:35:03
    public function datetime($converter)
    {
        $array_data = explode(" ", $converter);
        $array_dma = explode("-", $array_data[0]);
        $converter = $array_dma[2] . "/" . $array_dma[1] . "/" . $array_dma[0] . " " . $array_data[1];
        return $converter;
    }

    // CONVERTE BRASIL PARA DATE USA 
    public function datebr($converter)
    {
        $array_amd = explode("/", $converter);
        $converter = $array_amd[2] . "-" . $array_amd[1] . "-" . $array_amd[0];
        return $converter;
    }

    /*
     * GUARDA TODAS ATIVIDADES DO WEBSERVICE
     */

    function relatorioWs($formulario, $cliente, $dados)
    {
        $dados = json_encode($dados);

        $form = json_encode($formulario);

        $this->mysqli->query("INSERT INTO tb_admin_webservice_logs (id_cliente, ip, autenticacao, parametro, requisicao, retorno) VALUES ('" . $cliente->id . "','" . $formulario->ip . "','" . $formulario->autenticacao . "','" . $formulario->parametro . "', '" . $form . "' ,'" . $dados . "')");
    }

    /*
     * ATUALIZA Nº DE VISITAS, INFORMAR TABELA E ID_SEO
     */

    public function upViews($table = null, $id = null)
    {
        if ($table != null && $id != null) {
            $query = $this->DB_fetch_array("SELECT visitas FROM $table WHERE id_seo = $id");
            if ($query->num_rows) {
                $visitas = $query->rows[0]['visitas'];
                $views = $visitas + 1;
                $this->DB_update($table, "visitas = $views WHERE id_seo = $id");
            }
            unset($query);
        }
    }

    public function upViewsBackEnd($tabela = null, $idTarefa = null, $idUser = null)
    {
        if ($tabela != null && $idTarefa != null && $idUser != null) {
            $query = $this->DB_fetch_array("SELECT visitas FROM $tabela WHERE id_tarefa = $idTarefa AND id_usuario = $idUser");
            if ($query->num_rows) {
                $visitas = $query->rows[0]['visitas'];
                $views = $visitas + 1;
                $this->DB_update($tabela, "visitas = $views WHERE id_tarefa = $idTarefa");
            } else {
                $queryin = "INSERT INTO $tabela (id_tarefa, id_usuario, visitas) VALUES($idTarefa,$idUser,1)";
                $this->mysqli->query($queryin);
            }
            unset($query);
        }
    }

    //RETORNA NOME DO MÊS
    public function getNomeMes($int = null, $abr = null)
    {
        switch ($int) {
            case 1:
                if ($abr)
                    return "Jan";
                else
                    return "Janeiro";
                break;
            case 2:
                if ($abr)
                    return "Fev";
                else
                    return "Fevereiro";
                break;
            case 3:
                if ($abr)
                    return "Mar";
                else
                    return "Março";
                break;
            case 4:
                if ($abr)
                    return "Abr";
                else
                    return "Abril";
                break;
            case 5:
                if ($abr)
                    return "Mai";
                else
                    return "Maio";
                break;
            case 6:
                if ($abr)
                    return "Jun";
                else
                    return "Junho";
                break;
            case 7:
                if ($abr)
                    return "Jul";
                else
                    return "Julho";
                break;
            case 8:
                if ($abr)
                    return "Ago";
                else
                    return "Agosto";
                break;
            case 9:
                if ($abr)
                    return "Set";
                else
                    return "Setembro";
                break;
            case 10:
                if ($abr)
                    return "Out";
                else
                    return "Outubro";
                break;
            case 11:
                if ($abr)
                    return "Nov";
                else
                    return "Novembro";
                break;
            case 12:
                if ($abr)
                    return "Dez";
                else
                    return "Dezembro";
                break;
            default:
                return "Mês inválido";
                break;
        }
    }

    //CRIPTOGRAFIA PARA CONTEÚDOS IMPORTANTES
    public function embaralhar($plain_text = null)
    {
        $plain_text .= "\x13";
        $n = strlen($plain_text);
        if ($n % 16)
            $plain_text .= str_repeat("\0", 16 - ($n % 16));
        $i = 0;
        $iv_len = 16;
        $enc_text = '';
        while ($iv_len-- > 0) {
            $enc_text .= chr(mt_rand() & 0xff);
        }
        $iv = substr($this->criptBase ^ $enc_text, 0, 512);
        while ($i < $n) {
            $block = substr($plain_text, $i, 16) ^ pack('H*', md5($iv));
            $enc_text .= $block;
            $iv = substr($block . $iv, 0, 512) ^ $this->criptBase;
            $i += 16;
        }
        return base64_encode($enc_text);
    }

    public function desembaralhar($enc_text = null)
    {
        $enc_text = base64_decode($enc_text);
        $n = strlen($enc_text);
        $i = 16;
        $plain_text = '';
        $iv = substr($this->criptBase ^ substr($enc_text, 0, 16), 0, 512);
        while ($i < $n) {
            $block = substr($enc_text, $i, 16);
            $plain_text .= $block ^ pack('H*', md5($iv));
            $iv = substr($block . $iv, 0, 512) ^ $this->criptBase;
            $i += 16;
        }
        return preg_replace('/\x13\x00*$/', '', $plain_text);
    }

    /*
     * ATUALIZA TABELA DE SEO ACESSOS GENERAL 
     */

    public function seoAcessosAtualiza()
    {
        $diaAnterior = date("Y-m-d", strtotime("-1 day"));

        $dataVerificada = "2000-01-01";
        $verificar = $this->DB_fetch_array("SELECT DATE_FORMAT(data, '%Y-%m-%d') dia FROM tb_dashboards_configuracoes WHERE origem = 'general'");
        if ($verificar->num_rows) {
            if ($verificar->rows[0]['dia'] != "")
                $dataVerificada = $verificar->rows[0]['dia'];
        }

        if ($dataVerificada < $diaAnterior) {
            $paginas = $this->DB_fetch_object("SELECT A.id_seo id_seo, COUNT(A.id_seo) qtd, date(date) date FROM tb_seo_acessos A WHERE DATE(A.date) > '$dataVerificada' AND DATE(A.date) <= '$diaAnterior' GROUP BY DATE(A.date), A.id_seo");
            if ($paginas->num_rows) {
                foreach ($paginas->rows as $row) {
                    $insertData[] = "($row->id_seo, $row->qtd, '$row->date')";
                }

                if (isset($insertData)) {
                    $query = $this->mysqli->query("INSERT INTO tb_dashboards_paginas (id_seo,qtd,date) VALUES " . implode(',', $insertData));
                }
                unset($insertData);
            }

            $visitas = $this->DB_fetch_object("SELECT date, COUNT(session) visitas, COUNT(desktop) desktop, COUNT(tablet) tablet, COUNT(mobile) mobile FROM (SELECT session, DATE_FORMAT(date, '%Y-%m-%d') date, CASE dispositivo WHEN 1 THEN COUNT(dispositivo) END desktop, CASE dispositivo WHEN 2 THEN COUNT(dispositivo) END tablet, CASE dispositivo WHEN 3 THEN COUNT(dispositivo) END mobile FROM tb_seo_acessos WHERE DATE(date) > '$dataVerificada' AND DATE(date) <= '$diaAnterior' GROUP BY session) sub GROUP BY date ORDER BY date");
            if ($visitas->num_rows) {
                foreach ($visitas->rows as $row) {
                    $insertData[] = "('$row->date', $row->visitas, $row->desktop, $row->tablet, $row->mobile)";
                }

                if (isset($insertData)) {
                    $query = $this->mysqli->query("INSERT INTO tb_dashboards_visitas (date,visitas,desktop,tablet,mobile) VALUES " . implode(',', $insertData));
                }
                unset($insertData);
            }

            $utms = $this->DB_fetch_object("SELECT *, COUNT(session) visitas, COUNT(cadastro) cadastros, COUNT(contato) contatos FROM (SELECT DATE(date) date, 
                CONCAT('utm_source=',IFNULL(A.utm_source, ''),'&utm_medium=',IFNULL(A.utm_medium, ''),'&utm_term=',IFNULL(A.utm_term, ''),'&utm_content=',IFNULL(A.utm_content, ''),'&utm_campaign=',IFNULL(A.utm_campaign, '')) utm,
                A.utm_source, A.utm_medium, A.utm_term, A.utm_content, A.utm_campaign,
                A.session, CASE WHEN SUM(B.cadastro) > 0 THEN 1 END cadastro, CASE WHEN SUM(B.contato) > 0 THEN 1 END contato
                FROM tb_seo_acessos A LEFT JOIN (SELECT cadastro, contato, session FROM tb_seo_acessos) B ON B.session = A.session
                WHERE A.utm_source != '' AND DATE(A.date) > '$dataVerificada' AND DATE(A.date) <= '$diaAnterior'
                GROUP BY A.session) B GROUP BY date, utm");
            if ($utms->num_rows) {
                foreach ($utms->rows as $row) {
                    $insertData[] = "('$row->date','$row->utm_source','$row->utm_medium','$row->utm_term','$row->utm_content','$row->utm_campaign', $row->visitas, $row->cadastros, $row->contatos)";
                }
                if (isset($insertData)) {
                    $query = $this->mysqli->query("INSERT INTO tb_dashboards_utms (date,utm_source,utm_medium,utm_term,utm_content,utm_campaign,visitas,cadastros,contatos) VALUES " . implode(',', $insertData));
                }
                unset($insertData);
            }


            $organicos = $this->DB_fetch_object("SELECT date, COUNT(session) visitas , COUNT(cadastro) cadastros, COUNT(contato) contatos FROM (SELECT DATE(date) date, 
                CONCAT('utm_source=',IFNULL(A.utm_source, ''),'&utm_medium=',IFNULL(A.utm_medium, ''),'&utm_term=',IFNULL(A.utm_term, ''),'&utm_content=',IFNULL(A.utm_content, ''),'&utm_campaign=',IFNULL(A.utm_campaign, '')) utm,
                A.utm_source, A.utm_medium, A.utm_term, A.utm_content, A.utm_campaign,
                A.session, CASE WHEN SUM(B.cadastro) > 0 THEN 1 END cadastro, CASE WHEN SUM(B.contato) > 0 THEN 1 END contato
                FROM tb_seo_acessos A LEFT JOIN (SELECT cadastro, contato, session FROM tb_seo_acessos) B ON B.session = A.session
                WHERE A.utm_source = '' AND DATE(A.date) > '$dataVerificada' AND DATE(A.date) <= '$diaAnterior' AND A.session NOT IN
               (SELECT session FROM tb_seo_acessos WHERE utm_source != '')
                GROUP BY A.session) B GROUP BY date, utm");
            if ($organicos->num_rows) {
                foreach ($organicos->rows as $row) {
                    $insertData[] = "('$row->date',$row->visitas, $row->cadastros, $row->contatos)";
                }
                if (isset($insertData)) {
                    $query = $this->mysqli->query("INSERT INTO tb_dashboards_utms (date,visitas,cadastros,contatos) VALUES " . implode(',', $insertData));
                }
                unset($insertData);
            }



            if ($paginas->num_rows || $visitas->num_rows || $utms->num_rows || $organicos->num_rows) {
                $this->DB_update("tb_dashboards_configuracoes", " data = '$diaAnterior 23:59:59' WHERE origem = 'general'");
                $this->DB_delete("tb_seo_acessos", " DATE_FORMAT(date, '%Y-%m-%d') <= '$diaAnterior' ");
            }
        }
    }

    /*
     * RETORNA ALFANUMERICO
     */

    public function uniqueAlfa($length = 16)
    {
        $salt = "abcdefghijklmnopqrstuvwxyz0123456789";
        $len = strlen($salt);
        $pass = '';
        mt_srand(10000000 * (float) microtime());
        for ($i = 0; $i < $length; $i++) {
            $pass .= $salt[mt_rand(0, $len - 1)];
        }
        return $pass;
    }

    /**
     * ATUALIZA STATUS E-MAILS E-MAIL MARKETING
     */
    public function updateEmailsOfEmailMarketing()
    {
        $query = $this->DB_fetch_array("SELECT a.id_email, a.state, b.id_cliente FROM tb_disparos_disparos_has_tb_emails_emails a INNER JOIN tb_disparos_disparos b ON b.id = a.id_disparo WHERE a.state IS NOT NULL");
        if ($query->num_rows) {
            foreach ($query->rows as $email) {
                $this->DB_update("tb_emails_emails_has_tb_cadastros_clientes", "state = '{$email['state']}' WHERE id_email = {$email['id_email']} AND id_cliente = {$email['id_cliente']}");
            }
        }

        $query = $this->DB_fetch_array("SELECT a.id_email, a.state FROM tb_disparos_disparos_has_tb_emails_emails a WHERE state IS NOT NULL AND state != 'sent' AND state != 'soft-bounced'");
        if ($query->num_rows) {
            foreach ($query->rows as $email) {
                $this->DB_update("tb_emails_emails", "state = 'locked-system' WHERE id = {$email['id_email']}");
            }
        }

        $query = $this->DB_fetch_array("SELECT a.id_email, a.state FROM tb_emails_emails_has_tb_cadastros_clientes a WHERE state IS NOT NULL AND state != 'sent' AND state != 'soft-bounced'");
        if ($query->num_rows) {
            foreach ($query->rows as $email) {
                $this->DB_update("tb_emails_emails", "state = 'locked-system' WHERE id = {$email['id_email']}");
            }
        }
    }

    /**
     * VERIFICA SE DOMÍNIO DO E-MAIL EXISTE     
     */
    public function verifyDomainOfEmail($EMail)
    {
        list($User, $Domain) = explode("@", $EMail);

        if (in_array($Domain, $this->_dominios)) {
            return true;
        } else if (@checkdnsrr($Domain, 'MX')) {
            $this->_dominios[] = $Domain;
            return true;
        } else {
            return false;
        }
    }

    public function verifyBeforeDomain($eMail)
    {
        if (stristr($eMail, "@")) {
            $eMail = strstr($eMail, '@', true);

            if (stristr($eMail, " ")) {
                return false;
            } else if (stristr($eMail, ",")) {
                return false;
            } else {
                return true;
            }
        } else {
            return false;
        }
    }

    /**
     * 
     * GERA BACKUP DO BANCO DE DADOS
     */
    public function DB_backup()
    {
        $dir = "backups"; //diretorio dos backups
        $dir_backups = dirname(dirname(dirname(__FILE__))) . "/" . $dir;

        //APAGA BACKUPS COM MAIS DE 7 DIAS
        $antigos = $this->DB_fetch_array("SELECT *, DATE_FORMAT(data, '%d/%m/%Y') data FROM $this->_dbmain.tb_admin_backups WHERE DATE(data) < DATE_SUB(CURDATE(), INTERVAL 7 DAY)");
        if ($antigos->num_rows) {
            foreach ($antigos->rows as $antigo) {
                unlink(dirname(dirname(dirname(__FILE__))) . "/" . $antigo['arquivo']);
                unlink(dirname(dirname(dirname(__FILE__))) . "/" . str_replace('.php', '.sql', $antigo['arquivo']));
                unlink(dirname(dirname(dirname(__FILE__))) . "/" . str_replace('.php', '.zip', $antigo['arquivo']));
                $this->DB_delete("tb_admin_backups", "id={$antigo['id']}");
                $this->inserirRelatorio("Apagou backup database antigo: [{$antigo['data']}], id: [{$antigo['id']}]");
            }
        }

        //APAGA BACKUPS SQL COM MAIS DE 1 DIA
        $antigos = $this->DB_fetch_array("SELECT *, DATE_FORMAT(data, '%d/%m/%Y') data FROM $this->_dbmain.tb_admin_backups WHERE DATE(data) < DATE(CURDATE())");
        if ($antigos->num_rows) {
            foreach ($antigos->rows as $antigo) {
                $antigo['arquivo'] = str_replace(".php", ".sql", $antigo['arquivo']);
                if (file_exists(dirname(dirname(dirname(__FILE__))) . "/" . $antigo['arquivo'])) {
                    unlink(dirname(dirname(dirname(__FILE__))) . "/" . $antigo['arquivo']);
                    unlink(dirname(dirname(dirname(__FILE__))) . "/" . str_replace('.php', '.sql', $antigo['arquivo']));
                    unlink(dirname(dirname(dirname(__FILE__))) . "/" . str_replace('.php', '.zip', $antigo['arquivo']));
                    $this->inserirRelatorio("Apagou arquivo sql de backups: [{$antigo['arquivo']}]");
                }
            }
        }

        //CRIA DIRETÓRIO SE NÃO EXISTIR
        if (!file_exists("$dir_backups")) {
            mkdir($dir_backups, 0755);
            $abre = fopen("$dir_backups/.htaccess", "w");
            fwrite($abre, "Options -Indexes");
        }


        for ($i = 0; $i < count($this->dbs); $i++) {

            $db = $this->dbs[$i];
            $this->$db();

            $arquivo = $db . "_" . uniqid() . ".php"; //define nome do arquivo

            $abre = fopen("$dir_backups/" . $arquivo, "w"); // nome do arquivo que será salvo o backup

            $sql1 = $this->DB_fetch_array("SHOW TABLES");
            if ($sql1->num_rows) {
                fwrite($abre, "<?php\n/*-\n");
                fwrite($abre, "           
            -- --------------------------------------------------------
            -- Servidor: $this->db_host
            -- --------------------------------------------------------

            /*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
            /*!40101 SET NAMES utf8 */;
            /*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
            /*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;

            -- Copiando estrutura do banco de dados para $this->db_database
            CREATE DATABASE IF NOT EXISTS `$this->db_database` /*!40100 DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci */;
            USE `$this->db_database`;
            ");

                foreach ($sql1->rows as $ver) {
                    $tabela = $ver["Tables_in_$this->db_database"];
                    $sql2 = $this->DB_fetch_array("SHOW CREATE TABLE $tabela");
                    if ($sql2->num_rows) {
                        foreach ($sql2->rows as $ver2) {
                            fwrite($abre, "-- Criando tabela: $tabela\n");
                            $pp = fwrite($abre, "{$ver2['Create Table']};\n\n-- Salva os dados\n");
                            $sql3 = $this->DB_fetch_array("SELECT * FROM $tabela");
                            if ($sql3->num_rows) {
                                foreach ($sql3->rows as $ver3) {
                                    $grava = "INSERT INTO $tabela VALUES ('";
                                    $grava .= implode("', '", $ver3);
                                    $grava .= "');\n";
                                    fwrite($abre, $grava);
                                }
                            }
                            fwrite($abre, "\n\n");
                        }
                    }
                }
                fwrite($abre, "\n-*/\n");
            }
            $finaliza = fclose($abre);

            //SE GERAR BACKUP GRAVA NO BANCO DE DADOS
            if ($finaliza) {
                $this->DB_insert("$this->_dbmain.tb_admin_backups", "arquivo", "'main/$dir/$arquivo'");
                $this->inserirRelatorio("Gerou backup database: [" . date("d-m-Y") . "], arquivo: [$arquivo]");
            }
        }

        return $finaliza;
    }

    /*
     * IMPRIME VALORES DAS VARIÁVEIS
     */

    public static function printVars($_data, $_type = false)
    {
        try {
            if ($_type === false) {
                echo "<pre>";
                print_r($_data);
                echo "</pre>";
            }
        } catch (\Exception $ex) {
            return $ex->getMessage();
        }
    }

    public function renderView($module = 'main', $component = 'dashboard', $view = 'index', $param = array())
    {
        header('Content-type: text/html');
        $caminho = "environment/$module/module/$component/view/$view.phtml";

        if ($this->file_exists_ci($caminho)) {
            require_once $this->file_exists_ci($caminho);
        }
        else {
            require_once $this->main_layouts . "404.phtml";
        }
    }

    public function renderAjax($module = 'main', $component = 'dashboard', $ajax = 'index', $param = array())
    {
        $existe = false;
        $caminho = "environment/$module/module/$component/ajax/$ajax.phtml";

        if ($this->file_exists_ci($caminho)) {
            require_once $this->file_exists_ci($caminho);
            $existe = true;
        }

        if (!$existe) {
            require_once $this->main_layouts . "404.phtml";
        }
    }

    public function renderExport($module = 'main', $component = 'dashboard', $export = 'index', $param = array())
    {
        $existe = false;
        $caminho = "environment/$module/module/$component/export/$export.phtml";

        if ($this->file_exists_ci($caminho)) {
            require_once $this->file_exists_ci($caminho);
            $existe = true;
        }

        if (!$existe) {
            require_once $this->main_layouts . "404.phtml";
        }
    }

    public function noPermission($render = true)
    {
        if ($render === true)
            require_once $this->main_layouts . "no-permission.phtml";
        else
            require_once $this->main_layouts . "no-permission-no-render.phtml";
    }

    public function custompermissions($permission, $description){
        try {
            if($_SESSION['admin_grupo'] == 2) return true;
            return $this->permissions[$permission]['custom'][$description] === '1';
        } catch(Exception $ex) {
            return false;
        }
    }

    public function translate($variable, $value = null, $lang = "pt-br")
    {
        $language = array(
            "pt-br" => array(
                "metodo_nao_existe" => "Lamento, o método <b>$value</b> não existe!",
                "voltar" => "voltar"
            ),
            "en" => array(
                "metodo_nao_existe" => "Sorry, the <b>$value</b> method does not exist!",
                "voltar" => "go back"
            ),
        );

        if (isset($language[$lang][$variable]))
            return $language[$lang][$variable];
        else
            return $variable;
    }

    public function file_exists_ci($file)
    {
        if (file_exists($file)) {
            return $file;
        }
        $lowerfile = strtolower($file);

        foreach (glob(dirname($file) . '/*') as $file) {
            if (strtolower($file) == $lowerfile) {
                return $file;
            }
        }
        return false;
    }

    public function trataTexto($string = null)
    {
        if ($string != null) {
            $string = str_replace('src="../', 'src="', $this->stripslashes($string));
        }

        return $string;
    }

    public function stripslashes($string = null)
    {
        if ($string != null) {
            $string = stripslashes(stripslashes($string));
        }

        return $string;
    }

    public function uploadFilePostMax()
    {
        return ini_get('post_max_size');
    }

    public function uploadFileMax()
    {
        return ini_get('upload_max_filesize');
    }

    public function rowsHistorico($string = null, $indices = null, $firstDateUsa = false)
    {
        $rows = array();
        if ($string != null and $indices != null) {
            $string = trim($string);

            foreach (preg_split("/((\r?\n)|(\r\n?))/", $string) as $line) {
                $campos = explode(';', $line);
                $linhas = array();
                $indice = explode(',', $indices);
                $i = 0;

                foreach ($campos as $row) {
                    if (!empty($row)) {
                        $linhas[$indice[$i]] = $row;
                        $i++;
                    }
                }
                if (!empty($linhas)) {
                    if ($firstDateUsa) {
                        $aux = explode(' ', $linhas[$firstDateUsa]);
                        $comp = (isset($aux[1])) ? " " . $aux[1] : "";
                        $aux = explode('/', $aux[0]);
                        $dia = $aux[0];
                        $mes = $aux[1];
                        $ano = $aux[2];
                        $linhas['dateusa'] = "$ano-$mes-$dia$comp";
                    }
                    $rows[] = $linhas;
                }
            }
        }
        return $rows;
    }

    public function createSession($inp = null, $path = null, &$session = null)
    {
        if (($num_elements = count($inp)) > 0 && $inp != null && $path != null) {
            $this->deleteSession($path);
            foreach ($inp as $key => $value) {
                if (is_array($value)) {
                    $path_session = array();
                    $s;
                    if (is_array($path)) {
                        $path_session = $path;
                        $session[$key] = array();
                        $s = &$session[$key];
                    } else {
                        if ($session === null) {
                            $_SESSION[$path][$key] = array();
                            $s = &$_SESSION[$path][$key];
                        }
                    }
                    array_push($path_session, $key);
                    $this->createSession($value, $path_session, $s);
                } else {
                    if (is_array($path)) {
                        $path_session = $path;
                        array_push($path_session, $key);
                        $session[$key] = $value;
                    } else {
                        $_SESSION[$path][$key] = $value;
                    }
                }
            }
        }
    }

    public function deleteSession($key = null)
    {
        static $result = false;

        if ($result !== false)
            return $result;

        if (!is_array($key)) {
            if (isset($_SESSION[$key])) {
                unset($_SESSION[$key]);
                $result = true;
            }
        }
    }

    public function getIndices($grupo = null)
    {
        if ($grupo != null) {
            $where = " WHERE grupo = $grupo ORDER BY ordem ";
        } else {
            $where = " ORDER BY grupo, ordem ";
        }

        $query = $this->DB_fetch_array("SELECT * FROM hbrd_main_index $where");

        if ($query->num_rows)
            return $query->rows;
    }

    // RETORNA UMAS STRING 'X HORAS E Y MINUTOS' OU 'Y MINUTOS'
    public function formataMinutos($minutos)
    {
        $horas = floor($minutos / 60);
        /* TEM Q INCREMENTAR QUANDO DER NEGATIVO POIS A FUNCAO FLOOR ARREDONDA PARA BAIXO, ENTAO -2.5 VAI PARA -3, NÃO -2 COMO DESEJADO */
        if ($horas != ($minutos / 60) and $horas < 0)
            $horas++;
        if ($horas != 0)
            return $horas . " hora" . (($horas == 1) ? "" : "s") . " e " . abs($minutos % 60) . " minutos";
        else
            return round($minutos) . " minutos";
    }

    // RETORNA O IP DO USUÁRIO
    public function getIP()
    {
        $ip = "";
        if (getenv('HTTP_CLIENT_IP'))
            $ip = getenv('HTTP_CLIENT_IP');
        else if (getenv('HTTP_X_FORWARDED_FOR'))
            $ip = getenv('HTTP_X_FORWARDED_FOR');
        else if (getenv('HTTP_X_FORWARDED'))
            $ip = getenv('HTTP_X_FORWARDED');
        else if (getenv('HTTP_FORWARDED_FOR'))
            $ip = getenv('HTTP_FORWARDED_FOR');
        else if (getenv('HTTP_FORWARDED'))
            $ip = getenv('HTTP_FORWARDED');
        else if (getenv('REMOTE_ADDR'))
            $ip = getenv('REMOTE_ADDR');
        else
            $ip = 'DESCONHECIDO';
        return $ip;
    }

    // RETORNA UM HORÁRIO COM BASE EM UMA ENTRADA DE MINUTOS
    public function getHourFromMinutes($minutes)
    {
        return str_pad(floor($minutes / 60), 2, '0', STR_PAD_LEFT) . ":" . str_pad($minutes % 60, 2, '0', STR_PAD_LEFT);
    }

    // RETORNA QTD DE MINUTOS PRESENTE EM UM HORÁRIO
    public function getMinutes($time = false)
    {
        if ($time == "")
            return 0;
        $minutes = 0;
        $time = explode(":", $time);
        $minutes += $time[0] * 60 + $time[1];
        if (isset($time[2])) {
            $minutes += $time[3] / 60;
        }
        return $minutes;
    }

    // RETORNA DIA DA SEMANA EM PT-BR, PODE-SE PASSAR UMA DATA POR PARAMENTRO OU Ñ, RETORNANDO DIA ATUAL
    public function getDay($date = false)
    {
        $dias = array(null, "Segunda-Feira", "Terça-Feira", "Quarta-Feira", "Quinta-Feira", "Sexta-Feira", "Sábado", "Domingo");
        $timestamp = ($date) ? strtotime($date) : time();
        return $dias[date("N", $timestamp)];
    }

    /**
     * 
     * @param type $id
     * @return type String
     * @access public
     * retorna nome de categoria e subcategorias separado por barra
     * @example Receita / Site
     */
    public function getCategoriaNome($id = null, $table = null)
    {
        $cat = "";
        if ($id != null and $table != null) {
            $query = $this->DB_fetch_array("SELECT id_pai, nome FROM $table WHERE id = $id");
            if ($query->num_rows) {
                $categorias = $query->rows;

                foreach ($categorias as $categoria) {
                    $cat .= $categoria['nome'] . ' , ';
                    if ($categoria['id_pai']) {
                        $cat .= $this->getCategoriaNome($categoria['id_pai'], $table);
                    }
                }
            }

            $array = explode(",", $cat);
            $array = array_reverse($array);

            $cat = "";

            foreach ($array as $value) {
                $cat .= $value;
            }

            unset($query);

            return $cat . " / ";
        } else {
            return $cat;
        }
    }

    //CRIPTOGRAFIA PARA SENHA DE CONSULTORES E COMISSIONADOS

    public function generateHash($pass = null)
    {
        return hash("sha256", $this->salt . $pass);
    }

    public function permissions($permission, $identificador = 'ler')
    {
        try {
            if (!isset($this->permissions[$permission])) return false;
            return $this->permissions[$permission][$identificador];
        } catch (Exception $ex) {
            return false;
        }
    }
    
}
