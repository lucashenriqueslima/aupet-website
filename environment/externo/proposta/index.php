<?php
use System\Base\{SistemaBase, BadRequest};
use app\classes\{Util, Variaveis, Extenso};
class __classe__ extends SistemaBase {
    function __construct() {
        parent::__construct();
        $this->module_title = "Indicação";
        $this->variaveis = new Variaveis();
    }
    private function vistoria() {
        $hash = $this->getParameter("vistoria");
        $this->vistoria = $this->DB_fetch_array("SELECT A.id, A.status, A.observacao, B.nome, B.modelo, B.marca, B.ano, B.placa, B.id_consultor FROM hbrd_adm_inspection A INNER JOIN hbrd_adm_indication B ON B.id_inspection = A.id WHERE A.hash = '$hash'")->rows[0];
        if(!(bool)$this->vistoria) { echo "<h1>Vistoria não encontrada</h1>"; return; }
        if((bool)$this->vistoria['id_consultor'])
            $this->vistoria['consultor'] = $this->DB_fetch_array("SELECT nome FROM hbrd_adm_consultant where id = {$this->vistoria['id_consultor']}")->rows[0];
        $this->vistoria['anexos'] = $this->DB_fetch_array("SELECT descricao FROM hbrd_adm_inspection_attachment where id_inspection = {$this->vistoria['id']}")->rows;
        $this->vistoria['items'] = $this->DB_fetch_array("SELECT A.id, A.image, B.descricao, B.imagem_exemplo, B.lib_access, B.required, A.aproved FROM hbrd_adm_inspection_item A LEFT JOIN hbrd_adm_inspection_model_item B ON B.id = A.id_model_item WHERE A.id_inspection = '{$this->vistoria['id']}' order by B.ordem")->rows;
        $this->empresa = $this->DB_fetch_array("SELECT nome FROM hbrd_adm_company")->rows[0];
        require_once 'vistoria.phtml';
    }
    private function assinatura() {
        $hash = $this->getParameter("assinatura");
        if($hash == null) die('<h3>Proposta não encontrada</h3>');
        $this->pet = $this->DB_fetch_array("SELECT A.id, A.nome, A.peso, A.cor, A.sexo, A.porte, A.id_plano, A.id_associado, A.hash, B.titulo especie, C.titulo raca FROM hbrd_app_pet A LEFT JOIN hbrd_app_pet_especie B ON B.id = A.id_especie LEFT JOIN hbrd_app_pet_raca C ON C.id = A.id_raca WHERE A.hash = ?",[$hash])->rows[0];
        if($this->pet['id_plano'] == null) die('<h3>Esse pet não possui um plano.</h3>');
        $this->plano = $this->DB_fetch_array("SELECT A.id, A.titulo, A.valor FROM hbrd_app_planos A WHERE A.id = ?",[$this->pet['id_plano']])->rows[0];
        $this->tutor = $this->DB_fetch_array("SELECT C.*, B.id_consultor FROM hbrd_app_pet A LEFT JOIN hbrd_app_proposta B ON B.id = A.id_proposta LEFT JOIN hbrd_app_pessoa C ON C.id = B.id_pessoa WHERE A.id = ?",[$this->pet['id']])->rows[0];
        $this->consultor = $this->DB_fetch_array("SELECT B.nome, A.id FROM hbrd_app_consultor A LEFT JOIN hbrd_app_pessoa B ON B.id = A.id_pessoa WHERE A.id = ?",[$this->tutor['id_consultor']])->rows[0];
        $this->empresa = $this->DB_fetch_array("SELECT nome FROM hbrd_adm_company")->rows[0];

        $this->dadosAssociado = [
            'id' => $this->tutor['id'],
            'id_associado' => $this->pet['id_associado'],
            'id_consultor' => $this->consultor['id'],
            'nome' => $this->tutor['nome'],
            'cpf' => $this->tutor['cpf'],
            'email' => $this->tutor['email'],
            'telefone' =>  $this->tutor['telefone'],
            'bairro' => $this->tutor['bairro'],
            'data_nascimento' =>  Util::formataDataDeBanco($this->tutor['data_nascimento']),
            'cep' =>  $this->tutor['cep'],
            'rua' =>  $this->tutor['rua'],
            'numero' =>  $this->tutor['numero'],
            'id_estado' =>  $this->tutor['id_estado'],
            'id_cidade' => $this->tutor['id_cidade'],
            'complemento' =>  $this->tutor['complemento']
        ];

        $this->beneficios = $this->DB_fetch_array("SELECT * FROM hbrd_app_plano_beneficio A WHERE A.delete_at IS NULL AND A.stats = 1 ORDER BY A.ordem")->rows;
        $planoBeneficios =  $this->DB_fetch_array("SELECT * FROM hbrd_app_plano_use_beneficio A WHERE A.id_plano = {$this->plano['id']}")->rows;

        $this->estados = $this->DB_fetch_array("SELECT * FROM hbrd_main_util_state")->rows;
        $this->public_key = $this->DB_fetch_array("SELECT public_key FROM hbrd_adm_integration")->rows[0];

        if(!(bool)$this->pet) die('<h3>Indicação não encontrada</h3>');
        $termo = $this->DB_fetch_array("SELECT assinatura, selfie, frente_doc, atras_doc, status, doc_recusados FROM hbrd_app_pet_termo where id_pet = {$this->pet['id']}")->rows[0];
        $doc_recusados = ((bool)$termo['doc_recusados']) ? json_decode($termo['doc_recusados']) : null;
        $assinatura = $this->DB_fetch_array("SELECT apenas_assinatura FROM hbrd_adm_setting WHERE id = 1")->rows[0];
        require_once dirname(__DIR__).'/assinatura_mp/views/home.php';
    }
    protected function visualizar() {
        $hash = $this->getParameter("visualizar");
        $query = "SELECT DISTINCT
            A.*,
            DATE_FORMAT(A.create_at, '%d/%m/%Y') dt_proposta,
            (SELECT nome FROM hbrd_app_pessoa WHERE id = A.id_pessoa) nome,
            (SELECT email FROM hbrd_app_pessoa WHERE id = A.id_pessoa) email,
            (SELECT telefone FROM hbrd_app_pessoa WHERE id = A.id_pessoa) telefone,
            (SELECT cep FROM hbrd_app_pessoa WHERE id = A.id_pessoa) cep,
            (SELECT complemento FROM hbrd_app_pessoa WHERE id = A.id_pessoa) complemento,
            (SELECT numero FROM hbrd_app_pessoa WHERE id = A.id_pessoa) numero,
            (SELECT bairro FROM hbrd_app_pessoa WHERE id = A.id_pessoa) bairro,
            (SELECT cpf FROM hbrd_app_pessoa WHERE id = A.id_pessoa) cpf,
            (SELECT endereco FROM hbrd_app_pessoa WHERE id = A.id_pessoa) endereco,
            (SELECT orgao_exp FROM hbrd_app_pessoa WHERE id = A.id_pessoa) orgao_exp,
            (SELECT rg FROM hbrd_app_pessoa WHERE id = A.id_pessoa) rg,
            (SELECT data_nascimento FROM hbrd_app_pessoa WHERE id = A.id_pessoa) data_nascimento,
            (SELECT B.cidade FROM hbrd_app_pessoa A LEFT JOIN hbrd_main_util_city B ON A.id_cidade = B.id  WHERE A.id = A.id_pessoa) cidade,
            (SELECT B.estado FROM hbrd_app_pessoa A LEFT JOIN hbrd_main_util_state B ON A.id_estado = B.id  WHERE A.id = A.id_pessoa) estado,
            B.nome pet_nome,B.porte pet_porte,B.sexo pet_sexo, B.id id_pet,
            C.shared_msg, C.shared_pdf, C.id id_contrato,
            C.titulo pet_plano_nome,C.valor pet_plano_valor,
            Z.titulo pet_raca, Y.titulo pet_especie,
            F.nome indicador_nome,
            F.telefone indicador_telefone,
            (SELECT GROUP_CONCAT(A.nome) FROM hbrd_app_plano_beneficio A LEFT JOIN hbrd_app_plano_use_beneficio B ON A.id = B.id_beneficio WHERE B.id_plano = C.id) beneficios_virgula,
            X.template,
            P.assinatura,
            P.perguntas_contrato
        FROM
            hbrd_app_proposta A
            LEFT JOIN hbrd_app_pet B ON B.id_proposta = A.id   
            LEFT JOIN hbrd_app_pet_raca Z ON B.id_raca = Z.id
            LEFT JOIN hbrd_app_pet_especie Y ON B.id_especie = Y.id	
            LEFT JOIN hbrd_app_planos C ON C.id = B.id_plano
            LEFT JOIN hbrd_app_plano_has_termo W ON W.id_plano = C.id
            LEFT JOIN hbrd_app_termo X ON X.id = W.id_termo
            LEFT JOIN hbrd_app_pet_termo P ON P.id_pet = B.id
            LEFT JOIN hbrd_app_consultor E ON E.id = A.id_consultor
            LEFT JOIN hbrd_app_pessoa F ON F.id = E.id_pessoa
            WHERE B.hash = '$hash'";
        $pet = $this->DB_fetch_array($query)->rows[0];
        if(!(bool)$pet || !(bool)$pet['id_contrato']) die('<h3>Proposta não encontrada</h3>');
        $name = 'termo de filiacao.pdf';
        $template = $this->replaceVarIndTermo($pet);
        $mpdf = new \Mpdf\Mpdf(['mode' => 'utf-8','default_font' => 'helvetica', 'margin_top' => 10,'margin_bottom' => 10,'margin_left'=> 10,'margin_right'=>10]);
        $mpdf->WriteHTML(mb_convert_encoding($template, 'UTF-8', 'UTF-8'));
        $mpdf->Output($name,\Mpdf\Output\Destination::INLINE);
    }
    public function replaceVarIndTermo($data) {
        $query = [];
        $query['{[assos_nome]}'] = $data['nome'] ?: ' ';
        $query['{[assos_telefone]}'] = $data['telefone'] ?: ' ';
        $query['{[assos_cpfcnpj]}'] = $data['cpf'] ?: ' ';
        $query['{[assos_rg]}'] = $data['rg'] ?: ' ';
        $query['{[assos_orgao_exp]}'] = $data['orgao_exp'] ?: ' ';
        $query['{[assos_CEP]}'] = $data['cep']  ?: ' ';
        $query['{[assos_complemento]}'] = $data['complemento'] ?: ' ';
        $query['{[assos_numero]}'] = $data['numero']  ?: ' ';
        $query['{[assos_bairro]}'] = $data['bairro']  ?: ' ';
        $query['{[assos_endereco]}'] = $data['endereco'] ?: ' ';
        $query['{[assos_cidade]}'] = $data['cidade'] ?: ' ';
        $query['{[assos_estado]}'] = $data['estado'] ?: ' ';
        $query['{[assos_whatsapp]}'] = $data['telefone'] ?: ' ';
        $query['{[assos_dtnasc]}'] = ((bool)$data['data_nascimento']) ? Util::formataDataDeBanco($data['data_nascimento']) : ' ';
        $query['{[assos_email]}'] = $data['email'] ?: ' ';
        $query['{[pet_nome]}'] = $data['pet_nome'] ?: ' ';
        $query['{[pet_especie]}'] = $data['pet_especie'] ?: ' ';
        $query['{[pet_raca]}'] = $data['pet_raca'] ?: ' ';
        $query['{[pet_sexo]}'] = $data['pet_sexo'] ?: ' ';
        $query['{[pet_porte]}'] = $data['pet_porte'] ?: ' ';
        $query['{[pet_plano_nome]}'] = $data['pet_plano_nome'] ?: ' ';
        $query['{[pet_plano_valor]}'] = ((bool)$data['pet_plano_valor']) ? "R$ ". Util::formataMoeda($data['pet_plano_valor']) : ' ';
        $query['{[beneficios]}'] =  ((bool)$data['beneficios_virgula']) ? str_replace(',','<br>',$data['beneficios_virgula']) : ' ';
        $query['{[beneficios_virgula]}'] = $data['beneficios_virgula'] ?: ' ';
        $query['{[indicador_nome]}'] = $data['indicador_nome'] ?: ' ';
        $query['{[indicador_telefone]}'] = $data['indicador_telefone'] ?: ' ';
        $query['{[dt_contrato]}'] =  date('d/m/Y');
        setlocale(LC_ALL, 'pt_BR', 'pt_BR.utf-8', 'pt_BR.utf-8', 'portuguese');
        $query['{[dt_contrato_ext]}'] = strftime("%d de %B de %Y", strtotime(date('Y-m-d')));
        $query['{[img_assinatura]}'] = ((bool)$data['assinatura']) ? "<img src='{$data['assinatura']}'>" : ' '  ;
        $query['{[pagebreak]}'] =  "<pagebreak/>";
        $query['{[num_indicacao]}'] = $data['id']  ?: ' ';

        $contrato = $data['template'];
        foreach ($this->variaveis->contratoPet as $value) {
            if((bool)$query[$value['variavel']])
                $contrato = str_replace($value['variavel'], $query[$value['variavel']],$contrato);
        }

        $vars = Util::json_decode($data['perguntas_contrato']);
        foreach ($vars as $value) {
            $contrato = str_replace($value["variavel"], $value["resposta"], $contrato);
        }

        return $contrato;
    }
    private function sharedPDF() {
        $hash = $this->getParameter("pdf");
        $data = $this->DB_fetch_array("SELECT DISTINCT
            A.*,
            DATE_FORMAT(A.create_at, '%d/%m/%Y') dt_proposta,
            (SELECT nome FROM hbrd_app_pessoa WHERE id = A.id_pessoa) nome,
            (SELECT email FROM hbrd_app_pessoa WHERE id = A.id_pessoa) email,
            (SELECT telefone FROM hbrd_app_pessoa WHERE id = A.id_pessoa) telefone,
            B.nome pet_nome,B.porte pet_porte,B.sexo pet_sexo, B.id id_pet,
            C.shared_msg, C.shared_pdf,
            C.titulo pet_plano_nome,C.valor pet_plano_valor,
            Z.titulo pet_raca, Y.titulo pet_especie,
            F.nome indicador_nome,
            F.telefone indicador_telefone,
            (SELECT GROUP_CONCAT(A.nome) FROM hbrd_app_plano_beneficio A LEFT JOIN hbrd_app_plano_use_beneficio B ON A.id = B.id_beneficio WHERE B.id_plano = C.id) beneficios_virgula
        FROM
            hbrd_app_proposta A
            LEFT JOIN hbrd_app_pet B ON B.id_proposta = A.id   
            LEFT JOIN hbrd_app_pet_raca Z ON B.id_raca = Z.id
            LEFT JOIN hbrd_app_pet_especie Y ON B.id_especie = Y.id	
            LEFT JOIN hbrd_app_planos C ON C.id = B.id_plano
            LEFT JOIN hbrd_app_consultor E ON E.id = A.id_consultor
            LEFT JOIN hbrd_app_pessoa F ON F.id = E.id_pessoa
            WHERE B.hash = ?",[$hash])->rows[0];
        if(!(bool)$data) die('Proposta não encontrada');
        $template = $this->replaceVarIndPDF($data['id_pet']);
        $mpdf = new \Mpdf\Mpdf(['mode' => 'utf-8','default_font' => 'helvetica', 'margin_top' => 5,'margin_bottom' => 5,'margin_left'=> 5,'margin_right'=>5]);
        
        $mpdf->WriteHTML(mb_convert_encoding($template, 'UTF-8', 'UTF-8'));
        $name = 'Proposta de Filiacao.pdf';
        $mpdf->Output($name,\Mpdf\Output\Destination::INLINE);
    }
    public function replaceVarIndPDF($id_pet) {
        $data = $this->DB_fetch_array("SELECT DISTINCT
            A.*,
            DATE_FORMAT(A.create_at, '%d/%m/%Y') dt_proposta,
            (SELECT nome FROM hbrd_app_pessoa WHERE id = A.id_pessoa) nome,
            (SELECT email FROM hbrd_app_pessoa WHERE id = A.id_pessoa) email,
            (SELECT telefone FROM hbrd_app_pessoa WHERE id = A.id_pessoa) telefone,
            B.nome pet_nome,B.porte pet_porte,B.sexo pet_sexo,
            C.shared_msg, C.shared_pdf,
            C.titulo pet_plano_nome,C.valor pet_plano_valor,
            Z.titulo pet_raca, Y.titulo pet_especie,
            F.nome indicador_nome,
            F.telefone indicador_telefone,
            (SELECT GROUP_CONCAT(A.nome) FROM hbrd_app_plano_beneficio A LEFT JOIN hbrd_app_plano_use_beneficio B ON A.id = B.id_beneficio WHERE B.id_plano = C.id) beneficios_virgula
        FROM
            hbrd_app_proposta A
            LEFT JOIN hbrd_app_pet B ON B.id_proposta = A.id   
            LEFT JOIN hbrd_app_pet_raca Z ON B.id_raca = Z.id
            LEFT JOIN hbrd_app_pet_especie Y ON B.id_especie = Y.id	
            LEFT JOIN hbrd_app_planos C ON C.id = B.id_plano
            LEFT JOIN hbrd_app_consultor E ON E.id = A.id_consultor
            LEFT JOIN hbrd_app_pessoa F ON F.id = E.id_pessoa
            WHERE B.id = '{$id_pet}'")->rows[0];
        $query = [];
        $query['{[dt_proposta]}'] = $data['dt_proposta'] ?: ' ';
        $query['{[nome]}'] = $data['nome'] ?: ' ';
        $query['{[email]}'] = $data['email'] ?: ' ';
        $query['{[telefone]}'] = $data['telefone'] ?: ' ';
        $query['{[pet_nome]}'] = $data['pet_nome'];
        $query['{[pet_especie]}'] = $data['pet_especie'] ?: ' ';
        $query['{[pet_raca]}'] = $data['pet_raca'] ?: ' ';
        $query['{[pet_sexo]}'] = $data['pet_sexo'] ?: ' ';
        $query['{[pet_porte]}'] = $data['pet_porte'] ?: ' ';
        $query['{[pet_plano_nome]}'] = $data['pet_plano_nome'] ?: ' ';
        $query['{[pet_plano_valor]}'] = ((bool)$data['pet_plano_valor']) ? "R$ ". Util::formataMoeda($data['pet_plano_valor']) : ' ';
        $query['{[beneficios]}'] =  ((bool)$data['beneficios_virgula']) ? str_replace(',','<br>',$data['beneficios_virgula']) : ' ';
        $query['{[beneficios_virgula]}'] =   $data['beneficios_virgula'] ?: ' ';
        $query['{[indicador_nome]}'] = $data['indicador_nome'] ?: ' ';
        $query['{[indicador_telefone]}'] = $data['indicador_telefone'] ?: ' ';
        $template = $data['shared_pdf'];
        foreach ($this->variaveis->planovariaveis as $value) {
            $template = str_replace($value['variavel'], $query[$value['variavel']],$template);
        }
        return $template;
    }
    protected function aceitarTermoAction() {
        $hash = $this->getParameter("aceitarTermo");
        $pet = $this->DB_fetch_array("SELECT * FROM hbrd_app_pet where hash = '$hash'")->rows[0];
        if(!(bool)$pet) throw new BadRequest('Indicação não encontrada');
        $doc = $this->DB_fetch_array("SELECT B.titulo termo FROM hbrd_app_pet_termo A inner join hbrd_app_termo B ON B.id = A.id_contrato where A.id_pet = {$pet['id']}")->rows[0];
        if($_POST['assinatura']) $assinatura = $this->putObjectAws($this->rotateAssinatura($_POST['assinatura']));
        if($_POST['atras_doc']) $atras_doc = $this->putObjectAws(Util::getFileDaraUriAwsDecode($_POST["atras_doc"]));
        if($_POST['frente_doc']) $frente_doc = $this->putObjectAws(Util::getFileDaraUriAwsDecode($_POST["frente_doc"]));
        if($_POST['selfie']) $selfie = $this->putObjectAws(Util::getFileDaraUriAwsDecode($_POST["selfie"]));
        $this->DBInsertOrUpdate('hbrd_app_pet_termo', ['id_pet' => $pet['id'], 'assinatura' => $assinatura, 'selfie' => $selfie, 'frente_doc' => $frente_doc, 'atras_doc' => $atras_doc, 'tipo_doc' => $_POST['tipo_doc'], 'lat' => $_POST['lat'], 'lng' => $_POST['lng'], 'ip' => $_SERVER['REMOTE_ADDR'], 'dt_envio'=> date("Y-m-d H:i:s"), 'status'=>'pendente', 'feito_em'=>'externo'],'id_pet');
        $acao =  preg_replace('!\s+!', ' ',"{$pet["nome"]} assinou documento '{$doc["termo"]}'");
        $history = ['id_proposta' => $pet['id_proposta'], 'atividade' => $acao, 'nome' => 'Associado'];
        $this->DBInsert("hbrd_app_proposta_hist", $history);
        // $this->gerarTermo($pet['id']);
    }
    function rotateAssinatura($file) {
        $simpleImage = new \SimpleImage3();
        $simpleImage->fromDataUri($file);
        $simpleImage->rotate(-90);
        return $simpleImage->bestFit(1300,1300)->toString();
    }
    function gerarTermo($indicacao_id){
        $template = $this->sharedIndicacao->replaceVarIndTermo($indicacao_id);
        $mpdf = new \Mpdf\Mpdf(['mode' => 'utf-8','default_font' => 'helvetica', 'margin_top' => 10,'margin_bottom' => 10,'margin_left'=> 10,'margin_right'=>10]);
        $mpdf->WriteHTML(mb_convert_encoding($template));
        $file = base64_encode($mpdf->Output('',\Mpdf\Output\Destination::STRING_RETURN));
        $this->putObjectAws($file,'contratos_pdf');
    }
    protected function termoArqRecusadosAction() {
        $hash = $this->getParameter("termoArqRecusados");
        $pet = $this->DB_fetch_array("SELECT * FROM hbrd_app_pet where hash = '$hash'")->rows[0];
        if(!(bool)$pet) throw new BadRequest('Indicação não encontrada');
        $doc = $this->DB_fetch_array("SELECT B.titulo termo FROM hbrd_app_pet_termo A inner join hbrd_app_termo B ON B.id = A.id_contrato where A.id_pet = {$pet['id']}")->rows[0];
        if((bool)$_POST['assinatura']) {
            $assinatura = $this->putObjectAws($this->rotateAssinatura($_POST['assinatura']));
            $this->DBInsertOrUpdate('hbrd_app_pet_termo', ['id_pet' => $pet['id'], 'assinatura' => $assinatura]);
        }
        if((bool)$_POST['atras_doc']) {
            $atras_doc = $this->putObjectAws(Util::getFileDaraUriAwsDecode($_POST["atras_doc"]));
            $this->DBInsertOrUpdate('hbrd_app_pet_termo', ['id_pet' => $pet['id'], 'atras_doc' => $atras_doc]);
        }
        if((bool)$_POST['frente_doc']) {
            $frente_doc = $this->putObjectAws(Util::getFileDaraUriAwsDecode($_POST["frente_doc"]));
            $this->DBInsertOrUpdate('hbrd_app_pet_termo', ['id_pet' => $pet['id'], 'frente_doc' => $frente_doc]);
        }
        if((bool)$_POST['selfie']) {
            $selfie = $this->putObjectAws(Util::getFileDaraUriAwsDecode($_POST["selfie"]));
            $this->DBInsertOrUpdate('hbrd_app_pet_termo', ['id_pet' => $pet['id'], 'selfie' => $selfie]);
        }
        $this->DBInsertOrUpdate('hbrd_app_pet_termo', ['id_pet' => $pet['id'], 'dt_envio'=> date("Y-m-d H:i:s"), 'status'=>'reanalise', 'doc_recusados'=> null]);
        $acao =  preg_replace('!\s+!', ' ',"{$pet["nome"]} enviou documentos recusados do termo '{$doc["termo"]}'");
        $history = ['id_proposta' => $pet['id_proposta'], 'atividade' => $acao, 'nome' => 'Associado'];
        $this->DB_insert("hbrd_app_proposta_hist", $history);
        // $this->gerarTermo($pet['id']);
    }
    function atualizarAssociadoAction(){
        $id = $this->getParameter("atualizarAssociado");
        $pessoa = $this->arrayTable($_POST,'hbrd_app_pessoa');
        $pessoa['data_nascimento'] = Util::maskToDate($pessoa['data_nascimento']);

        $this->DBUpdate($this->table_pessoa, $pessoa, " WHERE id = ". $id);

        $associado = $this->DB_fetch_array("SELECT A.* FROM hbrd_app_associado A WHERE A.id_pessoa = $id");
        if($associado->rows){ 
            echo json_encode([ 'id' => $associado->rows[0]['id']]);
         }
        else {
            $id_associado = $this->DBInsert('hbrd_app_associado',['id_pessoa' => $id]);
            echo json_encode([ 'id' => $id_associado]);
        }
    }
    function criarAssinaturaMpAction(){
        $access_token = $this->DB_fetch_array("SELECT access_token FROM hbrd_adm_integration")->rows[0]['access_token'];
        $lastSignature = $this->DB_fetch_array("SELECT id FROM hbrd_app_assinatura ORDER BY id desc")->rows[0]['id'];
        $city = $this->DB_fetch_array("SELECT cidade FROM hbrd_main_util_city WHERE id = {$_POST['id_cidade']}")->rows[0]['cidade'];
        $state = $this->DB_fetch_array("SELECT UF FROM hbrd_main_util_state WHERE id = {$_POST['id_estado']}")->rows[0]['UF'];

        MercadoPago\SDK::setAccessToken($access_token);

        $colector_id = json_decode($this->handlerHttp('GET', 'https://api.mercadolibre.com/users/me', ['headers' => ['Content-Type' => 'application/json', 'Authorization' => "Bearer $access_token"]]), true)["id"];

        if(!(bool)$colector_id) throw 'Vendedor não enconstrado!';

        $data_request = new MercadoPago\Preapproval();

        $data_request->auto_recurring = [
            "currency_id" => "BRL",
            "transaction_amount" => $_POST['transaction_amount'],
            "frequency" => 1,
            "frequency_type" => 'months',
            "billing_day" => 10,
            "billing_day_proportional" => true
        ];
        $data_request->back_url = "https://app.aupetheinsten.com.br/#/associado";
        $data_request->collector_id = $colector_id;
        $data_request->external_reference = $lastSignature . $_POST['id'];
        $data_request->reason = $_POST['descricao'];
        $data_request->card_token_id = $_POST['token'];
        $data_request->payer_email = $_POST['email'];
        $data_request->status = "authorized";
        $data_request->payer = [
            "email" => $_POST['email'],
            "identification" => [ "type" => "CPF", "number" => $_POST['cpf'] ],
            "address" => [
                "zip_code" => $_POST['cep'],
                "street_name" => $_POST['rua'],
                "street_number" => $_POST['numero'],
                "neighborhood" => $_POST['bairro'],
                "city" => $city,
                "federal_unit" => $state
            ]
        ];

        $data_request->save();

        if((bool)$data_request->error) echo json_encode($data_request->error);
        else { 
            $this->DBUpdate('hbrd_app_pet',['id_plano' => $_POST['id_pet'], 'valor' => $_POST['valor_plano'], 'classificacao' => 'ativada'],'WHERE id = '.$_POST['id_pet']);

            $assinatura = [
                "id_associado" => $_POST['id_associado'],
                "id_pet" => $_POST['id_pet'],
                "assinatura" => $data_request->id,
                "payment_method_id" => $data_request->payment_method_id,
                "application_id" => $data_request->application_id,
                "external_reference" => $lastSignature . $_POST['id'],
                "status" => "authorized",
                "valor" => $_POST['valor_plano'],
                "descricao" => $_POST['descricao']
            ];

            $this->DBInsert('hbrd_app_assinatura',$assinatura);

            $associado = $this->DB_fetch_array("SELECT B.*, A.* FROM hbrd_app_associado A inner join hbrd_app_pessoa B ON B.id = A.id_pessoa where A.id = {$_POST['id_associado']}")->rows[0];
            $consultor = $this->DB_fetch_array("SELECT B.nome, A.id FROM hbrd_app_consultor A LEFT JOIN hbrd_app_pessoa B ON B.id = A.id_pessoa WHERE A.id = ?",[$_POST['id_consultor']])->rows[0];

            $info = $this->DB_fetch_array("SELECT A.nome, B.titulo raca, C.titulo especie, D.titulo plano, (SELECT GROUP_CONCAT(Z.nome) FROM hbrd_app_plano_beneficio Z LEFT JOIN hbrd_app_plano_use_beneficio Y ON Z.id = Y.id_beneficio WHERE Y.id_plano = A.id_plano) beneficios_virgula FROM hbrd_app_pet A LEFT JOIN hbrd_app_pet_raca B ON B.id = A.id_raca LEFT JOIN hbrd_app_pet_especie C ON C.id = A.id_especie LEFT JOIN hbrd_app_planos D ON D.id = A.id_plano WHERE A.id = ?",[$_POST['id_pet']])->rows[0];

            $query = [];
            $query['{[assos_nome]}'] = $associado['nome'];
            $query['{[assos_telefone]}'] = $associado['telefone'];
            $query['{[nome_consultor]}'] = $consultor['nome'];
            $query['{[pet_nome]}'] = $info['nome'];
            $query['{[pet_especie]}'] = $info['especie'];
            $query['{[pet_raca]}'] = $info['raca'];
            $query['{[pet_plano_nome]}'] = $info['plano'];
            $query['{[pet_plano_valor]}'] = "R$ ". Util::formataMoeda($_POST['valor_plano']);
            $query['{[beneficios]}'] = str_replace(',',PHP_EOL,$info['beneficios_virgula']);
            $query['{[beneficios_virgula]}'] =  $info['beneficios_virgula'];

            $notficacao = $this->DB_fetch_array("SELECT * FROM hbrd_app_notificacao where id = 19")->rows[0];
            if($notficacao['desativado'] == '0') {
                foreach ($this->variaveis->notf19 as $value) {
                    $notficacao['mensagem'] = str_replace($value['variavel'], $query[$value['variavel']],$notficacao['mensagem']);
                }
                $this->sendLeadszApp($associado, $notficacao['mensagem']);

                if((bool)$notficacao['envia_email']){
                    $email_template = $notficacao['template_email'];
                    foreach ($this->variaveis->notf19 as $value) {
                        $email_template = str_replace($value['variavel'], $query[$value['variavel']],$email_template);
                    }

                    $email_assunto = $notficacao['assunto'];
                    foreach ($this->variaveis->notf19 as $value) {
                        $email_assunto = str_replace($value['variavel'], $query[$value['variavel']],$email_assunto);
                    }

                    $this->enviarEmail(['nome' => $associado['nome'], 'email' => $associado['email']],"",Util::utf8_decode($email_assunto), Util::utf8_decode($email_template));
                }
            }

            echo json_encode(['id_assinatura' => $data_request->id]); 
        };
    }

    function getCidadesAction(){
        $dados = $this->DB_fetch_array("SELECT * FROM hbrd_main_util_city WHERE id_estado='" . $_POST['id'] . "' ORDER BY cidade");
        echo json_encode($dados->rows);
    }

    public function setAction() {
        try {
            $this->DB_connect();
            $this->getPOST();
            $uri = explode("/", str_replace(strrchr($_SERVER["REQUEST_URI"], "?"), "", $_SERVER["REQUEST_URI"]));
            array_shift($uri);
            $ctrl = $uri[array_search('indicacao', $uri) + 1];
            if($ctrl == 'vistoria') $this->vistoria();
            else if($ctrl == 'visualizar') $this->visualizar();
            else if($ctrl == 'pdf') $this->sharedPDF();
            else if($ctrl == 'assinatura') $this->assinatura();
            else if($ctrl == 'aceitarTermo') $this->aceitarTermoAction();
            else if($ctrl == 'termoArqRecusados') $this->termoArqRecusadosAction();
            else if($ctrl == 'atualizarAssociado') $this->atualizarAssociadoAction();
            else if($ctrl == 'criarAssinaturaMp') $this->criarAssinaturaMpAction();
            else if($ctrl == 'getCidades') $this->getCidadesAction();


            else header("HTTP/1.0 404 Not Found");
            $this->mysqli->commit();
        } catch(BadRequest $e) {
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
}
define("__classe__", 'indicacao');
$class = new __classe__();
$class->setAction();