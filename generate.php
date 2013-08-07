<?php
if (isset($_GET['testdata']) && $_GET['testdata'] == "1") {
  $_POST["countryName"]            = "DK";
  $_POST["stateOrProvinceName"]    = " ";
  $_POST["localityName"]           = "Randers";
  $_POST["organizationName"]       = "DanDomain";
  $_POST["organizationalUnitName"] = "IT";
  $_POST["commonName"]             = "dandomain.dk";
  $_POST["emailAddress"]           = "drift@dandomain.dk";
  $_POST["kayakoRef"]              = "ABC-123-45678";
}

if (!empty($_POST)) {
  $countryName            = trim($_POST["countryName"]);
  $stateOrProvinceName    = trim($_POST["stateOrProvinceName"]);
  $localityName           = trim($_POST["localityName"]);
  $organizationName       = trim($_POST["organizationName"]);
  $organizationalUnitName = trim($_POST["organizationalUnitName"]);
  $commonName             = trim($_POST["commonName"]);
  $emailAddress           = trim($_POST["emailAddress"]);
  $kayakoRef              = trim($_POST["kayakoRef"]);
  
  // Check for empty vars and use space if empty
  if (empty($countryName)) {
    $countryName = " ";
  }
  if (empty($stateOrProvinceName)) {
    $stateOrProvinceName = " ";
  }
  if (empty($localityName)) {
    $localityName = " ";
  }
  if (empty($organizationName)) {
    $organizationName = " ";
  }
  if (empty($organizationalUnitName)) {
    $organizationalUnitName = " ";
  }
  if (empty($commonName)) {
    $commonName = " ";
  }
  if (empty($emailAddress)) {
    $emailAddress = " ";
  }
  if (empty($kayakoRef)) {
    $kayakoRef = " ";
  }
  
  // Open database
  require_once("database_conn.php");
  
  // Populate the array for the CSR
  $dn = array(
    "countryName" => $countryName,
    "stateOrProvinceName" => $stateOrProvinceName,
    "localityName" => $localityName,
    "organizationName" => $organizationName,
    "organizationalUnitName" => $organizationalUnitName,
    "commonName" => $commonName,
    "emailAddress" => $emailAddress
  );
  // var_dump($dn);
  
  //  while ($err = openssl_error_string()) {
  //    /* just consume previous error messags and do nothing */
  //  }
  
  // OpenSSL Configuration vars. http://php.net/manual/en/function.openssl-csr-new.php
  $pkconfig = array(
    "private_key_bits" => 2048,
    "digest_alg" => 'des3',
    //    "encrypt_key" => false,
    "private_key_type" => OPENSSL_KEYTYPE_RSA
  );
  // Generate a new private (and public) key pair
  if (!$res = openssl_pkey_new($pkconfig)) {
    echo "Error generating Private Key\n";
  }
  
  // Generate a certificate signing request
  if (!$csr = openssl_csr_new($dn, $res)) {
    echo "Error generating Certificate Signing Request\n";
  }
  
  // Extract the private key from $res to $privKey
  if (!openssl_pkey_export($res, $privKey)) {
    echo "Error exporting Private Key\n";
  }
  
  if (!openssl_csr_export($csr, $csrout)) {
    echo "Error exporting CSR\n";
  }
  // Show CSR
  echo $csrout . "\n";
  
  // Show the private key
  echo $privKey . "\n";
  
  // Save private key to file
  openssl_pkey_export_to_file($privKey,"./certs/$commonName.key");
  
  // CSR Status 0 = Pending, 1 = Completed
  $csrStatus = 0;
  
  // Save data in database:
  
  if (!($stmt = $mysqli->prepare("INSERT INTO `certstor` (cn,status,csr,pk,createdate,kayako_ref) VALUES (?, ?, ?, ?, ?, ?)"))) {
    echo "<b>Prepare failed: (" . $mysqli->errno . ") " . $mysqli->error . "</b>";
  }
  $stmt->bind_param('sissss', $commonName, $csrStatus, $csrout, $privKey, date("Y-m-d H:i:s"), $kayakoRef);
  if (isset($_GET['noinsert']) && $_GET['noinsert'] == "1") {
    echo "<br /><b>INSERT disabled!</b>";
    }
  else
    {
    $stmt->execute();
    }
  //  printf("<b>%d Row inserted.</b>\n", $stmt->affected_rows);
  
  // debug to show DB contents  
  if (isset($_GET['show']) && $_GET['show'] == "1") {
    $selstmt = $mysqli->prepare("SELECT cn,status,createdate,kayako_ref FROM `certstor`");
    $selstmt->execute();
    $selstmt->bind_result($cn, $stus, $crdate, $kanonr);
    while ($selstmt->fetch()) {
      echo "<br /> $cn $stus $crdate $kanonr";
    }
    $selstmt->close();
    
  }
  
  /* close statement and connection */
  $stmt->close();
  
  // Show any errors that occurred here PS. includes bogus errors so disable it. http://www.php.net/manual/en/function.openssl-error-string.php
  /*  while (($e = openssl_error_string()) !== false) {
  echo $e . "\n";
  }*/
  
  //Close DB
  $mysqli->close();
}

?>
