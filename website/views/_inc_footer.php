<footer>
	<div class="content">
        <a href="<?= $sistema->seo_pages['home']['seo_url'] ?>" class="logo" style="background-image: url(<?php echo $sistema->root_path;?>website/img/temporario/header/logo.svg);"></a>
		<div class="paginas">
			<a href="<?= $sistema->seo_pages['home']['seo_url'] ?>">Home</a>
			<a href="<?= $sistema->seo_pages['sobre']['seo_url'] ?>">Sobre</a>
			<a href="<?= $sistema->seo_pages['rede-credenciada']['seo_url'] ?>">Redes credenciadas</a>
			<a href="<?= $sistema->seo_pages['blog']['seo_url'] ?>">Blog</a>
			<a href="https://app.aupetheinsten.com.br/#/cadastro/consultor">Seja um consultor</a>
			<a href="<?= $sistema->seo_pages['contato']['seo_url'] ?>">Contato</a>
		</div>
		<div class="plataformas_redes">
			<div class="plataformas">
			<?php if($conf['link_applestore']) : ?>
				<a href="<?php echo $conf['link_applestore'] ?>" target="_blank"><img src="<?php echo $sistema->root_path;?>website/img/ios-app-white.svg" alt=""></a>
			<?php endif ?>	
			<?php if($conf['link_playstore']) : ?>
				<a href="<?php echo $conf['link_playstore'] ?>" target="_blank"><img src="<?php echo $sistema->root_path;?>website/img/android-app-white.svg" alt=""></a>
			<?php endif ?>	
			</div>
			<div class="redes">
			<?php if($conf['link_facebook']) : ?>
				<a href="<?php echo $conf['link_facebook'] ?>" class="facebook" target="_blank"><svg xmlns="http://www.w3.org/2000/svg" width="13.358" height="28.975" viewBox="0 0 13.358 28.975"><path id="Caminho_2861" data-name="Caminho 2861" d="M50.632,36.223H46.67V50.738h-6V36.223H37.812v-5.1h2.855v-3.3c0-2.361,1.121-6.057,6.056-6.057l4.447.019v4.952H47.944a1.222,1.222,0,0,0-1.273,1.391v3h4.486Z" transform="translate(-37.812 -21.763)" fill="#fff"/></svg></a>
			<?php endif ?>	

			<?php if($conf['link_instagram']) : ?>
				<a href="<?php echo $conf['link_instagram'] ?>" class="instagram" target="_blank"><svg xmlns="http://www.w3.org/2000/svg" width="25.618" height="25.623" viewBox="0 0 25.618 25.623"><g id="instagram_1_" data-name="instagram (1)" transform="translate(-0.449)"><path id="Caminho_2865" data-name="Caminho 2865" d="M26,7.533a9.363,9.363,0,0,0-.6-3.108A6.564,6.564,0,0,0,21.657.676a9.387,9.387,0,0,0-3.108-.6C17.177.015,16.741,0,13.262,0S9.348.015,7.982.075a9.365,9.365,0,0,0-3.108.6A6.252,6.252,0,0,0,2.6,2.152,6.308,6.308,0,0,0,1.124,4.42a9.388,9.388,0,0,0-.6,3.108C.464,8.9.449,9.335.449,12.814s.015,3.914.075,5.281a9.362,9.362,0,0,0,.6,3.108,6.563,6.563,0,0,0,3.749,3.749,9.388,9.388,0,0,0,3.108.6c1.366.06,1.8.075,5.281.075s3.914-.015,5.281-.075a9.36,9.36,0,0,0,3.108-.6A6.554,6.554,0,0,0,25.4,21.2a9.394,9.394,0,0,0,.6-3.108c.06-1.367.075-1.8.075-5.281S26.061,8.9,26,7.533ZM23.694,17.995a7.023,7.023,0,0,1-.441,2.378A4.25,4.25,0,0,1,20.821,22.8a7.048,7.048,0,0,1-2.378.44c-1.351.06-1.757.075-5.176.075s-3.829-.015-5.176-.075a7.019,7.019,0,0,1-2.378-.44,3.943,3.943,0,0,1-1.472-.956,3.984,3.984,0,0,1-.956-1.472A7.049,7.049,0,0,1,2.846,18c-.06-1.351-.075-1.757-.075-5.176s.015-3.829.075-5.176a7.019,7.019,0,0,1,.44-2.378A3.894,3.894,0,0,1,4.248,3.8a3.978,3.978,0,0,1,1.472-.956A7.053,7.053,0,0,1,8.1,2.4c1.351-.06,1.757-.075,5.176-.075s3.829.015,5.176.075a7.023,7.023,0,0,1,2.378.441A3.94,3.94,0,0,1,22.3,3.8a3.983,3.983,0,0,1,.956,1.472,7.052,7.052,0,0,1,.441,2.378c.06,1.351.075,1.757.075,5.176s-.015,3.819-.075,5.171Zm0,0" fill="#fff"/><path id="Caminho_2866" data-name="Caminho 2866" d="M131.531,124.5a6.582,6.582,0,1,0,6.582,6.582A6.584,6.584,0,0,0,131.531,124.5Zm0,10.852a4.27,4.27,0,1,1,4.27-4.27A4.27,4.27,0,0,1,131.531,135.352Zm0,0" transform="translate(-118.269 -118.268)" fill="#fff"/><path id="Caminho_2867" data-name="Caminho 2867" d="M365.523,90.138a1.537,1.537,0,1,1-1.537-1.537A1.537,1.537,0,0,1,365.523,90.138Zm0,0" transform="translate(-343.881 -84.167)" fill="#fff"/></g></svg></a>
			<?php endif ?>		
			</div>
			<a href="https://api.whatsapp.com/send?phone=+55<?= str_replace(['(',')',' ', '-'],'',$conf['telefone']) ?>" class="whatsapp" target="_blank">
				<svg xmlns="http://www.w3.org/2000/svg" width="31.927" height="31.928" viewBox="0 0 31.927 31.928"><g id="whatsapp" transform="translate(0.5 0.5)"><path id="Caminho_2859" data-name="Caminho 2859" d="M15.468,0H15.46A15.454,15.454,0,0,0,2.944,24.527L1.017,30.272l5.944-1.9A15.461,15.461,0,1,0,15.468,0Z" fill="none" stroke="#fff" stroke-width="1"/><path id="Caminho_2860" data-name="Caminho 2860" d="M125.25,131.852a4.364,4.364,0,0,1-3.035,2.182c-.808.172-1.863.309-5.416-1.164-4.544-1.883-7.471-6.5-7.7-6.8a8.832,8.832,0,0,1-1.836-4.664,4.937,4.937,0,0,1,1.581-3.764,2.247,2.247,0,0,1,1.581-.555c.191,0,.363.01.518.017.454.019.682.046.982.764.373.9,1.282,3.118,1.39,3.346a.92.92,0,0,1,.066.837,2.672,2.672,0,0,1-.5.709c-.228.263-.445.464-.673.746-.209.245-.445.508-.182.963a13.726,13.726,0,0,0,2.509,3.118,11.369,11.369,0,0,0,3.626,2.236.978.978,0,0,0,1.09-.172,18.714,18.714,0,0,0,1.208-1.6.863.863,0,0,1,1.11-.336c.418.145,2.627,1.237,3.081,1.463s.754.336.864.528A3.851,3.851,0,0,1,125.25,131.852Z" transform="translate(-100.785 -110.015)" fill="#fafafa"/></g></svg>
				<?php echo $conf['telefone']; ?>
			</a>
		</div>
	</div>

 	<a href="http://www.hibridaweb.com.br/?utm_source=aupetwesite&amp;utm_medium=logomarca&amp;utm_campaign=assinaturas" class="logo_hibrida" target="_blank"> <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="45.955" height="9.679" viewBox="0 0 45.955 9.679"> <defs> <clipPath id="clip-path"> <path id="Clip_6" data-name="Clip 6" d="M0,9.679H45.955V0H0Z" transform="translate(0 0.154)" fill="none"></path> </clipPath> </defs> <g id="Group_12" data-name="Group 12" transform="translate(0 -0.167)"> <path id="Fill_1" data-name="Fill 1" d="M4.338,0V2.648H1.909V0H0V6.968H1.909V4.33H4.338V6.968h1.89V0Z" transform="translate(0 2.877)" fill="#58585b" style=" fill: white;"></path> <path id="Fill_2" data-name="Fill 2" d="M0,6.969H1.9V0H0Z" transform="translate(8.561 2.867)" fill="#58585b" style=" fill: white;"></path> <g id="Group_11" data-name="Group 11" transform="translate(0 0.012)"> <path id="Fill_3" data-name="Fill 3" d="M3.48,6.969H0V0H3.451C4.635,0,5.9.513,5.9,1.951A1.357,1.357,0,0,1,5.158,3.3a1.849,1.849,0,0,1,.935,1.623C6.093,6.751,4.266,6.962,3.48,6.969ZM1.88,4.111V5.436H3.49A.625.625,0,0,0,4.2,4.779a.642.642,0,0,0-.714-.668Zm0-2.518V2.658H3.4c.38,0,.608-.193.608-.518a.539.539,0,0,0-.589-.547Z" transform="translate(12.793 2.855)" fill="#58585b" style=" fill: white;"></path> <path id="Clip_6-2" data-name="Clip 6" d="M0,9.679H45.955V0H0Z" transform="translate(0 0.154)" fill="none"></path> <g id="Group_11-2" data-name="Group 11" clip-path="url(#clip-path)"> <path id="Fill_5" data-name="Fill 5" d="M0,6.969H1.9V0H0Z" transform="translate(26.423 2.855)" fill="#f26f21"></path> <path id="Fill_7" data-name="Fill 7" d="M2.893,6.969H0V0H2.893A3.231,3.231,0,0,1,6.421,3.434,3.283,3.283,0,0,1,2.893,6.969Zm-1-5.325V5.306h1c1.026,0,1.639-.707,1.639-1.892A1.57,1.57,0,0,0,2.893,1.643Z" transform="translate(30.655 2.855)" fill="#f26f21"></path> <path id="Fill_8" data-name="Fill 8" d="M2.025,6.969H0L3.028,0H4.955L7.972,6.967H5.929L5.485,5.824H2.468L2.025,6.969ZM3.982,1.951l-.868,2.22H4.84Z" transform="translate(37.982 2.855)" fill="#f26f21"></path> <path id="Fill_9" data-name="Fill 9" d="M3.682,4.5A2.1,2.1,0,0,0,5.109,2.4,2.4,2.4,0,0,0,2.486,0H0A2.828,2.828,0,0,1,.946,1.573H2.486a.776.776,0,0,1,.742.866.722.722,0,0,1-.723.807H1.8V4.765l1.533,2.2H5.524Z" transform="translate(19.165 2.855)" fill="#f26f21"></path> <path id="Fill_10" data-name="Fill 10" d="M0,1.573H1.887L2.981,0H1.174Z" transform="translate(8.561 0.154)" fill="#f26f21"></path> </g> </g> </g> </svg> </a>
</footer>

<?php if(!isset($_COOKIE['termos'])): ?>
<section class="politica_modal" active="true">
	<div class="content">
		<p>Nós utilizamos cookies e outras tecnologias semelhantes para melhorar a sua experiência em nossos serviços, personalizar publicidade e recomendar conteúdo de seu interesse. A utilizar nossos serviços, você concorda com tal monitoramento. Informamos ainda que atualizamos nossa <a target="_blank" href="">política de privacidade</a>.</p>
		<button>Concordar</button>
	</div>
</section>
<?php endif ?>

<!-- BOOTSTRAP 4 -->
<script src="<?php echo $sistema->root_path;?>website/plugins/bootstrap/bootstrap.min.js" integrity="sha384-JjSmVgyd0p3pXB1rRibZUAYoIIy6OrQ6VrjIEaFf/nJGzIxFDsf4x0xIM+B07jRM" crossorigin="anonymous"></script>

<script src="<?php echo $sistema->root_path;?>website/js/jquery.js"></script>
<script src="<?php echo $sistema->root_path;?>website/js/scripts.js"></script>
<script src="<?php echo $sistema->root_path;?>website/js/jquery.popupoverlay.js"></script>
<script src="<?php echo $sistema->root_path;?>website/js/jquery.inputmask.bundle.min.js"></script>
<script src="<?php echo $sistema->root_path;?>website/js/jquery.clickform.js"></script>
<script src="<?php echo $sistema->root_path;?>website/js/jquery.makeclickform.js"></script>
<script src="<?php echo $sistema->root_path;?>website/plugins/mask/jquery.mask.min.js"></script>
<script src="<?php echo $sistema->root_path;?>website/js/animacoes.js"></script>
<script src="<?php echo $sistema->root_path;?>website/plugins/slick/slick.min.js" type="text/javascript" charset="utf-8"></script>


<script src="<?php echo $sistema->root_path;?>https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/js/select2.min.js"></script>

<script>
	$('section.politica_modal .content button').click(function () {
		$('section.politica_modal').attr('active', 'false');
		var d = new Date();
		d.setTime(d.getTime() + (365*24*60*60*1000));
		var expires = "expires="+ d.toUTCString();
		document.cookie = "termos=aceito;" + expires;
	});
</script>


<?php
	include "website/plugins/photoswipe/photoswipe.php";
	include '_inc_seo_acessos.php';
?>