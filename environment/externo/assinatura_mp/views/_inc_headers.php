<?php $path = ""; ?>

<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>

<base href="<?php echo $path; ?>" />

<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>

<meta name="revisit-after" content="5" />
<meta name="description" content="<?php "Olá ".$this->tutor['nome'].", Este é o seu link para pagamento da assinatura AupetHeinsten" ?>" />
<meta property="og:title" content="Proposta de filiação">
<!-- <title>Title</title> -->
<meta id="viewport" name="viewport" content="width=device-width, initial-scale=1, user-scalable=yes">

<!-- CSS -->
<link href="/environment/externo/assinatura_mp/css/principal.css" rel="stylesheet" />

<!-- BOOTSTRAP 4 -->
<link rel="stylesheet" type="text/css" href="/environment/externo/assinatura_mp/plugins/bootstrap/bootstrap.min.css">
<link href="/main/templates/supradmin/css/plugins.css" rel="stylesheet" />
<link href="/main/templates/supradmin/css/icons.css" rel="stylesheet" />

<script type="text/javascript">
	var pagina = "<?php echo $pagina; ?>";
	var path = "<?php echo $path;?>";
</script>