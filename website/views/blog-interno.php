<?php
	$pagina = "blog-interno";
	$currentpage = $sistema->DB_fetch_array("
        SELECT
            A.*,
            B.id AS id_seo,
            B.seo_title,
            B.seo_description,
            B.seo_keywords,
            C.nome AS nome_categoria,
            IFNULL(A.imagem, B.imagem) AS imagem,
            DATE_FORMAT(A.data, '%d/%m/%Y') AS data,
            CONCAT(B.seo_url_breadcrumbs, B.seo_url) AS url
        FROM
            hbrd_cms_blog A
                INNER JOIN
            hbrd_cms_paginas B ON A.id_pagina = B.id
                INNER JOIN
            hbrd_cms_blog_categorias C ON C.id = A.id_categoria
        WHERE
            A.id_pagina = {$dynamic_id} and A.stats = 1")->rows[0];
	if(!(bool)$currentpage) {
	    return include "404.php";
    }
    $currentpage['galeria'] = $sistema->DB_fetch_array("SELECT * FROM hbrd_cms_blog_galerias_fotos B WHERE B.id_galeria = {$currentpage['id']} ORDER BY B.ordem")->rows;
?>
<?php include "_inc_headers.php"; ?>
<body id="<?= $pagina; ?>">
<?php include "_inc_header.php"; ?>

<section class="header_title" style='background-image: url("<?= $sistema->getImageFileSized($sistema->seo_pages['blog']['imagem'], 1920, 350); ?>");'>
	<h2><?= $sistema->seo_pages['blog']['seo_pagina_title']; ?></h2>
</section>

<section class="blog_interno">
	<div class="content">
		<div class="data_categoria">
			<span class="data"><?= $currentpage['data']; ?></span> |
			<span class="categoria"><?= $currentpage['nome_categoria']; ?></span>
		</div>
		<h1><?= $currentpage['titulo']; ?></h1>
		<div class="editable-content">
			<p><img src="<?= $sistema->getImageFileSized($currentpage['imagem'], 555, 295); ?>" alt="" class="imgRight"></p>
			<p><?= $sistema->trataTexto($currentpage['conteudo']); ?></p>
		</div>
		<div class="clear"></div>

		<div class="galeria">
            <?php foreach ($currentpage['galeria'] as $index => $foto) : ?>
                <?php list($width,$height) = getimagesize($foto['url']) ?>
                <?php if (!empty($foto['url'])) : ?>
                    <a class="galeria_item" href="<?= $foto['url']; ?>" style='background-image: url("<?= $foto['url']; ?>");' title="<?= $foto['legenda']; ?>" data-index="<?= $index; ?>" data-width="<?= $width; ?>" data-height="<?= $height; ?>"></a>
                <?php endif; ?>
            <?php endforeach ?>
		</div>
		<a href="<?= $sistema->seo_pages['blog']['seo_url']; ?>" class="voltar">voltar</a>
	</div>
</section>

<?php include "_inc_footer.php"; ?>
</body>
</html>
