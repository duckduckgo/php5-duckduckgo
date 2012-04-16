The DuckDuckGo PHP5 API
=======================

Notes
-----
- This API requires PHP *v5.3* or higher, due to the use of namespaces.
  A namespaceless version will be available later.
- This API is still *experimental* - please report any and all bugs using the ticket system.

Usage
-----
To use the DuckDuckGo PHP5 API in your application, include DuckDuckGo/API.php where you want
to use the API, create a new instance of the DuckDuckGo\API class, and use it to improve the world!

Example
-------
    <?php
    require_once('DuckDuckGo/API.php');
    $api = new DuckDuckGo\API();
    $api->secure = TRUE;
    $ircInfo = $api->zeroClickQuery('Internet Relay Chat');
    echo $ircInfo->definition;

Documentation
-------------
Due the use of JavaDoc, documentation can easily be generated from the files using e.g. Doxygen.
A pre-generated version of the documentation will be supplied in the near future.

Comments?
---------
Hit me up a message, or file a ticket! :)
For quicker response, try catching me on IRC on `#duckduckgo @ irc.freenode.net`.
