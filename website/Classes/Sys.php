<?php

namespace website\Classes;

use System\Core\Bootstrap;
use System\Libs\MobileDetect;

class Sys extends Bootstrap {

    public $upload_folder = "../main/uploads/";
    public $log_path = "admin/log/";
    public $upload_path = "main/uploads/";
    public $pagination_tag = "pg";
    public $pagination_page = 1;
    public $seo_pages = array();
    public $seo_dynamic_pages = array();
    public $is_mobile = 0;

    function __construct() {

        parent::__construct();
        $this->upload_folder = dirname(dirname(__dir__)) . "/main/uploads/";

        // -----------------------------------------------------------------------
        // BUSCA TODAS AS PÁGINAS QUE NÃO SÃO DINAMICAS
        // --------------------------------------------------------  -------------

        // $query = $this->DB_fetch_array("SELECT *, id id_pagina FROM hbrd_cms_paginas");
        // $i = $query->num_rows - 1;

        // while ($i >= 0) {

        //     if ($query->rows[$i]['seo_pagina_dinamica'] == 0) {
        //         $this->seo_pages[$query->rows[$i]['seo_pagina']] = new \stdClass();
        //         $this->seo_pages[$query->rows[$i]['seo_pagina']] = $query->rows[$i];
        //     } else {
        //         $this->seo_dynamic_pages[$query->rows[$i]['seo_pagina']][$query->rows[$i]['id']] = $query->rows[$i];
        //     }

        //     $i--;
        // }

        $pages = $this->DB_fetch_array("SELECT *, id id_pagina FROM hbrd_cms_paginas")->rows;
        foreach ($pages as $page) {
            if ($page['seo_pagina_dinamica'] == 0) {
                $this->seo_pages[$page['seo_pagina']] = new \stdClass();
                $this->seo_pages[$page['seo_pagina']] = $page;
            } else {
                $this->seo_dynamic_pages[$page['seo_pagina']][$page['id']] = $page;
            }
            if((bool)$page['interna']) $this->internas[$page['seo_url']] = $page['seo_pagina'];
        }
        if ($this->getParameter("pag")) {
            $this->pagination_page = $this->getParameter("pag");
        }


        // -----------------------------------------------------------------------
    }

    function getCidades($id)
    {
        $dados = $this->DB_fetch_array("SELECT * FROM hbrd_main_util_city WHERE id_estado='" . $id . "' ORDER BY cidade");
        return $dados->rows;
    }

    public function getBots() {
        $bots = array();
        $query = $this->DB_fetch_array("SELECT * FROM hbrd_cms_rastreios_bots");
        if ($query->num_rows) {
            foreach ($query->rows as $row) {
                $bots[] = $row['identificador'];
            }
        }
        return $bots;
    }

    function quartos($array = null) {
        $quartos = '';
        if ($array != null) {
            $separador = "";
            $i = 1;
            $plural = "";
            foreach ($array as $row) {
                if ($row > 1)
                    $plural = "s";
                $quartos .= $separador . $row;
                $separador = ", ";

                $i++;

                if (count($array) == $i)
                    $separador = " e ";
            }
            $quartos = "{$quartos} Quarto{$plural}";
        }

        return $quartos;
    }

    function suites($array = null) {
        $quartos = '';
        if ($array != null) {
            $separador = "";
            $i = 1;
            $plural = "";
            foreach ($array as $row) {
                if ($row > 1)
                    $plural = "s";
                $quartos .= $separador . $row;
                $separador = ", ";

                $i++;

                if (count($array) == $i)
                    $separador = " e ";
            }
            $quartos = "{$quartos} Suíte{$plural}";
        }

        return $quartos;
    }

    function vagas($array = null) {
        $quartos = '';
        if ($array != null) {
            $separador = "";
            $i = 1;
            $plural = "";
            foreach ($array as $row) {
                if ($row > 1)
                    $plural = "s";
                $quartos .= $separador . $row;
                $separador = ", ";

                $i++;

                if (count($array) == $i)
                    $separador = " e ";
            }
            $quartos = "{$quartos} vaga{$plural}";
        }

        return $quartos;
    }

    function quartosBanners($array = null, $array1 = null) {
        $quartos = '';
        if ($array != null) {
            $separador = "";
            $i = 1;

            for ($c = 0; $c < count($array); $c++) {
                $plural = "";
                $suites = "";
                $pluralSuites = "";
                if ($array1[$c] > 0) {
                    if ($array1[$c] > 1)
                        $pluralSuites = "s";
                    $suites = "com " . $array1[$c] . " suíte" . $pluralSuites;
                }
                if ($array[$c] > 1)
                    $plural = "s";
                $quartos .= $separador . $array[$c] . " Qto{$plural} " . $suites;
                $separador = ", ";

                $i++;

                if (count($array) == $i)
                    $separador = " e ";
            }
        }

        return $quartos;
    }

    function isInterna($rota_internas, $i) {
        global $uri;
        foreach ($rota_internas as $key => $value) {
            if (is_array($value)) {
                if (isset($uri[$i]) AND $key == $uri[$i])
                    return $this->isInterna($value, $i + 1);
            }else if (is_string($key) AND isset($uri[$i]) AND $key == $uri[$i] AND isset($uri[$i + 1]) AND $value == $uri[$i + 1] AND isset($uri[$i + 2]) AND $uri[$i + 2] != "" AND ! isset($uri[$i + 3]))
                return $i + 2;
            else if (isset($uri[$i + 1]) AND $uri[$i + 1] != "" AND $uri[$i] == $value AND ( !isset($uri[$i + 2])))
                return $i + 1;
        }

        return false;
    }

    public function getCategorias($idProduto) {
        $categorias = "";
        $query = $this->DB_fetch_array("SELECT B.nome FROM hbrd_cms_empreendimentos A INNER JOIN hbrd_cms_empreendimentos_categorias B ON B.id = A.id_categoria WHERE A.id = $idProduto ORDER BY B.ordem");
        if ($query->num_rows) {
            $separador = "";
            foreach ($query->rows as $categoria) {
                $categorias .= $separador . $categoria['nome'];
                $separador = ", ";
            }
        }

        //return $categorias;
        return $categoria['nome'];
    }

    function putBetween($context, $piece, $glue_after, $glue_before) {
        $piece = explode(" ", $piece);
        $context = explode(" ", $context);
        for ($j = 0; $j < count($context); $j++) {
            for ($z = 0; $z < count($piece); $z++) {
                if ($context[$j] == $piece[$z])
                    $context[$j] = $glue_after . $context[$j] . $glue_before;
            }
        }
        return implode(" ", $context);
    }

    //detecta página (URI) corrente e devolve o número da página corrente para paginação substituindo o $pagination_page (correção 06/08/214)
    function paginaCorrente() {
        $pag = explode("/", $_SERVER['REQUEST_URI']);

        $pagina = 1;
        if (is_numeric($pag[3])) {
            $pagina = (int) $pag[3];
        } else if (is_numeric($pag[4])) {
            $pagina = (int) $pag[4];
        } else {

            for ($i = 0; $i < count($pag); $i++) {

                if (is_numeric($pag[$i])) {
                    $pagina = (int) $pag[$i];
                }
            }
        }

        return $pagina;
    }

    function pagination($itens = 5, $total = 0, $range = 5, $current = 0) {

        $pagination = new \stdClass();

        $pagination->itens_per_page = $itens; // quantidade de registros por página
        $pagination->itens_total = $total; // quantidade total de registros
        $pagination->range_of_numbers = $range; // quantidade de numeros visiveis (raio) na paginação
        $pagination->page_current = $current; // numero da pagina atual

        $pagination->bd_search_starts_at = $pagination->itens_per_page * ($pagination->page_current - 1);
        $pagination->range_centered_number = ceil($pagination->range_of_numbers / 2);
        $pagination->pages_total = ceil($pagination->itens_total / $pagination->itens_per_page);
        $pagination->page_prev = $pagination->page_current - 1;
        $pagination->page_next = $pagination->page_current + 1;

        $pagination->range_initial_limit = $pagination->pages_total - $pagination->range_of_numbers;

        // DEFININDO O INICIO DO RAIO
        if ($pagination->page_current <= $pagination->range_centered_number) {
            $pagination->range_initial_number = 1;
        } else {
            $pagination->range_initial_number = $pagination->page_current - $pagination->range_centered_number + 1;
        }

        // VERIFICA SE O INICIO DO RAIO ESTÁ SEU LIMITE NO FINAL
        if ($pagination->range_initial_number > $pagination->range_initial_limit) {

            // DEFINE O INICIO E FIM DO RAIO
            $pagination->range_initial_number = $pagination->range_initial_limit + 1;
            $pagination->range_end_number = $pagination->pages_total;
        } else {

            //DEFINE O FIM DO RAIO
            $pagination->range_end_number = ($pagination->range_initial_number + $pagination->range_of_numbers) - 1;
        }

        return $pagination;
    }

}

class pagination {

    public $itens_per_page, $itens_total, $bd_search_starts_at, $pages_total, $page_current, $page_prev, $page_next, $range_initial_number, $range_end_number;
    private $range_of_numbers, $page_number_from_browser;

    public function pagination($itens = 5, $total = 0, $range = 5, $current = 1) {
        $this->itens_per_page = $itens; // quantidade de registros por página
        $this->itens_total = $total; // quantidade total de registros
        $this->range_of_numbers = $range; // quantidade de numeros visiveis (raio) na paginação
        $this->page_current = $current; // numero da pagina atual

        $this->bd_search_starts_at = $this->itens_per_page * ($this->page_current - 1);
        $this->range_centered_number = ceil($this->range_of_numbers / 2);
        $this->pages_total = ceil($this->itens_total / $this->itens_per_page);
        $this->page_prev = $this->page_current - 1;
        $this->page_next = $this->page_current + 1;

        $this->range_initial_limit = $this->pages_total - $this->range_of_numbers;

        // DEFININDO O INICIO DO RAIO
        if ($this->page_current <= $this->range_centered_number) {
            $this->range_initial_number = 1;
        } else {
            $this->range_initial_number = $this->page_current - $this->range_centered_number + 1;
        }

        // VERIFICA SE O INICIO DO RAIO ESTÁ SEU LIMITE NO FINAL
        if ($this->range_initial_number > $this->range_initial_limit) {

            // DEFINE O INICIO E FIM DO RAIO
            $this->range_initial_number = $this->range_initial_limit + 1;
            $this->range_end_number = $this->pages_total;
        } else {

            //DEFINE O FIM DO RAIO
            $this->range_end_number = ($this->range_initial_number + $this->range_of_numbers) - 1;
        }
    }

}

?>
