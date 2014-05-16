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

namespace Webinterface\Components;

/**
 * WPN-XM Webinterface - Class for MongoDb
 */
class MongoDb extends AbstractComponent
{
    public $name = 'MongoDb';

    public $registryName = 'mongodb';

    public $installationFolder = '\bin\mongodb';

    public $files = array(
        '\bin\mongodb\mongodb.conf',
        '\bin\mongodb\bin\mongod.exe'
    );

    public $configFile = '\bin\mongodb\mongodb.conf';

    public function getVersion()
    {
        if($this->isInstalled() === false) {
            return 'not installed';
        }
        
        if (!extension_loaded('mongo')) {
            return \Webinterface\Helper\Serverstack::printExclamationMark(
                'The PHP Extension "Mongo" is required.'
            );
        }

        try {
            $m = new \Mongo();

            //require admin privilege
            $db = $m->admin;

            //$mongodb_info = $db->command(array('buildinfo'=>true));
            //$mongodb_version = $mongodb_info['version'];

            // this returns an array with the keys "retval","ok"
            $mongodb_version = $db->execute('return db.version()');

        } catch (\MongoConnectionException $e) {
            return \Webinterface\Helper\Serverstack::printExclamationMark(
                    $e->getMessage() . '. Please wake the daemon.'
            );
        }

        return $mongodb_version['retval'];
    }

    public function getPassword()
    {
        $ini = new \Webinterface\Helper\INIReaderWriter(WPNXM_INI);

        return $ini->get('MongoDB', 'password');
    }
    
    public function setPassword($password)
    {
        return 'Not implemented, yet.';
    }
}
