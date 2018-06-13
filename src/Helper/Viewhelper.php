<?php
/**
 * WPИ-XM Server Stack
 * Copyright (c) Jens-André Koch <jakoch@web.de>
 * https://wpn-xm.org/
 *
 * Licensed under the MIT License.
 * See the bundled LICENSE file for copyright and license information.
 */

namespace WPNXM\Webinterface\Helper;

class Viewhelper
{
    public static function showMenu()
    {
        // fetch HTML fragment for the tools topmenu
        $tools_list_html = file_get_contents(WPNXM_DATA_DIR.'tools-topmenu.html');

        //$updateLink = '<li class="divider"></li><li><a href="'.WPNXM_WEBINTERFACE_ROOT.'index.php?page=updater">Update</a></li>';

        $menu = '<div class="main_menu navbar">
                 <ul class="nav">
                    <li class="first"><a href="'.WPNXM_WEBINTERFACE_ROOT.'index.php?page=overview">Overview</a></li>
                    <li><a href="'.WPNXM_WEBINTERFACE_ROOT.'index.php?page=config">Configuration</a></li>
                    <li><a class="active" href="'.WPNXM_WEBINTERFACE_ROOT.'index.php?page=projects">Projects & Tools</a></li>
                    <li class="dropdown">
                        <a class="dropdown-toggle" data-toggle="dropdown" href="#">Tools <b class="caret"></b></a>
                        <ul class="dropdown-menu">'.
                             $tools_list_html
                             /*. $updateLink
                             . '<li><a href="#">Filter2</a></li>
                             <li class="divider"></li>
                             <li class="nav-header">Nav header</li>
                             <li><a href="#">Filter1</a></li>
                             <li><a href="#">Filter2</a></li>'*/
                        .'</ul>
                    </li>
                    <li class="last"><a href="'.WPNXM_WEBINTERFACE_ROOT.'index.php?page=phpinfo">PHP Info</a></li>
                </ul>
             </div>';

        echo $menu;
    }

    public static function showWelcome()
    {
        if (self::fileCounter(WPNXM_DATA_DIR.'/welcomeMsgCounter.txt', 3) === true) {
            return;
        }

        echo '<div class="row">
                <div class="col-md-7 content-centered">
                  <div class="panel panel-default">
                    <div class="panel-heading panel-heading-gray">
                        <span class="pull-left" style="font-size: 30px">
                            <i class="empty heart icon" style="padding-top:14px"></i>
                        </span>
                        <h4>Welcome to the WPИ-XM Server Stack!</h4>
                    </div>
                    <div class="panel-body panel-body-gray">
                        <p>Congratulations! You have successfully installed WPИ-XM on this system.</p>
                    </div>
                  </div>
                </div>
             </div>';
    }

    /**
     * Uses a file for counting the display times of the welcome message.
     *
     * @param  string $file       The file containing the counter.
     * @param  int    $max_counts The number of times to return false.
     * @return bool   When the number of max_displays is reached, method will return true; else false;
     */
    public static function fileCounter($file, $max_counts)
    {
        $max_counts = (int) $max_counts;

        // if file not existing, create and start counting with 1
        if (is_file($file) === false) {
            file_put_contents($file, 1);
        } else {
            // read, comparison, incr
            $current = file_get_contents($file);

            if ((int) $current === (int) $max_counts) {
                return true;
            }

            if ($current < $max_counts) {
                $current++;
                file_put_contents($file, $current);
            }
        }

        return false;
    }
}
