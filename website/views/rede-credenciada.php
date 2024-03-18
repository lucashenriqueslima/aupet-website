<?php
	$pagina = "rede-credenciada";
	$currentpage = $sistema->seo_pages[$pagina];

	include "_inc_headers.php";

	$where = "";
	$join = "";

	//BUSCA POR PLANOS
	if(isset($_GET['plano']) && $_GET['plano'] != ''){
		$join .= "LEFT JOIN hbrd_app_clinica_use_beneficio D ON D.id_clinica = A.id ";
		$where .= " AND D.id_beneficio = {$_GET['plano']}";
	}

	//BUSCA POR ESPECIALIDADES
	if(isset($_GET['especialidade']) && $_GET['especialidade'] != ''){
		$join .= "LEFT JOIN hbrd_app_clinica_use_beneficio E ON E.id_clinica = A.id ";
		$where .= " AND E.id_beneficio = {$_GET['especialidade']}";
	}

	//BUSCA POR ESTADO
	if(isset($_GET['estado']) && $_GET['estado'] != ''){
		$where .= " AND C.id = {$_GET['estado']}";
	}

	//BUSCA POR CIDADE
	if(isset($_GET['cidade']) && $_GET['cidade'] != ''){
		$where .= " AND B.id = {$_GET['cidade']}";
	}

	$stores = $sistema->DB_fetch_array("SELECT A.id, A.nome_fantasia, A.latitude, A.longitude, A.zoom, F.rua, F.bairro, F.telefone, F.telefone2, B.cidade AS cidade, C.uf AS uf FROM hbrd_app_clinica A LEFT JOIN hbrd_app_pessoa F ON F.id = A.id_pessoa INNER JOIN hbrd_main_util_city B ON B.id = F.id_cidade INNER JOIN hbrd_main_util_state C ON C.id = F.id_estado WHERE A.stats = 1 AND A.situacao = 'aprovado' AND A.delete_at IS NULL $where");

	$beneficios = $sistema->DB_fetch_array("SELECT A.*, B.id_clinica FROM hbrd_app_plano_beneficio A LEFT JOIN hbrd_app_clinica_use_beneficio B ON B.id_beneficio = A.id AND B.id_clinica = 0 ORDER BY A.id");
	$planos = $sistema->DB_fetch_array("SELECT A.* FROM hbrd_app_planos A WHERE A.delete_at IS NULL ORDER BY A.ordem");

	$estados = $sistema->DB_fetch_array("SELECT * FROM hbrd_main_util_state");
?>
<title>Seja um credenciado</title>
</head>
<body id="<?php echo $pagina; ?>">

<?php 
	include "_inc_header.php";
?>

<section class="rede_header" style='background-image: url("<?= $sistema->getImageFileSized($currentpage['imagem'], 1920, 420); ?>")'>
	<div class="content">
		<h1><?= $sistema->trataTexto($currentpage['seo_pagina_title']); ?></h1>
		<p><?= $sistema->trataTexto($currentpage['seo_pagina_conteudo']); ?></p>
		<a href="<?= $sistema->trataTexto($currentpage['link_conteudo']); ?>">CADASTRE SUA EMPRESA</a>
	</div>
</section>

<section class="rede_content" filter-open="false">
	<h3><?= $sistema->trataTexto($currentpage['text_aux']); ?></h3>

	<div class="filter">
		<button class="filtrar_mobile">Filtre os resultados</button>
		<form action="">
			<p>Filtrar resultados</p>
			
			<select name="plano" id="">
				<option value="">Plano</option>
				<?php if ($planos->num_rows) : ?>
					<?php foreach ($planos->rows as $plano) : ?>
						<option <?php if(isset($_GET['plano']) && $plano['id'] == $_GET['plano']) echo "selected"; ?> value="<?php echo $plano['id']; ?>"><?php echo $plano['titulo']; ?></option>
					<?php endforeach; ?>
				<?php endif; ?>
			</select>

			<select name="especialidade" id="">
				<option value="">Especialidade</option>
				<?php if ($beneficios->num_rows) : ?>
					<?php foreach ($beneficios->rows as $especialidade) : ?>
						<?php if ($especialidade['stats'] !="0") : ?>
						<option <?php if(isset($_GET['especialidade']) && $especialidade['id'] == $_GET['especialidade']) echo "selected"; ?> value="<?php echo $especialidade['id']; ?>"><?php echo $especialidade['nome']; ?></option>
						<?php endif; ?>
					<?php endforeach; ?>
				<?php endif; ?>
			</select>

			<select name="estado" id="id_estado">
				<option value="" id="">Estado</option>
				<?php if ($estados->num_rows) : ?>
					<?php foreach ($estados->rows as $estado) : ?>
						<option <?php if(isset($_GET['estado']) && $estado['id'] == $_GET['estado']) echo "selected"; ?> value="<?php echo $estado['id']; ?>"><?php echo $estado['estado']; ?></option>
					<?php endforeach; ?>
				<?php endif; ?>
			</select>

			<select name="cidade" id="">
				<option value="">Cidade</option>
			</select>
		</form>
	</div>

	<div class="content_unidades">
		<?php if (!$stores->rows) : ?>
			<p class="vazio">Nenhum resultado encontrado.</p>
		<?php endif ?>
		<?php foreach ($stores->rows as $store) : ?>
			<?php
				$beneficios = $sistema->DB_fetch_array("SELECT D.nome FROM hbrd_app_plano_beneficio D INNER JOIN hbrd_app_clinica_use_beneficio E ON E.id_beneficio = D.id AND E.id_clinica = {$store['id']} ORDER BY D.id");
			?>

			<div class="item">
				<h2><?php echo $store['nome_fantasia'] ?></h2>
				<div class="endereco"><?php echo $store['rua'] ?>, nº <?php echo $store['numero'] ?> - St. <?php echo $store['bairro'] ?>. <?php echo $store['cidade'] ?>-<?php echo $store['uf'] ?>.</div>
				<div class="especialidades">
					<?php
						for($i = 0; $i < $beneficios->num_rows; $i++){
							if($i + 1 < $beneficios->num_rows){ echo $beneficios->rows[$i]['nome'] . ", "; }else{ echo $beneficios->rows[$i]['nome'] . "."; }
						}
					?>
				</div>
				<div class="telefones"><?php echo $store['telefone'] ; if(!($store['telefone2'] == "" || $store['telefone1'] == null)) echo " / " . $store['telefone1'] ?></div>
				<a href="https://www.google.com/maps/search/?api=1&query=<?php echo $store['latitude'] ?>,<?php echo $store['longitude'] ?>" class="ver_mapa" target="_blank">ver no mapa</a>
			</div>
		<?php endforeach ?>
	</div>
</section>

<?php 
	include "_inc_footer.php";
?>

<script>
	$(document).on("click", function(e) {
		var modal_content = document.querySelector("section.rede_content .filter form");
		var dentro = modal_content.contains(e.target);

		var button_open = document.querySelector("section.rede_content .filter button.filtrar_mobile");
		var dentro_button = button_open.contains(e.target);

		if (dentro) {
			$('section.rede_content').attr('filter-open', 'true');
		}
		else{
			if(dentro_button){
				$('section.rede_content').attr('filter-open', 'true');
			}
			else{
				$('section.rede_content').attr('filter-open', 'false');
			}
		}
	});

	<?php if(isset($_GET['estado']) && $_GET['estado'] != ''): ?>
		$(window).on("load", function() {
			$.ajax({
				method:"POST",
				url:"sistema/main/general/getCidadesByEstado",
				data:{id:<?php echo $_GET['estado'] ?>},
				async:false
			}).done(function(data){
				$("select[name=cidade]").html(data);
				$("select[name=cidade]").find("option[value='<?php echo $_GET['cidade'] ?>']").attr("selected","selected");
			});
		})
	<?php endif ?>

	$('select').on("change",function() {
		$('form').submit();
	});

	$("#id_estado").on("change",function () {
		var id = $(this).val();
		if(id !== ""){
			$.ajax({
				method:"POST",
				url:"sistema/main/general/getCidadesByEstado",
				data:{id:id},
				async:false
			}).done(function(data){
				$("select[name=cidade]").html(data);
			});
		}
	});
</script>
</body>
</html>
