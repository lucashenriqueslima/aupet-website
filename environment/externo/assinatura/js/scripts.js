$(document).ready(function () { });
angular.module('nova', ['novaServices', 'ngRaven'])
.controller('Ctrl', function ($scope, $http, $sce, NS, $window) {
	this.data = { selfie: '', frente_doc: '', atras_doc: '', 'tipo_doc': 'rg', assinatura: '', 'lat': '', 'lng': '' };
	if (typeof recusados !== 'undefined') {
		$scope.doc_recusados = recusados;
	}
	this.ngInit = () => {
		$('body').show();
	}
	this.fotoProp;
	this.fotoPasso;
	this.changeSection = async (openSection) => {
		if (!openSection) return;
		if (openSection === 3 && !$('#termo')[0].checked) return;
		else if (openSection === 4 && (!this.data.lat || !this.data.lng)) await obterLocalizacao();
		else if (openSection === 7 && (!this.data.atras_doc || !this.data.frente_doc)) return NS.notifInfo('Arquivos requeridos');
		else if (openSection === 8) setTimeout(() => montarCanvas(), 500);
		else if (openSection === 9) await setAssinatura();
		else if (openSection === 10) {
			if(!this.data.selfie) return alert('Selfie requerida');
			await submit();
		}
		$('section.passo_item').attr('active', 'false');
		$(`section.passo_${openSection}`).attr('active', 'true');
	}
	var obterImagem = async () => {
		let input = window.document.getElementById('generic-input');
		input.setAttribute('type', "file");
		input.setAttribute('accept', "image/*");
		if (this.fotoProp === 'selfie') {
			input.setAttribute('capture', "user");
		}
		else input.setAttribute('capture', "environment");
		input.click();
		await new Promise(resolve => setTimeout(resolve, 1000));
		await new Promise(resolve => input.addEventListener('change', evt => resolve(evt)));
		if (!input.files[0]) return;
		return await NS.resizeImg(input.files[0]);
	}
	this.obterFotoFrente = async (prop, passo) => {
		this.fotoProp = prop;
		this.fotoPasso = passo;
		this.data[this.fotoProp] = await obterImagem();
		$('section.passo_item').attr('active', 'false');
		$(`section.revisar_foto`).attr('active', 'true');
		$('section.revisar_foto img').attr('src', this.data[this.fotoProp]);
		$scope.$digest();
	}
	var obterLocalizacao = async () => {
		let location = await new Promise((resolve, reject) => navigator.geolocation.getCurrentPosition(resolve, reject));
		this.data.lat = location.coords.latitude;
		this.data.lng = location.coords.longitude;
	}
	var montarCanvas = () => {
		var canvas = $('canvas')[0];
		canvas.height = canvas.getBoundingClientRect().height;
		canvas.width = canvas.getBoundingClientRect().width;
		top = canvas.getBoundingClientRect().top;
		left = canvas.getBoundingClientRect().left;
		this.sigPad = new SignaturePad(canvas);
	}
	this.limparCanvas = () => {
		this.sigPad.clear();
	}
	this.desfazerCanvas = () => {
		var data = this.sigPad.toData();
		if (!data) return;
		data.pop();
		this.sigPad.fromData(data);
	}
	var setAssinatura = async () => {
		var canvas = $('canvas')[0];
		let file = await new Promise(resolve => { canvas.toBlob(resolve, 'image/png', 1); });
		this.data.assinatura = await NS.resizeImg(file, 400, 400, "image/png");
	}
	var submit = async () => {
		try {
			$('body').loading({ message: 'Aguarde ...' });
			await $http.post(`/proposta/aceitarTermo/${hash}`, this.data), 'body', 'Sucesso!';
		} catch(e) {
			NS.notifError("Ocorreu um erro inesperado. Tente novamente mais tarde.");
			throw e;
		} finally {
			$('body').loading('stop');
		}
	}
	// recusados
	this.getAssinatura = () => {
		$('section').attr('active', 'false');
		$(`section.assinatura`).attr('active', 'true');
		setTimeout(() => montarCanvas(), 500);
	}
	this.recSetAssinatura = async () => {
		await setAssinatura();
		$('section').attr('active', 'false');
		$(`section.arquivos_recusados`).attr('active', 'true');
		$scope.$digest();
	}
	this.salvarRec = async () => {
		if(!checkRecusados()) return NS.notifInfo("Todos os arquivos são requeridos")
		await NS.actionHandler($http.post(`/proposta/termoArqRecusados/${hash}`, this.data), 'body', 'Sucesso!');
		$('section').attr('active', 'false');
		$(`section.passo_final`).attr('active', 'true');
	}
	var checkRecusados = () => {
		let check = true;
		for (let item in $scope.doc_recusados) {
			if ($scope.doc_recusados.hasOwnProperty(item)) {
				if (!this.data.hasOwnProperty(item)) {
					check = false;
				}
			}
		}
		return check;
	}
	// apenas assinatura
	this.changeSectionAss = async (openSection) => {
		if (!openSection) return;
		else if (openSection === 8) setTimeout(() => montarCanvas(), 500);
		else if (openSection === 10) {
			await setAssinatura();
			await submit();
		}
		$('section.passo_item').attr('active', 'false');
		$(`section.passo_${openSection}`).attr('active', 'true');
	}
});