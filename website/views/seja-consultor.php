<?php
	$pagina = "seja-consultor";
	$currentpage = $sistema->seo_pages[$pagina];
	include "_inc_headers.php";
?>
<title>Seja consultor</title>
</head>
<body id="<?php echo $pagina; ?>">

<?php 
	include "_inc_header.php";
?>

<section class="header_title" style="background-image: url(website/img/temporario/blog/banner[1920x350].jpg)">
	<h1>Seja um consultor Aupet Heinsten</h1>
</section>

<section class="seja_consultor">
	<div class="content">
		<h2>Venha ser um consultor da Aupetheinsten. Faça seu cadastro e garanta uma renda que jamais imaginou.</h2>
		<form action="javascript:;" method="POST" name="form_seja_consultor" enctype="multipart/form-data">
			<div class="item half_item">
				<input type="text" placeholder=" " name="nome">
				<label class="placeholder" for="">Nome completo</label>
			</div>
			<div class="item half_item">
				<input type="text" placeholder=" " name="email">
				<label class="placeholder" for="">Seu e-mail</label>
			</div>
			<div class="item half_item">
				<input type="text" placeholder=" " mask="telefone" name="telefone">
				<label class="placeholder" for="">Whatsapp</label>
			</div>
			<div class="item half_item">
				<select vazio="true" name="como_conheceu" id="como_conheceu">
					<option value=""></option>
					<option value="whatsapp">Whatsapp </option>
				</select>
				<label class="placeholder" for="">Como conheceu a Aupet?</label>
			</div>

			<div class="trabalhando">
				<p>Está trabalhando atualmente?</p>
				<div class="trabalhando_content">
					<input type="radio" name="trabalhando" id="radio_1" value="sim">
					<label for="radio_1">Sim</label>
					<input type="radio" name="trabalhando" id="radio_2" value="nao">
					<label for="radio_2">Não</label>
				</div>
			</div>

			<div class="item">
				<textarea name="mensagem" id="" cols="30" rows="10" placeholder="Conte-nos sobre sua experiência profissional:"></textarea>
			</div>
			<div class="item">
				<input type="file" id="arquivo" name="curriculo">
				<label for="arquivo">Anexar currículo</label>
			</div>
			<div class="item">
				<input type="checkbox" id="termo" name="termo">
				<label class="checkbox" for="termo">Quero receber ofertas via e-mail, whatsapp e demais canais.</label>
			</div>
			<button class="clickformsubmit enviar" >Enviar</button>
		</form>
	</div>
</section>

<?php 
	include "_inc_footer.php";
?>

</body>
</html>
