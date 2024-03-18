<?php 
$path = '//' . $_SERVER['HTTP_HOST'] . dirname(dirname(dirname($_SERVER['PHP_SELF']))) . '/';
$protocol = "http://";
if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') {
    $protocol = "https://";
}
 ?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "//www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="//www.w3.org/1999/xhtml">
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <base href="<?php echo $path; ?>" />
        <title>Supr admin</title>
        <!-- Mobile Specific Metas -->
        <meta name="viewport" content="width=device-width, initial-scale=1.0" />
        <!-- Force IE9 to render in normla mode -->
        <meta http-equiv="X-UA-Compatible" content="IE=edge" />

        <!-- Le styles -->
        <!-- Use new way for google web fonts 
        //www.smashingmagazine.com/2012/07/11/avoiding-faux-weights-styles-google-web-fonts -->
        <link href='//fonts.googleapis.com/css?family=Open+Sans:400,700' rel='stylesheet' type='text/css' /> <!-- Headings -->
        <link href='//fonts.googleapis.com/css?family=Droid+Sans:400,700' rel='stylesheet' type='text/css' /> <!-- Text -->
        <!--[if lt IE 9]>
        <link href="//fonts.googleapis.com/css?family=Open+Sans:400" rel="stylesheet" type="text/css" />
        <link href="//fonts.googleapis.com/css?family=Open+Sans:700" rel="stylesheet" type="text/css" />
        <link href="//fonts.googleapis.com/css?family=Droid+Sans:400" rel="stylesheet" type="text/css" />
        <link href="//fonts.googleapis.com/css?family=Droid+Sans:700" rel="stylesheet" type="text/css" />
        <![endif]-->

        <!-- Core stylesheets do not remove -->
        <link href="templates/supradmin/css/bootstrap.css" rel="stylesheet" />
        <!-- Plugins stylesheets (all plugin custom css) -->
        <link href="templates/supradmin/css/plugins.css" rel="stylesheet" />
        <link href="templates/supradmin/css/supr-theme/jquery.ui.supr.css" rel="stylesheet" type="text/css"/>
        <link href="templates/supradmin/css/icons.css" rel="stylesheet" type="text/css" />


        <link href="templates/supradmin/plugins/files/elfinder/elfinder.css" type="text/css" rel="stylesheet" />
        <link href="templates/supradmin/plugins/files/plupload/jquery.ui.plupload/css/jquery.ui.plupload.css" type="text/css" rel="stylesheet" />

        <!-- Main stylesheets -->
        <link href="templates/supradmin/css/main.css" rel="stylesheet" type="text/css" /> 

        <!-- Custom stylesheets ( Put your own changes here ) -->
        <link href="templates/supradmin/css/custom.css" rel="stylesheet" type="text/css" />  

        <!--[if IE 8]><link href="css/ie8.css" rel="stylesheet" type="text/css" /><![endif]-->

        <!-- Le HTML5 shim, for IE6-8 support of HTML5 elements -->
        <!--[if lt IE 9]>
          <script type="text/javascript" src="templates/supradmin/js/libs/excanvas.min.js"></script>
          <script type="text/javascript" src="//html5shim.googlecode.com/svn/trunk/html5.js"></script>
          <script type="text/javascript" src="templates/supradmin/js/libs/respond.min.js"></script>
        <![endif]-->

        <!-- Le fav and touch icons -->
        <link rel="shortcut icon" href="templates/supradmin/images/favicon.ico" />
        <link rel="apple-touch-icon-precomposed" sizes="144x144" href="templates/supradmin/images/apple-touch-icon-144-precomposed.png" />
        <link rel="apple-touch-icon-precomposed" sizes="114x114" href="templates/supradmin/images/apple-touch-icon-114-precomposed.png" />
        <link rel="apple-touch-icon-precomposed" sizes="72x72" href="templates/supradmin/images/apple-touch-icon-72-precomposed.png" />
        <link rel="apple-touch-icon-precomposed" href="templates/supradmin/images/apple-touch-icon-57-precomposed.png" />

        <!-- Windows8 touch icon ( //www.buildmypinnedsite.com/ )-->
        <meta name="application-name" content="Supr"/> 
        <meta name="msapplication-TileColor" content="#3399cc"/> 

        <!-- Load modernizr first -->
        <script type="text/javascript" src="templates/supradmin/js/libs/modernizr.js"></script>

    </head>

    <body>

        <div class="row">
            <div class="col-lg-12">
                <div class="panel panel-default toggle">
                    <div class="panel-body noPad">
                        <div id="elfinder"></div>
                        <div id="html4_uploader"></div>
                    </div>
                </div>
            </div>
        </div>


        <script type="text/javascript" src="//ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.min.js"></script>
        <script type="text/javascript" src="//ajax.googleapis.com/ajax/libs/jqueryui/1.10.2/jquery-ui.min.js"></script>
        <script type="text/javascript" src="//code.jquery.com/jquery-migrate-1.2.1.min.js"></script>

        <script type="text/javascript" src="templates/supradmin/plugins/files/elfinder/elfinder.min.js"></script>
        <script type="text/javascript" src="templates/supradmin/plugins/files/plupload/plupload.js"></script>
        <script type="text/javascript" src="templates/supradmin/plugins/files/plupload/plupload.html4.js"></script>
        <script type="text/javascript" src="templates/supradmin/plugins/files/plupload/plupload.flash.js"></script>
        <script type="text/javascript" src="templates/supradmin/plugins/files/plupload/jquery.plupload.queue/jquery.plupload.queue.js"></script>

        <script type="text/javascript">
            var root_path = "<?php echo $path; ?>";

            var FileBrowserDialogue = {
                init: function () {
                    window.parent.tinymce.activeEditor.windowManager.setParams({});
                },
                mySubmit: function (URL) {
                    window.parent.tinymce.activeEditor.windowManager.getParams().setUrl(URL);
                    window.parent.tinymce.activeEditor.windowManager.close();
                }
            }

            $().ready(function () {
                var elf = $('#elfinder').elfinder({
                    url: 'System/includes/connector.php',
                    getFileCallback: function (file) {
                        arquivo = '<?= $protocol. $_SERVER['HTTP_HOST'] ?>'+file.url
                        FileBrowserDialogue.mySubmit(arquivo);
                    }
                }).elfinder('instance');
            });
        </script>
    </body>
</html>

