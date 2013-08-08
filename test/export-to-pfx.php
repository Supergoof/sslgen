<?php
$testcert = file_get_contents("./certs/star.dandomain.dk.crt");
$testkey = file_get_contents("./certs/star.dandomain.dk.key");
$ca_bundle = file_get_contents("./certs/ca-bundle.pem");
$pass = "afjleriVar4";
$array = array(
  "extracerts" => $ca_bundle,
  "friendly_name" => "dandomain.dk wildcard",
);
openssl_pkcs12_export_to_file($testcert, "star_dandomain_dk.pfx", $testkey, $pass, $array);
?>