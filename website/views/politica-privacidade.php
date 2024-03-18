<?php
	$pagina = "politica-privacidade";

	$blogPage = $sistema->seo_pages[$pagina];
	// CURRENTPAGE DATA ---- //
	$query = $sistema->DB_fetch_array("SELECT A.*, DATE_FORMAT(A.data, '%d/%m/%Y') data, A.id_pagina AS id_pagina, B.seo_title, B.seo_url, B.seo_keywords, B.seo_description FROM hbrd_cms_blog  A INNER JOIN hbrd_cms_paginas B ON A.id_pagina = B.id WHERE A.id_pagina = $dynamic_id GROUP BY A.id");
	if (!$query->num_rows) {
		header('Location: ' . $sistema->root_path . $sistema->seo_pages['home']['seo_url']);
		die();
	}

	$currentpage = $query->rows[0];
	$currentpage['seo_imagem'] = $sistema->getImageFileSized($currentpage['imagem'], 500, 320);
	$currentpage['seo_imagemw'] = 500;
	$currentpage['seo_imagemh'] = 320;
	// --------------------------------------------- //
	$protocol = stripos($_SERVER['SERVER_PROTOCOL'], 'https') === 0 ? 'https://' : 'http://';
	$urlPagina = $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
	$query = $sistema->DB_fetch_array("SELECT A.* FROM hbrd_cms_blog_categorias A INNER JOIN hbrd_cms_blog_hbrd_cms_blog_categorias B ON B.id_categoria = A.id WHERE A.stats = 1 AND B.id_blog = " . $currentpage['id']);
	if ($query->num_rows) {
		$noticiaCategorias = array_map(function ($categoria) {
			return $categoria['nome'];
		}, $query->rows);

		$noticiaCategorias = implode(', ', $noticiaCategorias);
	}

	$categorias = $sistema->DB_fetch_array("SELECT A.* FROM hbrd_cms_blog_categorias A WHERE A.stats = 1 AND EXISTS (SELECT * FROM hbrd_cms_blog_hbrd_cms_blog_categorias B INNER JOIN hbrd_cms_blog C ON C.id = B.id_blog WHERE B.id_categoria = A.id AND C.stats = 1) ORDER BY A.ordem");

	$agendamento = ' AND (';
	$agendamento .= '(A.agendar_entrada IS NULL AND A.agendar_saida IS NULL)';
	$agendamento .= ' OR ';
	$agendamento .= '(A.agendar_entrada <= NOW() AND A.agendar_saida >= NOW())';
	$agendamento .= ' OR ';
	$agendamento .= '(A.agendar_entrada <= NOW() AND A.agendar_saida IS NULL)';
	$agendamento .= ' OR ';
	$agendamento .= '(A.agendar_entrada IS NULL AND A.agendar_saida >= NOW())';
	$agendamento .= ') ';

	$maisVisitadas = $sistema->DB_fetch_array("SELECT A.*, DATE_FORMAT(A.data, '%d/%m/%Y') data, CONCAT(B.seo_url_breadcrumbs,B.seo_url) seo_url, (SELECT COUNT(DISTINCT session) FROM hbrd_cms_seo_acessos WHERE id_seo = B.id) AS visitas FROM hbrd_cms_blog A INNER JOIN hbrd_cms_paginas B ON B.id = A.id_pagina WHERE A.stats = 1 $agendamento GROUP BY A.id ORDER BY visitas DESC");


	$galerias = $sistema->DB_fetch_array("SELECT A.* FROM hbrd_cms_blog_galerias A INNER JOIN hbrd_cms_blog_galerias_fotos B ON B.id_galeria = A.id WHERE A.stats = 1 AND A.id_blog = {$currentpage['id']} GROUP BY A.id ORDER BY A.ordem ");
	function getFotos($id = null)
	{
		global $sistema;
		if ($id != null) {
			$query = $sistema->DB_fetch_array("SELECT * FROM hbrd_cms_blog_galerias_fotos A WHERE A.id_galeria = $id ORDER BY A.ordem");
			return $query;
		}
	}


	include "_inc_headers.php";
?>
<title>Política de privacidade</title>
</head>
<body id="<?php echo $pagina; ?>">


<?php 
	include "_inc_header.php";
?>


<section class="header_title" style="background-image: url(<?php echo $sistema->root_path;?>website/img/temporario/blog/banner[1920x350].jpg)">
	<h1>Política de privacidade</h1>
</section>

<section class="blog_interno">
	<div class="content">
		<div class="editable-content">
			<p>Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua. At vero eos et accusam et justo duo dolores et ea rebum.</p>
			<p>Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua. At vero eos et accusam et justo duo dolores et ea rebum.Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua. At vero eos et accusam et justo duo dolores et ea rebum.</p>
			<p>Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua. At vero eos et accusam et justo duo dolores et ea rebum.Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua. At vero eos et accusam et justo duo dolores et ea rebum.</p>
			<p>Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua. At vero eos et accusam et justo duo dolores et ea rebum.Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua. At vero eos et accusam et justo duo dolores et ea rebum.</p>
			<p>Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua. At vero eos et accusam et justo duo dolores et ea rebum.Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua. At vero eos et accusam et justo duo dolores et ea rebum.</p>
		</div>
		<div class="clear"></div>
	</div>
</section>


<?php 
	include "_inc_footer.php";
?>

</body>
</html>
