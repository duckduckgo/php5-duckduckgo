<?php

/**
 * @file DuckDuckGo/APIResult.php
 * This file provides the base class for API results.
 * All API result classes must extend from this class.
 */

namespace DuckDuckGo;

/**
 * Abstract class where API result classes are based on.
 */
abstract class APIResult
{

    /**
     * Direct API field --> member mappings.
     * These mappings allow to directly map API keys to members of the result class.
     */
    protected static $directMappings = array();

}
