<?php
$certfile = 'test/server.crt';
$csrfile = 'test/server.csr';
$keyfile = 'test/server.key';
if (file_exists($certfile) && file_exists($csrfile) && file_exists($keyfile)) {
  $testcert = file_get_contents($certfile);
  $testcsr = file_get_contents($csrfile);
  $testkey = file_get_contents($keyfile);
  verify($testcert, $testcsr, $testkey);
} else {
  echo "a file is missing!<br />";
}


function verify($cert, $csr, $key)
{
  echo "<pre>";
  // Extract the data we need
  
  // the cert
  $cert_get_details = openssl_pkey_get_details(openssl_pkey_get_public($cert));
    if ($cert_get_details !== false) {
    //echo "openssl_pkey_get_details on Cert OK<br />";
    //print_r($cert_get_details);
  } else {
    echo "openssl_pkey_get_details on Cert Failed<br />";
    print_r($cert_get_details);
    echo "<br />";
  }

  
  // the csr
  $csr_get_details = openssl_pkey_get_details(openssl_csr_get_public_key($csr));
  if ($csr_get_details !== false) {
    //echo "openssl_pkey_get_details on CSR OK<br />";
    //print_r($csr_get_details);
  } else {
    echo "openssl_pkey_get_details on CSR Failed<br />";
    print_r($csr_get_details);
    echo "<br />";
  }
  
  // the key
  $key_priv = openssl_pkey_get_private($key);
  if ($key_priv !== false) {
    //echo "openssl_pkey_get_private on KEY OK<br />";
    //print_r($key_priv);
    $key_get_details = openssl_pkey_get_details($key_priv); // Is FALSE if failed else is privkey
    if ($key_get_details !== false) {
        //echo "openssl_pkey_get_details on KEY OK<br />";
        //print_r($key_get_details);
    } else {
        echo "openssl_pkey_get_details on KEY Failed<br />";
        print_r($key_get_details);
        echo "<br />";
    }
  } else {
    echo "openssl_pkey_get_private on KEY Failed<br />";
    print_r($key_priv);
    echo "<br />";
  }
  $md5cert = md5($cert_get_details['rsa']['n']);
  $md5csr = md5($csr_get_details['rsa']['n']);
  $md5key = md5($key_get_details['rsa']['n']);
  
  echo "</pre>";
  /*
  echo "<b>md5 sums:</b><br />";
  echo "Cert: ".md5($cert_get_details['rsa']['n'])."<br />";
  echo "CSR: ".md5($csr_get_details['rsa']['n'])."<br />";
  echo "KEY: ".md5($key_get_details['rsa']['n'])."<br />";
  */
  if (($md5cert == $md5csr) && ($md5csr == $md5key)) {
    echo "Cert, Key and CSR match!<br />";
  } else {
    echo "ERROR: Cert, Key and CSR matching failed!<br />";
    echo "Cert: ".$md5cert."<br />";
    echo "CSR: ".$md5csr."<br />";
    echo "KEY: ".$md5key."<br />";
  }
}


?>
