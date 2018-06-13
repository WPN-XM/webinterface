<?php
/**
 * WPИ-XM Server Stack
 * Copyright (c) Jens-André Koch <jakoch@web.de>
 * https://wpn-xm.org/
 *
 * Licensed under the MIT License.
 * See the bundled LICENSE file for copyright and license information.
 */
?>
<!DOCTYPE html>
<html lang="en" dir="ltr" xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta charset="utf-8" />
    <meta http-equiv="content-type" content="text/html; charset=utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>WPИ-XM Server Stack for Windows - <?php echo WPNXM_STACK_VERSION; ?></title>
    <meta name="description" content="WPИ-XM Server Stack for Windows - Webinterface.">
    <meta name="author" content="Jens-André Koch" />
    <link rel="shortcut icon" href="favicon.ico" />

    <!-- Pace.js -->
    <script data-pace-options='{ "ajax": true, "document": false, "eventLag": false }'
            src="<?=WPNXM_ASSETS?>js/pace/pace.min.js"></script>
    <link type="text/css" href="<?=WPNXM_ASSETS?>css/pace.css" rel="stylesheet" />
    <!-- jQuery -->
    <script type="text/javascript" src="<?=WPNXM_ASSETS?>js/jquery.min.js"></script>
    <!-- Twitter's Bootstrap -->
    <script type="text/javascript" src="<?=WPNXM_ASSETS?>js/bootstrap.min.js"></script>
    <link type="text/css" href="<?=WPNXM_ASSETS?>css/bootstrap.min.css" rel="stylesheet" />
    <!-- Twitter Typeahead & Bloodhound -->
    <script  type="text/javascript" src="<?=WPNXM_ASSETS?>js/typeahead.bundle.min.js"></script>
    <?php if (isset($load_jquery_additionals) && $load_jquery_additionals === true) { ?>
    <!-- jQuery Plugins -->
    <script type="text/javascript" src="<?=WPNXM_ASSETS?>js/jquery.treeTable.js"></script>
    <script type="text/javascript" src="<?=WPNXM_ASSETS?>js/jquery.jeditable.js"></script>
    <link type="text/css" href="<?=WPNXM_ASSETS?>css/jquery.treeTable.css" rel="stylesheet" />
    <?php } ?>
    <!-- WPN-XM stuff last in line, because using jQuery and overwriting CSS -->
    <link type="text/css" href="<?=WPNXM_ASSETS?>css/style.css" rel="stylesheet" />
    <script type="text/javascript" src="<?=WPNXM_ASSETS?>js/wpnxm.js"></script>
    <!-- Semantic UI Icon -->
    <link type="text/css" href="<?=WPNXM_ASSETS?>css/icon.min.css" rel="stylesheet" />
</head>
<body>

<noscript><!--
The CSS will come alive only, when Javascript is disabled.
It's displaying a message for all the security nerds with disabled javascript.
We need this reminder, because the WPN-XM configuration pages depend on jQuery and AJAX.
--><style type="text/css">
#page{ display:none; }
#javascript-off-errorbox { display:block; font-size:20px; color:red; }
</style></noscript>

<div class="container center">

    <h1 class="logo">
        <small style="position:relative; top:90px; left:150px;">Version <?php echo WPNXM_STACK_VERSION; ?></small>
    </h1>

    <?php
        WPNXM\Webinterface\Helper\Viewhelper::showMenu();
        WPNXM\Webinterface\Helper\Viewhelper::showWelcome();
    ?>

    <div class="alert alert-danger fade in" id="javascript-off-errorbox">
        <a href="#" class="close" data-dismiss="alert">&times;</a>
        <strong>Error!</strong> Please enable "javascript" in your browser in order to use this application.
    </div>

    <div class="col-md-12 content-centered">
<!-- stop "header.php" -->
