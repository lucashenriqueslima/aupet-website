
<div class="pswp" tabindex="-1" role="dialog" aria-hidden="true">

    <!-- Background of PhotoSwipe. 
         It's a separate element as animating opacity is faster than rgba(). -->
    <div class="pswp__bg"></div>

    <!-- Slides wrapper with overflow:hidden. -->
    <div class="pswp__scroll-wrap">

        <!-- Container that holds slides. 
            PhotoSwipe keeps only 3 of them in the DOM to save memory.
            Don't modify these 3 pswp__item elements, data is added later on. -->
        <div class="pswp__container">
            <div class="pswp__item"></div>
            <div class="pswp__item"></div>
            <div class="pswp__item"></div>
        </div>

        <!-- Default (PhotoSwipeUI_Default) interface on top of sliding area. Can be changed. -->
        <div class="pswp__ui pswp__ui--hidden">

            <div class="pswp__top-bar">

                <!--  Controls are self-explanatory. Order can be changed. -->

                <div class="pswp__counter"></div>

                <button class="pswp__button pswp__button--close" title="Close (Esc)"></button>

                <button class="pswp__button pswp__button--share" title="Share"></button>

                <button class="pswp__button pswp__button--fs" title="Toggle fullscreen"></button>

                <button class="pswp__button pswp__button--zoom" title="Zoom in/out"></button>

                <!-- Preloader demo http://codepen.io/dimsemenov/pen/yyBWoR -->
                <!-- element will get class pswp__preloader--active when preloader is running -->
                <div class="pswp__preloader">
                    <div class="pswp__preloader__icn">
                      <div class="pswp__preloader__cut">
                        <div class="pswp__preloader__donut"></div>
                      </div>
                    </div>
                </div>
            </div>

            <div class="pswp__share-modal pswp__share-modal--hidden pswp__single-tap">
                <div class="pswp__share-tooltip"></div> 
            </div>

            <button class="pswp__button pswp__button--arrow--left" title="Previous (arrow left)">
            </button>

            <button class="pswp__button pswp__button--arrow--right" title="Next (arrow right)">
            </button>

            <div class="pswp__caption">
                <div class="pswp__caption__center"></div>
            </div>

        </div>

    </div>

</div>

<script src="website/plugins/photoswipe/photoswipe.js"></script>
<script src="website/plugins/photoswipe/photoswipe-ui-default.js"></script>

<script>

    var pswpcss = document.createElement('link');
    pswpcss.setAttribute('rel', 'stylesheet');
    pswpcss.setAttribute('type', 'text/css');
    pswpcss.setAttribute('href', 'website/plugins/photoswipe/photoswipe.css');
    document.getElementsByTagName("head")[0].appendChild(pswpcss);

    pswpcss = document.createElement('link');
    pswpcss.setAttribute('rel', 'stylesheet');
    pswpcss.setAttribute('type', 'text/css');
    pswpcss.setAttribute('href', 'website/plugins/photoswipe/default-skin/default-skin.css');
    document.getElementsByTagName("head")[0].appendChild(pswpcss);
    

    function photoswipe_init(elements){


        var pswpElement = document.querySelectorAll('.pswp')[0];
        var options = {  
            history: false,
            focus: false,
            bgOpacity: 0.85
        };
        var galery = [];
        var each = document.querySelectorAll(elements);
        Array.prototype.forEach.call(each, function(el, i){

            galery.push({'src':el.href, 'w': el.getAttribute('data-width'), 'h': el.getAttribute('data-height'), 'title': el.title});

            /*
            el.addEventListener('click', function(e){
                console.log(el.getAttribute('data-index'));
                e.preventDefault();
                options.index = el.getAttribute('data-index');
                pswp = new PhotoSwipe( pswpElement, PhotoSwipeUI_Default, galery, options);
                pswp.init();
            });
            */

        });
        $(document).on('click',elements, function(e){
            e.preventDefault();
            console.log($(this).data('index'))
            options.index = $(this).data('index');
            pswp = new PhotoSwipe( pswpElement, PhotoSwipeUI_Default, galery, options);
            pswp.init();
        });

        return 0;

    }

</script>