$(document).ready(function () {
    var metade_tela = ($(window).height() / 2);

    //HOME
    if(pagina == 'home'){

    }


    //Ancora scroll
    $('[scrollTo]').click(function () {
        var alvo = $(this).attr('scrollTo');
        var distancia_alvo = $(alvo).offset().top;
        var scroll = distancia_alvo;

        $("html, body").animate({
            scrollTop: scroll
        }, 1200);
    });


    //ANIMAÇÃO SCROLL
    let alvo = $('#first_info');
    let active_scroll = alvo.offset().top;
    let animacao_scroll = function() {
        if ($(window).scrollTop() >= (active_scroll + alvo.height() - $(window).height())) {
        }
    }
    $(window).scroll(animacao_scroll);
    animacao_scroll();
    //----------
});