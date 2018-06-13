<?php
/**
 * WPИ-XM Server Stack
 * Copyright (c) Jens-André Koch <jakoch@web.de>
 * https://wpn-xm.org/
 *
 * Licensed under the MIT License.
 * See the bundled LICENSE file for copyright and license information.
 */

class ErrorHandler
{
	public static function init()
	{
		set_error_handler('ErrorHandler::handleError', E_ALL | E_STRICT);
        set_exception_handler('ErrorHandler::handleException');
	}

	public static function handleException($e) /** Throwable **/
	{
	    $html = '<div class="centered" style="font-size: 16px;">';
	    $html .= '<div class="panel panel-red">';
	    $html .= '  <div class="panel-heading">';
	    $html .= '    <h3 class="panel-title">Error</h3>';
	    $html .= '  </div>';
	    $html .= '  <div class="panel-body">';
	    $html .= '    <b>'.$e->getMessage().'</b>';
	    $html .= '    <p><pre>'.$e->getTraceAsString().'</pre></p>';
	    $html .= '  </div>';
	    $html .= '</div>';
	    $html .= '</div>';

	    echo $html;
	}

	/**
	 * Convert Errors to ErrorException.
	 */
	public static function handleError($errno, $errstr, $errfile, $errline, $errcontext)
	{
	    // error was suppressed with the @-operator
	    if (0 === error_reporting()) {
	        return false;
	    }

	    throw new ErrorException($errstr, 0, $errno, $errfile, $errline);
	}
}