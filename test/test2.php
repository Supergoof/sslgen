<?php
error_reporting(E_ALL);
ini_set('display_errors', 'On');

$pkconfig = array(
  "private_key_bits" => 2048,
  "digest_alg" => 'des3',
  "private_key_type" => OPENSSL_KEYTYPE_RSA
);

// Create the keypair
$res = openssl_pkey_new($pkconfig);	// Is FALSE if failed else is resource
if ($res !== false) {
  echo "Key Generated OK<br />";
} else {
  echo "Key Generate Failed<br />";
}

// Get public key
$pubkey = openssl_pkey_get_details($res);
$pubkey = $pubkey["key"];

// Get details
$KeyDetails = openssl_pkey_get_details($res);
print "<pre>";
//print_r($KeyDetails);
print "</pre>";
echo "<hr>";


// Export the key to var
if (openssl_pkey_export($res,$privKey)) {
  echo "openssl_pkey_export to \$privKey ok!<br />";
} else {
  echo "openssl_pkey_export to \$privKey failed<br />";
}

$importKeyFromRes = openssl_pkey_get_private($privKey);	// Is FALSE if failed else is privkey
if ($importKeyFromRes !== false) {
  echo "openssl_pkey_get_private from \$privKey OK<br />";
} else {
  echo "openssl_pkey_get_private from \$privKey Failed<br />";
  print_r($importKeyFromRes);
  echo "<br />";
}

// Expoty the key to file
$pkey_file = "test/testpk.pem";
if (openssl_pkey_export_to_file($res,$pkey_file)) {
  echo "openssl_pkey_export_to_file ok!<br />";
} else {
  echo "openssl_pkey_export_to_file failed<br />";
}

$importKeyFromFile = openssl_pkey_get_private($pkey_file); // Is FALSE if failed else is privkey
if ($importKeyFromRes !== false) {
  echo "openssl_pkey_get_private from $pkey_file OK<br />";
} else {
  echo "openssl_pkey_get_private from $pkey_file Failed<br />";
}
    

// now we have a key lets make a CSR and sign it

$dn = array(
    "countryName" => "UK",
    "stateOrProvinceName" => "Somerset",
    "localityName" => "Glastonbury",
    "organizationName" => "The Brain Room Limited",
    "organizationalUnitName" => "PHP Documentation Team",
    "commonName" => "somedomain.net",
    "emailAddress" => "wez@example.com"
);
// The CSR
if ($csr = openssl_csr_new($dn, $privkey)) {
  echo "Generated CSR<br />";
} else {
  echo "Error generating CSR!<br />";
}
if (openssl_csr_export($csr, $csrout)) {
  echo "openssl_csr_export OK! <br />";
} else {
  echo "openssl_csr_export failed!<br />";
}

if (openssl_csr_export_to_file($csr, "test/testcsr.csr")) {
  echo "openssl_csr_export_to_file OK! <br />";
} else {
  echo "openssl_csr_export_to_file failed!<br />";
}

// Sign the CSR and make the cert
if ($sscert = openssl_csr_sign($csr, null, $privkey, 365)) {
  echo "openssl_csr_sign Ok!<br />";
} else {
  echo "openssl_csr_sign Failed!<br />";
}

// Export the x509 cert

if (openssl_x509_export($sscert, $certout)) {
  echo "openssl_x509_export to \$certout Ok!<br />";
} else {
  echo "openssl_x509_export to \$certout Failed!<br />";
}
//echo $certout."<br />";

$cert_file = "test/testcert.crt";
if (openssl_x509_export_to_file($sscert, $cert_file)) {
  echo "openssl_x509_export_to_file $cert_file Ok!<br />";
} else {
  echo "openssl_x509_export_to_file $cert_file Failed!<br />";
}
    

// cleanup
openssl_pkey_free($res);

// DATABASE TEST:

echo "<hr><h3> Now we try the export/import from the DB </h3><br />";
// Open database
require_once("database_conn.php");

// Create the keypair
$dbres = openssl_pkey_new($pkconfig);     // Is FALSE if failed else is resource
if ($dbres !== false) {
  echo "Key Generated OK<br />";
} else {
  echo "Key Generate Failed<br />";
}
// Export the key to a var
if (openssl_pkey_export($dbres,$dbprivKey)) {
  echo "openssl_pkey_export to \$dbprivKey ok!<br />";
} else {
  echo "openssl_pkey_export to \$dbprivKey failed<br />";
}
// Create the CSR and export to var
if ($dbcsr = openssl_csr_new($dn, $dbprivkey)) {
  echo "Generated CSR<br />";
} else {
  echo "Error generating CSR!<br />";
}
if (openssl_csr_export($dbcsr, $dbcsrout)) {
  echo "openssl_csr_export OK! <br />";
} else {
  echo "openssl_csr_export failed!<br />";
}

// Save data in database:
if (!($stmt = $mysqli->prepare("INSERT INTO `certstor` (cn,status,csr,pk,createdate,kayako_ref) VALUES (?, ?, ?, ?, ?, ?)"))) {
  echo "<b>Prepare failed: (" . $mysqli->errno . ") " . $mysqli->error . "</b>";
}
$dbcommonName = "somedomain.net";
$dbcsrStatus = 0;
//$dbcsrout = mysqli_real_escape_string($mysqli,$dbcsrout);
$dbkayakoRef = "XXX-13-66666";
                            
$stmt->bind_param('sissss', $dbcommonName, $dbcsrStatus, $dbcsrout, $dbprivKey, date("Y-m-d H:i:s"), $dbkayakoRef);
//$stmt->execute();
printf("<br />%d Row inserted.\n", $stmt->affected_rows);
printf ("<br />New Record has id %d.\n", $stmt->insert_id);
$last_insert_id = $stmt->insert_id;

// Now get the data back from DB
echo "<br /><b>Get data back from DB</b><br />";
if (!($stmt2 = $mysqli->prepare("SELECT cn,csr,pk FROM `certstor` WHERE `id` = ?"))) {
  echo "<br /><b>Prepare failed: (" . $mysqli->errno . ") " . $mysqli->error . "</b>";
}
$last_insert_id = 54;	// Test variable
if ( !$stmt2->bind_param('i',$last_insert_id) ) {
  echo "<br /><b>bind_param failed: (" . $mysqli->errno . ") " . $mysqli->error . "</b>";
}
if ( !$stmt2->execute() ) {
  echo "<br /><b>execute failed: (" . $mysqli->errno . ") " . $mysqli->error . "</b>";
}

if ( !$stmt2->bind_result($fromdb_cn,$fromdb_csr,$fromdb_pk) ) {
  echo "<br /><b>bind_result failed: (" . $mysqli->errno . ") " . $mysqli->error . "</b>";
}
while ($stmt2->fetch()) {
  echo "<br /><p>" .$fromdb_cn."</p>";
//  echo "<br /><p>" .$fromdb_csr."</p>";
//  echo "<br /><p>" .$fromdb_pk."</p>";
}

$importKeyFromDB = openssl_pkey_get_private($fromdb_pk); // Is FALSE if failed else is privkey
if ($importKeyFromDB !== false) {
  echo "openssl_pkey_get_private from \$fromdb_pk OK<br />";
} else {
  echo "openssl_pkey_get_private from \$fromdb_pk Failed<br />";
  print_r($importKeyFromDB);
echo "<br />";
}

$importCsrFromDBdetails = openssl_pkey_get_details(openssl_csr_get_public_key($fromdb_csr));
//echo "<hr>";
//print_r($importCsrFromDBdetails);
$importKeyFromDBdetails = openssl_pkey_get_details(openssl_pkey_get_private($fromdb_pk));
//echo "<hr>";
//print_r($importKeyFromDBdetails);
echo "Trying to read $cert_file<br />";
if (file_exists($cert_file)) {
  $cert_file_res = openssl_x509_read(file_get_contents($cert_file));
  echo "Success!<br />";
  // print_r(openssl_x509_parse($cert_file_res));
} else {
  echo "ERROR: the file $cert_file don't exist!<br />";
}
$importCertFromFiledetails = openssl_pkey_get_public($cert_file_res);
$importCertFromFiledetails2 = openssl_pkey_get_public($cert_file);

// Check if the CSR, key and cert match
//print_r($importCsrFromDBdetails);
echo "<br /><br />";
echo "importCsrFromDBdetails: " .md5($importCsrFromDBdetails['rsa']['n']);
echo "<br /><br />";
//print_r($importKeyFromDBdetails);
echo "<br /><br />";
echo "importKeyFromDBdetails: " .md5($importKeyFromDBdetails['rsa']['n']);
echo "<br /><br />";
//print_r($importCertFromFiledetails);
echo "<br /><br />";
echo "importCertFromFiledetails: " .md5($importCertFromFiledetails['rsa']['n']);
echo "<br />importCertFromFiledetails2: " .md5($importCertFromFiledetails2['rsa']['n']);
echo "<br /><br />";

$stmt->close();
$stmt2->close();
$mysqli->close();
?>