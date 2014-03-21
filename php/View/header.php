﻿<?php
   /**
    * WPИ-XM Server Stack - Webinterface
    * Jens-André Koch © 2010 - onwards
    * http://wpn-xm.org/
    *
    *        _\|/_
    *        (o o)
    +-----oOO-{_}-OOo------------------------------------------------------------------+
    |                                                                                  |
    |    LICENSE                                                                       |
    |                                                                                  |
    |    WPИ-XM Server Stack is free software; you can redistribute it and/or modify   |
    |    it under the terms of the GNU General Public License as published by          |
    |    the Free Software Foundation; either version 2 of the License, or             |
    |    (at your option) any later version.                                           |
    |                                                                                  |
    |    WPИ-XM Server Stack is distributed in the hope that it will be useful,        |
    |    but WITHOUT ANY WARRANTY; without even the implied warranty of                |
    |    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the                 |
    |    GNU General Public License for more details.                                  |
    |                                                                                  |
    |    You should have received a copy of the GNU General Public License             |
    |    along with this program; if not, write to the Free Software                   |
    |    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA    |
    |                                                                                  |
    +----------------------------------------------------------------------------------+
    */
?>
<!DOCTYPE html>
<html lang="en" dir="ltr" xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta charset="utf-8" />
    <meta http-equiv="content-type" content="text/html; charset=utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
        
    <title>WPИ-XM Server Stack for Windows - <?php echo WPNXM_VERSION; ?></title>
    <meta name="description" content="WPИ-XM Server Stack for Windows - Webinterface.">
    <link rel="shortcut icon" href="favicon.ico" />
    
    <!-- jQuery -->
    <script type="text/javascript" src="/webinterface/assets/js/jquery-1.9.1.min.js"></script>
    <!-- Twitter's Bootstrap -->
    <script type="text/javascript" src="/webinterface/assets/js/bootstrap.min.js"></script>
    <link type="text/css" href="/webinterface/assets/css/bootstrap.min.css" rel="stylesheet" />
    
    <?php if (isset($load_jquery_additionals) && $load_jquery_additionals === true) { ?>
    <!-- jQuery Plugins -->
    <script type="text/javascript" src="/webinterface/assets/js/jquery.form.js"></script>
    <script type="text/javascript" src="/webinterface/assets/js/jquery.organicTabs.js"></script>
    <script type="text/javascript" src="/webinterface/assets/js/jquery.treeTable.js"></script>
    <script type="text/javascript" src="/webinterface/assets/js/jquery.jeditable.js"></script>
    <script type="text/javascript" src="/webinterface/assets/js/jquery.modal.js"></script>
    <link type="text/css" href="/webinterface/assets/css/jquery.treeTable.css" rel="stylesheet" />
    <?php } ?>
    
    <!-- WPN-XM stuff last in line, because using jQuery and overwriting CSS -->
    <link rel="stylesheet" type="text/css" href="/webinterface/assets/css/style.css"  media="screen, projection" />
    <script type="text/javascript" src="/webinterface/assets/js/wpnxm.js"></script>
    
</head>
<body>

<!--
    These CSS will come alive only, when Javascript is disabled.
    It's displaying a message for all the security nerds with disabled javascript.
    We need this reminder, because the WPN-XM configuration pages depend on jQuery and AJAX.
-->
<noscript><style type="text/css">
#page{ display:none; }
#javascript-off-errorbox { display:block; font-size:20px; color:red; }
</style></noscript>

<div class="container center">
    
    <h1 class="logo">
        <small style="position:relative; top:90px; left:150px;">Version <?php echo WPNXM_VERSION; ?></small>
    </h1>
   
    <?php
        Webinterface\Helper\Viewhelper::showMenu();
        Webinterface\Helper\Viewhelper::showWelcome();
    ?>

    <div id="javascript-off-errorbox">
      <div class="error">
      Please enable "javascript" in your browser in order to use this application.
      </div>
    </div>

    <div class="content-centered">
<!-- stop "header.php" -->
