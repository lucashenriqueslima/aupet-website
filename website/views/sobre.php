<?php
	$pagina = "sobre";
	$currentpage = $sistema->seo_pages[$pagina];
    $sobre = $sistema->DB_fetch_array("SELECT * FROM hbrd_cms_sobre WHERE id = 1;")->rows[0];
    $sobre['galeria'] = $sistema->DB_fetch_array("
        SELECT 
            *
        FROM
            hbrd_cms_sobre_galeria A
                LEFT JOIN
            hbrd_cms_sobre_galeria_fotos B ON B.id_galeria = A.id
        WHERE
            A.id_sobre = {$sobre['id']} AND A.stats = 1
        ORDER BY A.id, B.ordem")->rows;
?>
<?php include "_inc_headers.php"; ?>
<body id="<?= $pagina; ?>">
<?php include "_inc_header.php"; ?>

<section class="institucional_header">
	<div class="content">
		<div class="text">
            <h1> <?= $sistema->trataTexto($currentpage['seo_pagina_title']); ?></h1>
			<p><?= $sistema->trataTexto($currentpage['seo_pagina_conteudo']); ?></p>
		</div>
		<div class="image" style='background-image: url("<?= $sistema->getImageFileSized($currentpage['imagem'], $currentpage['crop_w'], $currentpage['crop_h']); ?>");'></div>
	</div>
</section>

<?php if (!empty($sobre)) : ?>
<section class="institucional_text">
    <div class="content">
		<div class="imagem" style='background-image: url("<?= $sistema->getImageFileSized($sobre['imagem'], 580, 660); ?>");'></div>
		<div class="text_content">
			<svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="81" height="79.903" viewBox="0 0 81 79.903"><defs><linearGradient id="linear-gradient" x1="0.5" x2="0.5" y2="1" gradientUnits="objectBoundingBox"><stop offset="0" stop-color="#0098c5"/><stop offset="1" stop-color="#772e8a"/></linearGradient></defs><g id="bone" transform="translate(0 -3.467)"><g id="Grupo_758" data-name="Grupo 758" transform="translate(0 3.467)"><g id="Grupo_757" data-name="Grupo 757"><path id="Caminho_3531" data-name="Caminho 3531" d="M77.508,20.516a11.864,11.864,0,0,0-8.444-3.5,12.037,12.037,0,0,0-1.729.124,11.946,11.946,0,1,0-21.892,4.694L19.023,48.256a11.944,11.944,0,1,0-7.081,21.563,12.037,12.037,0,0,0,1.729-.124,11.946,11.946,0,1,0,21.443-5.348L61.366,38.1a11.944,11.944,0,0,0,16.142-17.58ZM75.27,35.169a8.78,8.78,0,0,1-12.416,0c-.11-.11-.221-.227-.33-.349a1.582,1.582,0,0,0-2.3-.062L31.851,63.135a1.582,1.582,0,0,0-.022,2.215A8.824,8.824,0,0,1,31.7,77.634a8.781,8.781,0,0,1-14.314-9.583,1.582,1.582,0,0,0-2.068-2.068,8.78,8.78,0,1,1,2.7-14.442,1.582,1.582,0,0,0,2.215-.023l28.4-28.4a1.582,1.582,0,0,0,.1-2.126,8.779,8.779,0,1,1,14.882-2.207,1.582,1.582,0,0,0,2.069,2.069A8.78,8.78,0,0,1,75.27,35.169Z" transform="translate(0 -3.467)" fill="url(#linear-gradient)"/></g></g><g id="Grupo_760" data-name="Grupo 760" transform="translate(24.612 42.194)"><g id="Grupo_759" data-name="Grupo 759"><path id="Caminho_3532" data-name="Caminho 3532" d="M165.648,248.721a1.582,1.582,0,0,0-2.237,0l-7.378,7.378a1.582,1.582,0,1,0,2.237,2.237l7.378-7.378A1.582,1.582,0,0,0,165.648,248.721Z" transform="translate(-155.569 -248.257)" fill="url(#linear-gradient)"/></g></g><g id="Grupo_762" data-name="Grupo 762" transform="translate(35.315 38.832)"><g id="Grupo_761" data-name="Grupo 761"><path id="Caminho_3533" data-name="Caminho 3533" d="M225.964,227.473a1.582,1.582,0,0,0-2.237,0l-.036.036a1.582,1.582,0,1,0,2.237,2.237l.036-.036A1.582,1.582,0,0,0,225.964,227.473Z" transform="translate(-223.227 -227.009)" fill="url(#linear-gradient)"/></g></g></g></svg>
			<div class="texto">
                <?= $sobre['texto']; ?>
			</div>
        </div>
	</div>
</section>
<?php endif; ?>

<?php if (!empty($sobre['galeria'])) : ?>
<section class="institucional_beneficios">
	<h2>Nossos benefícios:</h2>
	<div class="content">
        <?php foreach ($sobre['galeria'] as $foto) : ?>
		<div class="item">
			<div class="icone" style='background-image: url("<?= $sistema->getImageFileOriginal($foto['url']); ?>");'></div>
			<?= $foto['legenda']; ?>
		</div>
        <?php endforeach; ?>
	</div>
</section>
<?php endif; ?>

<?php include "_inc_footer.php"; ?>
</body>
</html>
