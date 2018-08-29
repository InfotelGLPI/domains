<?php
/*
 * @version $Id: HEADER 15930 2011-10-30 15:47:55Z tsmr $
 -------------------------------------------------------------------------
 domains plugin for GLPI
 Copyright (C) 2009-2018 by the domains Development Team and teicee team

 https://github.com/InfotelGLPI/domains: original code
 https://github.com/teicee/domains: fork is there. It is only intended to make pull requests
 -------------------------------------------------------------------------

 LICENSE
      
 This file is part of domains.

 domains is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 domains is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with domains. If not, see <http://www.gnu.org/licenses/>.
 --------------------------------------------------------------------------
 */
if (strpos($_SERVER['PHP_SELF'], "whois.php")) {
   $AJAX_INCLUDE = 1;
   include('../../../inc/includes.php');
   header("Content-Type: application/json; charset=UTF-8");
   Html::header_nocache();
}

Session::checkCentralAccess();

/**
 * @param string whoisdate
 * @return string: dd-mm-yyyy or yyyy-mm-dd or whatever comes with whois :(
 */
function cleanupwhoisdate($whoisdate) {
    $values = explode(':',$whoisdate) ;
    $v = $values[1] ;
    $values = explode('T',$v) ;
    $v = str_replace('/','-',trim($values[0])) ;
    return $v ;
}
$domainname = $_GET['domain'] ;

//
// Error handling
// Positive value => Warnings
// Null value = Everything's fine
// Negative value => Error or unable to get information.
$WHOIS_ERR_EU_DOMAIN = array( 'value' => -1, 'msg' => "_('EU domain names are not available for request.')") ;
$WHOIS_ERR_WHOIS_REQUEST = array('value' => -2, 'msg' => "_('WHOIS request failed for some reason...')") ;
$WHOIS_ERR_DOMAIN_DOES_NOT_EXIST = array('value' => -3, 'msg' => $domainname . ": " . "_(': Domain name does not exist')") ;
$WHOIS_OK = array('value' => 0, 'msg' => "_('Everything is fine. Really')") ;
$WHOIS_WARN_FORMAT = array('value' => 1, 'msg' => "_('The date format (DD-MM-YYYY) is used by default, no clue to verify it is the right one.')") ;

$myreturnvalues = array('error' => $WHOIS_OK) ;

$createdstrings = array('^created:','^Creation Date','^Registration Time:','Domain Name Commencement Date:') ;
$updatedstrings = array('^Updated Date:','^last-update:') ;
$expiratedstrings = array('^Expiry Date:','^Registrar Registration Expiration Date:','^Expiration Time:','Registry Expiry Date:') ;
$nodomainfound = array("^No match for domain","^%% No entries found in the AFNIC Database.","^NOT FOUND") ;
$formatstrings = array('complete date format') ;

// Domainname must be something like xxx.yy
// This is not currently checked.
// Checking if domain is '.eu', can not be requested.
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
        $myreturnvalues['error'] = $WHOIS_ERR_WHOIS_REQUEST ;
        if ($domain_does_not_exist) $myreturnvalues['error'] = $WHOIS_ERR_DOMAIN_DOES_NOT_EXIST ;
    }
    else if ($domain_does_not_exist) {
        $myreturnvalues['error'] = $WHOIS_ERR_DOMAIN_DOES_NOT_EXIST ;
    }
    else {

        /* Trying to figure out the format used for dates */
        /* Some nice servers tells us... */
        $formatok = 0 ;
        $updatedate = 0 ;
        foreach ($formatstrings as $w) {
            if ($lines = preg_grep("/$w/",$woutput)) {
                foreach($lines as $l) {
                    $f = strtolower(cleanupwhoisdate($l)) ;
                }
            }
        }
        // Looking for domain creation date. At least for this registrar.
        foreach ($createdstrings as $w) {
            if ($lines = preg_grep("/$w/",$woutput)) {
                foreach($lines as $l) {
                    $c = cleanupwhoisdate($l) ;
                }
            }
        }
        // Looking for last update.
        // This value is actually _not_ used. It's there if one want it anyway.
        foreach ($updatedstrings as $w) {
            if ($lines = preg_grep("/$w/",$woutput)) {
                foreach($lines as $l) {
                    $u = cleanupwhoisdate($l) ;
                }
            }
            $updatedate = 1 ;
        }
        // Looking for the expiration date. This one is the one everybody should be interested in.
        foreach ($expiratedstrings as $w) {
            if ($lines = preg_grep("/$w/",$woutput)) {
                foreach($lines as $l) {
                    $e = cleanupwhoisdate($l) ;
                }
            }
        }
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
                // There I could not see how math would help
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
            $myreturnvalues['_last_update_date'] = $u ; // Yes I know it's a disruption with the rest of the naming, but really guys? date_creation? This is a bit too frenchy, aint it?
            $myreturnvalues['last_update_date'] = implode("-",array($uyear,$umonth,$uday)) ;
        }
    }
}
echo json_encode($myreturnvalues) ;
