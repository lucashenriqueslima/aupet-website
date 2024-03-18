$(document).ready(function () {
	new photoswipe_init('section.blog_interno .content .galeria a.galeria_item');


    var color = "#FFA02D";

    $('select[vazio]').change(function() {
        if($(this).val() == ''){
            $(this).attr("vazio", "true")
        }
        else{
            $(this).attr("vazio", "false")
        }
    });


    //MENU
    $('header .content button.menu').click(function () {
        $('section.drop_menu').attr('active', 'true');
    });
    $('section.drop_menu .content button.fechar').click(function () {
        $('section.drop_menu').attr('active', 'false');
    });



    if (pagina == 'home'){

        var blog_home_carousel = $('section.home_blog .content .carousel_content .carousel');
        blog_home_carousel.slick({
            infinite: true,
            slidesToShow: 3,
            slidesToScroll: 1,
            arrows: false,
            dots: false,
            variableWidth: true,
            appendDots: $('section.home_blog .content .carousel_content .dots'),
            responsive: [
                {
                    breakpoint: 1151,
                    settings: {
                        slidesToShow: 2,
                        slidesToScroll: 2,
                        dots: true
                    }
                },
                {
                    breakpoint: 771,
                    settings: {
                        slidesToShow: 1,
                        slidesToScroll: 1,
                        dots: true
                    }
                }
            ]
        });

    }

    if(pagina === 'contato'){
        $formContato = $('form[name=form_contato]');
		$formContato.makeclickform({
			form_name: 'E-mail de Contato',
			table: "hbrd_desk_contato",
			form: 1,
			list: 1,
			conversion_script: 13,
			custom_verify: {
				nome: {
					type: "attention",
					message: "Preencha o campo nome"
				},
				telefone: {
					type: "attention",
					message: "Preencha o campo Telefone"
                },
                comentario: {
					type: "attention",
					message: "Preencha o campo Mensagem"
				},
				email: {
					type: "attention",
					call: "validaEmail",
					message: "Preencha o campo e-mail com um endereço válido"
				},
				interesse: {
					type: 'attention',
					message: 'Selecione o interesse'
				},
			},  not_required: {
                checkbox1: 0, checkbox2: 0
            }
        });
    }

    if(pagina == 'seja-consultor'){
        $formContato = $('form[name=form_seja_consultor]');
		$formContato.makeclickform({
			form_name: 'E-mail de Contato',
			table: "hbrd_desk_seja_consultor",
			form: 1,
			list: 1,
			conversion_script: 13,
			custom_verify: {
				nome: {
					type: "attention",
					message: "Preencha o campo nome"
				},
				telefone: {
					type: "attention",
					message: "Preencha o campo Telefone"
                },
                mensagem: {
					type: "attention",
					message: "Preencha o campo Mensagem TESTE"
				},
				email: {
					type: "attention",
					call: "validaEmail",
					message: "Preencha o campo e-mail com um endereço válido"
				},
                curriculo: {
                    type: "attention",
                    message: "Anexe um currículo",
                    upload: ["doc", "pdf", "docx"]
                },				
			},not_required: {
                checkbox1: 0, checkbox2: 0
            }
        });
    }

    $('#arquivo').change(function () {
        var files = $(this)[0].files;
        var nome = files[0].name;
        $('label[for="arquivo"]').html(nome);
    });

    //SLICK
    // carousel.slick({
    //     infinite: true,
    //     slidesToShow: 2,
    //     slidesToScroll: 1,
    //     arrows: false,
    //     dots: true,
    //     variableWidth: true,
    //     appendDots: $('.carousel div.dots'),
    //     responsive: [
    //         {
    //             breakpoint: 501,
    //             settings: {
    //                 slidesToShow: 1
    //             }
    //         }
    //     ]
    // });

    //ARROWS SLICK
    // var arrow_button = $('.carouse div.arrow button');
    // arrow_button.click(function () {
    //     var direction = $(this).attr('class');

    
    //     carousel.slick(direction);
    // });

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
});