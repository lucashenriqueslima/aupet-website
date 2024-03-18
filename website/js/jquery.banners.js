
function banners_home(banner) {
    var $banners = {};

    $banners.ctrl = {};
    $banners.ctrl.index = 0;
    $banners.ctrl.length = ($(banner + " .display .banner").length - 1);
    $banners.ctrl.time = 8000;
    $banners.ctrl.tempimage = new Image();
    $banners.ctrl.preloadimage = new Image();

    $banners.dom = {};
    $banners.dom.display = $(banner + " .display");

    $banners.ctrl.nav = {};
    $banners.ctrl.nav.numbers = false;
    $banners.ctrl.nav.arrows = false;

    //SE TIVER NUMERAÇÃO
    if ($(banner + " .numeros").length > 0) {

        $banners.ctrl.nav.numbers = true;
        $banners.dom.numbers = $(banner + " .numeros");

        $banners.dom.numbers.empty();
        for(i=0;i<=$banners.ctrl.length;i++){
            $('<span></span>').appendTo($banners.dom.numbers);
        }
        $banners.dom.numbers.find('span:first').addClass('atual');

        $banners.dom.numbers.find("span").click(function () {
            $banners.ctrl.index = $(this).index();
            change();
        });

    }

    //SE TIVER SETAS DE NAVEGAÇÃO
    if ($(banner + " .setas").length > 0) {

        if($banners.ctrl.length > 0){
            $banners.ctrl.nav.arrows = true;

            $banners.dom.bt_left = $(banner + " .setas .left");
            $banners.dom.bt_right = $(banner + " .setas .right");

            $banners.dom.bt_left.click(prev);
            $banners.dom.bt_right.click(next);
        }else{
            $(banner + " .setas").hide();
        }

    }

    //SE TIVER ANIMAÇÃO DE TEMPO 
    if($(banner + ' .time').length > 0){
        $banners.dom.timeanimation = $(banner + ' .time')
    }

    //SE TIVER TIMPO INDIVIDUAL PARA CADA BANNER
    setIndividualTimeOut();

    //SE TIVER VÍDEO
    if($banners.dom.display.find('.videoplay').length > 0){
        $('.videoplay').click(function(){
            $(this).parents('.banner').find('video').css({'position':'relative','display':'block','z-index':'100','width':'100%'});
            pause();
            var vElement = $(this).parents('.banner').find('video')[0];
            vElement.load();
            vElement.onloadeddata = function() {
                vElement.play();
            };
            vElement.loop = false;
            vElement.onended = function(){
                $('.banner video').css({'position':'static'});
                vElement.loop = true;
                vElement.play();
                next();
            }
        });
    }

    function next() {
        $banners.ctrl.index++;
        change();
        preLoadNext();
    }

    function prev() {
        $banners.ctrl.index--;
        change();
    }

    function pause(){
        clearInterval($banners.ctrl.interval);
        $banners.dom.timeanimation.stop();
    }

    function change() {

        clearInterval($banners.ctrl.interval);

        if ($banners.ctrl.index > $banners.ctrl.length)
            $banners.ctrl.index = 0;
        if ($banners.ctrl.index < 0)
            $banners.ctrl.index = $banners.ctrl.length;

        setIndividualTimeOut();


        if ($banners.dom.display.find(".banner:eq(" + $banners.ctrl.index + ")").attr("data-image") == "loaded" || $banners.dom.display.find(".banner:eq(" + $banners.ctrl.index + ")").attr("data-image") == "video") {

            if ($banners.ctrl.nav.numbers) {

                $banners.dom.numbers.find("span.atual").removeClass("atual");
                $banners.dom.numbers.find("span:eq(" + $banners.ctrl.index + ")").addClass("atual");

            }

            $banners.dom.display.find(".banner:visible").fadeOut();
            $banners.dom.display.find(".banner:eq(" + $banners.ctrl.index + ")").fadeIn();
            $banners.ctrl.interval = setInterval(next, $banners.ctrl.time);

            timeCount();
            playInternalAnimation();


        } else {

            $banners.ctrl.tempimage.src = $banners.dom.display.find(".banner:eq(" + $banners.ctrl.index + ")").attr("data-image");
            load();

        }

    }

    function load() {
        if ($banners.ctrl.tempimage.complete) {
            $banners.dom.display.find(".banner:eq(" + $banners.ctrl.index + ")").css({'background-image': 'url("' + $banners.ctrl.tempimage.src + '")'}).attr("data-image", "loaded");
            change();
        } else {
            setTimeout(load, 2000);
        }
    }

    function preLoadNext(){
        var index = ($banners.ctrl.index+1);
        if ($banners.dom.display.find(".banner:eq(" + index + ")").attr("data-image") != "loaded" || $banners.dom.display.find(".banner:eq(" + index + ")").attr("data-image") != "video") {
            $banners.ctrl.preloadimage.src = $banners.dom.display.find(".banner:eq(" + index + ")").attr("data-image");
            preLoading(index)
        }
    }

    function preLoading(index) {
        if ($banners.ctrl.preloadimage.complete) {
            if($banners.dom.display.find(".banner:eq(" + index + ")").attr('data-image') != "loaded" && $banners.dom.display.find(".banner:eq(" + index + ")").attr("data-image") != "video"){
                $banners.dom.display.find(".banner:eq(" + index + ")").css({'background-image': 'url("' + $banners.ctrl.preloadimage.src + '")'}).attr("data-image", "loaded");
            }
        } else {
            setTimeout(function(){preLoading(index);}, 2000);
        }
        console.log('preloading...'+ index);
    }
    
    function timeCount(){
        if($(banner + ' .time').length > 0){
            $banners.dom.timeanimation.dequeue().css({'width':'0%'}).animate({'width':'100%'}, $banners.ctrl.time,'linear');
        }
    }

    function playInternalAnimation(){
        /*
        if($banners.dom.display.find('.banner:eq('+ $banners.ctrl.index +') .box').length > 0){
            $banners.dom.display.find('.banner:eq('+ $banners.ctrl.index +') .box.left > div').each(function(i){
                $(this).css({'opacity':0,'margin-left':50}).delay(i*300).animate({'opacity':1, 'margin-left':0},600,'easeOutQuad');
                //$(this).hide().delay(i*500).fadeIn(800);
            });
            $banners.dom.display.find('.banner:eq('+ $banners.ctrl.index +') .box.right > div').each(function(i){
                $(this).css({'opacity':0,'margin-right':50}).delay(i*300).animate({'opacity':1, 'margin-right':0},600,'easeOutQuad');
                //$(this).hide().delay(i*500).fadeIn(800);
            });
        }
        */     
    }

    function setIndividualTimeOut(){
        if($banners.dom.display.find('.banner:eq('+$banners.ctrl.index+')[data-time]').length){
            $banners.ctrl.time = parseInt($banners.dom.display.find('.banner:eq('+$banners.ctrl.index+')').attr('data-time'));
        }
    }

    if ($banners.ctrl.length>0) {
        playInternalAnimation();
        timeCount();
        preLoadNext();
        $banners.ctrl.interval = setInterval(next, $banners.ctrl.time);
    }

/*
    rsz_banners();

    $(window).resize(rsz_banners);


    function rsz_banners() {

        $banners.ctrl.ratioHeight = Math.round((1080/1920) * $(window).width());

        if($banners.ctrl.ratioHeight > $(window).height()) {
            $banners.dom.display.find('.banner').css({'background-size':'100% auto'});
        }else{
            $banners.dom.display.find('.banner').css({'background-size':'auto 100%'});
        }

        if($banners.dom.display.find(".banner[data-image=video]").length > 0){
            console.log('dd'+$banners.dom.display.find(".banner[data-image=video]").length)
            ///$banners.dom.display.find(".banner[data-image=video] video").width($(window).width()).height($(window).height());
        }


    }

*/
return $banners
}
