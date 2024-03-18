<?php
if (isset($currentpage)) {

    if ($sistema->detect->isTablet()) {
        $dispositivo = 2;
    } else if ($sistema->detect->isMobile()) {
        $dispositivo = 3;
    } else {
        $dispositivo = 1;
    }

    function contemBot($browser = null, $ip = null) {
        global $sistema;
        $bots = $sistema->getBots();

        if ($browser != null && $ip != null) {
            $existe = false;
            foreach ($bots as $bot) {
                if (stristr($browser, $bot) || $ip == $bot) {
                    $existe = true;
                }
            }
        } else {
            $existe = true;
        }
        return $existe;
    }

    if (isset($_SERVER['HTTP_REFERER'])) {
        if (!contemBot($_SERVER['HTTP_USER_AGENT'], $_SERVER['REMOTE_ADDR'])) {
            $query = $sistema->DB_insertData("hbrd_cms_seo_acessos", "date,ip,session,browser,origem,utm_source,utm_medium,utm_campaign,utm_content,utm_term,dispositivo", "NOW(),'" . $_SERVER['REMOTE_ADDR'] . "','" . $_SESSION["seo_session"] . "','" . $_SERVER['HTTP_USER_AGENT'] . "','" . $_SERVER['HTTP_REFERER'] . "','" . $utm_source . "','" . $utm_medium . "','" . $utm_campaign . "','" . $utm_content . "','" . $utm_term . "','" . $dispositivo . "'");
            $sistema->DB_insertData("hbrd_cms_seo_acessos_historico", "date,ip,session,browser,origem,utm_source,utm_medium,utm_campaign,utm_content,utm_term,dispositivo", "NOW(),'" . $_SERVER['REMOTE_ADDR'] . "','" . $_SESSION["seo_session"] . "','" . $_SERVER['HTTP_USER_AGENT'] . "','" . $_SERVER['HTTP_REFERER'] . "','" . $utm_source . "','" . $utm_medium . "','" . $utm_campaign . "','" . $utm_content . "','" . $utm_term . "','" . $dispositivo . "'");
        }
    } else {
        if (!contemBot($_SERVER['HTTP_USER_AGENT'], $_SERVER['REMOTE_ADDR'])) {
            $query = $sistema->DB_insertData("hbrd_cms_seo_acessos", "date,ip,session,browser,utm_source,utm_medium,utm_campaign,utm_content,utm_term,dispositivo", "NOW(),'" . $_SERVER['REMOTE_ADDR'] . "','" . $_SESSION["seo_session"] . "','" . $_SERVER['HTTP_USER_AGENT'] . "','" . $utm_source . "','" . $utm_medium . "','" . $utm_campaign . "','" . $utm_content . "','" . $utm_term . "','" . $dispositivo . "'");
            $sistema->DB_insertData("hbrd_cms_seo_acessos_historico", "date,ip,session,browser,utm_source,utm_medium,utm_campaign,utm_content,utm_term,dispositivo", "NOW(),'" . $_SERVER['REMOTE_ADDR'] . "','" . $_SESSION["seo_session"] . "','" . $_SERVER['HTTP_USER_AGENT'] . "','" . $utm_source . "','" . $utm_medium . "','" . $utm_campaign . "','" . $utm_content . "','" . $utm_term . "','" . $dispositivo . "'");
        }
    }

    if (!isset($_SESSION['localidade_analytics'])) :
        ?>
        <script>
            var idSeo = "<?php echo $query->insert_id; ?>";
            var ip = "<?php echo $_SERVER['REMOTE_ADDR']; ?>";
            var session = "<?php echo $_SESSION['seo_session']; ?>";
        </script>
        <?php
    endif;
}
?>
