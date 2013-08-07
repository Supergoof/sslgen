<?php
$testcert = file_get_contents("./certs/star.dandomain.dk.crt");
display_crt_info($testcert);

function display_crt_info( $crt )       // display some certificate info
{
  if ( $data = openssl_x509_parse($crt) ) {
    echo "<pre>";
    //print_r($data);
    echo "</pre><br /><br />";
    //subject
    $C = ( isset($data['subject']['C']) ? $data['subject']['C'] : "Commonname not set" );
    $ST = ( isset($data['subject']['ST']) ? $data['subject']['ST'] : "State not set" );
    $L = $data['subject']['L'];
    $O = $data['subject']['O'];
    $OU = $data['subject']['OU'];
    $CN = $data['subject']['CN'];
    $emailAddress = ( isset($data['subject']['emailAddress']) ? $data['subject']['emailAddress'] : "Subject emailaddress not set" );
    //issuer
    $iC = $data['issuer']['C'];
    $iST = $data['issuer']['ST'];
    $iL = $data['issuer']['L'];
    $iO = $data['issuer']['O'];
    $iOU = ( isset($data['issuer']['OU']) ? $data['issuer']['OU'] : "Issuer OU not set" );
    $iCN = ( isset($data['issuer']['CN']) ? $data['issuer']['CN'] : "issuer CN not set" );
    $iemailAddress = ( isset($data['issuer']['emailAddress']) ? $data['issuer']['emailAddress'] : "Issuer emailaddress not set" );
    // the rest
    $version = $data['version'];
    $serial = $data['serialNumber'];
    $validFrom = date('Y-m-d H:i:s', $data['validFrom_time_t']);
    $validTo = date('Y-m-d H:i:s', $data['validTo_time_t']);
    $altnames = ( isset($data["extensions"]["subjectAltName"]) ? $data["extensions"]["subjectAltName"] : "None" );
    echo "<br />----------Cert Info-------------------------<br />";
    echo "CommonName: " . $CN . "<br />";
    echo "Country: " . $C . "<br />";
    echo "State: " . $ST . "<br />";
    echo "Locality: " . $L . "<br />";
    echo "Organization: " . $O . "<br />";
    echo "Org. Unit: " . $OU . "<br />";
    echo "Email Address: " . $emailAddress . "<br />";
    echo "Subject Alternative Names: " . $altnames . "<br />";
    echo "Valid From: " .$validFrom . "<br />";
    echo "Valid To: " . $validTo . "<br />";
    echo "----------Issuer---------------------------<br />";
    echo "Issuer Organization: " . $iO . "<br />";
    echo "Issuer Commonname: " . $iCN . "<br />";
    echo "Issuer Country: " . $iC . "<br />";
    echo "<hr>";
  } else {
    echo "ERROR. could not parse cert <br /><pre>".$crt."</pre>";
  }
}
?>