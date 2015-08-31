<?php

/*
 * What you need in ILIAS:
 *
 * - Activate the ILIAS SOAP interface in "Administration" > "Server" > "SOAP"
 * - Create an ILIAS user that should be used, in our example: user "soap", password "soappw"
 * - Login manually with this user in ILIAS and accepct the user agreement. It is a common issue (at least in older
 *   ILIAS versions) that access for the soap user has been denied, because of the missing agreement.
 */

/*
 * Modify the following variables to match your ILIAS environment
 */
$ilias_base_url = "http://localhost/ilias"; 	// this your base ILIAS url, you should use https in productive environments
$ilias_client = "iliastrunk";					// the client ID (see ILIAS setup) of your ILIAS client
$ilias_soap_username = "soap";					// the name of the soap user you created in the ILIAS administration
$ilias_soap_user_password = "soappw";			// password of the soap user


/*
 * General initialisation
 */

// we use the nusoap lib for our client, see http://sourceforge.net/projects/nusoap/
require_once("./nusoap/nusoap.php");


// setting up the soap client
$wsdl = $ilias_base_url."/webservice/soap/server.php?wsdl"; // ILIAS url of soap wsdl
$client = new nusoap_client($wsdl, true);


/*
 * SOAP Calls
 *
 * This is the interesting part. You should always start with a call to the login operation. The login operation
 * will (if succeeded) return a session id. You will need this session ID for almost all other operations. When
 * you are finished, you should call the logout operation.
 *
 * You can generate a list of all operations by simply accessing http://<youriliasserver>/webservice/soap/server.php
 * e.g. http://www.ilias.de/docu/webservice/soap/server.php
 *
 * Some of the soap operations make use of XML. You usually find the corresponding DTD or XSD files in the subdirectory
 * "xml" of your ILIAS installation.
 */


// first call: login
$par = array(
	"client" => $ilias_client,
	"username" => $ilias_soap_username,
	"password" => $ilias_soap_user_password,
);
$ret = $client->call("login", $par);
$session_id = $ret;
echo "Called login.";
var_dump($ret);


// import user, see xml/ilias_user_x_y.dtd in your ilias installation (note that the version 4_5 refers to ILIAS 5.0)
// a simple way to create the XML for the import is the excel sheet, provided at http://www.ilias.de/docu/goto_docu_grp_4626.html
// another way to get an example is to export existing users in the ILIAS administration
$par = array(
	"sid" => $session_id,
	"folder_id" => -1,							// system user folder
	"usr_xml" => '<?xml version="1.0" encoding="UTF-8"?>'.
		'<Users>'.
			'<User Id="tim.thaler" Language="de" Action="Insert">'.
  				'<Active><![CDATA[true]]></Active>'.
				'<Role Id="2" Type="Global" Action="Assign"><![CDATA[Administrator]]></Role>'.
  				'<Login><![CDATA[tim.thaler]]></Login>'.
  				'<Password Type="PLAIN"><![CDATA[testpassword]]></Password>'.
  				'<Gender><![CDATA[m]]></Gender>'.
  				'<Firstname><![CDATA[Tim]]></Firstname>'.
  				'<Lastname><![CDATA[Thaler]]></Lastname>'.
  				'<Email><![CDATA[doesnotexist@ilias.de]]></Email>'.
 			'</User>'.
		'</Users>',
	"conflict_role" => 3,						// ignore on conflict
	"send_account_mail" => 0					// no account mail
);
$ret = $client->call("importUsers", $par);
echo "Called importUsers.";
var_dump($ret);


// last call: logout
$par = array(
	"sid" => $session_id
);
$ret = $client->call("logout", $par);
echo "Called logout.";
var_dump($ret);


?>