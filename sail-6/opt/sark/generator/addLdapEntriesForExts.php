<?php
/**
 * addLdapEntriesForDDIs
 * read through the extensions in ipphone table and attempt to create an LDAP directory object for each.
 */

 include("/opt/sark/php/srkLDAPHelperClass");
 include("/opt/sark/php/srkHelperClass");
 include("/opt/sark/php/srkDbClass");

$helper = new helper;
$ldap = new ldaphelper;
$dbh = DB::getInstance();

if (!$ldap->Connect()) {
	$helper->logIt("LDAP ERROR 19 - " . ldap_error($ldap->ds));
    exit;
}


$ldapargs = Array();
$ldap->addressbook = "ou=default" . ",ou=" . $ldap->baseou;

/**
 * Fetch all of the extension rows
 */
$rows = $helper->getTable("ipphone");

foreach ($rows as $row ) {  

    if (! isset($row['desc'])) {
        echo "No desc";
        continue;
    }
    /**
     * remove the word farm if present and trim the result 
     */
    $trimdesc = preg_replace ("/[Ff]arm/","",$row['desc']);
    $trimdesc = trim($trimdesc);

    if (!preg_match ("/FAX$/",$trimdesc)) {
        if (!preg_match ("/Alarm$/",$trimdesc)) {
            $trimdesc .= " Farm";
        }
    }
    
    $ldapargs["sn"] = $trimdesc;
	$ldapargs["cn"] = $trimdesc;
    $ldapargs["o"] = $row['cluster'];
	$ldapargs["telephonenumber"] = $row['pkey'];
/**
 * Check lineio for a matching DiD
 */
	$sql = $dbh->prepare("select pkey from lineio where openroute=?");
	$sql->execute(array($row['pkey']));
	$did = $sql->fetchColumn();	
    if ($did) {
        echo "found did $did \n";
        $ldapargs["homePhone"] = $did; 
    }
    else {
        echo "No DiD found for " . $row['pkey'] . "\n";
    }

/**
 * do something
 */	

    $search_arg = array("telephoneNumber", "cn");
    $filter = 'cn=' . $row['pkey'];
	$result = $ldap->Search($filter,$search_arg);
    if ($result['count']) {
        var_dump ($result);
        echo "duplicate " . $row['pkey'] . " already exists\n";
        unset ($ldapargs);
        continue;
    }
    $ldapargs["objectclass"] = array('top', 'person', 'organizationalPerson', 'inetOrgPerson');
		$message = $ldap->Add($ldapargs);
    echo "Added row... " . $row['pkey'] . " $message \n";
//    var_dump ($ldapargs);

    unset ($ldapargs);

}




$ldap->Close();
exit;