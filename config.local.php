<?php

$CONF['configured'] = true;

$CONF['database_type'] = 'xxDBSxx';
$CONF['database_host'] = 'xxDBHOSTxx';
$CONF['database_user'] = 'xxDBUSERxx';
$CONF['database_password'] = 'xxDBPASSxx';
$CONF['database_name'] = 'xxDBNAMExx';

$CONF['encrypt'] = 'dovecot:SHA512-CRYPT';
$CONF['dovecotpw'] = "/usr/bin/doveadm pw";

$CONF['fetchmail'] = 'NO';

// Leave blank to send email from the logged-in Admin's Email address.
//$CONF['admin_email'] = 'admin@';
$CONF['admin_email'] = '';

// Mail Server
// Hostname (FQDN) of your mail server.
// This is used to send email to Postfix in order to create mailboxes.
$CONF['smtp_server'] = 'localhost';
$CONF['smtp_port'] = '25';

// In what flavor should courier-authlib style passwords be encrypted?
// md5 = {md5} + base64 encoded md5 hash
// md5raw = {md5raw} + plain encoded md5 hash
// SHA = {SHA} + base64-encoded sha1 hash
// crypt = {crypt} + Standard UNIX DES-encrypted with 2-character salt
//$CONF['authlib_default_flavor'] = 'md5raw';
$CONF['authlib_default_flavor'] = 'SHA';

// Password validation
// New/changed passwords will be validated using all regular expressions in the array.
// If a password doesn't match one of the regular expressions, the corresponding
// error message from $PALANG (see languages/*) will be displayed.
// See http://de3.php.net/manual/en/reference.pcre.pattern.syntax.php for details
// about the regular expression syntax.
// If you need custom error messages, you can add them using $CONF['language_hook'].
// If a $PALANG text contains a %s, you can add its value after the $PALANG key
// (separated with a space).
$CONF['password_validation'] = array(
#    '/regular expression/' => '$PALANG key (optional: + parameter)',
    '/.{8}/'                => 'password_too_short 5',      # minimum length 5 characters
    '/([a-zA-Z].*){1}/'     => 'password_no_characters 1',  # must contain at least 3 characters
    '/([0-9].*){1}/'        => 'password_no_digits 1',      # must contain at least 2 digits
);

// Generate Password
// Generate a random password for a mailbox or admin and display it.
// If you want to automagically generate passwords set this to 'YES'.
$CONF['generate_password'] = 'YES';

// Footer
// Below information will be on all pages.
// If you don't want the footer information to appear set this to 'NO'.
$CONF['show_footer_text'] = 'NO';
$CONF['footer_text'] = 'Return to ';
$CONF['footer_link'] = 'http://';

$CONF['default_aliases'] = array (
  'abuse'      => 'abuse@',
  'hostmaster' => 'hostmaster@',
  'postmaster' => 'postmaster@',
  'webmaster'  => 'webmaster@',
  'ftp'        => 'root',
  'news'       => 'usenet',
  'noc'        => 'root',
  'security'   => 'root',
  'usenet'     => 'root',
  'uucp'       => 'root',
  'www'        => 'webmaster'
);
$CONF['special_alias_control'] = 'YES';

$CONF['domain_quota'] = 'NO';

// When creating mailboxes or aliases, check that the domain-part of the
// address is legal by performing a name server look-up.
$CONF['emailcheck_resolve_domain']='NO';

// If you use a recipient_delimiter in your postfix config, you can also honor it when aliases are checked.
// Example: $CONF['recipient_delimiter'] = "+";
// Set to "" to disable this check.
$CONF['recipient_delimiter'] = "+";

?>