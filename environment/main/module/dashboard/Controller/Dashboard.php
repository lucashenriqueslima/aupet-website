<?php
use System\Base\{SistemaBase, BadRequest};
class __classe__ extends SistemaBase {
    public $module = "";
    public $permissao_ref = __classe__;
    public $custom = "";
    public $customTitle = "";
    function __construct() {
        parent::__construct();
    }
    protected function indexAction() {
        $this->module_icon = "icomoon-icon-screen-2";
        $this->module_link = $this->getPath();
        $this->module_title = "Painel";
        // ESTATISTICAS
        $this->indicacoes_pendentes = $this->DB_fetch_array("SELECT COUNT(id) count FROM hbrd_app_pet WHERE classificacao = 'pendente'  AND id_proposta IS NOT NULL")->rows[0]['count'];
        $this->indicacoes_arquivadas = $this->DB_fetch_array("SELECT COUNT(id) count FROM hbrd_app_pet WHERE classificacao = 'arquivada'  AND id_proposta IS NOT NULL")->rows[0]['count'];
        $this->indicacoes_ativadas = $this->DB_fetch_array("SELECT COUNT(id) count FROM hbrd_app_pet WHERE classificacao = 'ativada'  AND id_proposta IS NOT NULL")->rows[0]['count'];
        $this->conversao = ($this->indicacoes_ativadas / ($this->indicacoes_arquivadas + $this->indicacoes_pendentes + $this->indicacoes_ativadas) * 100) ?? 0;
        // GRAFICO
        $this->dados_grafico = $this->DB_fetch_array("SELECT DATE_FORMAT(create_at, '%d/%m') dia_mes, create_at, SUM(indicacoes) indicacoes FROM ( SELECT *, COUNT(create_at) indicacoes FROM (SELECT DATE_FORMAT(create_at, '%d/%m') dia_mes, create_at FROM hbrd_app_pet WHERE DATE(create_at) BETWEEN DATE_SUB(CURDATE(), INTERVAL 30 DAY) AND CURDATE() ) sub GROUP BY dia_mes ORDER BY create_at ) tb_tabela GROUP BY create_at");
        $this->dados_acesso_page = $this->DB_fetch_array("SELECT DATE_FORMAT(date, '%d/%m') dia_mes, date , SUM(acessos) acessos FROM 
        ( SELECT *, COUNT(date) acessos FROM (SELECT DATE_FORMAT(date, '%d/%m') dia_mes, date 
        FROM hbrd_cms_seo_acessos WHERE DATE(date) BETWEEN DATE_SUB(CURDATE(), INTERVAL 30 DAY) AND CURDATE() AND 
        origem NOT LIKE '%sistema%' AND origem NOT LIKE '%main%' AND origem NOT LIKE '%wp-login%' ) sub GROUP BY dia_mes ORDER BY date) tb_tabela
        GROUP BY  date");

        $this->renderView($this->getService(), $this->getModule(), "index");
    }
    protected function chartAction() {
        if (!$this->permissions[$this->permissao_ref]['ler']) $this->noPermission(false);
        $de = "'{$_POST['de']}'";
        $ate = "'{$_POST['ate']}'";
        $this->alvo = "indicacoes";
        if (isset($_POST['alvo'])) $this->alvo = $_POST['alvo'];
        $this->dados_grafico = $this->DB_fetch_array(" SELECT DATE_FORMAT(create_at, '%d/%m') dia_mes, create_at, SUM(indicacoes) indicacoes FROM ( SELECT *, COUNT(create_at) indicacoes FROM (SELECT DATE_FORMAT(create_at, '%d/%m') dia_mes, create_at FROM hbrd_app_proposta WHERE DATE(create_at) >= $de AND DATE(create_at) <= $ate ) sub GROUP BY dia_mes ORDER BY create_at ) tb_tabela GROUP BY create_at");
        if ($this->dados_grafico->num_rows) $this->renderAjax($this->getService(), $this->getModule(), "chart");
        else echo "<div class='well' style='color:black'>$this->_sem_dados</div>";
    }

    protected function chartAcessosAction(){
        if (!$this->permissions[$this->permissao_ref]['ler']) $this->noPermission(false);
        $de = "'{$_POST['de']}'";
        $ate = "'{$_POST['ate']}'";
        $this->alvo = "acessos";
        if (isset($_POST['alvo'])) $this->alvo = $_POST['alvo'];
        $this->dados_acesso_page = $this->DB_fetch_array("SELECT DATE_FORMAT(date, '%d/%m') dia_mes, date , SUM(acessos) acessos FROM 
        ( SELECT *, COUNT(date) acessos FROM (SELECT DATE_FORMAT(date, '%d/%m') dia_mes, date 
        FROM hbrd_cms_seo_acessos WHERE DATE(date) >= $de  AND  DATE(date) <= $ate AND 
        origem NOT LIKE '%sistema%' AND origem NOT LIKE '%main%' AND origem NOT LIKE '%wp-login%' ) sub GROUP BY dia_mes ORDER BY date) tb_tabela
        GROUP BY  date");
        if ($this->dados_acesso_page->num_rows) $this->renderAjax($this->getService(), $this->getModule(), "chartAcessos");
        else echo "<div class='well' style='color:black'>$this->_sem_dados</div>";
    }
    /* =================================================== */
    protected function dadosOrigensAction() {
        $queryDt = "";
        if((bool)$_GET["de"] && (bool)$_GET["ate"]) {
            $datade = implode('-', array_reverse(explode('/',$_GET['de'])));
            $dataate = implode('-', array_reverse(explode('/',$_GET['ate'])));
            $queryDt = "AND DATE_FORMAT(A.create_at, '%Y-%m-%d') >= '{$datade}' AND DATE_FORMAT(A.create_at, '%Y-%m-%d') <= '{$dataate}'";
        }
        $this->indicacoes_total = $this->DB_fetch_array("SELECT COUNT(A.id) AS total FROM hbrd_app_proposta A where A.id is not null $queryDt")->rows[0]['total'];
        $this->origens_indicacoes = $this->DB_fetch_array("SELECT IFNULL(B.nome, 'Não definido') AS nome, COUNT(A.id) AS total, B.id FROM hbrd_app_proposta A LEFT JOIN hbrd_adm_indication_source B ON B.id = A.id_origem where A.id is not null  $queryDt GROUP BY nome ORDER BY total DESC")->rows;
        foreach ($this->origens_indicacoes as &$row) {
            $row['id'] = (!(bool)$row['id']) ? 'is null' : "= {$row['id']}";
            $row['situacoes'] = $this->DB_fetch_array("SELECT COUNT(*) cnt, B.nome FROM hbrd_app_proposta A LEFT JOIN hbrd_app_proposta_status B ON B.id = A.id_status WHERE A.id_origem {$row['id']} $queryDt GROUP BY A.id_status")->rows;
        }
        $this->origens_indicacoes[0]['show'] = 1;
        // echo json_encode(['total' => $this->indicacoes_total, 'origens' => $this->origens_indicacoes]);
    }
    protected function dadosVistoriasAction() {
        $queryDt = "";
        if((bool)$_GET["de"] && (bool)$_GET["ate"]) {
            $datade = implode('-', array_reverse(explode('/',$_GET['de'])));
            $dataate = implode('-', array_reverse(explode('/',$_GET['ate'])));
            $queryDt = "AND DATE_FORMAT(C.activated_at, '%Y-%m-%d') >= '{$datade}' AND DATE_FORMAT(C.activated_at, '%Y-%m-%d') <= '{$dataate}'";
        }
        $total['geral'] = (int)$this->DB_fetch_array("SELECT COUNT(A.id) total FROM hbrd_adm_inspection A LEFT JOIN hbrd_adm_indication C ON C.id_inspection = A.id WHERE C.classificacao = 'Ativada' AND C.id_inspection IS NOT NULL $queryDt")->rows[0]['total'];
        $externa = $this->DB_fetch_array("SELECT B.descricao, COUNT(A.id) cnt FROM hbrd_adm_inspection A LEFT JOIN hbrd_adm_inspection_model B ON B.id = A.id_model LEFT JOIN hbrd_adm_indication C ON C.id_inspection = A.id WHERE A.hash IS NOT NULL AND C.classificacao = 'Ativada' $queryDt GROUP BY B.id ORDER BY cnt DESC")->rows;
        $total['externa']  = array_sum(array_column($externa, 'cnt'));
        $vistoriador = $this->DB_fetch_array("SELECT B.descricao, COUNT(A.id) cnt FROM hbrd_adm_inspection A LEFT JOIN hbrd_adm_inspection_model B ON B.id = A.id_model LEFT JOIN hbrd_adm_indication C ON C.id_inspection = A.id WHERE A.id_inspector IS NOT NULL AND C.classificacao = 'Ativada' $queryDt GROUP BY B.id ORDER BY cnt DESC")->rows;
        $total['vistoriador']  = array_sum(array_column($vistoriador, 'cnt'));
        $consultor = $this->DB_fetch_array("SELECT B.descricao, COUNT(A.id) cnt FROM hbrd_adm_inspection A LEFT JOIN hbrd_adm_inspection_model B ON B.id = A.id_model LEFT JOIN hbrd_adm_indication C ON C.id_inspection = A.id WHERE A.hash IS NULL AND A.id_inspector IS NULL AND C.classificacao = 'Ativada' $queryDt GROUP BY B.id ORDER BY cnt DESC")->rows;
        $total['consultor']  = array_sum(array_column($consultor, 'cnt'));
        echo json_encode([ 'total' => $total, 'consultor' => $consultor, 'externa' => $externa, 'vistoriador' => $vistoriador]);
    }
    protected function dadosTermosAction() {
        $queryDt = "";
        if((bool)$_GET["de"] && (bool)$_GET["ate"]) {
            $datade = implode('-', array_reverse(explode('/',$_GET['de'])));
            $dataate = implode('-', array_reverse(explode('/',$_GET['ate'])));
            $queryDt = "AND DATE_FORMAT(A.activated_at, '%Y-%m-%d') >= '{$datade}' AND DATE_FORMAT(A.activated_at, '%Y-%m-%d') <= '{$dataate}'";
        }
        $total['geral'] = (int)$this->DB_fetch_array("SELECT COUNT(A.id) total FROM hbrd_adm_indication A WHERE A.classificacao = 'Ativada' $queryDt")->rows[0]['total'];
        $digital = $this->DB_fetch_array("SELECT B.descricao, COUNT(A.id) cnt FROM hbrd_adm_indication A  
        LEFT JOIN hbrd_adm_indication_termo C ON C.id_indicacao = A.id
        LEFT JOIN hbrd_app_termo B ON B.id = C.id_contrato
        WHERE C.assinatura IS NOT NULL AND A.classificacao = 'Ativada' $queryDt GROUP BY B.id")->rows;
        $total['digital']  = array_sum(array_column($digital, 'cnt'));
        $total['manuscrito'] = (int)$this->DB_fetch_array("SELECT COUNT(A.id) total FROM hbrd_adm_indication A
        LEFT JOIN hbrd_adm_indication_termo C ON C.id_indicacao = A.id
         WHERE A.classificacao = 'Ativada' AND C.assinatura IS NULL $queryDt")->rows[0]['total'];
        echo json_encode([ 'total' => $total, 'digital' => $digital]);
    }
    protected function dadosArquivamentoAction() {
        $queryDt = "";
        if((bool)$_GET["de"] && (bool)$_GET["ate"]) {
            $datade = implode('-', array_reverse(explode('/',$_GET['de'])));
            $dataate = implode('-', array_reverse(explode('/',$_GET['ate'])));
            $queryDt = "AND DATE_FORMAT(A.create_at, '%Y-%m-%d') >= '{$datade}' AND DATE_FORMAT(A.create_at, '%Y-%m-%d') <= '{$dataate}'";
        }
        $total = (int)$this->DB_fetch_array("SELECT COUNT(*) total FROM hbrd_app_proposta A WHERE A.id_motivo IS NOT NULL AND A.classificacao = 'arquivada' AND A.id_motivo is not null $queryDt")->rows[0]['total'];
        $motivos = $this->DB_fetch_array("SELECT COUNT(A.id) total, B.nome FROM hbrd_app_proposta A LEFT JOIN hbrd_app_motive B ON A.id_motivo = B.id WHERE A.id_motivo IS NOT NULL AND A.classificacao = 'arquivada' $queryDt GROUP BY B.id ORDER BY total DESC")->rows;
        echo json_encode(['total' => $total, 'motivos' => $motivos]);
    }
    
}
define("__service__", strtolower(str_replace(dirname(dirname(dirname(dirname(__DIR__)))) . DIRECTORY_SEPARATOR, "", dirname(dirname(dirname(__DIR__))))));
define("__classe__", strtolower(str_replace(".php", "", basename(__FILE__))));
$class = new __classe__();
$class->setAction();