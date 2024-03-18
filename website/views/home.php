<?php
    $pagina = "home";
    $currentpage = $sistema->seo_pages[$pagina];
    $blogs = $sistema->DB_fetch_array("
        SELECT
            A.*,
            DATE_FORMAT(A.data, '%d/%m/%Y') AS data,
            CONCAT(B.seo_url_breadcrumbs, B.seo_url) AS url,
            D.nome AS categoria
        FROM
            hbrd_cms_blog A
                INNER JOIN
            hbrd_cms_paginas B ON B.id = A.id_pagina
                LEFT JOIN
            hbrd_cms_blog_hbrd_cms_blog_categorias C ON C.id_blog = A.id
                LEFT JOIN
            hbrd_cms_blog_categorias D ON D.id = C.id_categoria AND D.stats = 1
        WHERE
            A.stats = 1
                AND ((A.agendar_entrada IS NULL
                AND A.agendar_saida IS NULL)
                OR (A.agendar_entrada IS NULL
                AND A.agendar_saida >= NOW())
                OR (A.agendar_entrada <= NOW()
                AND A.agendar_saida IS NULL)
                OR (A.agendar_entrada <= NOW()
                AND A.agendar_saida >= NOW()))
        GROUP BY A.id
        ORDER BY A.data DESC, A.id DESC")->rows;
    $app_infos = $sistema->DB_fetch_array("SELECT * FROM hbrd_cms_home_app_infos WHERE id = 1")->rows[0];
    $planos = $sistema->DB_fetch_array("SELECT A.* FROM hbrd_app_planos A WHERE A.delete_at IS NULL ORDER BY A.ordem LIMIT 3")->rows;
    $new_planos = [];
    foreach ($planos as $plano) {
        $plano['beneficios'] = $sistema->DB_fetch_array("
            SELECT 
                A.nome, A.ordem, B.id_plano
            FROM
                hbrd_app_plano_beneficio A
                    LEFT JOIN
                hbrd_app_plano_use_beneficio B ON A.id = B.id_beneficio
            WHERE
                 B.id_plano = {$plano['id']} AND A.delete_at IS NULL AND A.stats = 1
            ORDER BY ordem;")->rows;
        array_push($new_planos, $plano);
    }
    $beneficios = $sistema->DB_fetch_array("SELECT * FROM hbrd_app_plano_beneficio A WHERE A.delete_at IS NULL AND A.stats = 1 ORDER BY A.ordem")->rows;
    $rede_infos = $sistema->DB_fetch_array("SELECT * FROM hbrd_cms_home_rede_credenciada WHERE id = 1")->rows[0];
    $banners = $sistema->DB_fetch_array("SELECT * FROM hbrd_main_banners A WHERE A.id=1 ORDER BY A.ordem")->rows[0];
    $broches_imgs = [
        'bronze',
        'prata',
        'ouro',
    ]
?>
<?php include "_inc_headers.php"; ?>
<body id="<?= $pagina; ?>">
<?php include "_inc_header.php"; ?>

<section class="home_header">

<?php if (!empty($banners)) : ?>
    <div class="content">
        <div class="text">
            <h1><?= $banners['descricao']; ?></h1>
            <!-- scrollTo=".home_planos" -->
            <a  href="<?= $banners['link']  ?>" class="link">Saiba mais sobre a Aupet!</a>
        </div>
        <div class="image_content">
            <div class="image" style='background-image: url("<?= $sistema->getImageFileSized($banners['imagem_desktop'], 875, 585); ?>");'></div>
        </div>
    </div>
</section>
<?php endif; ?>

<?php if (!empty($planos)) : ?>
<section class="home_planos">
    <h2><b>Escolha o plano</b> com que seu filho de 4 patas se encaixa melhor!</h2>
    <div class="planos">
        <div class="planos_content">
            <?php foreach ($planos as $key=>$plano) : ?>
                <?php
                $planoBeneficios =  $sistema->DB_fetch_array("SELECT * FROM hbrd_app_plano_use_beneficio A WHERE A.id_plano = {$plano['id']}")->rows;
                ?>
                <div>
                <img class="broche" width="200px" height="250px" src="<?php echo $sistema->root_path;?>website/img/home/broche_<?= $broches_imgs[$key] ?>.png" alt="">

                <div class="item">
                    <div class="header">
                        <h3>
                            Plano <br> 
                            <?= ucfirst(strtolower($plano['titulo'])); ?>
                        </h3>
                    </div>
                    <?php if (!empty($plano['descricao'])) : ?>
                        <!-- <p class="descricao"><?=$plano['descricao'] ?></p> -->
                    <?php endif; ?>

                    <div class="content">
                        <?php foreach ($beneficios as $beneficio) : ?>
                            <?php
                            $benf = 'false';
                            foreach ($planoBeneficios as $planoBeneficio) {
                                    if ($planoBeneficio['id_beneficio'] == $beneficio['id']) {
                                        $benf = 'true';
                                    }
                                }
                            ?>
                            <div class="item_list" active="<?= $benf; ?>"><?= $beneficio['nome']; ?></div>
                        <?php endforeach; ?>
                    </div>
                    <div class="valor_content">
                    <p class="prefixo_valor">Por Apenas:</p>
                    <div class="sifra_valor_content">
                    <p class="sifra">R$</p>
                    <p class="valor"><?= substr($plano['valor'], 0, -3); ?></p>
                    <p class="valor_centavo">,<?= substr($plano['valor'], strrpos($plano['valor'], '.') + 1) ?>/mês</p>            
                </div>
                    </div>
                    <a href="https://app.aupetheinsten.com.br/#/cadastro/usuario/<?= $plano['id'] ?>" style="background-image: url(<?php echo $sistema->root_path;?>website/img/home/bg_plan_button.png);" class="quero_esse_plano">Assinar</a>
                    <!-- <a href="http://192.168.1.10:8100/#/cadastro/usuario/<?= $plano['id'] ?>" class="quero_esse_plano">quero esse plano</a> -->
                </div>
        </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<?php if (!empty($app_infos)) : ?>
<section class="home_info_app">
    <div class="content">
        <div class="left_content">
            <h2><b><?= $app_infos['titulo_um'];?></b></h2>
            <p><?= $app_infos['texto_um']; ?></p>            
            <div class="plataformas">
                <a href="<?= $company['link_applestore']; ?>" target="_blank"><img src="website/img/ios-app.svg" alt=""></a>
                <a href="<?= $company['link_playstore']; ?>" target="_blank"><img src="website/img/android-app.svg" alt=""></a>
            </div>
        </div>
        <div class="center_content">
            <div class="image" style='background-image: url("<?= $sistema->getImageFileSized($app_infos['imagem'], 295, 600); ?>");'></div>
        </div>
        <div class="right_content">
            <div class="item">
                <svg xmlns="http://www.w3.org/2000/svg" width="69.876" height="57.676" viewBox="0 0 69.876 57.676"><g id="record" transform="translate(-24 -46)"><circle id="Elipse_49" data-name="Elipse 49" cx="21.836" cy="21.836" r="21.836" transform="translate(24 51.268)" fill="#772e8a"/><g id="Grupo_189" data-name="Grupo 189" transform="translate(30.655 61.528)"><path id="Caminho_495" data-name="Caminho 495" d="M52,106h61v39.929H52Z" transform="translate(-50.891 -104.891)" fill="#fff"/><path id="Caminho_496" data-name="Caminho 496" d="M110.112,144.148h-61A1.109,1.109,0,0,1,48,143.039V103.109A1.109,1.109,0,0,1,49.109,102h61a1.109,1.109,0,0,1,1.109,1.109v39.929A1.109,1.109,0,0,1,110.112,144.148Zm-59.894-2.218H109V104.218H50.218Z" transform="translate(-48 -102)" fill="#4c241d"/></g><g id="Grupo_190" data-name="Grupo 190" transform="translate(36.201 70.351)"><path id="Caminho_498" data-name="Caminho 498" d="M80.2,157.162a12.214,12.214,0,0,1-12.2-12.2V134.929a1.11,1.11,0,0,1,1.747-.908l5.653,3.973a8.236,8.236,0,0,1,10.594-.054l4.584-3.86a1.109,1.109,0,0,1,1.823.849v10.033a12.214,12.214,0,0,1-12.2,12.2Zm-9.982-20.1v7.9a9.982,9.982,0,1,0,19.965,0v-7.648l-3.511,2.956a1.108,1.108,0,0,1-1.518-.083,6.04,6.04,0,0,0-8.851,0,1.11,1.11,0,0,1-1.441.142Z" transform="translate(-68 -133.819)" fill="#4c241d"/></g><g id="Grupo_191" data-name="Grupo 191" transform="translate(26.218 46)"><circle id="Elipse_50" data-name="Elipse 50" cx="1.092" cy="1.092" r="1.092" transform="translate(23.985 33.656)" fill="#4c241d"/><path id="Caminho_499" data-name="Caminho 499" d="M109.149,183.511c0,.527-.794,2.218-1.775,2.218s-1.775-1.691-1.775-2.218.794-.955,1.775-.955S109.149,182.984,109.149,183.511Z" transform="translate(-85.192 -144.691)" fill="#4c241d"/><ellipse id="Elipse_51" data-name="Elipse 51" cx="2.184" cy="1.092" rx="2.184" ry="1.092" transform="translate(17.434 33.656)" fill="#4c241d"/><path id="Caminho_500" data-name="Caminho 500" d="M181.982,132.218h-8.873a1.109,1.109,0,1,1,0-2.218h8.873a1.109,1.109,0,1,1,0,2.218Z" transform="translate(-133.18 -106.708)" fill="#4c241d"/><path id="Caminho_501" data-name="Caminho 501" d="M190.856,152.218H173.109a1.109,1.109,0,1,1,0-2.218h17.746a1.109,1.109,0,0,1,0,2.218Z" transform="translate(-133.18 -121.162)" fill="#4c241d"/><path id="Caminho_502" data-name="Caminho 502" d="M190.856,172.218H173.109a1.109,1.109,0,1,1,0-2.218h17.746a1.109,1.109,0,0,1,0,2.218Z" transform="translate(-133.18 -135.616)" fill="#4c241d"/><path id="Caminho_503" data-name="Caminho 503" d="M190.856,192.218H173.109a1.109,1.109,0,1,1,0-2.218h17.746a1.109,1.109,0,0,1,0,2.218Z" transform="translate(-133.18 -150.071)" fill="#4c241d"/><path id="Caminho_504" data-name="Caminho 504" d="M174.246,218.884a1.109,1.109,0,0,1-.784-1.893l3.529-3.529a1.109,1.109,0,0,1,1.568,1.568l-3.529,3.529A1.1,1.1,0,0,1,174.246,218.884Z" transform="translate(-134.002 -166.792)" fill="#4c241d"/><path id="Caminho_505" data-name="Caminho 505" d="M214.089,218.884a1.106,1.106,0,0,1-.738-.281l-1.979-1.764a1.109,1.109,0,1,1,1.476-1.656l1.2,1.068,2.789-2.788a1.109,1.109,0,1,1,1.568,1.568l-3.529,3.529A1.1,1.1,0,0,1,214.089,218.884Z" transform="translate(-161.366 -166.792)" fill="#4c241d"/><path id="Caminho_506" data-name="Caminho 506" d="M177.775,218.884a1.106,1.106,0,0,1-.784-.325l-3.529-3.529a1.109,1.109,0,1,1,1.568-1.568l3.529,3.529a1.109,1.109,0,0,1-.784,1.893Z" transform="translate(-134.001 -166.792)" fill="#4c241d"/><path id="Caminho_507" data-name="Caminho 507" d="M139.327,180.218h-2.218a1.109,1.109,0,0,1,0-2.218h2.218a1.109,1.109,0,1,1,0,2.218Z" transform="translate(-107.162 -141.398)" fill="#4c241d"/><path id="Caminho_508" data-name="Caminho 508" d="M75.327,180.218H73.109a1.109,1.109,0,0,1,0-2.218h2.218a1.109,1.109,0,0,1,0,2.218Z" transform="translate(-60.909 -141.398)" fill="#4c241d"/><path id="Caminho_509" data-name="Caminho 509" d="M49.109,50.437A1.109,1.109,0,0,1,48,49.327V47.109a1.109,1.109,0,0,1,2.218,0v2.218A1.109,1.109,0,0,1,49.109,50.437Z" transform="translate(-43.563 -46)" fill="#4c241d"/><path id="Caminho_510" data-name="Caminho 510" d="M49.109,74.437A1.109,1.109,0,0,1,48,73.327V71.109a1.109,1.109,0,0,1,2.218,0v2.218A1.109,1.109,0,0,1,49.109,74.437Z" transform="translate(-43.563 -63.345)" fill="#4c241d"/><path id="Caminho_511" data-name="Caminho 511" d="M35.327,64.218H33.109a1.109,1.109,0,0,1,0-2.218h2.218a1.109,1.109,0,1,1,0,2.218Z" transform="translate(-32 -57.563)" fill="#4c241d"/><path id="Caminho_512" data-name="Caminho 512" d="M59.327,64.218H57.109a1.109,1.109,0,0,1,0-2.218h2.218a1.109,1.109,0,0,1,0,2.218Z" transform="translate(-49.345 -57.563)" fill="#4c241d"/><circle id="Elipse_52" data-name="Elipse 52" cx="1.186" cy="1.186" r="1.186" transform="translate(23.989 6.771)" fill="#4c241d"/></g></g></svg>
                <h3><?= $app_infos['titulo_dois']; ?></h3>
                <p><?= $app_infos['texto_dois']; ?></p>
            </div>
            <div class="item">
                <svg xmlns="http://www.w3.org/2000/svg" width="52.427" height="63" viewBox="0 0 52.427 63"><g id="noun_Paw_Print_1143832" data-name="noun_Paw Print_1143832" transform="translate(91.95 -99.598)"><path id="Caminho_573" data-name="Caminho 573" d="M55.466,11.241a21.331,21.331,0,0,1,2.247,2.047,25.267,25.267,0,0,1,7.763,18.6,25.046,25.046,0,0,1-7.763,18.5,2.494,2.494,0,0,0-.25.474,1.906,1.906,0,0,0-.55.5l-14.829,15.6q-2.871,3.269-5.667,0L21.064,50.735l-.25-.349a25.048,25.048,0,0,1-7.765-18.5,25.266,25.266,0,0,1,7.765-18.6,18.945,18.945,0,0,1,2.3-2.047A24.729,24.729,0,0,1,39.314,5.6,24.785,24.785,0,0,1,55.466,11.241Zm6.542,20.571a21.9,21.9,0,0,0-6.691-16.052,21.953,21.953,0,0,0-16.1-6.691,21.9,21.9,0,0,0-16.053,6.691,21.892,21.892,0,0,0-6.691,16.052,21.892,21.892,0,0,0,6.691,16.052,21.9,21.9,0,0,0,16.053,6.691,21.952,21.952,0,0,0,16.1-6.691A21.9,21.9,0,0,0,62.008,31.812Zm-14.531-2.9a3.566,3.566,0,0,1,.275-2.646,3.41,3.41,0,0,1,2.072-1.622A3.37,3.37,0,0,1,52.471,25a3.513,3.513,0,0,1,1.323,4.719,3.368,3.368,0,0,1-2.1,1.622,3.2,3.2,0,0,1-2.6-.3A3.485,3.485,0,0,1,47.478,28.916Zm-6.217-10.91a3.42,3.42,0,0,1,2.946.525,4.088,4.088,0,0,1,1.823,2.545,4.275,4.275,0,0,1-.25,3.147,3.522,3.522,0,0,1-5.117,1.423,4.266,4.266,0,0,1-1.874-2.572,4.1,4.1,0,0,1,.3-3.146A3.445,3.445,0,0,1,41.261,18.005Zm5.592,24.666a6.123,6.123,0,0,0-4.394-1.4q-2.4.074-3.695,3.07-1.323,3.02-4.445-.775-3.145-3.87-1.148-7.414a9.475,9.475,0,0,1,5.917-4.643,10.641,10.641,0,0,1,7.889.9q3.92,2,2.9,6.891Q48.8,44.221,46.853,42.672ZM35.345,24.048a4.158,4.158,0,0,1-.3,3.17,3.3,3.3,0,0,1-2.173,1.872,3.507,3.507,0,0,1-2.9-.424,4.156,4.156,0,0,1-1.822-2.572,3.952,3.952,0,0,1,.25-3.146A3.291,3.291,0,0,1,30.627,21a3.182,3.182,0,0,1,2.846.5A4.169,4.169,0,0,1,35.345,24.048ZM24.659,32.786a3.59,3.59,0,0,1,2.122-1.649,3.159,3.159,0,0,1,2.6.326A3.28,3.28,0,0,1,31,33.559a3.224,3.224,0,0,1-.3,2.646,3.3,3.3,0,0,1-2.1,1.622,3.375,3.375,0,0,1-2.6-.3,3.487,3.487,0,0,1-1.623-2.1A3.556,3.556,0,0,1,24.659,32.786Z" transform="translate(-105 94)" fill="#772e8a" fill-rule="evenodd"/></g></svg>
                <h3><?= $app_infos['titulo_tres']; ?></h3>
                <p><?= $app_infos['texto_tres']; ?></p>
            </div>
            <div class="item">
                <svg xmlns="http://www.w3.org/2000/svg" width="59.163" height="50.482" viewBox="0 0 59.163 50.482"><g id="Grupo_662" data-name="Grupo 662" transform="translate(-1146.498 -1964.601)"><path id="calendar" d="M44.172,6.31h-2.1V2.1a2.1,2.1,0,0,0-2.1-2.1h-2.1a2.1,2.1,0,0,0-2.1,2.1V6.31H14.724V2.1a2.1,2.1,0,0,0-2.1-2.1h-2.1a2.1,2.1,0,0,0-2.1,2.1V6.31H6.31A6.318,6.318,0,0,0,0,12.621V44.172a6.318,6.318,0,0,0,6.31,6.31H44.172a6.318,6.318,0,0,0,6.31-6.31V12.621a6.318,6.318,0,0,0-6.31-6.31Zm2.1,37.862a2.107,2.107,0,0,1-2.1,2.1H6.31a2.107,2.107,0,0,1-2.1-2.1V21.119H46.276Zm0,0" transform="translate(1146.498 1964.601)" fill="#772e8a"/><circle id="Elipse_4" data-name="Elipse 4" cx="8.5" cy="8.5" r="8.5" transform="translate(1188.661 1964.877)" fill="red"/><text id="_4" data-name="4" transform="translate(1196.661 1977.877)" fill="#fff" font-size="12" font-family="OpenSans-Semibold, Open Sans" font-weight="600" letter-spacing="0.02em"><tspan x="-3.425" y="0">4</tspan></text></g></svg>
                <h3><?= $app_infos['titulo_quatro']; ?></h3>
                <p><?= $app_infos['texto_quatro']; ?></p>
            </div>
        </div>
    </div>
</section>
<?php endif; ?>

<?php if (!empty($rede_infos)) : ?>
<section class="home_rede">
    <div class="content">
        <div class="image" style='background-image: url("<?= $sistema->getImageFileSized($rede_infos['imagem'], 860, 520); ?>");'></div>
        <div class="text">
            <h2><?= $rede_infos['titulo']; ?></h2>
            <p><?= $rede_infos['texto']; ?></p>
            <a href="<?= $sistema->seo_pages['rede-credenciada']['seo_url']; ?>">Clique aqui e localize!</a>
        </div>
    </div>
</section>
<?php endif; ?>

<?php if (!empty($blogs)) : ?>
<section class="home_blog">
    <div class="content">
        <h2>Blog e dicas</h2>
        <div class="carousel_content">
            <div class="carousel">
                <?php foreach ($blogs as $blog) : ?>
                    <div class="item">
                        <div class="image" style='background-image: url("<?= $sistema->getImageFileSized($blog['imagem'], 555, 295); ?>");'>
                            <a href="<?= $blog['url']; ?>"></a>
                        </div>
                        <div class="item_content">
                            <div class="dados">
                                <span class="data"><?= $blog['data']; ?></span> |
                                <span class="categoria"><?= $blog['categoria']; ?></span>
                            </div>
                            <h3><?= $blog['titulo']; ?></h3>
                            <a href="<?= $blog['url']; ?>" class="saiba_mais">
                                <svg xmlns="http://www.w3.org/2000/svg" width="14.9" height="12.396" viewBox="0 0 14.9 12.396"><g id="right-arrow" transform="translate(0 -41.346)"><g id="Grupo_672" data-name="Grupo 672" transform="translate(0 41.346)"><path id="Caminho_2858" data-name="Caminho 2858" d="M14.662,46.965,9.281,41.584a.818.818,0,0,0-1.153,0l-.489.489a.809.809,0,0,0-.238.577.825.825,0,0,0,.238.583l3.139,3.146H.8a.8.8,0,0,0-.8.8v.691a.826.826,0,0,0,.8.837H10.814L7.639,51.872a.808.808,0,0,0,0,1.145l.489.487a.818.818,0,0,0,1.153,0l5.381-5.381a.822.822,0,0,0,0-1.157Z" transform="translate(0 -41.346)"/></g></g></svg>
                                Saiba mais
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            <div class="dots"></div>
        </div>
        <a href="<?= $sistema->seo_pages['blog']['seo_url']; ?>" class="ver_todas">Ver todas</a>
    </div>
</section>
<?php endif; ?>

<?php include "_inc_footer.php";?>
</body>
</html>
