$(document).ready(function () {

    //MASCARAS
    var celular = function (val) {
        return val.replace(/\D/g, '').length === 11 ? '(00) 00000-0000' : '(00) 0000-00009';
    },
    spOptions = {
        onKeyPress: function(val, e, field, options) {
            field.mask(celular.apply({}, arguments), options);
        }
    };

    $('[mask="telefone"]').mask(celular, spOptions);
    $('[mask="preco"]').mask("###.##0,00", {reverse: true});
    $('[mask="number"]').mask("##############################");
    $('[mask="data"]').mask("00/00/0000", {placeholder: "__/__/____"});
    $('[mask="cpf"]').mask('000.000.000-00');
    $('[mask="cep"]').mask('00.000-000');
    $('[mask="cartao"]').mask('0000 0000 0000 0000');
    $('[mask="mes"]').mask('00');
    $('[mask="ano"]').mask('00');

});

angular.module('nova', ['novaServices'])
.controller('Ctrl', function ($scope, $http, $sce, NS, $window){   
    this.dadosAssociado = associado;
    this.estados = estados;
    this.dadosPagamento;

    this.ngInit = () => {
		$('body').show();
        this.findCidades();
	}
    this.mudar_etapa = async (i) => {
        if(i == 2){ 
            if(
                this.dadosAssociado.nome &&
                this.dadosAssociado.cpf &&
                this.dadosAssociado.email &&
                this.dadosAssociado.telefone &&
                this.dadosAssociado.data_nascimento &&
                this.dadosAssociado.cep &&
                this.dadosAssociado.rua &&
                this.dadosAssociado.bairro &&
                this.dadosAssociado.numero &&
                this.dadosAssociado.id_estado &&
                this.dadosAssociado.id_cidade &&
                this.dadosAssociado.complemento
            ) i++;

            $('section.pagamento_content').attr("etapa", i);
        }

        if(i == 3) {
            if(!this.dadosAssociado.nome) return NS.notifInfo('O Campo Nome é obrigatorio');
            if(!this.dadosAssociado.cpf) return NS.notifInfo('O Campo CPF é obrigatorio');
            if(!this.validaCPF(this.dadosAssociado.cpf)) return NS.notifInfo('Informe um CPF válido');
            if(!this.dadosAssociado.email) return NS.notifInfo('O Campo Email é obrigatorio');
            if(!this.dadosAssociado.data_nascimento) return NS.notifInfo('O Campo Data de Nascimento é obrigatorio');
            if(!this.dadosAssociado.cep) return NS.notifInfo('O Campo CEP é obrigatorio');
            if(!this.dadosAssociado.rua) return NS.notifInfo('O Campo Logradouro é obrigatorio');
            if(!this.dadosAssociado.id_estado) return NS.notifInfo('O Campo Estado é obrigatorio');
            if(!this.dadosAssociado.id_cidade) return NS.notifInfo('O Campo Cidade é obrigatorio');

            let id = (await $http.post(`/proposta/atualizarAssociado/${this.dadosAssociado.id}`, this.dadosAssociado)).data?.id;
            this.dadosAssociado.id_associado = id;

            $('section.pagamento_content').attr("etapa", i);
        }

        if(i == 4){
            if($('#cardNumber').val() == null || $('#cardNumber').val() == '') return NS.notifInfo('O número do cartão é obrigatório!');
            if($('#cardExpirationMonth').val() == null || $('#cardExpirationMonth').val() == '') return NS.notifInfo('O mês de validade é obrigatorio!');
            if($('#cardExpirationMonth').val() > 12) return NS.notifInfo('Informe um mês válido!');
            if($('#cardExpirationYear').val() == null || $('#cardExpirationYear').val() == '') return NS.notifInfo('O ano de validade é obrigatorio!');
            if($('#securityCode').val() == null || $('#securityCode').val() == '') return NS.notifInfo('O codigo de segurança é obrigatorio!');
            if($('#docNumber').val() == null || $('#docNumber').val() == '') return NS.notifInfo('O CPF do titular do cartão é obrigatorio!');
            if($('#cardholderName').val() == null || $('#cardholderName').val() == '') return NS.notifInfo('O nome do titular, tal como está no cartão é obrigatorio!');
            
            await this.getCardToken(i);
        }

    }
    this.goHome = () => {
        window.location.href = 'https://aupetheinsten.com.br/';
    }


    this.buscaCEP = async (cep) => {
        if(cep.length < 9) return;
        let data = (await $http.get(`https://viacep.com.br/ws/${cep.replace('-','').replace('.','')}/json/`)).data;
        this.dadosAssociado.rua = data.logradouro;
        if (!this.dadosAssociado.complemento) this.dadosAssociado.complemento = data.complemento;
        this.dadosAssociado.bairro = data.bairro;
        this.dadosAssociado.id_estado = parseInt(this.estados.find(x => x.uf == data.uf).id);
        await this.findCidades();
        this.dadosAssociado.id_cidade = this.cidades.find(x => x.cidade == data.localidade).id;
        
        $scope.$digest();
    }
    this.findCidades = async () => {
        if(!this.dadosAssociado.id_estado) return;
        this.cidades = [];
        this.cidades = (await $http.post(`/proposta/getCidades`, { id: this.dadosAssociado.id_estado })).data;
        $scope.$digest();
    }

    this.validaCPF = (strCPF) => {
		var Soma;
		var Resto;
		Soma = 0;

		strCPF = strCPF.replace(/[^0-9]/g,"");
	    if (strCPF == "00000000000") return false;
	
        for (let i=1; i<=9; i++) Soma = Soma + parseInt(strCPF.substring(i-1, i)) * (11 - i);
        Resto = (Soma * 10) % 11;
	
		if ((Resto == 10) || (Resto == 11))  Resto = 0;
		if (Resto != parseInt(strCPF.substring(9, 10)) ) return false;
	
	    Soma = 0;
		for (let i = 1; i <= 10; i++) Soma = Soma + parseInt(strCPF.substring(i-1, i)) * (12 - i);
		Resto = (Soma * 10) % 11;
	
		if ((Resto == 10) || (Resto == 11))  Resto = 0;
		if (Resto != parseInt(strCPF.substring(10, 11) ) ) return false;
		return true;
	}

    // METODOS MERCADO PAGO

    this.guessPaymentMethod = async (event) => {
        debugger
        let cardnumber = document.getElementById("cardNumber").value;
        if (cardnumber.length >= 6) {
            let bin = cardnumber.substring(0,6);
            window.Mercadopago.getPaymentMethod({
                "bin": bin
            }, this.setPaymentMethod);
        }
    }

    this.setPaymentMethod = async (status, response) => {
        if(status == 200){
            let paymentMethod = response[0];
            document.getElementById('paymentMethodId').value = paymentMethod.id;
    
            this.getIssuers(paymentMethod.id);
        }else {
            alert('Erro na validação do cartão');
        }
    }

    this.getIssuers = async (paymentMethodId) => {
        window.Mercadopago.getIssuers(
            paymentMethodId,
            () => {}
        );
     }

    this.getCardToken = async (i) => {
        let $form = document.getElementById('paymentForm');
        window.Mercadopago.createToken($form, async (status, response) => {
            if (status == 200 || status == 201) {
                try {
                    this.dadosAssociado.token = response.id;
                    this.dadosAssociado.transaction_amount = valor;
                    this.dadosAssociado.descricao = `Assinatura AupetHeinsten - Plano ${plano} - Pet ${pet}`;

                    this.dadosAssociado.id_plano = id_plano;
                    this.dadosAssociado.valor_plano = valor;
                    this.dadosAssociado.id_pet = id_pet;

                    let assinatura = (await $http.post(`/proposta/criarAssinaturaMp`, this.dadosAssociado)).data;

                    if(assinatura?.status == 404) return NS.notifInfo('Erro ao realizar assinatura! Tente novamente');

                    $('section.pagamento_content').attr("etapa", i);
                } catch (error) {
                    return NS.notifInfo('Erro ao realizar cadastro de assinatura! Tente novamente mais tarde');
                }
            } else {
                return NS.notifInfo('Erro na geração de token do cartão!');
            }
        });
    }
});