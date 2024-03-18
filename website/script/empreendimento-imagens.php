<?php
session_start();

if (!isset($_SESSION["seo_session"])) {
    $_SESSION["seo_session"] = uniqid();
}

require_once '../../main/System/Core/Loader.php';

require_once "../Classes/Sys.php";

use website\Classes\Sys;

$sistema = new Sys();

$id = "";
if (isset($_GET['id']) && $_GET['id'] != "")
    $id = $_GET['id'];

if ($id != "") {
    $fotos = $sistema->DB_fetch_array("SELECT * FROM hbrd_cms_empreendimentos_galerias_fotos A WHERE A.stats = 1 AND A.id_galeria = $id ORDER BY A.ordem");
}
if (!empty($_GET['id_empreendimento']))
    $fotos = $sistema->DB_fetch_array("SELECT A.* FROM hbrd_cms_empreendimentos_galerias_fotos A INNER JOIN hbrd_cms_empreendimentos_galerias B ON B.id = A.id_galeria WHERE A.stats = 1 AND B.stats = 1 AND B.id_empreendimento = {$_GET['id_empreendimento']} ORDER BY A.ordem");
?>

<div class="carousel">
    <?php if ($fotos->num_rows) : ?>
        <?php $pswp = "pswp" . mt_rand(); ?>
        <?php
        $i = 0;
        foreach ($fotos->rows as $foto) :
            ?>
            <?php list($w, $h) = getimagesize("../../" . $sistema->getImageFileSized($foto['url'], 1920, 1080)); ?>
            <?php if (($i % 2) == 0) : ?>
                <div class="item">
                <?php endif; ?>
                <a href="<?php echo $sistema->getImageFileSized($foto['url'], 1920, 1080) ?>" class='galeria-imovel <?php echo $pswp; ?> imghover' title="<?php echo $foto['legenda']; ?>" style="background-image: url('<?php echo $sistema->getImageFileSized($foto['url'], 790, 496) ?>');" data-index="<?php echo $i; ?>" data-width="<?php echo $w; ?>" data-height="<?php echo $h; ?>"><span><?php echo $foto['legenda']; ?></span></a>
                        <?php if (($i % 2) == 1) : ?>
                </div>
            <?php endif; ?>
            <?php
            $i++;
        endforeach;
        ?>
    <?php endif; ?>
</div>
<script>
    new photoswipe_init('.<?php echo $pswp; ?>');

    var owl = $('.galeria .carousel');

    if ($(window).width() >= 1180) {
        owl.owlCarousel({'loop': false, 'items': 5, 'margin': 15});
    } else {
        if ($(window).width() >= 768) {
            owl.owlCarousel({'loop': false, 'items': 3, 'margin': 15});
        } else {
            owl.owlCarousel({'loop': false, 'items': 1, 'margin': 15});
        }
    }
</script>