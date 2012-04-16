<?php

/**
 * @file DuckDuckGo/API.php
 * This file provides the base class to interface with the DuckDuckGo API.
 * It will also include any necessary classes.
 * With this interface, you can currently only perform ZeroClickInfo queries.
 * Simple example:
 *     $api = new DuckDuckGo\API();
 *     $info = $api->zeroClickQuery('Internet Relay Chat');
 *     echo $info->definition;
 */

namespace DuckDuckGo;

/* Include the necessary classes. */
require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'APIResult.php';
require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'ZeroClickInfo.php';

class API
{

    /* General API options and flags. */

    /**
     * The API base URL. This defaults to api.duckduckgo.com.
     */
    public $baseURL;
    /**
     * Whether to use HTTPS or not. This defaults to FALSE (no HTTPS).
     */
    public $secure;
    /**
     * Whether to disallow HTML in the result. This defaults to FALSE (don't disallow HTML).
     */
    public $noHTML;

    /**
     * Query-specific flags
     */

    /**
     * Whether or not to return Disambiguation (D) results (ZeroClickInfo).
     * Defaults to FALSE (allow disambiguation results).
     */
    public $noDisambiguations;


    /* Constructors and internal functions. */

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->secure = FALSE;
        $this->noHTML = FALSE;
        $this->noDisambiguations = FALSE;
        $this->baseURL = 'api.duckduckgo.com';
    }

    /**
     * Construct an API URL, given a section and an associative options array.
     * @param section   Which part of the API to call.
     * @param options   An associative array containing options to pass in key => value form.
     * @return          The API URL that corresponds with this query, which then can be retrieved in order to get the query results.
     */
    protected function constructURL($section, $options)
    {
        $url = '';
        if($this->secure) {
            $url .= 'https://' . $this->baseURL;
        } else {
            $url .= 'http://' . $this->baseURL;
        }
        $url .= $section;

        if(count($options) > 0) {
            $url .= '?' . \urlencode(\current(\array_keys($options))) . '=' . \urlencode(\array_shift($options));

            foreach($options as $name => $value) {
                $url .= '&' . \urlencode($name) . '=' . \urlencode($value);
            }
        }

        return $url;
    }

    /**
     * Given an API section and query options, queries the API and returns the raw results.
     * @param section   Which part of the API to call.
     * @param options   An associative array containing options to pass in key => value form.
     * @return          The raw results of the API call.
     */
    protected function getAPIResult($section, $options)
    {
        $url = $this->constructURL($section, $options);

        if(\extension_loaded('curl')) {
            $curl = \curl_init($url);
            \curl_setopt($curl, CURLOPT_HEADER, FALSE);
            \curl_setopt($curl, CURLOPT_FOLLOWLOCATION, FALSE);
            \curl_setopt($curl, CURLOPT_FRESH_CONNECT, TRUE);
            \curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);

            $result = \curl_exec($curl);
            \curl_close($curl);

            return $result;
        }

        if(\function_exists('http_get') && \function_exists('http_parse_message')) {
            $options = array(
                'redirect' => 0,
            );

            return \http_parse_message(\http_get($url), $options)->body;
        }

        if(\ini_get('allow_url_fopen')) {
            $context = \stream_context_create(array(
                'http' => array(
                    'follow_location' => 0,
                )
            ));

            $handle = \fopen($url, 'r', FALSE, $context);
            $result = \stream_get_contents($handle);
            \fclose($handle);

            return $result;
        }

        throw new Exception('Could not find suitable method to retrieve API result. Either install the cURL or pear_http extension, or set allow_url_fopen to 1.');
    }


    /* API functions. */

    /**
     * Perform a ZeroClickInfo query against the DuckDuckGo API.
     * @param query     The term to query for.
     * @return          A ZeroClickInfo object containing the results of the query.
     * @see ZeroClickInfo
     */
    public function zeroClickQuery($query)
    {
        $result = $this->getAPIResult('/', array(
            'q' => $query,
            'format' => 'json',
            'no_html' => ($this->noHTML ? 1 : 0),
            'no_redirect' => 1,
            'skip_disambig' => ($this->noDisambiguations ? 1 : 0),
        ));

        if(!$result) {
            throw new Exception('Could not retrieve API result.');
        }

        return new ZeroClickInfo(\json_decode($result, TRUE));
    }

}
