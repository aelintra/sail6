<?php
  require_once $_SERVER["DOCUMENT_ROOT"] . "../php/srkLDAPHelperClass";
  require_once $_SERVER["DOCUMENT_ROOT"] . "../php/srkHelperClass";

  $ldap = new ldaphelper;
  
  if (!$ldap->Connect()) {
	echo  "LDAP ERROR - " . ldap_error($ldap->ds);
  }
  
  $dn = $_REQUEST['id'];
  if ( ! ldap_delete($ldap->ds,$dn)) {
    echo  "LDAP ERROR - " . ldap_error($ldap->ds);
  }
  else { 
    echo "ok";
  }
  
  $ldap->Close();

?>
