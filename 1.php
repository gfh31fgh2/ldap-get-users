<?php

$ldap_host    = "xx.yyy.cloud";
$ldap_port    = "389";

$ldap_dn[] = "OU=Users,OU=AD,DC=AD,DC=YYY,DC=CLOUD";
        
$ldap_usr_dom = "@xx.yyy.cloud";

// connect to active directory
$ldap         = ldap_connect($ldap_host, $ldap_port);
$ldap_id[]    = $ldap;

$username     = "admin";
$password     = "xxxx";

// verify user and password
if ($bind = @ldap_bind($ldap, $username . $ldap_usr_dom, $password)) {
	$filter = "cn=*";

	$result = ldap_search($ldap_id, $ldap_dn, $filter) or exit("Unable to search LDAP server");

    foreach ($result as $value) {
        if (ldap_count_entries($ldap, $value) > 0) {
            $search = $value;
            break;
        }
    }

    if ($search) {
        $entries = ldap_get_entries($ldap, $search);
        for ($x = 0; $x < $entries['count']; $x++) {
            if (!empty($entries[$x]['cn'][0])) {
                if ( !empty($entries[$x]['userprincipalname'][0]) ) {
                    $exp = trim($entries[$x]['samaccountname'][0]) ?? '';
                    if ( isset($entries[$x]['mail'][0]) ) {
                        $ml = trim($entries[$x]['mail'][0]) ?? '';
                    } else {
                        $ml = 'empty';
                    }
                    
                    if (trim($entries[$x]['cn'][0]) !== 'admin') {
                        $ad_users[] = array(
                            'fullname' => trim($entries[$x]['cn'][0]),
                            'exp' => $exp,
                            'userprincipalname' => trim($entries[$x]['userprincipalname'][0]),
                            'mail' => $ml
                        );
                    }
                }
                // print_r($ad_users);
            }
        }
    }

    // $ad = json_encode($ad_users);
    // echo ($ad);

    ldap_unbind($ldap); // Clean up after ourselves.
}

$txt = '';
foreach ($ad_users as $k1 => $v1) {
    $txt = $txt . "php passwords.php reset-password $v1['mail'] $v1['exp']\n";
}

$file = './commands_for_reset_pw.sh';
file_put_contents($file, $txt);

//var_dump($entries);
?>