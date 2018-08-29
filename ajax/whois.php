<?php
/*
 *
 * This code was developped by teicee based on other PHP code already developped by other GLPI and/or domains plugin developpers
 * Last update: 29-AUG-2018
 * Main developper: Philippe Chauvat
 * The license for this code is the same one used for the 'domains' plugin
 *
 * This PHP function requests the whois databases and mainly return dates
 * Caution: The date format is not always clear:
 * - how can we figure out if 08/06/2018 is 06-AUG-2018 or 08-JUN-2018?
 */
if (strpos($_SERVER['PHP_SELF'], "whois.php")) {
    $AJAX_INCLUDE = 1;
    include('../../../inc/includes.php');
    // What is returned is in JSON format.
    header("Content-Type: application/json; charset=UTF-8");
    Html::header_nocache();
}

Session::checkCentralAccess();

/**
 * @param string whoisdate
 * @return JSON object with required fields:
 *         - error value + message
 *         - date_creation and _date_creation
 *         - date_expiration and _date_expiration
 *         - last_update_date and _last_update_date
 * The last values are not always defined and are not displayed into the domains form.
 *
 * Purpose of this function is to return the date with a '-' as separator without any other information than day, month and year
 */
function ws_cleanupdate($whoisdate) {
    $values = explode(':',$whoisdate) ;
    $v = $values[1] ;
    $values = explode('T',$v) ;
    $v = str_replace('/','-',trim($values[0])) ;
    return $v ;
}
/**
/*  * @param array woutput: what whois DB returned */
/*  * @param array wstrings: which patterns are we looking for */
/*  * @return string: the "right part" of the line */
/*  * */
/*  * Purpose of this function is to return the date with a '-' as separator without any other information than day, month and year */
/*  *\/ */
function ws_getinfo($woutput,$wstrings) {
    $rightpart = "" ;
    foreach ($wstrings as $w) {
        if ($lines = preg_grep("/$w/",$woutput)) {
            foreach($lines as $l) {
                $rightpart = strtolower(ws_cleanupdate($l)) ;
            }
        }
    }
    return $rightpart;
}
//
// What is the domain name
$domainname = $_GET['domain'] ;
//
// Error handling
// Positive value => Warnings
$WHOIS_WARN_FORMAT = array('value' => 1, 'msg' => "__('The date format (DD-MM-YYYY) is used by default, no clue to verify it is the right one.')") ;
// Null value = Everything's fine
$WHOIS_OK = array('value' => 0, 'msg' => "__('Everything is fine. Really')") ;
// Negative value => Error or unable to get information.
$WHOIS_ERR_EU_DOMAIN = array( 'value' => -1, 'msg' => "__('EU domain names are not available for request.')") ;
$WHOIS_ERR_WHOIS_REQUEST = array('value' => -2, 'msg' => "__('WHOIS request failed for some reason...')") ;
$WHOIS_ERR_DOMAIN_DOES_NOT_EXIST = array('value' => -3, 'msg' => "__('Domain name does not exist')") ;
$WHOIS_ERR_WHOIS_NOT_INSTALLED = array('value' => -4, 'msg' => "__('It seems whois program is not installed')") ;
//
// Array of returned values
$myreturnvalues = array('error' => $WHOIS_OK) ;
//
// Here are the several strings identified as whois fields depending on which whois DB we are speaking with
//
// Creation time
$createdstrings = array('^created:','^Creation Date','^Registration Time:','Domain Name Commencement Date:') ;
//
// Update time
$updatedstrings = array('^Updated Date:','^last-update:') ;
//
// Expiration time
$expiredstrings = array('^Expiry Date:','^Registrar Registration Expiration Date:','^Expiration Time:','Registry Expiry Date:') ;
//
// What the whois DB will return in case of non existing domain name
$nodomainfound = array("^No match for domain","^%% No entries found in the AFNIC Database.","^NOT FOUND") ;
//
// Some whois DB (fr e.g.) precisely indicates what is the date format.
$formatstrings = array('complete date format') ;

// Domainname must be something like xxx.yy. This is not currently checked.
//
// Checking if domain is '.eu', can not be requested.
// For some reasons EU whois DB does not agree to be requested by programs. Only humans are allowed.
// Did not see any API neither.
if (substr($domainname,-3) == '.eu') {
    $myreturnvalues['error'] = $WHOIS_ERR_EU_DOMAIN ;
}
else {
    $domain_does_not_exist = 0 ;
    // Let's call whois...
    exec('whois ' . $domainname,$woutput,$wresult) ;
    //
    // Check the domain exists
    foreach ($nodomainfound as $w) {
        if ($lines = preg_grep("/$w/",$woutput)) {
            $domain_does_not_exist = 1 ;
        }
    }
    //
    // We already know that .eu domains are _not_ available for automatic request.
    if ($wresult != 0) {
        if ($domain_does_not_exist) {
            $myreturnvalues['error'] = $WHOIS_ERR_DOMAIN_DOES_NOT_EXIST ;
        }
        else if ($wresult == 127) {
            $myreturnvalues['error'] = $WHOIS_ERR_WHOIS_NOT_INSTALLED ;
        }
        else {
            $myreturnvalues['error'] = $WHOIS_ERR_WHOIS_REQUEST ;
        }
    }
    else if ($domain_does_not_exist) {
        $myreturnvalues['error'] = $WHOIS_ERR_DOMAIN_DOES_NOT_EXIST ;
    }
    else {
        /* Trying to figure out the format used for dates */
        /* Some nice servers tells us... */
        $formatok = 0 ;
        $updatedate = 0 ;
        $ret = ws_getinfo($woutput,$formatstrings) ;
        if ($ret != "") $f = $ret ;
        //
        // Looking for domain creation date. At least for this registrar.
        $ret = ws_getinfo($woutput,$createdstrings) ;
        if ($ret != "") $c = $ret ;
        //
        // Looking for last update.
        // This value is actually _not_ used. It's there if one want it anyway.
        $ret = ws_getinfo($woutput,$updatedstrings) ;
        if ($ret != "") {
            $u = $ret ;
            $updatedate = 1 ;
        }
        //
        // Looking for the expiration date. This one is the one everybody should be interested in.
        $ret = ws_getinfo($woutput,$expiredstrings) ;
        if ($ret != "") $e = $ret ;
        //
        // We did not get a format. Let's try to find out what it could be
        if (!$formatok) {
            $values = explode('-',$c) ;
            // The year is in first place. Our bet is YYYY-MM-DD
            if (strlen($values[0]) > 2) {
                $f = 'yyyy-mm-dd' ;
            }
            // First piece is over 12, then it's a day...
            // and format is DD-MM-YYYY because no one uses DD-YYYY-MM
            else if ($values[0] > 12) {
                $f = 'dd-mm-yyyy' ;
            }
            //
            // Second piece is over 12, then it's a day
            // and the format is MM-DD-YYYY because no one uses MM-YYYY-DD
            else if ($values[1] > 12) {
                $f = 'mm-dd-yyyy' ;
            }
            else {
                // I could not see how math would help
                // Let's decide it is the common european notation
                // Yes, it's a bit selfish
                $f = 'dd-mm-yyyy' ;
                $myreturnvalues['error'] = $WHOIS_WARN_FORMAT ;
            }
        }
        // The format is established
        // Let's find the corresponding values now...
        $daypos = strpos($f,'dd') ;
        $monthpos = strpos($f,'mm') ;
        $yearpos = strpos($f,'yyyy') ;
        // ... for creation date
        $cday = substr($c,$daypos,2) ;
        $cmonth = substr($c,$monthpos,2) ;
        $cyear =  substr($c,$yearpos,4) ;
        // ... for expiration date
        $eday = substr($e,$daypos,2) ;
        $emonth = substr($e,$monthpos,2) ;
        $eyear =  substr($e,$yearpos,4) ;
        // ... for last update date
        if ($updatedate) {
            $uday = substr($u,$daypos,2) ;
            $umonth = substr($u,$monthpos,2) ;
            $uyear =  substr($u,$yearpos,4) ;
        }
        //
        // Let's check how GLPI user decided to display dates
        // a) No decision => american way
        if (!isset($_SESSION["glpidate_format"])) {
            $_SESSION["glpidate_format"] = 0;
            
        }
        // b) A decision, let's get it!
        else {
            $format = $_SESSION["glpidate_format"];
        }
        switch ($format) {
            // American way
        case 0:
            $c = implode("-",array($cyear,$cmonth,$cday)) ;
            $e = implode("-",array($eyear,$emonth,$eday)) ;
            if ($updatedate) $u = implode("-",array($uyear,$umonth,$uday)) ;
            break ;
            // European way
        case 1:
            $c = implode("-",array($cday,$cmonth,$cyear)) ;
            $e = implode("-",array($eday,$emonth,$eyear)) ;
            if ($updatedate) $u = implode('-',array($uday,$umonth,$uyear)) ;
            break ;
            // UK? way
        case 2:
            $c = implode("-",array($cmonth,$cday,$cyear)) ;
            $e = implode("-",array($emonth,$eday,$eyear)) ;
            if ($updatedate) $u = implode("-",array($umonth,$uday,$uyear)) ;
            break ;
        }
        $myreturnvalues['_date_creation'] = $c ;
        $myreturnvalues['_date_expiration'] = $e ;
        // Hidden fields are always stored with the american format
        $myreturnvalues['date_creation'] = implode("-",array($cyear,$cmonth,$cday)) ;
        $myreturnvalues['date_expiration'] = implode("-",array($eyear,$emonth,$eday)) ;
        if ($updatedate) {
            $myreturnvalues['_last_update_date'] = $u ; // Yes I know it's a disruption with the rest of the naming, I've seen in GIT updates fields were renamed like 'creation_date' became 'date_creation'...
            $myreturnvalues['last_update_date'] = implode("-",array($uyear,$umonth,$uday)) ;
        }
    }
}
echo json_encode($myreturnvalues) ;
