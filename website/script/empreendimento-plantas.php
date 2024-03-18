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

if ($id == "") {
    exit();
} else {
    $fotos = $sistema->DB_fetch_array("SELECT * FROM hbrd_cms_empreendimentos_plantas_fotos A WHERE A.stats = 1 AND A.id_planta = $id ORDER BY A.ordem");
}
?>
<div class="carousel">
    <?php if ($fotos->num_rows) : ?>
        <?php $pswp = "pswp" . mt_rand(); ?>
        <?php $i = 0;
        foreach ($fotos->rows as $foto) :
            ?>
        <?php list($w, $h) = getimagesize("../../" . $sistema->getImageFileSized($foto['url'], 1920, 1080)); ?>
            <div class="item">
                <a href="<?php echo $sistema->getImageFileSized($foto['url'], 1920, 1080); ?>" class='plantas-imovel <?php echo $pswp; ?> imghover' title="<?php echo $foto['legenda']; ?>" data-index="<?php echo $i; ?>" data-width="<?php echo $w; ?>" data-height="<?php echo $h; ?>"><span><?php echo $foto['legenda']; ?></span><div class="imagem"><img src="<?php echo $sistema->getImageFileSized($foto['url'], 790, 496) ?>"></div></a>
            </div>
            <?php $i++;
        endforeach;
        ?>
<?php endif; ?>
</div>
<script>
    new photoswipe_init('.<?php echo $pswp; ?>');

    var owl2 = $('.plantas .carousel');


    if ($(window).width() >= 1180) {
        owl.owlCarousel({'loop': false, 'items': 5, 'margin': 15});
    } else {
        if ($(window).width() >= 768) {
            owl.owlCarousel({'loop': false, 'items': 3, 'margin': 15});
        } else {
            owl.owlCarousel({'loop': false, 'items': 1, 'margin': 15});
        }
    }

    owl2.owlCarousel({'loop': false, 'items': 1, 'dots': false});
</script>