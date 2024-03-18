<?php
	$pagina = "exemplos";       
	include "_inc_headers.php";
?>
<style type="text/css">
	
	body { text-align: center; }

	.roleta1 {
		position: relative;
		width: 1000px;
		height: 120px;
		margin: 10px auto;
	}

	.roleta1 .itens {
		width: 800px;
		height: 120px;
		margin: 0 auto;
	}

	.roleta1 .itens .item {
		width: 150px;
		height: 120px;
		margin: 0 auto;
		background: #00b6d2;
		line-height: 120px;
		font-size: 15px;
		font-weight: bold;
		text-align: center;
		color: #fff;
	}

	.roleta1 .setas {
		position: absolute;
		width: 100%;
		height: 1px;
		top: 0;
	}

	.roleta1 .setas span {
		display: block;
		position: absolute;
		width: 60px;
		height: 25px;
		line-height: 25px;
		top: 45px;
		background: #00b6d2;
		border-radius: 5px;
		font-weight: bold;
		text-align: center;
		color: #fff;
		cursor: pointer;
	}

	.roleta1 .setas span.right { right: 0; }



	@media (max-width: 1152px) {
		
		.roleta1 {
			width: 700px;
		}

		.roleta1 .itens {
			width: 600px;
		}
		
	}

	@media (max-width: 720px) {
		
		.roleta1 {
			width: 500px;
		}

		.roleta1 .itens {
			width: 300px;
		}

		.roleta1 .itens .item {
			width: 290px;
		}
		
	}

</style>
</head>
<body id="<?php echo $pagina; ?>">

<div class="conteudo">
	<br><br>
	<h4>Galeria 1</h4>
	<a href="website/img/temp_foto_principal1.jpg" class='pswp1' title="galeria 1 foto 1" data-index="0" data-width="1180" data-height="545">galeria 1-1</a> | 
	<a href="website/img/temp_foto_principal2.jpg" class='pswp1' title="galeria 1 foto 2" data-index="1" data-width="720" data-height="545">galeria 1-2</a> | 
	<a href="website/img/temp_foto_principal1.jpg" class='pswp1' title="galeria 1 foto 3" data-index="2" data-width="1180" data-height="545">galeria 1-3</a> |
	<a href="website/img/temp_foto_principal2.jpg" class='pswp1' title="galeria 1 foto 4" data-index="3" data-width="720" data-height="545">galeria 1-4</a>

	<br><br><br>

	<h4>Galeria 2</h4>
	<a href="website/img/temp_foto_principal1.jpg" class='pswp2' title="galeria 2 foto 1" data-index="0" data-width="1180" data-height="545">galeria 2-1</a> | 
	<a href="website/img/temp_foto_principal2.jpg" class='pswp2' title="galeria 2 foto 2" data-index="1" data-width="720" data-height="545">galeria 2-2</a> | 
	<a href="website/img/temp_foto_principal1.jpg" class='pswp2' title="galeria 2 foto 3" data-index="2" data-width="1180" data-height="545">galeria 2-3</a> |
	<a href="website/img/temp_foto_principal2.jpg" class='pswp2' title="galeria 2 foto 4" data-index="3" data-width="720" data-height="545">galeria 2-4</a>

	<br><br><br>

	<h4>Roleta Responsiva</h4>
	<div class="roleta1">
		<div class="itens">
			<div class="item">1</div>
			<div class="item">2</div>
			<div class="item">3</div>
			<div class="item">4</div>
			<div class="item">5</div>
			<div class="item">6</div>
			<div class="item">7</div>
			<div class="item">8</div>
			<div class="item">9</div>
		</div>
		<div class="setas">
			<span class="left">anterior</span>
			<span class="right">próx</span>
		</div>
	</div>
	<br><br><br>
	<h4>Abrir Popup</h4>
	<a href="_popup_cadastro" class="openPopup">Abrir Popup</a>
	<br><br><br>
</div>

<?php
	include "_inc_footer.php";
	include "website/plugins/photoswipe/photoswipe.php";
	include "website/plugins/owl/owl.php";
?>

<script>
	new photoswipe_init('.pswp1');
	new photoswipe_init('.pswp2');

    // configura resposividade das roletas
    var responsive = {
        0 : {
            'items':1
        },
        720 : {
            'items':3
        },
        1152 : {
            'items':6
        }
    }

    //inicializa da roleta 
    var owl = $('.roleta1 .itens');
    owl.owlCarousel({'loop':true,'responsive':responsive});

    $('.roleta1 .left').click(function(){
        owl.trigger('prev.owl.carousel');
    });

    $('.roleta1 .right').click(function(){
        owl.trigger('next.owl.carousel');
    });


</script>

<!-- YOUTUBE -->
<!-- <script>
	var idVideo = youtube_parser('https://www.youtube.com/watch?v=6Wm-m0xWqMo');

	var tag = document.createElement('script');

	tag.src = "https://www.youtube.com/iframe_api";
	var firstScriptTag = document.getElementsByTagName('script')[0];
	firstScriptTag.parentNode.insertBefore(tag, firstScriptTag);

	var player;

	function onYouTubeIframeAPIReady() {
		player = new YT.Player('player', {
			height: '460',
			width: '915',
			playerVars: {
				'controls': 1,
				'showinfo': 0,
				'rel': 0,
			},
			videoId: idVideo //id do vídeo
		});
	}

	$(document).on("click", function(e) {
		var play_button = document.querySelector("section.text_video_home .content .left .video .capa_video svg");
		var fora = !play_button.contains(e.target);

		if (fora) {
			$('.video').attr('data-play', 'false');
			player.pauseVideo();
		} else {
			$('.video').attr('data-play', 'true');
			player.playVideo();
		}
	});

	function youtube_parser(url) {
		var regExp = /^.*((youtu.be\/)|(v\/)|(\/u\/\w\/)|(embed\/)|(watch\?))\??v?=?([^#\&\?]*).*/;
		var match = url.match(regExp);
		if (match && match[7].length == 11) {
			var b = match[7];
			//alert(b);
			return b;
		} else {
			alert("Url incorrecta");
		}
	}
</script> -->
</body>
</html>