<?php

/**
 * @copyright   Copyright (C) 2019-2024 Jeffrey Bostoen
 * @license     https://www.gnu.org/licenses/gpl-3.0.en.html
 * @version     2024-12-31 13:00:00
 * @see         https://www.itophub.io/wiki/page?id=latest%3Aadvancedtopics%3Arest_json
 */
 
namespace JeffreyBostoen\iTopRestService;
 
use Exception;
use stdClass;

/**
 * Class RestException. 
 * This is meant for error codes returned by a successful iTop API call; that may however return an iTop error code.
 * This error class adds more details and extra functionality; such as built-in JSON output.
 */
class RestException extends Exception {

    /**
     * @var array $oResponse The API response.
     */
    private $oResponse = [];
    
    /**
     * Construct method
     *
     * @param String $sMessage Short message that describes the error.
     * @param Integer $iCode Integer indicating an error. Defaults to 0.
     * @param Exception $oPreviousException Previous exception.
     * @param Array $aResponseFromAPI Hashtable containing more details. Details are optional. If they are set, it should be an iTop API REST-response.
     */
    public function __construct($sMessage, $iCode = 0, Exception $oPreviousException = null, stdClass $oResponse) {
        
        // make sure everything is assigned properly
        parent::__construct($sMessage, $iCode, $oPreviousException);
        
        $this->oResponse = $oResponse;

    }
    
    
    /**
     * Returns detailed error information.
     *
     * @return stdClass
     */
    public function GetResponse() {

        return $this->oResponse;

    }
    
    /**
     * Returns JSON-encoded detailed error information.
     *
     * @return string
     */
    public function GetResponseAsJSON() {

        return json_encode($this->oResponse);
        
    }

    /**
     * Generates an RestException from an iTop API REST/JSON response.
     *
     * @param stdClass $oResponse The API response.
     * @param Exception $oPreviousException
     * 
     * @return RestException
     */
    public static function FromResponse(stdClass $oResponse, $oPreviousException = null) : RestException {

        return new RestException($oResponse->message, $oResponse->code, $oPreviousException, $oResponse);

    }
    
}