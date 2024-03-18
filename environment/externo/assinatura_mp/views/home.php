<?php
	$pagina = "Assinatura AupetHeinsten";
	include "_inc_headers.php";
	use app\classes\{Util};
?>
<title><?php echo $pagina; ?></title>
<script src="https://secure.mlstatic.com/sdk/javascript/v1/mercadopago.js"></script>
<script>
	let associado = <?=json_encode($this->dadosAssociado);?>;
	let estados = <?= json_encode($this->estados) ?>;
	let valor = <?=  $this->plano['valor'] ?>;
	let plano = '<?=  $this->plano['titulo'] ?>';
	let id_plano = '<?=  $this->plano['id'] ?>';
	let id_pet = '<?=  $this->pet['id'] ?>';
	let pet = '<?=  $this->pet['nome'] ?>';

	window.Mercadopago.setPublishableKey('<?= $this->public_key['public_key'] ?>');
</script>
</head>
<body ng-app='nova' ng-controller="Ctrl as ctrl" ng-init='ctrl.ngInit()'>
	<?php include "_inc_header.php"; ?>
	<section class="pagamento_content" etapa="1">
		<div class="header">
			<h1>Pagamento Aupetheinsten </h1>
		</div>

		<div class="content content_1">
			<p class="desc">Olá <b><?= $this->tutor['nome'] ?></b>, aqui é o(a) <b><?= $this->consultor['nome'] ?></b>, consultor do(a) <b><?= $this->empresa['nome'] ?></b>, esse é o seu link para visualização do termo de filiação e assinatura do Plano <b><?= $this->plano['titulo'] ?></b> .</p>

			<div class="nome">
				<svg xmlns="http://www.w3.org/2000/svg" width="52" height="51.997" viewBox="0 0 52 51.997"><g transform="translate(0 -0.015)"><g transform="translate(0.001 6.084)"><path d="M51.025,92a8.167,8.167,0,0,0-5.836-3.9.849.849,0,0,0-.115-.008H35.9l-.363-.363a6.5,6.5,0,0,0,2.435-1.507l3.4.971.476-1.666-2.718-.776a7.571,7.571,0,0,0,.71-1.607.877.877,0,0,0,.037-.25V81.12l2.075-.889-.682-1.593-1.393.6v-.667a.864.864,0,0,0-.124-.446l-2.6-4.333a.867.867,0,0,0-.637-.414.881.881,0,0,0-.718.248l-2.346,2.346h-.508V65.061l1.225-1.225h1.112l2.381,1.588a.867.867,0,0,0,1.2-.24l1.733-2.6a.866.866,0,0,0,0-.961l-3.466-5.2a.866.866,0,0,0-.721-.386h-2.6a.868.868,0,0,0-.776.479L31.539,59.5H22.211l-1.494-2.987a.865.865,0,0,0-.775-.479H16.476a.864.864,0,0,0-.7.363l-4.333,6.066a.867.867,0,0,0,.092,1.116l1.733,1.733a.867.867,0,0,0,1.094.108l2.381-1.588h1.978l.359.359v9.42c-2.288.831-8.534,3.682-11.209,10.712A18.245,18.245,0,0,0,7.22,94.882l-2.01-1.206V90.7a2.6,2.6,0,0,0-4.16-2.079A2.613,2.613,0,0,0,.011,90.7v6.066a.867.867,0,0,0,.407.735l6.933,4.333a.868.868,0,0,0,.459.132h39a.869.869,0,0,0,.387-.091l3.466-1.733a.864.864,0,0,0,.37-.354A8.384,8.384,0,0,0,51.025,92Zm-23.282,1.29-5.132-.016-.005,1.733,5.136.016v5.2H26.008a3.47,3.47,0,0,0-3.466-3.466H20.809a7.808,7.808,0,0,0-7.8-7.8h-.867V90.7h.867a6.073,6.073,0,0,1,6.066,6.066v.867a.866.866,0,0,0,.867.867h2.6a1.735,1.735,0,0,1,1.733,1.733H8.059L1.744,96.286V90.7a.871.871,0,0,1,.346-.693.867.867,0,0,1,1.387.693v3.466a.867.867,0,0,0,.42.743l4.333,2.6A.866.866,0,0,0,9.49,96.467a16.808,16.808,0,0,1,0-11.525c.127-.335.272-.651.417-.965h0A17.489,17.489,0,0,1,20.2,75.062a.864.864,0,0,0,.609-.827v-10.4a.864.864,0,0,0-.254-.613l-.867-.867a.863.863,0,0,0-.613-.254h-2.6a.871.871,0,0,0-.481.146l-2.009,1.339-.711-.711,3.647-5.106h2.484L20.9,60.757a.865.865,0,0,0,.775.479h10.4a.868.868,0,0,0,.776-.479l1.493-2.987h1.6L38.832,62.1,37.9,63.5l-1.879-1.252a.871.871,0,0,0-.481-.146H33.807a.863.863,0,0,0-.613.254L31.462,64.09a.864.864,0,0,0-.254.613V75.968H28.967l-2.346-2.346a.866.866,0,0,0-1.355.166l-2.6,4.333a.864.864,0,0,0-.124.446v.667l-1.392-.6-.682,1.593,2.074.888V82.9a.876.876,0,0,0,.037.25,7.473,7.473,0,0,0,1.033,2.106L20.5,86.423l.609,1.623,3.773-1.415a6.705,6.705,0,0,0,2.855,1.348v5.316Zm21.882,5.426L46.6,100.232H38.764l-.624-1.874v-2.2L39.25,94.5a.741.741,0,0,1,.619-.332.744.744,0,0,1,.665,1.075l-.568,1.137a.866.866,0,0,0,.163,1l.867.867a.863.863,0,0,0,.613.254h4.333V96.766H41.965l-.171-.171.289-.578a2.477,2.477,0,0,0-4.277-2.482l-1.255,1.883a.873.873,0,0,0-.146.481v2.6a.868.868,0,0,0,.045.274l.486,1.459H33.807V94.166H32.074v6.066h-2.6v-13a.867.867,0,0,0-.786-.863,5.076,5.076,0,0,1-1.78-.5l1.138-.426-.609-1.623-2.132.8a5.57,5.57,0,0,1-1.03-1.854v-.906l2.258.968.682-1.593-2.94-1.259v-1.17l1.9-3.175L28,77.447a.863.863,0,0,0,.613.254h5.2a.863.863,0,0,0,.613-.254l1.815-1.815,1.9,3.175v1.17L35.2,81.238l.682,1.593,2.259-.968v.906a5.683,5.683,0,0,1-.75,1.493l-1.611-.46L35.3,85.468l.622.178a5.053,5.053,0,0,1-2.2.726.866.866,0,0,0-.533,1.475l1.733,1.733a.863.863,0,0,0,.613.254h9.471a6.488,6.488,0,0,1,4.509,3.03A6.59,6.59,0,0,1,49.624,98.721Z" transform="translate(-0.011 -56.037)" fill="#772e8a"/></g><g transform="translate(0 0.015)"><g transform="translate(0 0)"><path d="M51.195,11.449C49.634,7.111,44.568.5,37,.053c-5.142-.309-9.057,3.7-10.929,5.623l-.107.11-.151-.156C23.972,3.715,20.1-.278,14.995.032,7.43.476,2.364,7.09.8,11.428c-2.084,5.8-.066,13.2,5.538,20.323L7.7,30.679C2.477,24.041.556,17.237,2.435,12.014,3.838,8.109,8.37,2.156,15.1,1.762,19.438,1.519,22.9,5.1,24.568,6.833c.318.329.575.6.766.764a.869.869,0,0,0,.368.194l.089.022a.866.866,0,0,0,.784-.194c.185-.164.433-.419.741-.735,1.692-1.738,5.225-5.348,9.585-5.1,6.727.4,11.258,6.348,12.662,10.252,2.136,5.938-.642,13.859-7.431,21.189L43.4,34.4C50.641,26.588,53.554,18.007,51.195,11.449Z" transform="translate(0 -0.015)" fill="#772e8a"/></g></g><g transform="translate(22.532 14.749)"><rect width="1.733" height="1.733" fill="#772e8a"/></g><g transform="translate(27.732 14.749)"><rect width="1.733" height="1.733" fill="#772e8a"/></g><g transform="translate(24.265 18.216)"><rect width="3.466" height="1.733" fill="#772e8a"/></g><g transform="translate(27.732 29.481)"><rect width="1.733" height="1.733" fill="#772e8a"/></g><g transform="translate(32.931 29.481)"><rect width="1.733" height="1.733" fill="#772e8a"/></g><g transform="translate(30.331 32.081)"><rect width="1.733" height="1.733" fill="#772e8a"/></g></g></svg>
				<h2>Nome do pet</h2>
				<p><?= $this->pet['nome'] ?></p>
			</div>

			<div class="dados">
				<div>
					<h3>Espécie</h3>
					<p><?= $this->pet['especie'] ?></p>
				</div>
				<div>
					<h3>Raça</h3>
					<p><?= $this->pet['raca'] ?></p>
				</div>
				<div>
					<h3>Sexo</h3>
					<p><?= $this->pet['sexo'] ?></p>
				</div>
				<div>
					<h3>Porte</h3>
					<p><?= $this->pet['porte'] ?></p>
				</div>
				<div>
					<h3>Peso</h3>
					<p><?= $this->pet['peso'] ?>kg</p>
				</div>
				<div>
					<h3>Cor</h3>
					<p><?= $this->pet['cor'] ?></p>
				</div>
			</div>

			<div class="dados_plano">
				<div class="title">
					<svg xmlns="http://www.w3.org/2000/svg" width="22.777" height="19.907" viewBox="0 0 22.777 19.907"><g transform="translate(0 0)"><path d="M76.823,184.5a5.546,5.546,0,0,0-9.339,0l-2.47,3.853a3.51,3.51,0,0,0,4.368,5.108l.047-.021a6.9,6.9,0,0,1,5.495.021,3.491,3.491,0,0,0,1.409.3,3.534,3.534,0,0,0,.786-.089,3.511,3.511,0,0,0,2.174-5.317Zm0,0" transform="translate(-61.592 -173.855)" fill="#772e8a"/><path d="M4.088,105.138a2.692,2.692,0,0,0,1.572-1.667,3.478,3.478,0,0,0-.061-2.4,3.481,3.481,0,0,0-1.563-1.82,2.692,2.692,0,0,0-2.283-.184,3.083,3.083,0,0,0-1.509,4.066,3.252,3.252,0,0,0,2.925,2.175,2.55,2.55,0,0,0,.92-.17Zm0,0" transform="translate(0 -94.495)" fill="#772e8a"/><path d="M127.759,7.625A3.606,3.606,0,0,0,131.1,3.813a3.374,3.374,0,1,0-6.691,0A3.606,3.606,0,0,0,127.759,7.625Zm0,0" transform="translate(-118.88)" fill="#772e8a"/><path d="M286.21,47.023h0a2.727,2.727,0,0,0,.862.139,3.468,3.468,0,0,0,3.157-2.484,3.738,3.738,0,0,0-.053-2.579,2.828,2.828,0,0,0-4.193-1.393,3.739,3.739,0,0,0-1.586,2.034,3.307,3.307,0,0,0,1.814,4.284Zm0,0" transform="translate(-271.552 -38.462)" fill="#772e8a"/><path d="M381.679,171.561h0a3.278,3.278,0,0,0-3.87,5.229,2.615,2.615,0,0,0,1.571.507,3.383,3.383,0,0,0,2.671-1.415A3.084,3.084,0,0,0,381.679,171.561Zm0,0" transform="translate(-359.976 -163.445)" fill="#772e8a"/></g></svg>
					dados do plano
				</div>

				<div class="plano_preco">
					<h4>Plano <?= $this->plano['titulo'] ?></h4>
					<p>R$ <?= Util::formataMoeda($this->plano['valor'])  ?></p>
				</div>

				<div class="lista">
					<?php foreach($this->beneficios as $beneficio): ?>
						<?php
							$benf = 'false';
							foreach ($planoBeneficios as $planoBeneficio) {
									if ($planoBeneficio['id_beneficio'] == $beneficio['id']) {
										$benf = 'true';
									}
								}
						?>
						<div active="<?= $benf ?>">
							<?= $beneficio['nome'] ?>
							<svg class="check" xmlns="http://www.w3.org/2000/svg" width="20.396" height="15.837" viewBox="0 0 20.396 15.837"><path d="M7.81,15.938a1.041,1.041,0,0,1-1.472,0L.458,10.057a1.561,1.561,0,0,1,0-2.209l.736-.736a1.562,1.562,0,0,1,2.209,0l3.671,3.671L16.993.864a1.562,1.562,0,0,1,2.209,0l.736.736a1.561,1.561,0,0,1,0,2.209Zm0,0" transform="translate(0 -0.406)" fill="#00d58d"/></svg>
							<svg class="nocheck" xmlns="http://www.w3.org/2000/svg" width="14" height="1" viewBox="0 0 14 1"><line x2="14" transform="translate(0 0.5)" fill="none" stroke="#000" stroke-width="1"/></svg>
						</div>
					<?php endforeach; ?>
				</div>
			</div>

			<div class="termo">
				<input type="checkbox" id="termo" ng-model="termo_assinatura">
				<label for="termo">Li e aceito o <a href="/proposta/pdf/<?= $this->pet['hash'] ?>" target="_blank">Termo de Filiação</a></label>
			</div>

			<button class="continuar" id="termo_ckeck" ng-disabled="!termo_assinatura" ng-click="ctrl.mudar_etapa(2)">Continuar</button>
		</div>

		<div class="content content_2">
			<h2>Dados pessoais</h2>

			<div>
				<?php if(!$this->dadosAssociado['nome']): ?>
					<div>
						<input type="text" placeholder=" " ng-model="ctrl.dadosAssociado.nome">
						<label>Nome</label>
					</div>
				<?php endif; ?>

				<?php if(!$this->dadosAssociado['cpf']): ?>
					<div>
						<input mask="cpf" type="text" placeholder=" " ng-model="ctrl.dadosAssociado.cpf" >
						<label>CPF</label>
					</div>
				<?php endif; ?>

				<?php if(!$this->dadosAssociado['email']): ?>
					<div>
						<input type="email" placeholder=" " ng-model="ctrl.dadosAssociado.email">
						<label>E-mail</label>
					</div>
				<?php endif; ?>

				<?php if(!$this->dadosAssociado['telefone']): ?>
					<div>
						<input type="text" placeholder=" " mask="telefone" ng-model="ctrl.dadosAssociado.telefone">
						<label>Telefone</label>
					</div>
				<?php endif; ?>

				<?php if(!$this->dadosAssociado['data_nascimento']): ?>
					<div>
						<input type="text" mask="data" placeholder=" " max="1979-12-31" ng-model="ctrl.dadosAssociado.data_nascimento"> 
						<label>Data de Nascimento</label>
					</div>
				<?php endif; ?>

				<?php if(!$this->dadosAssociado['cep']): ?>
					<div>
						<input mask="cep" type="text" placeholder=" " ng-model="ctrl.dadosAssociado.cep" ng-blur="ctrl.buscaCEP(ctrl.dadosAssociado.cep)">
						<label>CEP</label>
					</div>
				<?php endif; ?>

				<?php if(!$this->dadosAssociado['rua']): ?>
					<div>
						<input type="text" placeholder=" " ng-model="ctrl.dadosAssociado.rua">
						<label>Logradouro</label>
					</div>
				<?php endif; ?>

				<?php if(!$this->dadosAssociado['bairro']): ?>
					<div>
						<input type="text" placeholder=" " ng-model="ctrl.dadosAssociado.bairro">
						<label>Bairro</label>
					</div>
				<?php endif; ?>

				<?php if(!$this->dadosAssociado['numero']): ?>
					<div>
						<input type="text" placeholder=" " ng-model="ctrl.dadosAssociado.numero">
						<label>Número</label>
					</div>
				<?php endif; ?>

				<?php if(!$this->dadosAssociado['complemento']): ?>
					<div>
						<input type="text" placeholder=" " ng-model="ctrl.dadosAssociado.complemento">
						<label>Complemento</label>
					</div>
				<?php endif; ?>

				<?php if(!$this->dadosAssociado['id_estado']): ?>
					<div class="half">
						<select name="" id="" ng-model="ctrl.dadosAssociado.id_estado" ng-change="ctrl.findCidades()">
							<option value="" hidden>Estado</option>
							<option ng-repeat="row in ctrl.estados" ng-value="row.id">{{ row.estado }}</option>
						</select>
					</div>
				<?php endif; ?>

				<?php if(!$this->dadosAssociado['id_cidade']): ?>
					<div class="half">
						<select name="" id="" ng-model="ctrl.dadosAssociado.id_cidade">
							<option value="" hidden>Cidade</option>
							<option ng-repeat="row in ctrl.cidades" ng-value="row.id">{{ row.cidade }}</option>
						</select>
					</div>
				<?php endif; ?>
			</div>

			<button class="continuar"  ng-click="ctrl.mudar_etapa(3)">ir para pagamento</button>
		</div>

		<div class="content content_3">
			<img src="/environment/externo/assinatura_mp/img/card.png" alt="">
			<form id="paymentForm">
				<div>
					<input type="text" mask="cartao" placeholder="Número do cartão" id="cardNumber" data-checkout="cardNumber">
				</div>
				<div class="half">
					<input type="tel" placeholder="Mês validade" id="cardExpirationMonth" data-checkout="cardExpirationMonth" maxlength="2">
					<div class="left">
						<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 16 16"><circle cx="0.781" cy="0.781" r="0.781" transform="translate(7.219 11.047)" fill="#00d58d"/><path d="M8,0a8,8,0,1,0,8,8A8,8,0,0,0,8,0ZM8,14.75A6.75,6.75,0,1,1,14.75,8,6.746,6.746,0,0,1,8,14.75Z" fill="#00d58d"/><path d="M178.5,128.5A2.5,2.5,0,0,0,176,131a.625.625,0,0,0,1.25,0,1.25,1.25,0,1,1,1.25,1.25.625.625,0,0,0-.625.625v1.563a.625.625,0,0,0,1.25,0v-1.017a2.5,2.5,0,0,0-.625-4.921Z" transform="translate(-170.5 -124.484)" fill="#00d58d"/></svg>
						<div>Dado disponível na frente do cartão.</div>
					</div>
				</div>
				<div class="half">
					<input type="tel" placeholder="Ano validade" id="cardExpirationYear" data-checkout="cardExpirationYear" maxlength="2">
					<div class="left">
						<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 16 16"><circle cx="0.781" cy="0.781" r="0.781" transform="translate(7.219 11.047)" fill="#00d58d"/><path d="M8,0a8,8,0,1,0,8,8A8,8,0,0,0,8,0ZM8,14.75A6.75,6.75,0,1,1,14.75,8,6.746,6.746,0,0,1,8,14.75Z" fill="#00d58d"/><path d="M178.5,128.5A2.5,2.5,0,0,0,176,131a.625.625,0,0,0,1.25,0,1.25,1.25,0,1,1,1.25,1.25.625.625,0,0,0-.625.625v1.563a.625.625,0,0,0,1.25,0v-1.017a2.5,2.5,0,0,0-.625-4.921Z" transform="translate(-170.5 -124.484)" fill="#00d58d"/></svg>
						<div>Dado disponível na frente do cartão.</div>
					</div>
				</div>
				<div class="half">
					<input type="tel" placeholder="CVV" id="securityCode" data-checkout="securityCode" maxlength="4">
					<div class="right">
						<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 16 16"><circle cx="0.781" cy="0.781" r="0.781" transform="translate(7.219 11.047)" fill="#00d58d"/><path d="M8,0a8,8,0,1,0,8,8A8,8,0,0,0,8,0ZM8,14.75A6.75,6.75,0,1,1,14.75,8,6.746,6.746,0,0,1,8,14.75Z" fill="#00d58d"/><path d="M178.5,128.5A2.5,2.5,0,0,0,176,131a.625.625,0,0,0,1.25,0,1.25,1.25,0,1,1,1.25,1.25.625.625,0,0,0-.625.625v1.563a.625.625,0,0,0,1.25,0v-1.017a2.5,2.5,0,0,0-.625-4.921Z" transform="translate(-170.5 -124.484)" fill="#00d58d"/></svg>
						<div>Dado disponível na traseira do cartão.</div>
					</div>
				</div>
				<div class="half">
					<input type="text" mask="cpf" placeholder="CPF do dono do cartão" id="docNumber" name="docNumber" data-checkout="docNumber">
				</div>
				<div>
					<input type="text" placeholder="Nome como está no cartão" id="cardholderName" data-checkout="cardholderName" autocomplete="off">
				</div>
				<div>
					<input type="hidden" name="transactionAmount" id="transactionAmount" value="100" />
					<input type="hidden" name="docType" data-checkout="docType" value="CPF" />
					<input type="hidden" name="paymentMethodId" id="paymentMethodId" />
					<input type="hidden" name="description" id="description" />
				</div>
			</form>
			<button class="continuar"  ng-click="ctrl.mudar_etapa(4)">Finalizar pagamento</button>
		</div>

		<div class="content content_4">
			<svg xmlns="http://www.w3.org/2000/svg" width="118" height="118" viewBox="0 0 118 118"><path d="M59,0a59,59,0,1,0,59,59A59.065,59.065,0,0,0,59,0Zm0,0" fill="#00d58d"/><path d="M197.66,173.722,165.7,205.68a4.912,4.912,0,0,1-6.952,0L142.77,189.7a4.916,4.916,0,0,1,6.952-6.952l12.5,12.5,28.483-28.482a4.916,4.916,0,0,1,6.952,6.952Zm0,0" transform="translate(-108.756 -127.225)" fill="#fafafa"/></svg>
			<h2>Pagamento realizado com sucesso!</h2>
			<p>O Cartão foi registrado e a mensalidade da sua assinatura AupetHeinsten começará a ser debitada mensalmente!</p>
			<button class="continuar"  ng-click="ctrl.goHome()">Voltar ao site</button>
		</div>
	</section>
	<?php include "_inc_footer.php"; ?>
</body>
</html>
