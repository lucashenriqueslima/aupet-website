<?php
// if($_SERVER['HTTP_HOST']) die('not authorized');
require_once 'main/System/Core/Loader.php';
require __DIR__.'/vendor/autoload.php';
ini_set('memory_limit', '-1');
ini_set('max_execution_time', 0);
use System\Base\SistemaBase;
use adm\classes\{Util};
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();
// nohup php script.php > script.html 2>&1 &
class Script extends SistemaBase {
    function __construct() {
        parent::__construct();
    }
    private function run() {
        $items = $this->DB_fetch_array("SELECT * FROM hbrd_app_pessoa limit 100");
        $nomes = ["Izabella","Clarinha","Fernando","Zaira","Marcelino","Rose","Gilberto","Vanessa","Nikol","Mauri","Mariana","Isaque","Adelina","Natanael","Aryana","Eli","Bárbara","Francesco","Atílio","Evelina","Elsa","Thayra","Nicolae","Ming","Oziel","Núria","Luena","Mario","Ayani","Florbela","Mélanie","Iris","Cátia","Luke","Aliça","Hao","Jael","Camila","Daniel","Daisi","Viktoria","Eliane","Aylla","Xavier","Jordan","Malika","Júlio","Nikolas","Keyson","Martinho","Rahela","Russell","Kenny","Guilherme","Estela","Leandro","Adrien","Fabiana","Samuel","Kael","Francisca","Cassandra","Tom","Aminata","Erica","Antônio","Íris","Suzana","Nathaniel","Érico","Adélia","Louis","Lev","Alcino","Ndeye","Inaaya","Deivid","Kyle","Josefina","Jaden","Milena","íris","Marisol","Alberto","Azael","Malak","Maitê","Pérola","Valentino","Christian","Séfora","Kelvin","Jadir","Neide","Samanta","Andrei","Michael","Elzira","Bruna","Verónica"];
        foreach ($items->rows as $key => $row) {
            $this->DB_update('hbrd_app_pessoa',['nome'=> $nomes[$key]],' where id = '.$row['id']);
        }
    }
    public function setAction() {
        try {
            echo date("Y-m-d H:i:s").'<br>';
            $this->mysqli = new \mysqli($this->db_host, $this->db_user, $this->db_pwd, $this->db_database);
            if ($this->mysqli->connect_errno) throw new \Exception($this->mysqli->connect_error);
            $this->mysqli->autocommit(false);
            mysqli_set_charset($this->mysqli, "utf8");
            $this->run();
            // $this->mysqli->rollback();
            $this->mysqli->commit();
        } catch(\Throwable $e) {
            $this->mysqli->rollback();
            echo 'ERROR: '.$e->getMessage();
        } finally {
            echo '<br>finalizado';
            $this->mysqli->close();
            echo '<br>'.date("Y-m-d H:i:s");
        }
    }
}
$script = new Script();
$script->setAction();