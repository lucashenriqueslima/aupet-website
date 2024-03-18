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
    $fotos = $sistema->DB_fetch_array("SELECT * FROM hbrd_cms_empreendimentos_etapas_fotos A WHERE A.stats = 1 AND A.id_etapa = $id ORDER BY A.ordem");
}
if (!empty($_GET['id_empreendimento']))
    $fotos = $sistema->DB_fetch_array("SELECT * FROM hbrd_cms_empreendimentos_etapas_fotos A INNER JOIN hbrd_cms_empreendimentos_etapas B ON B.id = A.id_etapa WHERE A.stats = 1 AND B.stats = 1 AND B.id_empreendimento = {$_GET['id_empreendimento']} ORDER BY A.ordem");
?>

<?php if ($fotos->num_rows) : ?>
    <?php $pswp = "pswp" . mt_rand(); ?>
    <?php
    $i = 0;
    foreach ($fotos->rows as $foto) :
        ?>
        <?php list($w, $h) = getimagesize("../../" . $sistema->getImageFileSized($foto['url'], 1920, 1080)); ?>

        <a href="<?php echo $sistema->getImageFileSized($foto['url'], 1920, 1080); ?>" class='<?php echo $pswp; ?> imghover foto-galeria' title="<?php echo $foto['legenda']; ?>" style="background-image: url('<?php echo $sistema->getImageFileSized($foto['url'], 790, 496); ?>');" data-width="<?php echo $w; ?>" data-height="<?php echo $h; ?>"></a>
        <?php
        $i++;
    endforeach;
    ?>
<?php endif; ?>
<?php if ($i > 8) : ?>
<?php endif; ?>
<script>new photoswipe_init('.<?php echo $pswp; ?>');</script>