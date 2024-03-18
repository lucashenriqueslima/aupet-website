<?php
    $pagina = "blog";
    $currentpage = $sistema->seo_pages[$pagina];
    $order = " ORDER BY A.data DESC, A.id DESC";
    $where = "";
    $urlFiltro = $sistema->seo_pages['blog']['seo_url'];

    if (!$sistema->getParameter("pg")) {
        $pag = 1;
    } else {
        $pag = $sistema->getParameter("pg");
    }
    if ($sistema->getParameter('categoria')){
        $cat_url = $sistema->getParameter('categoria');
        $where .= " AND C.seo_url = '$cat_url'";
        $urlFiltro .= "/categoria/".$cat_url;
    }
    $palavra = $sistema->getParameter('categoria');

     //Buscar por pesquisa 
	if(isset($_GET['busca']) && $_GET['busca'] != ''){
		$where .= " AND A.titulo LIKE '%{$_GET['busca']}%'";
	}


    if ($palavra) {
        $urlFiltro .= "/categoria/" . $palavra;
    }
    $itens = 6;
    $query = "  
        SELECT 
            A.*,
            DATE_FORMAT(A.data, '%d.%m.%Y') AS data,
            CONCAT(B.seo_url_breadcrumbs, B.seo_url) AS url,
            C.id AS id_categoria,
            C.nome AS nome_categoria,
            C.seo_url
        FROM
            hbrd_cms_blog A
                INNER JOIN
            hbrd_cms_paginas B ON B.id = A.id_pagina
                LEFT JOIN
            hbrd_cms_blog_categorias C ON C.id = A.id_categoria
        WHERE
            A.stats = 1 
            AND ((A.agendar_entrada IS NULL
                AND A.agendar_saida IS NULL)
                OR (A.agendar_entrada IS NULL
                AND A.agendar_saida >= NOW())
                OR (A.agendar_entrada <= NOW()
                AND A.agendar_saida IS NULL)
                OR (A.agendar_entrada <= NOW()
                AND A.agendar_saida >= NOW()))
            {$where}
        GROUP BY A.id {$order}";
    $total = $sistema->DB_num_rows($query);
    $pagination = $sistema->pagination($itens, $total,4, $pag);
    $blogs = $sistema->DB_fetch_array("$query LIMIT " . $pagination->bd_search_starts_at . ", " . $pagination->itens_per_page)->rows;
    $categorias = $sistema->DB_fetch_array("
        SELECT 
            C.id AS id_categoria, C.nome, C.seo_url
        FROM
            hbrd_cms_blog A
                INNER JOIN
            hbrd_cms_paginas B ON B.id = A.id_pagina
                LEFT JOIN
            hbrd_cms_blog_categorias C ON C.id = A.id_categoria
        WHERE
            A.stats = 1
        GROUP BY C.id
        ORDER BY C.ordem")->rows;
?>
<?php include "_inc_headers.php"; ?>
<body id="<?= $pagina; ?>">
<?php include "_inc_header.php"; ?>

<section class="header_title" style='background-image: url("<?= $sistema->getImageFileSized($currentpage['imagem'], 1920, 350); ?>");'>
	<h1><?= $currentpage['seo_pagina_title']; ?></h1>
</section>

<section class="blog_busca">
	<form action="<?= $sistema->seo_pages['blog']['seo_url']; ?>">
		<input type="text" placeholder="Faça uma pesquisa" name="busca" id="busca">
		<button>
			<svg xmlns="http://www.w3.org/2000/svg" width="21.501" height="21.501" viewBox="0 0 21.501 21.501">
                <path id="search" d="M15.779,13.88a8.767,8.767,0,1,0-1.9,1.9L19.6,21.5l1.9-1.9L15.779,13.88Zm-7.044.9a6.047,6.047,0,1,1,6.047-6.047A6.054,6.054,0,0,1,8.735,14.784Z" transform="translate(0 -0.002)" fill="#772e8a"/>
            </svg>
		</button type="submit">
	</form>
</section>

<section class="blog_content">
	<div class="content">
        <?php if(isset($_GET['busca']) && $_GET['busca'] != '') : ?>
          <p class="resultado_busca">A busca por <b>"<?=$_GET['busca']?>"</b> encontrou <b> <?= $total ?> resultados</b>.</p>
          <?php endif ?>
		<div class="categorias">
			<div class="categoria_content">
                <?php if (!empty($blogs)) : ?>
                    <?php if (!(bool)$sistema->getParameter('categoria')) : ?>
                        <a href="<?= $sistema->seo_pages['blog']['seo_url']; ?>" class="active">Todos</a>
                    <?php else : ?>
                        <a href="<?= $sistema->seo_pages['blog']['seo_url']; ?>">Todos</a>
                    <?php endif ?>
                    <?php foreach ($categorias as $categoria) : ?>
                        <a href="<?= $currentpage['seo_url'] . '/categoria/' . $categoria['seo_url']; ?>" <?= $categoria['nome']; ?> <?= $sistema->getParameter('categoria') == $categoria['seo_url'] ? 'class="active"' : ''; ?>> <?= $categoria['nome'] ?> </a>
                    <?php endforeach; ?>

                <?php endif ?>
			</div>
		</div>
		<div class="list_item">
            <?php if (!empty($blogs)) : ?>
                <?php foreach ($blogs as $blog) : ?>
                <div class="item">
                    <div class="image" style="background-image: url('<?= $sistema->getImageFileSized($blog['imagem'], 555, 295) ?>')">
                        <a href="<?= $blog['url']; ?>"></a>
                    </div>
                    <div class="text_content">
                        <div class="data_categoria">
                            <span class="data"><?= $blog['data']; ?></span> |
                            <span class="categoria"><?= $blog['nome_categoria']; ?></span>
                        </div>
                        <h2><?= $blog['titulo']; ?></h2>
                        <p class="description"><?= $blog['descricao']; ?></p>
                        <a href="<?= $blog['url']; ?>" class="saiba_mais">Saiba mais</a>
                    </div>
                </div>
            <?php endforeach; ?>
            <?php else : ?>
                <p class="vazio">Nenhum resultado encontrado.</p>
            <?php endif; ?>
		</div>
	</div>
</section>

<?php if ($total > $itens) : ?>
    <?php
        $pg_atual = $sistema->getParameter("pg");
        if (!$pg_atual) {
            $pg_atual = 1;
        }
        $anterior = $pg_atual - 1;
        if ($anterior < 1) {
            $anterior = 1;
        }
    ?>
<section class="paginacao">
	<div class="content">
        <?php if ($pg_atual > 1) : ?>
            <a href="<?= $urlFiltro ?>/pg/<?= $anterior ?>"><small style="font-size: small">Anterior</small></a>
        <?php endif ?>
        <?php for($i = $pagination->range_initial_number; $i <= $pagination->range_end_number; $i++) : ?>
            <?php if ($i > 0) : ?>
                <a href="<?= $urlFiltro ?>/pg/<?= $i ?>" class="<?php if ($pagination->page_current == $i) echo "active";?>"><?= $i;?></a>
            <?php endif ?>
        <?php endfor ?>
        <?php $proximo = $pg_atual + 1; ?>
        <?php if ($pg_atual < $pagination->pages_total) : ?>
            <a href="<?= $urlFiltro ?>/pg/<?= $proximo ?>"><small style="font-size: small">Próximo</small></a>
        <?php endif ?>
    </div>
</section>
<?php endif ?>

<?php include "_inc_footer.php"; ?>
</body>
</html>
