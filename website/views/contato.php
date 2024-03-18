<?php
	$pagina = "contato";
	$currentpage = $sistema->seo_pages[$pagina];
    $form = $sistema->DB_fetch_array("SELECT * FROM hbrd_cms_formularios WHERE id = 1")->rows[0];
?>
<?php include "_inc_headers.php"; ?>
<body id="<?php echo $pagina; ?>">
<?php include "_inc_header.php"; ?>

<section class="header_title" style='background-image: url("<?= $sistema->getImageFileSized($currentpage['imagem'], $currentpage['crop_w'], $currentpage['crop_h']); ?>");'>
    <h1><?= $currentpage['seo_pagina_title']; ?></h1>
</section>

<section class="contato">
	<div class="content">
		<?= $currentpage['seo_pagina_conteudo']; ?>
		<form action="javascript:;" method="POST" name="form_contato" enctype="multipart/form-data" style='background-image: url("<?= $sistema->getImageFileSized($currentpage['imagem_dois'], 600, 600); ?>");'>
			<div class="item">
				<input type="text" placeholder=" " name="nome">
				<label class="placeholder" for="">Seu nome</label>
			</div>
			<div class="item">
				<input type="text" placeholder=" " name="email">
				<label class="placeholder" for="">Seu e-mail</label>
			</div>
			<div class="item">
				<input type="text" placeholder=" " mask="telefone" name="telefone">
				<label class="placeholder" for="">Seu telefone</label>
			</div>
			<div class="item">
				<select vazio="true" name="interesse" id="">
					<option value=""></option>
                    <option value="Associado">Associado</option>
                    <option value="Clínica">Clínica</option>
                    <option value="Consultor">Consultor</option>
				</select>
				<label class="placeholder" for="">Qual interesse?</label>
			</div>
			<div class="item">
				<textarea name="comentario" id="" cols="30" rows="10" placeholder="Deixe um recado"></textarea>
			</div>
            <?php if((bool)$form['ckbox_1_stats'] && (bool)$form['ckbox_1_texto']) : ?>
			<div class="item">
				<input placeholder="CheckBox 1" type="checkbox" id="checkbox1" name="checkbox1">
				<label class="checkbox" for="checkbox1"><?= $form['ckbox_1_texto']; ?></label>
			</div>
            <?php endif; ?>
            <?php if((bool)$form['ckbox_2_stats'] && (bool)$form['ckbox_2_texto']) : ?>
            <div class="item">
				<input placeholder="CheckBox 2" type="checkbox" id="checkbox2" name="checkbox2">
				<label class="checkbox" for="checkbox2"><?= $form['ckbox_2_texto']; ?></label>
			</div>
            <?php endif; ?>
			<button class="clickformsubmit enviar">Enviar</button>
		</form>
	</div>
</section>

<?php include "_inc_footer.php"; ?>
</body>
</html>
