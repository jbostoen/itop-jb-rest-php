# iTop REST implementation (PHP)

Copyright (C) 2019-2021 Jeffrey Bostoen

[![License](https://img.shields.io/github/license/jbostoen/iTop-custom-extensions)](https://github.com/jbostoen/iTop-custom-extensions/blob/master/license.md)
[![Donate](https://img.shields.io/badge/Donate-PayPal-green.svg)](https://www.paypal.me/jbostoen)
🍻 ☕

Need assistance with iTop or one of its extensions?  
Need custom development?  
Please get in touch to discuss the terms: **info@jeffreybostoen.be** / https://jeffreybostoen.be

## What?
A simple PHP class which offers easy access to the most important iTop REST/JSON API actions.

Not everything is implemented, mostly:
* core/get
* core/create
* core/update
* core/delete

There's a generic method to post and process other info; and prepare files to be sent to iTop.
Also supports sending base64 encoded data (files).

## Example
```

$oRest = new \iTop_Rest();
$oRest->user = 'user';
$oRest->password = 'pwd';
$oRest->url = 'http://localhost/itop/web/webservices/rest.php';

// Fetch objects of type "Person"
$aPersons = $oRest->Get([
	'key' => 'SELECT Person',
	'no_keys' => true
]);

// Create new Person
$oRest->Create([
	'class' => 'Person',
	'fields' => [
		'org_id' => 1,
		'first_name' => 'John',
		'name' => 'Smith',
		'notify' => 'yes'
	]
]);

// Update Person
$oRest->Update([
	'class' => 'Person',
	'key' => 1, // Id of Person, or OQL string
	'fields' => [
		'notify' => 'yes'
	],
	'comment' => 'Some comment about the update',
	'no_keys' => false
]);

// Delete person
$oRest->Delete([
	'class' => 'Person',
	'key' => 'SELECT Person WHERE first_name = "John" AND name = "Smith"', // Id of Person, or OQL string
	'comment' => 'Some comment about the deletion',
	'no_keys' => true
]);

```

