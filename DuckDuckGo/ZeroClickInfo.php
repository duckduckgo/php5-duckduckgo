<?php

/**
 * @file DuckDuckGo/ZeroClickInfo.php
 * This file contains the ZeroClickInfo result class.
 */

namespace DuckDuckGo;

/**
 * The class holding the result of a ZeroClickInfo query.
 */
class ZeroClickInfo extends APIResult
{

    /* Constants */

    /**
     * 'Enumeration' for ZeroClickInfo result types.
     */
    const TYPE_NONE = 0;
    const TYPE_ARTICLE = 1;
    const TYPE_CATEGORY = 2;
    const TYPE_DISAMBIGUATION = 3;
    const TYPE_EXCLUSIVE = 4;
    const TYPE_NAME = 5;


    /* Mappings */

    /**
     * Mappings from the Type field of the result to the enumeration above.
     */
    protected static $resultTypeMapping = array(
        'A' => self::TYPE_ARTICLE,
        'C' => self::TYPE_CATEGORY,
        'D' => self::TYPE_DISAMBIGUATION,
        'E' => self::TYPE_EXCLUSIVE,
        'N' => self::TYPE_NAME,
    );

    /**
     * Direct API field --> member mappings.
     * @see APIResult
     */
    protected static $directMappings = array(
        'Abstract' => 'summary',
        'AbstractText' => 'textSummary',
        'AbstractSource' => 'summarySource',
        'AbstractURL' => 'summarySourceURL',
        'Image' => 'imageURL',
        'Heading' => 'heading',
        'Answer' => 'answer',
        'AnswerType' => 'answerType',
        'Definition' => 'definition',
        'DefinitionSource' => 'definitionSource',
        'DefinitionURL' => 'definitionSourceURL',
    );


    /* Query result fields. */

    /**
     * Title of the result.
     */
    public $heading;

    /**
     * Summary of the topic.
     */
    public $summary;
    /**
     * Plain-text summary of the topic. ]
     */
    public $textSummary;
    /**
     * Source of the summary.
     */
    public $summarySource;
    /**
     * URL of the summary source.
     */
    public $summarySourceURL;

    /**
     * Image URL to a possibly relevant image.
     */
    public $imageURL;

    /**
     * Instant answer for the query, if applicable.
     */
    public $answer;
    /**
     * Type of instant answer (calc, color, phone, pw, regexp...)
     */
    public $answerType;

    /**
     * Dictionary definition of query, if applicable.
     */
    public $definition;
    /**
     * Source of dictionary definition.
     */
    public $definitionSource;
    /**
     * Source URL of the dictionary definition.
     */
    public $definitionSourceURL;

    /**
     * An array containing the related topics.
     * The keys in the first level of the array are the category names of the topics,
     * the values are arrays containing all the topics under that category.
     * A topic is a simple stdClass object containing the following members:
     *  - description, a description of the related subject.
     *  - URL, a URL to the ZeroClickInfo of that subject.
     *  - iconURL, a URL to an icon for that subject, if applicable.
     */
    public $relatedTopics;

    /**
     * An array of external links (results). 
     * For the result format, see the topic format above.
     */
    public $results;

    /**
     * Response type, one of the TYPE_ constants in this class.
     * To test for a certain type, use for example $result->type == DuckDuckGo\\ZeroClickInfo::TYPE_ARTICLE
     */
    public $type;


    /* Constructors and miscellaneous functions. */

    /**
     * Constructor. Not for public use, only to be used by the API object.
     */
    public function __construct($data)
    {
        if(!$data) {
            throw new Exception('Unable to parse result data.');
        }

        /* Process direct mappings first. */
        foreach(self::$directMappings as $apiKey => $memberName) {
            if(isset($data[$apiKey])) {
                $this->$memberName = $data[$apiKey];
            } else {
                $this->$memberName = NULL;
            }
        }

        /* Now process special API fields. */

        /* The ZeroClickInfo type. */
        if(isset($data['Type']) && isset(self::$resultTypeMapping[$data['Type']])) {
            $this->type = self::$resultTypeMapping[$data['Type']];
        } else {
            $this->type = self::TYPE_NONE;
        }

        /* Related URLs. */
        if(isset($data['Results'])) {
            $this->results = array();
            foreach($data['Results'] as $apiResult) {
                $result = new \stdClass();
                $result->description = $apiResult['Text'];
                $result->URL = $apiResult['FirstURL'];
                $result->iconURL = $apiResult['Icon']['URL'];

                $this->results[] = $result;
            }
        }

        /* Related internal topics. */
        if(isset($data['RelatedTopics'])) {
            $this->relatedTopics = array();

            foreach($data['RelatedTopics'] as $subject) {
                if(isset($subject['Name'])) {
                    /* This is a seperate category */
                    $this->relatedTopics[$subject['Name']] = array();

                    foreach($subject['Topics'] as $apiRelatedTopic) {
                        $relatedTopic = new \stdClass();
                        $relatedTopic->description = $apiRelatedTopic['Text'];
                        $relatedTopic->URL = $apiRelatedTopic['FirstURL'];
                        $relatedTopic->iconURL = $apiRelatedTopic['Icon']['URL'];

                        $this->relatedTopics[$subject['Name']][] = $relatedTopic;
                    }
                } else {
                    /* This is a 'global' topic, put it in the general category. */
                    $relatedTopic = new \stdClass();
                    $relatedTopic->description = $subject['Text'];
                    $relatedTopic->URL = $subject['FirstURL'];
                    $relatedTopic->iconURL = $subject['Icon']['URL'];

                    if(!isset($this->relatedTopics['General'])) {
                        $this->relatedTopics['General'] = array();
                    }
                    $this->relatedTopics['General'][] = $relatedTopic;
                }
            }
        }
    }

}
