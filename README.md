### Webinterface of the WPN-XM Server Stack

| Branch  	| Build Status  	| Quality Score | Code Coverage |
|-----------|-----------------|---------------|---------------|
| [master](https://github.com/WPN-XM/webinterface/tree/master)	|  [![Build Status](https://travis-ci.org/WPN-XM/webinterface.svg)](https://travis-ci.org/WPN-XM/webinterface) 	|   [![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/WPN-XM/webinterface/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/WPN-XM/webinterface/?branch=master) | [![Code Coverage](https://scrutinizer-ci.com/g/WPN-XM/webinterface/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/WPN-XM/webinterface/?branch=master)
| [develop](https://github.com/WPN-XM/webinterface/tree/develop)	| [![Build Status](https://travis-ci.org/WPN-XM/webinterface.svg?branch=develop)](https://travis-ci.org/WPN-XM/webinterface)  	| [![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/WPN-XM/webinterface/badges/quality-score.png?b=develop)](https://scrutinizer-ci.com/g/WPN-XM/webinterface/?branch=develop)	| [![Code Coverage](https://scrutinizer-ci.com/g/WPN-XM/webinterface/badges/coverage.png?b=develop)](https://scrutinizer-ci.com/g/WPN-XM/webinterface/?branch=develop)

#### Usage

During the installation process of WPN-XM the webinterface is installed to `/server/www/tools/webinterface`.

By default the webinterface is served by Nginx, but it may also be served by the embedded PHP server.

##### Webinterface served by Nginx

You might reach the webinterface via the URL `http://localhost/tools/webinterface/index.php`

Hint: The webinterface will automatically open up in your browser, when the stack is started with `start-wpnxm.bat`.

##### Webinterface served by embedded PHP server

When using the embedded PHP server to serve the webinterface, it might be used as a control center application for the server stack.

To start it, launch the following command on CLI: `C:\server\bin\php\php -S localhost:90 -t C:\server\www`.

Or simply use `start-scp-server.bat`.

You might reach the webinterface via the URL `http://localhost:90/tools/webinterface/index.php`

#### Development

**Debug Page**

The webinterface has a debug screen showing Constants and their values.
It might be helpful, when working with Paths.

`http://localhost/tools/webinterface/index.php?page=debug`

##### Frontend Dependencies

The frontend dependencies are managed with [Bower](http://bower.io/)
and defined in the dependencies section of [`bower.json`](https://github.com/WPN-XM/webinterface/blob/master/bower.json#L28).

- Bootstrap     - https://github.com/twbs/bootstrap
- JQuery        - https://github.com/jquery/jquery
- Typeahead.js  - https://github.com/twitter/typeahead.js
- Pace          - https://github.com/HubSpot/pace
- MetisMenu     - https://github.com/onokumus/metisMenu
