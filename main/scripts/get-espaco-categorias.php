<?php

session_start();

require_once '../sistema/System/Core/Loader.php';

use System\Core\Bootstrap;

require_once "../_system.php";

$sistema = new _sys();

$id = "";
if (isset($_POST['id']) && $_POST['id'] != "") {
    $id = $_POST['id'];
}

$combo = $_POST['combo'];
$arquivo = $_POST['arquivo'];


$resultCategoria = '';
$resultArquivo = '';
if ($id != "") {
    $query = $sistema->DB_fetch_array("SELECT A.* FROM tb_espaco_categorias A INNER JOIN tb_espaco_arquivos_has_tb_espaco_categorias  B ON B.id_categoria = A.id INNER JOIN tb_espaco_arquivos_has_tb_espaco_usuarios C ON C.id_arquivo = B.id_arquivo WHERE A.id_pai = $id AND C.id_usuario = {$_SESSION['corretor_id']}");
    if ($query->num_rows) {
        $combo_ = $combo + 1;
        $resultCategoria .= '
        <label data-combo="' . $combo_ . '" class="categoria" id="categoria_' . $combo . '">
            <select class="categoria-select" name="id_categoria[]">
                <option value="">Selecionar</option>';
        ?>
        <?php

        foreach ($query->rows as $categoria) {
            $resultCategoria .= '<option value="' . $categoria['id'] . '">' . $categoria['nome'] . '</option>';
        }
        ?>
        <?php

        $resultCategoria .= '</select>
        </label>';
    }
}

$where = "";
if ($id != "")
    $where = " AND C.id_categoria = $id ";
$busca = $sistema->DB_fetch_array("SELECT A.*, DATE_FORMAT(A.data,'%d/%m/%Y') data FROM tb_espaco_arquivos A INNER JOIN tb_espaco_arquivos_has_tb_espaco_usuarios B ON B.id_arquivo = A.id INNER JOIN tb_espaco_arquivos_has_tb_espaco_categorias C ON C.id_arquivo = B.id_arquivo WHERE B.id_usuario = {$_SESSION['corretor_id']} $where AND A.titulo LIKE '%$arquivo%' GROUP BY A.id");
if ($busca->num_rows) {
    foreach ($busca->rows as $arquivo) {
        $nomes = "";
        $cats = $sistema->DB_fetch_array("SELECT B.nome FROM tb_espaco_arquivos_has_tb_espaco_categorias A INNER JOIN tb_espaco_categorias B ON B.id = A.id_categoria WHERE A.id_arquivo = {$arquivo['id']} ORDER BY B.id");
        if ($cats->num_rows) {
            $separador = "";
            foreach ($cats->rows as $cat) {
                $nomes .= $separador . $cat['nome'];
                $separador = ", ";
            }
        }
        $resultArquivo .= '
                    <div class="col-md-12 linha-arquivo center">
                        <div class="col-sm-3">' . $arquivo['titulo'] . '</div>
                        <div class="col-sm-3">' . $nomes . '</div>
                        <div class="col-sm-2"><a href="' . $sistema->root_path . $sistema->upload_path . $arquivo['arquivo'] . '" download><img src="img/icon-download.gif" alt=""></a></div>
                        <div class="col-sm-2">'. $arquivo['tamanho'] .'</div>
                        <div class="col-sm-2">'. $arquivo['data'] .'</div>
                        <div class="clear"></div>
                    </div>   
                ';
    }
} else {
    $resultArquivo = '<div style="text-align:center">Arquivo não encontrado!</div>';
}
echo json_encode(Array('categoria' => $resultCategoria, 'arquivo' => $resultArquivo));
?>