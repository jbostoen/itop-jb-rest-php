<?php

/**
 * @copyright   Copyright (C) 2019-2025 Jeffrey Bostoen
 * @license     https://www.gnu.org/licenses/gpl-3.0.en.html
 * @version     2025-01-1§ 16:03:00
 * @see         https://www.itophub.io/wiki/page?id=latest%3Aadvancedtopics%3Arest_json
 */
 
namespace JeffreyBostoen\iTopRestService;
 
use Exception;
use stdClass;

/**
 * Class iTopRestService. 
 * A class to communicate with iTop API more efficiently in PHP implementations.
 */
class Service {
	
	/**
	* @var string $sUserDisplayName The name that is used by default in REST comments.
	*/
	private $sUserDisplayName = 'iTop REST';		
	
	/**
	 * @var string $sPassword The password of the iTop user to authenticate with. has the REST User Profile (in iTop)
	 */
	private $sPassword = 'password';
	
	/**
	 * @var string|null $sTraceLogFileName For debugging purposes. Outputs the network request and response info sent to iTop REST/JSON to this filename.
	 */
	private $sTraceLogFileName = null;
	
	/**
	 * @var bool $bSkipCertificateCheck For development purposes. Skips SSL/TLS checks.
	 */
	private $bSkipCertificateCheck = false;
	
	/**
	 * @var bool $bSupportBasicAuth When enabled, this service will authenticate using Basic Auth.
	 */
	private $bSupportBasicAuth = true;

	/** 
	 * @var string $sURL URL of the iTop web services, including version.  
	 * Example: http://localhost/itop/web/webservices/rest.php
	 * 
	 * @details If left blank, an attempt to derive this info will happen in __construct()
	 */
	private $sUrl = '';
	
	/**
	 *@var string $sUserLogin The user account that will be used to authenticate ("auth_user").  
	 * This user needs to have the REST User Profile (in iTop). 
	 * Note: iTop REST/JSON error messages might be returned in the language set for the specified user account.
	 */
	private $sUserLogin = 'admin';


	/** @var string $sOutputFields The default output fields.
	 * Best practice: Per request, specify only the fields that are needed.
	 * 
	 * - "@*" means all the attributes of the queried class.
     * - "*+" (since 2.0.3) means all the attributes of each object found (subclasses may have more attributes than the queried class).
	 * 
	 * This class defaults to '*', as it is unknown what class will be queried.
	 */
	private $sOutputFields = '*';
	
	/**
	 * @var String $sVersion describing the REST API version. 
	 * @See https://www.itophub.io/wiki/page?id=latest:advancedtopics:rest_json#changes_history
	 */
	private $sVersion = '1.3';

	/**
	 * Whether or not to enable a trace log.
	 * 
	 * The trace log can be useful for debugging.
	 *
	 * @param string $sFileName The file name for the trace log.
	 * 
	 * @return Service This service.
	 */
	public function SetTraceLogFileName(?string $sFileName = null) : Service {

		$this->sTraceLogFileName = $sFileName;
		return $this;

	}

	/**
	 * Whether or not to skip certificate validation of the REST/JSON API endpoint.
	 *
	 * @param boolean $bValue
	 * 
	 * @return Service This service.
	 */
	public function SetSkipCertificateCheck(bool $bValue) : Service {

		$this->bSkipCertificateCheck = $bValue;
		return $this;

	}

	/**
	 * Sets the user display name.
	 *
	 * @param string $sUserDisplayName
	 * 
	 * @return Service This service.
	 * 
	 * @throws Exception
	 */
	public function SetUserDisplayName(string $sUserDisplayName = 'iTop REST') : Service {

		if($sUserDisplayName == '') {
			throw new Exception('The user display name can not be empty.');
		}

		$this->sUserDisplayName = $sUserDisplayName;
		return $this;

	}

	/**
	 * Sets the user login name.
	 *
	 * @param string $sUserLogin
	 * 
	 * @return Service This service.
	 */
	public function SetUserLogin(string $sUserLogin) : Service {

		$this->sUserLogin = $sUserLogin;
		return $this;

	}


	/**
	 * Sets the user password.
	 *
	 * @param param $sPassword
	 * 
	 * @return Service This service.
	 */
	public function SetPassword(string $sPassword) : Service {

		$this->sPassword = $sPassword;
		return $this;

	}


	/**
	 * Whether or not to support Basic Authentication.
	 *
	 * @param bool $bValue
	 * 
	 * @return Service This service.
	 */
	public function SetSupportBasicAuth(bool $bValue) : Service {

		$this->bSupportBasicAuth = $bValue;
		return $this;

	}

	/**
	 * Sets the API URL to use.
	 *
	 * @param string $sUrl
	 * 
	 * @return Service This service.
	 */
	public function SetUrl(string $sUrl) : Service {

		$this->sUrl = $sUrl;
		return $this;

	}

	/**
	 * Sets the API version to use.
	 *
	 * @param string $sVersion
	 * 
	 * @return Service This service.
	 */
	public function SetVersion(string $sVersion) : Service {

		$this->sVersion = $sVersion;
		return $this;

	}

	/**
	 * Sets the default output fields.
	 * If not explicitly specified in the parameters of a method, this default value will be used.
	 * 
	 * Best practice: Per request, specify only the fields that are needed.
	 * 
	 * - "@*" means all the attributes of the queried class.
	 * - "*+" (since 2.0.3) means all the attributes of each object found (subclasses may have more attributes than the queried class).
	 * 
	 * This class defaults to '*', as it is unknown what class will be queried.
	 * 
	 * @param string $sOutputFields
	 * @return Service
	 */
	public function SetOutputFields(string $sOutputFields = '*') : Service {

		$this->sOutputFields = $sOutputFields;
		return $this;

	}
	
	
	/**
	 * Constructor
	 *
	 * @param string $sUrl URL Optional iTop URL (REST). Example: http://localhost/itop/web/webservices/rest.php
	 * @param string $sUserLogin Optional User login name.
	 * @param string $sPassword Optional Password.
	 *
	 * @throws Exception
	 */		 
	public function __construct($sUrl, $sUserLogin, $sPassword) {
		
		$this->SetUrl($sUrl);
		$this->SetUserLogin($sUserLogin);
		$this->SetPassword($sPassword);
		
	}
	
	
	/**
	 * Sends data to the iTop REST services and returns data (decoded JSON).
	 *
	 * @param $aJSONData [
	 *  'operation'       => Required. String.  
	 *		(other fields, depending on the operation. Read iTop Rest/JSON documentation.)
	 * ];
	 * 
	 * @return stdClass A standard class, containing the data obtained from the iTop REST Services.
	 * 
	 * @throws Exception
	 */ 
	public function Post(array $aJSONData = []) : stdClass {
		
		// Initiate curl.
		$ch = curl_init();
		
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
		curl_setopt($ch, CURLOPT_URL, $this->sUrl);
		
		// To support BasicAuth:
		if($this->bSupportBasicAuth == true) {
			curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
			curl_setopt($ch, CURLOPT_USERPWD, sprintf('%1$s:%2$s', $this->sUserLogin, $this->sPassword));
		}

		// If needed: Disable SSL/TLS verification.
		if($this->bSkipCertificateCheck == true) {
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
		}

		// The json_data needs to be encoded.
		$aPostData = [
			'version' => $this->sVersion,
			'auth_user' => $this->sUserLogin,
			'auth_pwd' => $this->sPassword,
			'json_data' => json_encode($aJSONData)
		];

		curl_setopt($ch, CURLOPT_POSTFIELDS, $aPostData);
		
		$this->Trace('Url: %1$s', $this->sUrl);
		$this->Trace('Request:');
		$this->Trace(json_encode($aPostData, JSON_PRETTY_PRINT));
		$this->Trace('cURL Start execution.');
		
		// Execute
		$sResult = curl_exec($ch);
		
		$this->Trace('cURL HTTP status code: %1$s', curl_getinfo($ch, CURLINFO_HTTP_CODE));
		$this->Trace('cURL Finished execution: %1$s', date('Y-m-d H:i:s'));
		
		// Closing
		curl_close($ch);

		$this->Trace('Response:');
		$this->Trace($sResult);
		
		$oResponse = json_decode($sResult);

		if($sResult == '' && $this->bSkipCertificateCheck == false) {
			throw new Exception('Unable to get a response. Try disabling the SSL/TLS check.');
		}
		elseif(is_object($oResponse) == false) {
			throw new Exception('Unable to decode API response.');
		}
		
		$this->IsSuccessfulResponse($oResponse);

		return $oResponse;
	
	}
	
	/**
	 * Shortcut to properly encode data in base64. Required to send to iTop REST/JSON services. 
	 *
	 * @param string $sFileName Path of the file(already on the same file system as this PHP application).  
	 * 
	 * @return array
	 * The array contains these keys:
	 * - data: The base64 encoded file.
	 * - filename: The short file name (without path).
	 * - mimetype: The MIME-type of the file.
	 * ]; 
	 */ 
	public function PrepareFile(string $sFileName) : array {
		
		$sFileName = $sFileName;
		$sType = mime_content_type($sFileName);
		$oData = file_get_contents($sFileName);
		
		return [
			'data' => base64_encode($oData), // Warning: escape url_encode!
			'filename' => basename($sFileName),
			'mimetype' => $sType,
			'downloads_count' => 0, // Since iTop 3.2.
		];
		
	}
	
	/**
	 * Validates the response retrieved from the iTop REST/JSON API service.
	 * 
	 * @return void
	 * 
	 * @throws Exception If the error is likely related to connectivity instead.
	 * @throws RestException If the API endpoint actually provided an (error) response.
	 */
	private function IsSuccessfulResponse(stdClass $oResponse) : void {
		
		// Valid response ('code' = 0)
		if(isset($oResponse->code) == false) {

			throw new Exception('No response from iTop REST/JSON Service. Check connection and credentials.');

		}
		
		if($oResponse->code != 0) {

			throw RestException::FromResponse($oResponse);

		}
		
	}
	
	/**
	 * If an OQL query is specified as a key, this will automatically detect and set the class name if it's missing.
	 * 
	 * @param array $aInput Expects at least either a key named 'class' or a key named 'key' containing an iTop OQL query.
	 * @return string $sInput Class name.
	 *
	 */
	private function GetClassName(array $aInput = []) : string {
						
		if(isset($aInput['class']) == true) {
			
			// The class was already explicitly defined.
			return $aInput['class'];
		
		}
		else {
							
			// Is this an OQL query? 
			// Other possibilities: Integer (ID); Array of one or more fields and their values.
			if(is_string($aInput['key']) == true) {
					
				// Dealing with an OQL query. 
				// Generic: SELECT UserRequest
				// Specific: SELECT UserRequest WHERE ...
				if(preg_match('/SELECT (.*?)(?: |$)/', $aInput['key'], $aMatches)) {
					
					return $aMatches[1];

				}					
			} 
			
		}
		
		throw new Exception(sprintf('Error: Unable to derive the iTop class name. The class was not explicitly defined, nor in the key: %1$s', $aInput['key']));
		
	}
	
	/**
	 * Shortcut to create an iTop object.
	 *
	 * @param array $aParameters array [  
	 * Required keys:
	 * - fields: Array. The fields and values for the objects that need to be updated.
	 * - class: String. The iTop class name must be specified. Some examples: Organization, Contact, Person, ...
	 * 
	 * Optional keys:
	 * - comment: String. Describes the action and is stored in iTop's history tab.
	 * - output_fields: Array. List of attribute codes to retrieve.
	 * 
	 * @return stdClass
	 * 
	 * @throws Exception If the error is likely related to connectivity instead.
	 * @throws RestException If the API endpoint actually provided an (error) response.
	 *
	 */ 
	public function Create(array $aParameters = []) : stdClass {

		$sClassName = $this->GetClassName($aParameters);
					
		$oResponse = $this->Post([
			'operation' => 'core/create',
			'class' => $sClassName,
			'fields' => $aParameters['fields'],
			'comment' => $aParameters['comment'] ?? sprintf('Created by %1$s', $this->sUserDisplayName),
			'output_fields' => $aParameters['output_fields'] ?? $this->sOutputFields
		]);

		return $oResponse;
		
	}
	
	/**
	 * Shortcut to delete iTop objects.
	 *
	 * @param array $aParameters Array [  
	 * Required keys:
	 * - class: String. If the key is NOT an OQL query, the iTop class name must be specified. Some examples: Organization, Contact, Person, ...
	 * - fields: Array. The fields and values for the objects that need to be updated.
	 * - key: Integer (iTop ID), string (OQL query) or array (one or more attribute codes and their values leading to the identification of an iTop object).
	 * 
	 * Optional keys:
	 * - comment: String. Describes the action and is stored in iTop's history tab.
	 * - output_fields: Array. List of attribute codes to retrieve.
	 * - simulate: Boolean. Defaults to false. According to iTop documentation, only available for delete operation.  
	 * 
	 * ]
	 * 
	 * @return stdClass
	 * 
	 * @throws Exception If the error is likely related to connectivity instead.
	 * @throws RestException If the API endpoint actually provided an (error) response.
	 *
	 */ 
	public function Delete(array $aParameters = []) : stdClass {
		
		$sClassName = $this->GetClassName($aParameters);
		
		$oResponse = $this->Post([
			'operation' => 'core/delete',
			'class' => $sClassName,
			'key' => $aParameters['key'],
			'comment' => $aParameters['comment'] ?? sprintf('Deleted by %1$s', $this->sUserDisplayName),
			'output_fields' => $aParameters['output_fields'] ?? $this->sOutputFields,
			'simulate' => $aParameters['simulate'] ?? false
		]);

		return $oResponse;
		
	}
	
	/**
	 * Shortcut to get iTop objects.
	 *
	 * @param array $aParameters Array.
	 * 
	 * Required keys:
	 * - class: String. If the key is NOT an OQL query, the iTop class name must be specified. Some examples: Organization, Contact, Person, ...
	 * - key: Integer (iTop ID), string (OQL query) or array (one or more attribute codes and their values leading to the identification of an iTop object).
	 * 
	 * Optional keys:
	 * - output_fields: Array. List of attribute codes to retrieve.
	 * 
	 *
	 * @return stdClass
	 * 
	 * @throws Exception If the error is likely related to connectivity instead.
	 * @throws RestException If the API endpoint actually provided an (error) response.
	 * 
	 */ 
	public function Get(Array $aParameters = []) : stdClass {
		
		$sClassName = $this->GetClassName($aParameters);
					
		$oResponse = $this->Post([
			'operation' => 'core/get',
			'class' => $sClassName,
			'key' => $aParameters['key'],
			'output_fields' => $aParameters['output_fields'] ?? $this->sOutputFields
		]);

		return $oResponse;
		
	} 

	/**
	 * Shortcut to update iTop objects.
	 *
	 * @param array $aParameters Array.
	 * 
	 * Required keys:
	 * - fields: Array. The fields and values for the objects that need to be updated.
	 * - key: Integer (iTop ID), string (OQL query) or array (one or more attribute codes and their values leading to the identification of an iTop object).
	 * - class: String. If the key is NOT an OQL query, the iTop class name must be specified. Some examples: Organization, Contact, Person, ...
	 * 
	 * Optional keys:
	 * - comment: String. Describes the action and is stored in iTop's history tab.
	 * - output_fields: Array. List of attribute codes to retrieve.
	 * 
	 * @return stdClass
	 * 
	 * @throws Exception If the error is likely related to connectivity instead.
	 * @throws RestException If the API endpoint actually provided an (error) response.
	 *
	 */ 
	public function Update(array $aParameters = []) : stdClass {
		
		$sClassName = $this->GetClassName($aParameters);
		
		$oResponse = $this->Post([
			'operation' => 'core/update',
			'class' => $sClassName,
			'key' => $aParameters['key'],
			'fields' => $aParameters['fields'],
			'comment' =>$aParameters['comment'] ?? sprintf('Updated by %1$s', $this->sUserDisplayName),
			'output_fields' => $aParameters['output_fields'] ?? $this->sOutputFields,
		]);
		
		return $oResponse;
		
	}
	
	/**
	 * Shortcut to first update iTop objects and then apply a stimulus to them.
	 *
	 * @param array $aParameters Array.
	 * 
	 * Required keys:
	 * - fields: Array. The fields and values for the objects that need to be updated.
	 * - key: Integer (iTop ID), string (OQL query) or array (one or more attribute codes and their values leading to the identification of an iTop object).
	 * - class: String. If the key is NOT an OQL query, the iTop class name must be specified. Some examples: Organization, Contact, Person, ...
	 * - stimulus: String. A valid stimulus, e.g. ev_assign.
	 * 
	 * Optional keys:
	 * - comment: String. Describes the action and is stored in iTop's history tab.
	 * - output_fields: Array. List of attribute codes to retrieve.
	 * 
	 * @param string $sStimulus
	 * 
	 * @return stdClass
	 * 
	 * @throws Exception If the error is likely related to connectivity instead.
	 * @throws RestException If the API endpoint actually provided an (error) response.
	 *
	 */ 
	public function ApplyStimulus(array $aParameters = []) : stdClass {
		
		$sClassName = $this->GetClassName($aParameters);
		
		$oResponse = $this->Post([
			'operation' => 'core/apply_stimulus',
			'class' => $sClassName,
			'key' => $aParameters['key'],
			'fields' => $aParameters['fields'],
			'stimulus' => $aParameters['stimulus'],
			'comment' => $aParameters['comment'] ?? sprintf('Updated by %1$s', $this->sUserDisplayName),
			'output_fields' => $aParameters['output_fields'] ?? $this->sOutputFields,
		]);
		
		return $oResponse;
		
	}

	/**
	 * Shortcut to checking credentials.  
	 * It does not return any user information (neither does the iTop API in version 1.4 and below)
	 *
	 * @return stdClass
	 * 
	 * @throws Exception If the error is likely related to connectivity instead.
	 * @throws RestException If the API endpoint actually provided an (error) response.
	 */ 
	public function CheckCredentials() : stdClass {
		
		$oResponse = $this->Post([
			'operation' => 'core/check_credentials',
			'user' => $this->sUserLogin,
			'password' => $this->sPassword
		]);
		
		return $oResponse;
		
	}
	
	/**
	 * Trace function. Logs output, facilitates debugging.
	 *
	 * @param string $sMessage
	 * @param mixed ...$args
	 * 
	 * @return void
	 */
	public function Trace($sMessage, ...$args) : void {
		
		if($this->sTraceLogFileName !== null) {
			
			// It's possible "%" is used in an OQL.
			$aArgs = func_get_args();
			if(count($aArgs) > 1) {
				$sMessage = call_user_func_array('sprintf', $aArgs);
			}

			file_put_contents($this->sTraceLogFileName, sprintf('%1$s | %2$s' . PHP_EOL, 
				date('Y-m-d H:i:s'),
				$sMessage
			), FILE_APPEND);
			
		}
		
	}

	/**
	 * From a response: Get an array of objects.
	 *
	 * @param stdClass $oResponse The REST/JSON API response.
	 * @param boolean $bWithKeys Whether to return an associative array, where the key is something like <iTopClassName>::<id>
	 * 
	 * @return array
	 */
	public function GetObjectsAsArray(stdClass $oResponse, bool $bWithKeys) {

		if(property_exists($oResponse, 'objects') == false || $oResponse->objects === null) {
			static::Trace('Objects as array: No objects. Returning empty array.');
			return [];
		}

		$aObjects = get_object_vars($oResponse->objects);

		if(!$bWithKeys) {
			$aObjects = array_values($aObjects);
		}

		return $aObjects;

	}
	
}

