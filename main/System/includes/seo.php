<?php

$seo->id = $_POST["id_seo"];
$seo->seo_title = $_POST["seo_title"];
$seo->seo_description = $_POST["seo_description"];
$seo->seo_keywords = $_POST["seo_keywords"];
$seo->seo_url = $_POST["seo_url"];
$seo->seo_scripts = $_POST["seo_scripts"];


if ($seo->seo_url == "") {
    $seo->seo_url = $seo->url_secundaria;
}

$seo->seo_url = $this->formataUrlAmiga($seo->seo_url);

$seo_request = new stdClass();

if ($seo->id == "") {

    if (!$this->validaUrlAmiga($seo->seo_url)) {
        $seo->seo_url = $seo->seo_url . "-" . uniqid();
    }

    $fields = array('seo_pagina_dinamica', 'seo_pagina', 'seo_title', 'seo_description', 'seo_keywords', 'seo_url', 'seo_scripts');
    $values = array(1, "'" . $seo->pagina . "'", "'" . $seo->seo_title . "'", "'" . $seo->seo_description . "'", "'" . $seo->seo_keywords . "'", "'" . $seo->seo_url . "'", "'" . $seo->seo_scripts . "'");

    if (isset($seo->breadcrumbs)) {
        array_push($fields, "seo_url_breadcrumbs");
        array_push($values, "'" . $seo->breadcrumbs . "'");
    }

    $resposta->query = "tb_seo_paginas " . implode(',', $fields) . implode(',', $values);

    $seo_request->response = $this->DB_insert("tb_seo_paginas", implode(',', $fields), implode(',', $values));



    $seo_request->id = $seo_request->response->insert_id;
    $seo_request->response = $seo_request->response->query;

    unset($fields,$values);
} else {

    if (!$this->validaUrlAmiga($seo->seo_url, $seo->id)) {
        $seo->seo_url = $seo->seo_url . "-" . uniqid();
    }


    $fields_values = array(
        "seo_pagina='" . $seo->pagina . "'",
        "seo_title='" . $seo->seo_title . "'",
        "seo_description='" . $seo->seo_description . "'",
        "seo_keywords='" . $seo->seo_keywords . "'",
        "seo_url='" . $seo->seo_url . "'",
        "seo_scripts='" . $seo->seo_scripts . "'"
    );

    if (isset($seo->breadcrumbs)) {
        array_push($fields_values, "seo_url_breadcrumbs='" . $seo->breadcrumbs . "'");
    }

    $seo_request->response = $this->DB_update("tb_seo_paginas", implode(',', $fields_values) . " WHERE id=" . $seo->id);
    unset($fields_values);
}
?>