<?php

$VERSION = '0.4';

$NAME = 'The Team Name'; // What is the assembly's name for which you are using the minute taker
$ADMIN_EMAIL = 'admin@domain.tld'; // Who should be contacted if an internal server error occurs (is publicly displayed on error page)
$PROTO_PATH = './'; // the relative path (to the the scripts directory) where the protocol should be saved
$WEBSITE = 'https://domain.tld/protokoller/'; // Homepage URL for footer of the generated meeting minute pages
$TIMEZONE = 'Europe/Berlin'; // In what time zone is the minute taker deployed, important for correct time stamps
$PASSWORD = 'password'; // The submit password against unauthorized usage, choose something cryptographically difficult
$USE_GIT = False; // Should the minute taker push the protocol to a git repository
$SEND_MAIL = False; // Should the minute taker send the protocol by mail
$MAIL_RECIPIENT = 'protocols@domain.tld'; // Who should receive the protocol by mail?
$MAIL_SENDER = 'protocols@domain.tld'; // Who should send the protocol by mail?
$MAX_PROTOS = 100; // max trials to save file and max protocols per day
$PEOPLE = array("Max Mustermann", "Marlene Musterfrau"); // The static list of the assembly's members

?>
