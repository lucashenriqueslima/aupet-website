<script type="text/javascript">
    var pagina = "<?php echo $pagina; ?>";
    var path = "<?php echo $sistema->root_path;?>";
    var is_desktop = <?php if (!$sistema->detect->isMobile() && !$sistema->detect->isTablet()){echo 1;}else{echo 0;} ?>;
    var is_tablet = <?php if ($sistema->detect->isTablet()){echo 1;}else{echo 0;} ?>;
    var is_mobile = <?php if ($sistema->detect->isMobile()){echo 1;}else{echo 0;} ?>;
    var scripts_path = "website/script/";
</script>

<header>
    <div class="content">
        <a href="<?= $sistema->seo_pages['home']['seo_url'] ?>" class="logo" style="background-image: url(<?php echo $sistema->root_path;?>website/img/temporario/header/logo.svg);"></a>
        <div class="paginas_content">
            <div class="paginas">
                <a href="<?= $sistema->seo_pages['home']['seo_url'] ?>" <?php if($pagina == 'home'){ echo 'class="active"'; } ?>>Home</a>
                <a href="<?= $sistema->seo_pages['sobre']['seo_url'] ?>" <?php if($pagina == 'sobre'){ echo 'class="active"'; } ?>>Sobre</a>
                <a href="<?= $sistema->seo_pages['rede-credenciada']['seo_url'] ?>" <?php if($pagina == 'rede-credenciada'){ echo 'class="active"'; } ?>>Redes credenciadas</a>
                <a href="<?= $sistema->seo_pages['blog']['seo_url'] ?>" <?php if($pagina == 'blog'){ echo 'class="active"'; } ?>>Blog</a>
                <a href="https://app.aupetheinsten.com.br/#/cadastro/consultor" <?php if($pagina == 'seja-consultor'){ echo 'class="active"'; } ?>>Seja um consultor</a>
                <a href="<?= $sistema->seo_pages['contato']['seo_url'] ?>" <?php if($pagina == 'contato'){ echo 'class="active"'; } ?>>Contato</a>
            </div>
            <a href="https://app.aupetheinsten.com.br/" class="login">LOGIN</a>
            <a href="https://api.whatsapp.com/send?phone=+55<?= str_replace(['(',')',' ', '-'],'',$conf['telefone']) ?>" class="whatsapp" target="_blank">
                <svg xmlns="http://www.w3.org/2000/svg" width="20.937" height="20.937" viewBox="0 0 20.937 20.937"><g id="whatsapp" transform="translate(0.5 0.5)"><path id="Caminho_2859" data-name="Caminho 2859" d="M9.971,0h0A9.962,9.962,0,0,0,1.9,15.811l-1.242,3.7,3.832-1.225A9.967,9.967,0,1,0,9.971,0Z" fill="none" stroke="#fff" stroke-width="1"/><path id="Caminho_2860" data-name="Caminho 2860" d="M118.858,126.605a2.813,2.813,0,0,1-1.956,1.407c-.521.111-1.2.2-3.491-.75a12.488,12.488,0,0,1-4.963-4.384,5.693,5.693,0,0,1-1.184-3.007,3.182,3.182,0,0,1,1.019-2.426,1.448,1.448,0,0,1,1.019-.358c.123,0,.234.006.334.011.293.012.44.03.633.492.24.579.826,2.01.9,2.157a.593.593,0,0,1,.042.54,1.722,1.722,0,0,1-.323.457c-.147.169-.287.3-.434.481-.135.158-.287.328-.117.621a8.848,8.848,0,0,0,1.617,2.01,7.329,7.329,0,0,0,2.338,1.442.63.63,0,0,0,.7-.111,12.063,12.063,0,0,0,.779-1.032.556.556,0,0,1,.715-.217c.269.093,1.693.8,1.986.943s.486.217.557.34A2.483,2.483,0,0,1,118.858,126.605Z" transform="translate(-103.087 -112.529)" fill="#fafafa"/></g></svg>
                <span><?php echo $conf['telefone']; ?></span>
            </a>
        </div>
        <button class="menu"><svg xmlns="http://www.w3.org/2000/svg" width="40" height="23" viewBox="0 0 40 23"><g id="Grupo_630" data-name="Grupo 630" transform="translate(-295.5 -60)"><line id="Linha_1" data-name="Linha 1" x2="40" transform="translate(295.5 82.5)" fill="none" stroke="#fff" stroke-width="1"/><line id="Linha_2" data-name="Linha 2" x2="40" transform="translate(295.5 71.5)" fill="none" stroke="#fff" stroke-width="1"/><line id="Linha_3" data-name="Linha 3" x2="40" transform="translate(295.5 60.5)" fill="none" stroke="#fff" stroke-width="1"/></g></svg></button>
    </div>
</header>

<section class="drop_menu" active="false">
    <div class="content">
        <a href="<?= $sistema->seo_pages['home']['seo_url'] ?>" class="logo" style="background-image: url(<?php echo $sistema->root_path;?>website/img/temporario/header/logo.svg);"></a>
        <button class="fechar"><svg xmlns="http://www.w3.org/2000/svg" width="25.367" height="25.367" viewBox="0 0 25.367 25.367"><path id="close" d="M15.008,12.82l9.877-9.878A1.643,1.643,0,0,0,22.561.618L12.683,10.5,2.806.618A1.643,1.643,0,0,0,.482,2.942L10.36,12.82.482,22.7a1.643,1.643,0,1,0,2.324,2.324l9.877-9.878,9.878,9.878A1.643,1.643,0,1,0,24.885,22.7Zm0,0" transform="translate(0 -0.136)" fill="#fff"/></svg></button>
        <a href="<?= $sistema->seo_pages['home']['seo_url'] ?>" class="page">Home</a>
        <a href="<?= $sistema->seo_pages['sobre']['seo_url'] ?>" class="page">Sobre</a>
        <a href="<?= $sistema->seo_pages['rede-credenciada']['seo_url'] ?>" class="page">Redes credenciadas</a>
        <a href="<?= $sistema->seo_pages['blog']['seo_url'] ?>" class="page">Blog</a>
        <a href="https://app.aupetheinsten.com.br/#/cadastro/consultor" class="page">Seja um consultor</a>
        <a href="<?= $sistema->seo_pages['contato']['seo_url'] ?>" class="page">Contato</a>
        <a href="https://app.aupetheinsten.com.br/" class="login">login</a>
    </div>
</section>

