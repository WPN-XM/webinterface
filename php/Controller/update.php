<?php
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

function index()
{
    // fetch header date
    $url = 'https://raw2.github.com/WPN-XM/registry/master/wpnxm-software-registry.php';
    
    stream_context_set_default(
        array(
            'http' => array(
                'method' => 'HEAD'
            )
        )
    );
    
    $headers = get_headers($url);
    
    // parse header date
    $date_str = str_replace('Date: ', '', $headers[1]);
    $date = DateTime::createFromFormat('D, d M Y H:i:s O', $date_str);
    $last_modified = filemtime(WPNXM_DATA_DIR . 'wpnxm-software-registry.php');
    $update = $date->getTimestamp() >= $last_modified + (7 * 24 * 60 * 60); 

    if($update === true) {
       file_put_contents(WPNXM_DATA_DIR . 'wpnxm-software-registry.php', file_get_contents($url)); 
    }    
    
    $tpl_data = array(
        'load_jquery' => true,
        'components' => \Webinterface\Helper\Serverstack::getInstalledComponents(),
        'registry' => include WPNXM_DATA_DIR . 'wpnxm-software-registry.php',
        'windows_version' => \Webinterface\Helper\Serverstack::getWindowsVersion(),
        'bitsize' => \Webinterface\Helper\Serverstack::getBitSizeString() 
    );

    render('page-action', $tpl_data);
}
