<?php

date_default_timezone_set('America/Sao_Paulo');

DEFINE("__root_path__", dirname(dirname(dirname(__DIR__))) . DIRECTORY_SEPARATOR);
DEFINE("__sys_path__", dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR);
$dotenv = Dotenv\Dotenv::createImmutable(__root_path__);
$dotenv->load();

abstract class Config {
	protected $db_host = "";
	protected $db_user = "";
	protected $db_pwd = "";
	protected $db_database = "";
	protected $criptBase = "";
	public $root_path = "";
	public $system_path = "";
	public $upload_folder = "";
	public $upload_path = "";
	public $templates = "templates/";
	public $_cliente_;
	public $admin_indices = "hbrd_main_index";
	function __construct() {
		$protocol = "http://";
        if (array_key_exists('HTTPS', $_SERVER) && $_SERVER['HTTPS'] == 'on' || array_key_exists('HTTP_X_SCHEME', $_SERVER) && $_SERVER["HTTP_X_SCHEME"] == "https") {
            $protocol = "https://";
		}
		$this->root_path = $protocol.$_SERVER['HTTP_HOST']."/";
		if (null === @$_SERVER['HTTP_HOST']) $this->root_path = __root_path__;
		$this->_cliente_ = 'aupet';
		$this->system_path = $this->root_path . "sistema/";
		$this->main_template = '../main/' . $this->templates . "supradmin/";
		$this->main_layouts = __root_path__.'main/' . $this->templates . "supradmin/layouts/";
		$this->main_scripts = '../main/scripts/';
		$this->_dbmain = $this->dbMain();
		$this->upload_folder = "main/uploads/";
		$this->upload_path = "../main/uploads/";
		$this->_tb_prefix = "hbrd_";
		$this->logo_client = "/api/sistema/logosistema";
		$this->criptBase = "tmsg_solidy";
		$this->salt = "N0V4!";
	}
	public function dbMain() {
		$this->db_host = $_ENV['DB_HOST'];
		$this->db_user = $_ENV['DB_USERNAME'];
		$this->db_database = $_ENV['DB_DATABASE'];
		$this->db_pwd = $_ENV['DB_PASSWORD'];
		return $this->db_database;
	}
	//https://aupetheinsten.com.br/main/adminer.php?username=aupethei_hibrida&db=aupethei_aupet2021
	//senha: RgsF8[XOHm8M
	public function showLog($var) {
		echo "<pre>";
		echo var_dump($var);
		echo "</pre>";
	}
}