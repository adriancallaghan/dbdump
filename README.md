# dbdump


Introduction
============

Very lightweight database dumper that automatically locks onto access details provided by other PHP frameworks.

PhpMyAdmin is able to do exactly the same, but this is a single file that can be uploaded quickly alongside an existing site, it supports systems like wordpress and concrete5.

Useful for when a quick database dump is required, just enter some users account details at the top to protect from unauthorised use, upload and hit the URL.


Usage
=====

Enter your usernames and passwords into the constructor in either plain text (insecure if read by someone else) or encypted

	Example 1: allowing bob access with the password 1234 and joe access with password 5678 - no encryption 

		dbDump::Init(array(
			'users'	=> array(
				array('username'=>'bob','password'=>'1234'),
				array('username'=>'joe','password'=>'5678'),
			)));



	Example 2: allowing bob access with the password 1234 and joe access with password 5678 - with encyption method and hashed passwords that must match the method result

		dbDump::Init(array(
			'users'	=> array(
				array('username'=>'bob','password'=>'81dc9bdb52d04dc20036dbd8313ed055'),
				array('username'=>'joe','password'=>'674f3c2c1a8a6f90461e8a66fb5550ba'),
			),
			'passwordEncryption'=>function($pass){
				return md5($pass);
			}
		));

Upload to the domain root, typically httd_docs, public_html etc, and Hit the URL e.g /dump.php

Provide the details you added in the constructor and once logged in it will lock onto the database and offer it for download.


Requirements
============
PHP 5.4+
