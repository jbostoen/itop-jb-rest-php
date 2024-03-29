<?php

/**
 * @copyright   Copyright (C) 2019-2023 Jeffrey Bostoen
 * @license     https://www.gnu.org/licenses/gpl-3.0.en.html
 * @version     2023-08-27 10:16:00
 * @see         https://www.itophub.io/wiki/page?id=latest%3Aadvancedtopics%3Arest_json
 *
 * Defines class iTop_Rest, which communicates with iTop REST/JSON API
 *
 * 
 */
 
 namespace jb_itop_rest;
 
 use \Exception;
 
 
	/**
	 * Class iTopRest. A class to communicate with iTop API more efficiently in PHP implementations.
	 */
	class iTopRest {
		
		/**
		* @var \String $sRestName. Name which is used by default in REST comments.
		*/
		public $sRestName = 'iTop REST';		
		
		/**
		 * @var \String $sPassword. Password of the iTop user which has the REST User Profile (in iTop)
		 */
		public $sPassword = 'password';
		
		/**
		 * @var \Boolean $bTrace. For debugging purposes. Outputs the network request and response info sent to iTop REST/JSON.
		 */
		public $bTrace = false;
		
		/**
		 * @var \Boolean $bSkipCertificateCheck. For development purposes. Skips SSL/TLS checks.
		 */
		public $bSkipCertificateCheck = false;
		
		/** 
		 * @var \String $sURL. URL of the iTop web services, including version. Example: 'http://localhost/itop/web/webservices/rest.php'
		 * @details If left blank, an attempt to derive this info will happen in __construct()
		 */
		public $sUrl = '';
		
		/**
		 *@var \String $sUserName. User in iTop which has the REST User Profile (in iTop). iTop REST/JSON error messages might be in the native language of the specified user.
		 */
		public $sUserName = 'admin';
		
		/**
		 * @var \String $sVersion. describing the REST API version. 
		 * @See https://www.itophub.io/wiki/page?id=latest:advancedtopics:rest_json#changes_history
		 */
		public $sVersion = '1.3';
		
		
		/**
		 * Constructor
		 *
		 * @param \String $sUserName Optional User name.
		 * @param \String $sPassword Optional Password.
		 * @param \String $sUrl URL Optional iTop URL (REST) string.
		 *
		 * @throws \Exception
		 */		 
		public function __construct($sUserName = '', $sPassword = '', $sUrl = '') {
			
			if($sUserName != '') {
				$this->sUserName = $sUserName;
			}

			if($sPassword != '') {
				$this->sPassword = $sPassword;
			}
			
			if($sUrl != '') {
				$this->sUrl = $sUrl;
			}
			
			// If url is unspecified by default and this file is placed within iTop-directory as expected, the url property will automatically be adjusted
			if($this->sUrl == '') {
				 
				// Assume we're in iTop directory; get definitions for APPCONF and ITOP_DEFAULT_ENV
				$sDirName = __DIR__ ;
				
				while($sDirName != dirname($sDirName)) {
					
					$sFile = $sDirName.'/approot.inc.php';
					if(file_exists($sFile) == true) {

						// Compatibility with iTop 2.7; NOT loading Twig etc. Defaults!
						defined('APPROOT') || define('APPROOT', dirname($sFile).'/');
						defined('APPCONF') || define('APPCONF', APPROOT.'conf/');
						defined('ITOP_DEFAULT_ENV') || define('ITOP_DEFAULT_ENV', 'production');
						
						// Get iTop config file 
						if(file_exists(APPCONF . ITOP_DEFAULT_ENV . '/config-itop.php') == true) {
							
							require(APPCONF . ITOP_DEFAULT_ENV . '/config-itop.php'); // local scope
							$this->sUrl = $MySettings['app_root_url'] . 'webservices/rest.php';

							return;
							
						}
						
					}
						
					// folder up
					$sDirName = dirname($sDirName);
					
				}
				
				// return hasn't happened: this means we have an error here.
				$this->Trace('Could not automatically derive iTop Rest/JSON URL. It must be set manually.');
				
			}
		}
		
		
		/**
		 * Sends data to the iTop REST services and returns data (decoded JSON)
		 *
		 * @param $aJSONData [
		 *  'operation'       => Required. String.
		 *		(other fields, depending on the operation. Read iTop Rest/JSON documentation.)
		 * ];
		 * 
		 * @return \Array containing the data obtained from the iTop REST Services
		 * @throws \Exception
		 */ 
		public function Post(Array $aJSONData = []) {
			
			//  Initiate curl
			$ch = curl_init();
			 
			// Disable SSL verification
			if($this->bSkipCertificateCheck == true) {
				curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
				curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
			}
			
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');        
			

			curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
			curl_setopt($ch, CURLOPT_USERPWD, $this->sUserName.':'.$this->sPassword);

			 
			// Set the url
			curl_setopt($ch, CURLOPT_URL, $this->sUrl);
			
			// URL encode here. A base64 string easily includes plus signs which need to be escaped
			$aPostData = [
				'version' => $this->sVersion,
				'auth_user' => $this->sUserName,
				'auth_pwd' => $this->sPassword,
				'json_data' => json_encode($aJSONData)
			];
	
			curl_setopt($ch, CURLOPT_POSTFIELDS, $aPostData);
			
			$this->Trace('Url: '.$this->sUrl);
			$this->Trace('Request: '.PHP_EOL.json_encode($aPostData));
			$this->Trace('Data for iTop API: '.json_encode($aJSONData, JSON_PRETTY_PRINT));
			$this->Trace('cURL start exec: '.date('Y-m-d H:i:s'));
			
			// Execute
			$sResult = curl_exec($ch);
			
			$this->Trace('cURL status code: '.curl_getinfo($ch, CURLINFO_HTTP_CODE));
			$this->Trace('cURL end exec: '.date('Y-m-d H:i:s'));
			
			// Closing
			curl_close($ch);
  
			$this->Trace('Response: '.$sResult);
			
			$aResult = json_decode($sResult, true);
			
			if(is_array($aResult) == false || isset($aResult['code']) == false){
				throw new Exception('Invalid response from iTop API/REST. Incorrect configuration or something wrong with network or iTop?');
			}
    
			return $aResult;
		
		}
		
		/**
		 * Shortcut to properly encode data in base64. Required to send to iTop REST/JSON services. 
		 *
		 * @param String $sFileName Path of the file you want to prepare (already on your server)  
		 * 
		 * @return \Array
		 * [
		 *  'data'            => base64 encoded file
		 *  'mimetype'        => MIME-type of the file
		 *  'filename'        => Filename (short)
		 * ];
		 */ 
		public function PrepareFile(String $sFileName) {
			
			$sFileName = $sFileName;
			$sType = mime_content_type($sFileName);
			$oData = file_get_contents($sFileName);
			//$base64 = "data:".$sType . ";base64," . base64_encode($oData);
			
			return [
				'data' => base64_encode($oData), // Warning: escape url_encode!
				'filename' => basename($sFileName),
				'mimetype' => $sType
			];
			
		}
		
		/**
		 * Processes JSON data retrieved from the iTop REST/JSON services. 
		 * Handles and simplifies the output of successful API calls. 
		 *  
		 * @param \Array $aServiceResponse Service response after REST/JSON call
		 * @param \Array $aParameters [
		 *  'no_keys'         => Optional. Boolean. Defaults to false. Removes array keys if true.
		 * ]
		 * 
		 * @return \Array, being similar to:
		 * 
		 * On error:
		 * 
		 * Array [
		 *  'code'            => iTop error code (see iTop REST Documentation)
		 *  'message'         => iTop error message
		 * ] 
		 * 
		 * No error and no_keys = false (default): 
		 * 
		 * Array [
		 *  iTopclass::<Id1>' => [ iTop object data ], 
		 *  iTopclass::<Id2>' => [ iTop object data ], 
		 * 	  ...
		 * ]
		 * 
		 *
		 * No error and no_keys = true:
		 *
		 * @return \Array
		 * Array [
		 *   [ iTop object data ], 
		 *   [ iTop object data ], 
		 * 	  ...
		 * ]
		 *
		 *  
		 * @details Simplification happens because we only return an array of objects, either with or without key. 
		 * If you want to check for errors, just check in the array if 'code' still exists.
		 */
		private function ProcessResult(Array $aServiceResponse = [], Array $aParameters = []) {
			
			// Valid response ('code' = 0)
			if(isset($aServiceResponse['code']) == true && $aServiceResponse['code'] == 0) {
								
				// Valid call, no results? (usually after 'operation/get')
				if(isset($aServiceResponse['objects']) == false) {
					return [];
				}
				else {
					$aObjects = $aServiceResponse['objects'];
					return (isset($aParameters['no_keys']) == true && $aParameters['no_keys'] == true ? array_values($aObjects) : $aObjects);
				}
			}
			else {
				
				// Service response contained an error.
				// Return all.
				if(isset($aServiceResponse['code']) == true && isset($aServiceResponse['message']) == true) {
					// Valid response but error
					throw new iTop_Rest_Exception('Invalid response from iTop REST/JSON Service: '.$aServiceResponse['message'], $aServiceResponse['code'], null, $aServiceResponse);
				}
				else {
					// Invalid response
					// Must still have been an array or exception would have occurred earlier
					throw new iTop_Rest_Exception('No response from iTop REST/JSON Service. Check connection and credentials.');
				}
				
			} 
			
		}
		
		/**
		 * If an OQL query is specified as a key, this will automatically detect and set the class name if it's missing.
		 * 
		 * @param \Array $aInput Expects at least either a key named 'class' or a key named 'key' containing an iTop OQL query.
		 * @return String $sInput Class name.
		 *
		 */
		private function GetClassName(Array $aInput = []) {
							
			if(isset($aInput['class']) == true) {
				
				return $aInput['class'];
			
			}
			else {
				 				
				// Is this an OQL query? 
				// Other possibilities: Integer (ID); Array of one or more fields and their values.
				if(is_string($aInput['key']) == true) {
					 
					if(preg_match('/^select /i', $aInput['key'])) {
						// Dealing with an OQL query. 
						// Generic: SELECT UserRequest
						// Specific: SELECT UserRequest WHERE ...
						// Class names can't contain space, so:
						return explode(' ', $aInput['key'])[1]; 
					}					
				} 
				
			}
			
			throw new Exception('Error in ' . __METHOD__ . '(): class was not defined and it could also not be derived from key.');
			
		}
		
		/**
		 * Shortcut to create data
		 *
		 * @param \Array $aParameters Array [
		 *  'comment'         => Optional. String. Describes the action and is stored in iTop's history tab.
		 *  'fields'          => Required. Array. The fields and values for the object to create.
		 *  'class'           => Required. String. iTop class name (examples: Organization, Contact, Person ...)
		 *  'output_fields'   => Optional. Array. List of field names you want to retrieve. 
		 *                       If not specified, all fields are returned.
		 * 
		 *  'no_keys'         => Optional. Boolean. 
		 *                       Not related to iTop. Will return the objects without a key.
		 * ]
		 * 
		 * @return \Array - see processResult()
		 * @throws \iTop_Rest_Exception
		 *
		 */ 
		public function Create(Array $aParameters = []) {
			
			$sClassName = $this->GetClassName($aParameters);
						
			$aResult = $this->Post([
				'operation' => 'core/create', // Action
				'class' => $sClassName, // Class of object to create
				'fields' => $aParameters['fields'], // Field data to be saved
				'comment' => (isset($aParameters['comment']) == true ? $aParameters['comment'] : 'Created by ' . $this->sRestName), // Comment in history tab
				'output_fields' => (isset($aParameters['output_fields']) == true ? $aParameters['output_fields'] :	'*' /* All fields */)
			]);
			
			return $this->ProcessResult($aResult, $aParameters); 
			
		}
		
		/**
		 * Shortcut to delete data
		 *
		 * @param \Array $aParameters Array [
		 *  'comment'         => Required. String. Describing the action. 
		 *  'key'             => Required.
		 *                       Int (iTop ID) 
		 *                       String (OQL Query) 
		 *                       Array (one or more fields and their values)
		 *  'class'           => Required, if key is not an OQL Query. 
		 *                       String. iTop class name (examples: Organization, Contact, Person ...)
		 *  'output_fields'   => Optional. Array. List of field names you want to retrieve. 
		 *                       If not specified, all fields are returned.
		 *  'simulate'        => Optional. Boolean. Defaults to false. According to iTop documentation, only available for delete operation.
		 * 
		 *  'no_keys'         => Optional. Boolean. 
		 *                       Not related to iTop. Will return the objects without a key.
		 * 
		 * ]
		 * 
		 * @return \Array - see processResult()
		 * @throws \iTop_Rest_Exception
		 *
		 */ 
		public function Delete(Array $aParameters = []) {
			
			$sClassName = $this->GetClassName($aParameters);
			
			$aResult = $this->Post([
				'operation' => 'core/delete', // iTop REST/JSON operation
				'class' => $sClassName, // Class of object to delete
				'key' => $aParameters['key'], // OQL query (String), ID (Float) or fields/values (Array)
				'comment' => (isset($aParameters['comment']) == true ? $aParameters['comment'] : 'Deleted by ' . $this->sRestName), // Comment in history tab?
				'output_fields' => (isset($aParameters['output_fields']) == true ? $aParameters['output_fields'] :	'*' /* All fields */),
				'simulate' => (isset($aParameters['simulate']) == true ? $aParameters['simulate'] : false)
			]);
			
			return $this->ProcessResult($aResult, $aParameters); 
			
		}
		
		/**
		 * Shortcut to get data
		 *
		 * @param \Array $aParameters Array [
		 *  'key'             => Required.
		 *                       Int (iTop ID) 
		 *                       String (OQL Query) 
		 *                        Array (one or more fields and their values)
		 *  'class'           => Required if key is not an OQL Query. 
		 *                       String. iTop class name (examples: Organization, Contact, Person ...)
		 *  'output_fields'   => Optional. Array. List of field names you want to retrieve. 
		 *                       If not specified, all fields are returned.
		 * 
		 *  'no_keys'         => Optional. Boolean. 
		 *                       Not related to iTop. Will return the objects without a key.
		 *                        
		 * ]
		 * 
		 *
		 * @return \Array - see processResult()
		 * @throws \iTop_Rest_Exception
		 * 
		 */ 
		public function Get(Array $aParameters = []) {
			
			$sClassName = $this->GetClassName($aParameters);
			 			
			$aResult = $this->Post([
				'operation' => 'core/get', // iTop REST/JSON operation
				'class' => $sClassName, // Class of object(s) to retrieve
				'key' => $aParameters['key'], // OQL query (String), ID (Float) or fields/values (Array)
				'output_fields' => (isset($aParameters['output_fields']) == true ? $aParameters['output_fields'] :	'*' /* All fields */)			
			]);

			return $this->ProcessResult($aResult, $aParameters); 
			
		} 

		/**
		 * Shortcut to update data
		 *
		 * @param \Array $aParameters Array [
		 *  'comment'          => Optional. String. Describes the action and is stored in iTop's history tab.
		 *  'fields'           => Required. Array. The fields and values for them that need to be updated
		 *  'key'              => Required.
		 *                        Int (iTop ID) 
		 *                        String (OQL Query) 
		 *                        Array (one or more fields and their values)
		 *  'class'            => Required if key is not an OQL Query. 
		 *                        String. iTop class name (examples: Organization, Contact, Person ...)
		 *  'output_fields'    => Optional. Array. List of field names you want to retrieve. 
		 *                        If not specified, it returns all fields.
		 * ]
		 * 
		 * @return \Array - see processResult()
		 * @throws \iTop_Rest_Exception
		 *
		 */ 
		public function Update(Array $aParameters = []) {
			
			$sClassName = $this->GetClassName($aParameters);
			
			$aResult = $this->Post([
				'operation' => 'core/update', // iTop REST/JSON operation
				'class' => $sClassName, // Class of object to update
				'key' => $aParameters['key'], // OQL query (String), ID (Float) or fields/values (Array)
				'fields' => $aParameters['fields'], // Field data to be updated
				'comment' => (isset($aParameters['comment']) == true ? $aParameters['comment'] : 'Updated by ' . $this->sRestName), // Comment in history tab
				'output_fields' => (isset($aParameters['output_fields']) == true ? $aParameters['output_fields'] :	'*' /* All fields */),
			]);
			
			return $this->ProcessResult($aResult, $aParameters); 
			
		}
		
		/**
		 * Shortcut to checking credentials. It does not return any user information (neither does the iTop API in version 1.4 and below)
		 *
		 * @param \Array $aParameters Array [
		 *  'user'             => Required. User name
		 *  'password'         => Required. Password
		 * ]
		 * 
		 * @return \Array - see processResult()
		 * @throws \iTop_Rest_Exception
		 */ 
		public function CheckCredentials(Array $aParameters = []) {
			
			$sClassName = $this->GetClassName($aParameters);
			
			$aResult = $this->Post([
				'operation' => 'core/check_credentials', // iTop REST/JSON operation
				'user' => $aParameters['user'],
				'password' => $aParameters['password']
			]);
			
			return $this->ProcessResult($aResult, $aParameters); 
			
		}
		
		/**
		 * Trace function. For debugging purposes.
		 */
		public function Trace($sTrace) {
			
			if($this->bTrace == true) {
				
				file_put_contents('itop_api_trace.txt', date('Y-m-d H:i:s').' | '.$sTrace.PHP_EOL, FILE_APPEND);
				
			}
			
		}
		
	}
	
	
	
	/**
	 * Class iTop_Rest_Exception. Adds more details, mostly to output to JSON.
	 */
	class iTop_Rest_Exception extends Exception {
	
		/**
		 * @var \Array $aDetails Array with detailed information.
		 * @used-by iTop_Rest_Exception::GetDetails()
		 * @used-by iTop_Rest_Exception::ToJSON()
		 */
		private $aDetails = [];
		
		
		// Redefine the exception so message isn't optional
		/**
		 * Construct method
		 *
		 * @param \String $sMessage Short message describing the error
		 * @param \Integer $iCode Integer indicating an error. Defaults to 0
		 * @param \Exception $oPreviousException Previous exception
		 * @param \Array $aResponseFromAPI Hashtable containing more details. Details are optional, but should provide an iTop API REST-response.
		 */
		public function __construct($sMessage, $iCode = 0, Exception $oPreviousException = null, $aResponseFromAPI = []) {
			
			// make sure everything is assigned properly
			parent::__construct($sMessage, $iCode, $oPreviousException);
			
			// Extend
			$this->aDetails = $aResponseFromAPI;
		}
		
		
		/**
		 * Returns detailed error information.
		 *
		 * @uses \iTop_Rest_Exception::$aDetails
		 * @return \Array
		 */
		public function GetDetails() {
			return $this->aDetails;
		}
		
		/**
		 * Returns JSON-encoded detailed error information.
		 *
		 * @uses \iTop_Rest_Exception::$aDetails
		 * @return \String ($aDetails JSON-encoded)
		 */
		public function ToJSON() {
			return json_encode($aDetails);
		}
		
	}
	
