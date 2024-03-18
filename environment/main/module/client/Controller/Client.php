<?php
use System\Base\{SistemaBase, BadRequest};
class __classe__ extends SistemaBase {
    public $module = "";
    public $permissao_ref = 'client';
    public function __construct() {
        parent::__construct();
        $this->table = "hbrd_main_cliente";
        $this->module_title = "Clientes";
        $this->module_icon = "icomoon-icon-users";
        $this->module_link = $this->getPath();
        $this->retorno = $this->getPath();
    }
    protected function indexAction() {
        if (!$this->permissions[$this->permissao_ref]['ler']) $this->noPermission();
        $this->pendentes = $this->DB_fetch_array("SELECT DISTINCT hbrd_main_cliente_modulo.*, COALESCE(nome_fantasia, empresa, nickname) AS nickname, hbrd_main_modulos.nome, TIMESTAMPDIFF(DAY, NOW(), hbrd_main_cliente_modulo.data_vencimento) FROM nova_adm.hbrd_main_cliente_modulo 
        LEFT JOIN nova_adm.hbrd_main_cliente ON hbrd_main_cliente_modulo.id_cliente = hbrd_main_cliente.id
        INNER JOIN nova_adm.hbrd_main_modulos ON hbrd_main_cliente_modulo.id_modulo = hbrd_main_modulos.id
        WHERE TIMESTAMPDIFF(DAY, NOW(), hbrd_main_cliente_modulo.data_aviso_renovacao) <= 30 AND TIMESTAMPDIFF(DAY, NOW(), hbrd_main_cliente_modulo.data_aviso_renovacao) > 0");
        $this->total = $this->DB_fetch_array("SELECT COUNT(*) AS total  FROM {$this->table}")->rows[0]['total'];
        $this->actives = $this->DB_fetch_array("SELECT COUNT(*) AS total FROM {$this->table} WHERE stats = 1")->rows[0]['total'];
        $this->inactives = $this->DB_fetch_array("SELECT COUNT(*) AS total FROM {$this->table} WHERE stats = 0")->rows[0]['total'];
        $this->modulos = $this->DB_fetch_array("SELECT id, nome FROM nova_adm.hbrd_main_modulos ORDER BY nome");
        $this->list = $this->DB_fetch_array("SELECT *, DATE_FORMAT(registrado_em, '%d/%m/%Y') registrado_em_ FROM {$this->table}");
        $this->renderView($this->getService(), $this->getModule(), "index");
    }
    protected function createAction() {
        if (!$this->permissions[$this->permissao_ref]['gravar']) $this->noPermission();
        $this->estados = $this->DB_fetch_array("SELECT * FROM hbrd_main_util_state");
        $this->renderView($this->getService(), $this->getModule(), "create");
    }
    protected function editAction() {
        $this->id = $this->getParameter("id");
        if (!$this->permissions[$this->permissao_ref]['editar']) $this->noPermission();
        $this->registro = $this->DB_fetch_array("SELECT * FROM {$this->table} WHERE id = {$this->id}")->rows[0];
        $this->lancamentos = $this->DB_fetch_array("SELECT * FROM hbrd_main_lancamentos WHERE id_cliente = {$this->id} ORDER BY criado_em");
        $this->anexos = $this->DB_fetch_array("SELECT A.*, U.nome AS usuario FROM hbrd_main_cliente_anexos A INNER JOIN hbrd_main_usuarios U ON U.id = A.id_usuario WHERE A.id_cliente = {$this->id} ORDER BY A.registrado_em DESC");
        $this->members = $this->DB_fetch_array("SELECT * FROM hbrd_main_cliente_membros WHERE id_cliente = {$this->id}");
        foreach ($this->DB_fetch_array("SELECT A.identificador FROM nova_function A INNER JOIN nova_function_cliente B ON B.id_funcao = A.id WHERE B.id_cliente = {$this->id}")->rows as $row) {
            $this->permission[$row['identificador']] = 1;
        }
        $this->modulos = $this->DB_fetch_array("SELECT hbrd_main_modulos.*,
        (SELECT id_cliente FROM nova_adm.hbrd_main_cliente_modulo WHERE id_cliente = {$this->id} AND id_modulo = hbrd_main_modulos.id) as id_cliente,
        (SELECT id_modulo FROM nova_adm.hbrd_main_cliente_modulo WHERE id_cliente = {$this->id} AND id_modulo = hbrd_main_modulos.id) as id_modulo,
        (SELECT valor FROM nova_adm.hbrd_main_cliente_modulo WHERE id_cliente = {$this->id} AND id_modulo = hbrd_main_modulos.id) as valor,
        (SELECT data_vencimento FROM nova_adm.hbrd_main_cliente_modulo WHERE id_cliente = {$this->id} AND id_modulo = hbrd_main_modulos.id) as data_vencimento,
        (SELECT data_aviso_renovacao FROM nova_adm.hbrd_main_cliente_modulo WHERE id_cliente = {$this->id} AND id_modulo = hbrd_main_modulos.id) as data_aviso_renovacao
        FROM nova_adm.hbrd_main_modulos");
        $this->estados = $this->DB_fetch_array("SELECT * FROM hbrd_main_util_state");
        $this->renderView($this->getService(), $this->getModule(), "edit");
    }
    protected function saveAction() {
        if(!(bool)$_POST['id']) {
            $this->criarCliente($_POST);
        } else {
             $formulario = $this->formularioObjeto($_POST);
            $data = $this->formularioObjeto($_POST, $this->table);
            $data->registrado_em = $this->formataDataDeMascara($data->registrado_em);
            $data->id_estado = $data->id_estado ?: 'NULL';
            $data->id_cidade = $data->id_cidade ?: 'NULL';
            $data->bi_senha = $this->embaralhar($data->bi_senha);
            if($data->bairro && $data->id_estado && $data->id_cidade){
                $estado = $this->DB_fetch_array("SELECT * FROM nova_adm.hbrd_main_util_state WHERE id = {$data->id_estado}")->rows[0]['estado'];
                $cidade = $this->DB_fetch_array("SELECT * FROM nova_adm.hbrd_main_util_city WHERE id =  {$data->id_cidade}")->rows[0]['cidade'];
                if($data->logradouro) $endereco_completo .= $data->logradouro.', ';
                if($data->numero) $endereco_completo .= $data->numero.', ';
                if($data->complemento) $endereco_completo .= $data->complemento.', ';
                if($data->bairro) $endereco_completo .= $data->bairro.', ';
                if($data->id_estado) $endereco_completo .= $estado.', ';
                if($data->id_cidade) $endereco_completo .= $cidade;        
                $curl = curl_init('https://us1.locationiq.com/v1/search.php?key=9434c6f69003b0&q='.$endereco_completo.'&format=json');
                curl_setopt_array($curl, array(CURLOPT_RETURNTRANSFER => true, CURLOPT_FOLLOWLOCATION => true, CURLOPT_MAXREDIRS => 10, CURLOPT_TIMEOUT => 30, CURLOPT_CUSTOMREQUEST => 'GET',));
                $response = curl_exec($curl);
                $err = curl_error($curl);
                curl_close($curl);
                if ($err) {
                } else {
                    $response = json_decode($response, true);
                    $data->latitude = $response[0]['lat'];
                    $data->longitude = $response[0]['lon'];
                }
            }
            if (!$this->permissions[$this->permissao_ref]['editar']) die();
            $this->DB_update($this->table, $data, "WHERE id = {$formulario->id}");
            $this->DB_delete('hbrd_main_cliente_membros', "id_cliente = {$formulario->id}");
            foreach ($_POST['modules'] as $modules) {
                $modulo = $this->DB_fetch_array("SELECT * FROM nova_adm.hbrd_main_cliente_modulo WHERE hbrd_main_cliente_modulo.id_cliente = {$formulario->id} and hbrd_main_cliente_modulo.id_modulo = {$modules['id']}")->rows[0]['id_cliente'];
                if($modules['check'] && $modulo == null){
                    $data = $this->formularioObjeto($modules, 'hbrd_main_cliente_modulo');
                    $data->id_cliente = $formulario->id;
                    $data->id_modulo = $modules['id'];
                    $data->valor = $modules['valor'];
                    $data->data_vencimento = $this->formataDataDeMascara($modules['data_vencimento']);
                    $this->DB_insert('hbrd_main_cliente_modulo', $data);
                } elseif($modules['check']){
                    $data = $this->formularioObjeto($modules, 'hbrd_main_cliente_modulo');
                    $data->valor = $modules['valor'];
                    $data->data_vencimento = $this->formataDataDeMascara($modules['data_vencimento']);
                    $this->DB_update('hbrd_main_cliente_modulo', $data, " WHERE id_modulo = {$modules['id']} and id_cliente = {$formulario->id}");
                } else {
                    $this->DB_delete('hbrd_main_cliente_modulo', "id_modulo = {$modules['id']}");
                }
            }
            foreach ($_POST['members'] as $member) {
                $data = $this->formularioObjeto($member, 'hbrd_main_cliente_membros');
                $data->id_cliente = $formulario->id;
                $data->nascimento = $this->formataDataDeMascara($member['nascimento']);
                $this->DB_insert('hbrd_main_cliente_membros', $data);
            }
            if(!isset($_POST['registrado_em'])){
                $this->DB_delete('nova_function_cliente', "id_cliente = {$formulario->id}");
                $funcoes = $this->DB_fetch_array("SELECT * FROM nova_function")->rows;
                foreach ($funcoes as $row) {
                    if ($_POST['perm'][$row["identificador"]]) {
                        $this->DB_insert('nova_function_cliente', ['id_funcao' => $row["id"], 'id_cliente' => $formulario->id]);
                    }
                }
            }
            echo json_encode(['type' => 'success', 'message' => 'Registro alterado com sucesso!']);
        }
    }
    protected function statusAction() {
        $a = $_POST["a"];
        $table = $_POST["t"];
        $id = $_POST["i"];
        $permit = $_POST["p"];
        if ($this->permissions[$permit]['editar']) {
            if ($a == "ativar") {
                $this->inserirRelatorio("Ativou registro na tabela [{$table}] id [{$id}]");
                $this->DB_update($table, ["stats" => 1], " WHERE id = {$id}");
                echo "desativar";
            } else if ("desativar") {
                $this->inserirRelatorio("Desativou registro na tabela [{$table}] id [{$id}]");
                $this->DB_update($table, ["stats" => 0], "WHERE id= {$id}");
                echo "ativar";
            }
        } else {
            echo "Você não tem permissão para editar esta função";
        }
    }
    protected function fileUploadAction() {
        $this->id = (int) $this->getParameter('id');
        $resposta = new stdClass();
        try {
            $uploaded = $this->uploadFile('file', "*");
            if ($uploaded->return) {
                $this->DB_insert('hbrd_main_cliente_anexos', [
                    'id_cliente' => $this->id,
                    'id_usuario' => $_SESSION['admin_id'],
                    'descricao' => $_POST['descricao'],
                    'arquivo' => $uploaded->file_uploaded,
                ]);
            } else {
                throw new Exception($uploaded->message);
            }
            $resposta->type = "success";
            $resposta->message = "Registro salvo com sucesso!";
        } catch (Exception $e) {
            $resposta->type = "error";
            $resposta->message = "Aconteceu um erro no sistema, favor tente novamente mais tarde!";
        }
        echo json_encode($resposta);
    }
    protected function fileDelAction() {
        if (!$this->permissions[$this->permissao_ref]['excluir']) {
            exit();
        }
        $id = (int) $this->getParameter("id");
        $this->DB_delete('hbrd_main_cliente_anexos', "id = {$id}");
        echo $_POST['retorno'];
    }
    protected function validaUpload($form) {
        $resposta = new stdClass();
        $resposta->return = true;
        return $resposta;
    }
    protected function gerarLancamentosAction() {
        try {
            $data->valor = $_POST["valor"];
            $data_vencimento = $this->formataDataDeMascara($_POST["data"]);
            $qtde = $_POST["qtde"];
            $data->tipo = ($data->valor < 0)? 'P' : 'R';
            $data->id_cliente = (int) $this->getParameter('id');
            $modulo_id = $_POST["id"];
            for($i = 1; $i <= $qtde; $i++) {
                $data->data_vencimento = $data_vencimento;
                $this->DB_insert("hbrd_main_lancamentos", $data);
                if($i == $qtde - 1){
                    $this->DB_update('hbrd_main_cliente_modulo', ['data_aviso_renovacao' => $data_vencimento], " WHERE id_modulo = {$modulo_id}");
                }
                $data_vencimento = date('Y-m-d', strtotime('+1 month', strtotime($data->data_vencimento))); 
            }       
            $resposta->type = "success";
            $resposta->message = "Registro salvo com sucesso!";
        } catch (Exception $e) {
            $resposta->type = "error";
            $resposta->message = "Aconteceu um erro no sistema, favor tente novamente mais tarde!";
        }
        echo json_encode($resposta);
    }
    public function datatableAction() {
        if (!$this->permissions[$this->permissao_ref]['ler']) exit();
        $aColumns = array("hbrd_main_cliente.*, (SELECT group_concat(hbrd_main_modulos.nome) FROM nova_adm.hbrd_main_cliente_modulo INNER JOIN nova_adm.hbrd_main_modulos ON nova_adm.hbrd_main_cliente_modulo.id_modulo = nova_adm.hbrd_main_modulos.id WHERE hbrd_main_cliente_modulo.id_cliente = hbrd_main_cliente.id) as modulos");
        $aColumnsWhere = array("hbrd_main_cliente_modulo.id_modulo");
        $sortColumns = ["hbrd_main_cliente.nickname", "hbrd_main_cliente.empresa", "hbrd_main_cliente.telefone", "modulos", "stats"];
        $sTable = "hbrd_main_cliente LEFT JOIN nova_adm.hbrd_main_cliente_modulo ON hbrd_main_cliente.id = hbrd_main_cliente_modulo.id_cliente";
        $sWhere = '';        
        if ($_POST['modulos']) {
            $sWhere .= ($sWhere == '') ? "WHERE ( " : " AND ( ";
            foreach (explode(',',$_POST['modulos']) as $value) {
                $sWhere .= " hbrd_main_cliente_modulo.id_modulo = '{$value}' OR ";
            }
            $sWhere = substr_replace($sWhere, "", -3);
            $sWhere .= ' )';
        }
        $sLimit = "";
        if (isset($_POST['iDisplayStart']) && $_POST['iDisplayLength'] != '-1') {
            $sLimit = "LIMIT ".$_POST['iDisplayStart'].", " .
            $_POST['iDisplayLength'];
        }
        if (isset($_POST['iSortCol_0'])) {
            $sOrder = "ORDER BY ";
            $campo_array = $sortColumns[$_POST['iSortCol_0']];
            $sOrder .= $campo_array." ".$_POST['sSortDir_0'];
        }
        if ($_POST['sSearch'] != "") {
            if ($sWhere == "") {
                $sWhere = "WHERE (";
            } else {
                $sWhere .= " and (";
            }
            for ($i = 0; $i < count($aColumnsWhere); $i++) {
                $sWhere .= $aColumnsWhere[$i]." LIKE '%".$_POST['sSearch']."%' OR ";
            }
            $sWhere = substr_replace($sWhere, "", -3);
            $sWhere .= ')';
        }
		$rResult = array();
		$collums = str_replace(" , ", " ", implode(", ", $aColumns));
		$query = "SELECT DISTINCT SQL_CALC_FOUND_ROWS {$collums} FROM {$sTable} {$sWhere} {$sOrder} {$sLimit}";
        $_SESSION['query_indication'] = "SELECT SQL_CALC_FOUND_ROWS {$collums} FROM {$sTable} {$sWhere} {$sOrder}";
        $sQuery = $this->DB_fetch_array($query);
        if ($sQuery->num_rows) $rResult = $sQuery->rows;
        $iFilteredTotal = $sQuery->total;
        $output = array(
            "sEcho" => intval($_POST['sEcho']),
            "iTotalDisplayRecords" => $iFilteredTotal,
            "aaData" => array()
        );
        if ($rResult) {
            foreach ($rResult as $aRow) {
                $row = array();
                $id = $aRow['id'];
                $row[] = '<div align="left"><a href="'.$this->getPath().'/edit/id/'.$id.'">'.$aRow ['nickname'].'</a></div>';
                $row[] = '<div align="left"><a href="'.$this->getPath().'/edit/id/'.$id.'">'.$aRow ['empresa'].'</a></div>';
                $row[] = '<div align="left"><a href="'.$this->getPath().'/edit/id/'.$id.'">'.$aRow ['telefone'].'</a></div>';
                $row[] = '<div align="left"><a href="'.$this->getPath().'/edit/id/'.$id.'">'.$aRow ['modulos'].'</a></div>';
                $acoes  = '<div align="center">';
                $img = $this->main_template.($aRow['stats'] === '0' ? 'images/status_vermelho.png' : 'images/status_verde.png');
                $acoes .= "<a onclick='updateStatusItem($id)' class='cursor' title='".($aRow['stats'] === '0' ? 'Ativar' : 'Desativar')."'><img src='{$img}'></a>";
                $acoes .= '<a href="'.$this->getPath().'/edit/id/'.$id.'"><span class="icon12 icomoon-icon-pencil"></span></a>';
                $acoes .= '</div>';
                $row[] = $acoes;
                $output['aaData'][] = $row;
            }
        }
        echo json_encode($output);
    }
    protected function updateStatusAction() {
        $client = $this->DB_fetch_array("SELECT * from hbrd_main_cliente where id = ".$_POST['id'])->rows[0];
        $this->DB_update('hbrd_main_cliente',['stats'=> ($client['stats'] === '1'?'0':'1')],' where id ='.$_POST['id']);
    }
    private function criarCliente($data) {
        $cliente = $_POST["nickname"];
        $database = 'nova_cliente_'.$cliente;
        $this->DB_exec("CREATE SCHEMA ".$database);
        $this->DB_exec("use ".$database);
        $dir = __DIR__;
        exec("mysqldump -h $this->db_host -u $this->db_user -p$this->db_pwd --no-data nova_cliente_hibrida > $dir/all_create_tables.sql");
        exec("mysql -h $this->db_host -u $this->db_user -p$this->db_pwd $database < $dir/all_create_tables.sql");
        $tables = "hbrd_adm_status hbrd_main_util_city hbrd_main_util_state hbrd_adm_app_leadszapp hbrd_adm_sinister_doc_variables hbrd_adm_sinister_email_item hbrd_adm_sinister_email hbrd_adm_sinister_leadszapp hbrd_adm_store hbrd_adm_store_has_type hbrd_adm_store_type";
        exec("mysqldump -h $this->db_host -u $this->db_user -p$this->db_pwd nova_cliente_hibrida $tables > $dir/defaultdata.sql");
        exec("mysql -h $this->db_host -u $this->db_user -p$this->db_pwd $database < $dir/defaultdata.sql");
        unlink("$dir/all_create_tables.sql");
        unlink("$dir/defaultdata.sql");
        $this->DB_exec("INSERT INTO `hbrd_main_group` (`id`, `stats`, `nome`) VALUES ('1', '1', '_Top Admin');");
        $this->DB_exec("INSERT INTO `hbrd_main_group` (`id`, `stats`, `nome`) VALUES ('2', '1', 'Administrador');");
        $this->DB_exec("INSERT INTO `hbrd_main_usuarios` (`id`, `id_grupo`, `nome`, `email`, `telefone`, `usuario`, `senha`, `anotacoes`, `code`, `session`, `stats`, `data`, `usuario_sga`, `senha_sga`, `status_sga`) VALUES ('1', '2', 'Suporte - Híbrida Inteligência Web', 'suporte@hibridaweb.com.br', '(62) 3638-7338', 'suporte', 'vDJatcRuawTXSOX+TnsOzL2pWATa4gzhXCma00JbwMY=', '', '3607335bf86bfa35ae26d0871f56045f96496c60', '5d24a4c3e88bb', '1', '2015-05-14 13:47:45', 'thiago', 'gZd9AoZiO/fJuA2PR8N+LIrAgl+OkJl2m9C+pHLnKxg=', '1');");
        $this->DB_exec("INSERT INTO `hbrd_adm_integration` (`id`) VALUES ('1');");
        $this->DB_exec("INSERT INTO `hbrd_adm_setting` (`id`) VALUES ('1');");
        $estado = $this->DB_fetch_array("SELECT * FROM hbrd_main_util_state where id = '{$_POST["id_estado"]}'")->rows[0];
        $cidade = $this->DB_fetch_array("SELECT * FROM hbrd_main_util_city where id = '{$_POST["id_cidade"]}'")->rows[0];
        $endereco = "{$_POST["logradouro"]} {$_POST["numero"]} {$_POST["complemento"]} {$_POST["bairro"]} {$cidade['cidade']} {$estado['estado']}";
        $this->DB_exec("INSERT INTO `hbrd_main_smtp` (`id`, `autenticado`, `email_host`, `email_port`, `email_user`, `email_password`, `email_padrao`) VALUES ('1', '1', 'smtp.mandrillapp.com', '587', 'j2bdigital@gmail.com', 'BYka3Vr1hCFqjY5W34Ly4g', 'contato@coop.org.br');");
        $this->DB_insertWithId('hbrd_adm_company',['id'=>1,'nome'=>$_POST["empresa"],'nome_fantasia'=>$_POST["nome_fantasia"],'telefone'=>$_POST["telefone"],'cnpj'=>$_POST["cnpj"],'email'=>$_POST["email"],'endereco'=>$endereco]);
        $this->DB_exec("INSERT INTO `hbrd_adm_indication_source` (`id`, `nome`, `stats`, `delete_at`) VALUES ('1', 'Aplicativo', '1', '9999-12-31 00:00:00');");
        $this->DB_exec("INSERT INTO `hbrd_adm_indication_source` (`id`, `nome`, `stats`, `delete_at`) VALUES ('20', 'RD Station', '1', '9999-12-31 00:00:00');");
        $this->DB_exec("INSERT INTO `hbrd_adm_indication_source` (`id`, `nome`, `stats`, `delete_at`) VALUES ('21', 'Formulario', '1', '9999-12-31 00:00:00');");
        $adm_cliente = $this->formularioObjeto($_POST, "nova_adm.hbrd_main_cliente");
        $id_cliente = $this->DB_insert("nova_adm.hbrd_main_cliente", $adm_cliente);
        $funcoes = $this->DB_fetch_array("SELECT * FROM nova_adm.nova_function")->rows;
        foreach ($funcoes as $row) {
            $this->DB_insert('nova_adm.nova_function_cliente', ['id_funcao' => $row["id"], 'id_cliente' => $id_cliente]);
        }
        $this->managerApache($cliente);
        echo $id_cliente;
    }
    function managerApache($cliente) {
        $dir_apache = "/etc/apache2/sites-available";
        $dir_docroot = getenv('DB_HOST') ? "/var/www/nova_site" : "/home/jales/Desktop/site";
        $config = '
#'.$cliente.'
<VirtualHost *:80>
    ServerAdmin webmaster@localhost
    DocumentRoot '.$dir_docroot.'
    ServerName '.$cliente.'.ileva.com.br
    <Directory '.$dir_docroot.'/>
        Options Indexes FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>
    ErrorLog ${APACHE_LOG_DIR}/error.log
    CustomLog ${APACHE_LOG_DIR}/access.log combined
    <IfModule mod_dir.c>
        DirectoryIndex index.php index.pl index.cgi index.html index.xhtml index.htm
    </IfModule>
    RewriteEngine on
    RewriteCond %{SERVER_NAME} ='.$cliente.'.ileva.com.br
    RewriteRule ^ https://%{SERVER_NAME}%{REQUEST_URI} [END,NE,R=permanent]
</VirtualHost>
<VirtualHost *:443>
    ServerAdmin webmaster@localhost
    DocumentRoot '.$dir_docroot.'
    ServerName '.$cliente.'.ileva.com.br
    <Directory '.$dir_docroot.'/>
        Options Indexes FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>
    ErrorLog ${APACHE_LOG_DIR}/error.log
    CustomLog ${APACHE_LOG_DIR}/access.log combined
    <IfModule mod_dir.c>
        DirectoryIndex index.php index.pl index.cgi index.html index.xhtml index.htm
    </IfModule>
    SSLCertificateFile /etc/letsencrypt/live/ileva.com.br-0001/fullchain.pem
    SSLCertificateKeyFile /etc/letsencrypt/live/ileva.com.br-0001/privkey.pem
    Include /etc/letsencrypt/options-ssl-apache.conf
</VirtualHost>';
        file_put_contents("$dir_apache/ileva-sistemas.conf", $config, FILE_APPEND);
        // exec("cd $dir_apache && a2ensite $cliente.conf");
        // exec("service apache2 reload");
    }
}
define("__service__", strtolower(str_replace(dirname(dirname(dirname(dirname(__DIR__)))).DIRECTORY_SEPARATOR, "", dirname(dirname(dirname(__DIR__))))));
define("__classe__", strtolower(str_replace(".php", "", basename(__FILE__))));
$class = new __classe__();
$class->setAction();
