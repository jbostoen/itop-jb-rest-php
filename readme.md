# iTop REST implementation (PHP)

Copyright (C) 2019-2024 Jeffrey Bostoen

[![License](https://img.shields.io/github/license/jbostoen/iTop-custom-extensions)](https://github.com/jbostoen/iTop-custom-extensions/blob/master/license.md)
[![Donate](https://img.shields.io/badge/Donate-PayPal-green.svg)](https://www.paypal.me/jbostoen)
🍻 ☕

Need assistance with iTop or one of its extensions?  
Need custom development?  
Please get in touch to discuss the terms: **info@jeffreybostoen.be** / https://jeffreybostoen.be

## What?

A simple PHP implementation that offers easy access to the most important iTop REST/JSON API actions.

Basic support for:

```
* core/check_credentials
* core/get
* core/create
* core/update
* core/delete
```

There's a generic method to post and process other info.  
Also supports preparing and sending base64 encoded data (files).


## Quick install

`composer require jbostoen/itop-rest-php`


## Examples

```

require('vendor/autoload.php');

use JeffreyBostoen\iTopRestService\Service;

$oService = new iTopRest('user', 'pwd', 'http://localhost/itop/web/webservices/rest.php');

// Fetch objects of type "Person".
$oResponse = $oService->Get([
	'key' => 'SELECT Person',
]);

foreach($oResponse->results as $sId => $aData) {
	
	// Do something.

}

// Create new Person.
$oResponse->Create([
	'class' => 'Person',
	'fields' => [
		'org_id' => 1,
		'first_name' => 'John',
		'name' => 'Smith',
		'notify' => 'yes'
	]
]);

// Update Person.
$oResponse->Update([
	'class' => 'Person',
	'key' => 1, // Id of Person, or OQL string
	'fields' => [
		'notify' => 'yes'
	],
	'comment' => 'Some comment about the update',
]);

// Delete person.
$oResponse->Delete([
	'class' => 'Person',
	'key' => 'SELECT Person WHERE first_name = "John" AND name = "Smith"', // Id of Person, or OQL string
	'comment' => 'Some comment about the deletion',
]);

```

**Options**

Show trace output (shows cURL and iTop errors, sent and received data, ...):

```
$oService->SetTraceLogFileName('/var/log/api.log');
```

Bypass SSL/TLS check:
```
$oService->SetSkipCertificateCheck(true);
```

