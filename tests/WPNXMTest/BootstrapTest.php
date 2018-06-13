<?php

/**
 * WPИ-XM Server Stack
 * Copyright (c) Jens-André Koch <jakoch@web.de>
 * https://wpn-xm.org/
 *
 * Licensed under the MIT License.
 * See the bundled LICENSE file for copyright and license information.
 */

namespace WPNXM\Webinterface\Test;

class BootstrapTest extends \PHPUnit_Framework_TestCase
{
    public function testSetup()
    {
        /**
         * ensure include path is set
         */
        $includePath = get_include_path();
        $this->assertContains(realpath(__DIR__.'/../..'), $includePath);
        $this->assertContains(realpath(__DIR__.'/../../tests'), $includePath);
    }
}
