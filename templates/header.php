<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <meta name="docsearch:language" content="fr">
    <meta name="docsearch:version" content="1.0">

    <title><?php echo $lang["title"]; ?></title>

    <script src="<?php echo $config["curenttheme"];?>/js/jquery.js"></script>
    <script src="<?php echo $config["curenttheme"];?>/js/ezrecorder.js"></script>
    <script src="<?php echo $config["curenttheme"];?>/js/player/hls.js"></script>

    <link rel="stylesheet" href="<?php echo $config["curenttheme"];?>/css/bootstrap.min.css">
    <link rel="stylesheet" href="<?php echo $config["curenttheme"];?>/css/fontawesome/css/all.min.css">
    <link rel="stylesheet" href="<?php echo $config["curenttheme"];?>/css/ezrecorder.css">

</head>
<body>
    <div class="container">
        <div class="header">
            <div class="float-left">
                <div class="logo">
                    <img src="<?php echo $config["curenttheme"];?>/img/logo-ulb.png"/>
                </div>
                <div class="logo">
                    <a href="index.php"><span class="ez">EZ</span><span class="recorderword">recorder</span></a>
                </div>
            </div>
            <div class="float-right">
                <a href="?action=help" target="_blank"><span class="btn btn-secondary big_help_btn"><i class="fas fa-info-circle"></i> <?php echo $lang["need_help"]; ?></span></a>
                <a href="?action=help" target="_blank"><span class="btn btn-secondary small_help_btn"><i class="fas fa-info-circle"></i></span></a>
            </div>
            <div class="clearfix"></div>
        </div>