<?php
  require_once $_SERVER["DOCUMENT_ROOT"] . "../php/srkLDAPHelperClass";
  require_once $_SERVER["DOCUMENT_ROOT"] . "../php/srkHelperClass";

  $ldap = new ldaphelper;
  $helper = new helper;
  
  if (!$ldap->Connect()) {
	echo  "LDAP ERROR - " . ldap_error($ldap->ds);
  }
  
  $dn = $_REQUEST['dn'];
  if (ldap_delete($ldap->ds,$dn)) {
	echo "ok";
  }
  else { 
  $this->helper->logit("ldap error with dn=$lkey, arg=$arg",0);
	echo  "LDAP ERROR - " . ldap_error($ldap->ds);
  }
  
  $ldap->Close();

?>
