<?php
// http://php.net/manual/en/features.file-upload.errors.php
$error_types = array( 
	0=>"There is no error, the file uploaded with success", 
	1=>"The uploaded file exceeds the upload_max_filesize directive in php.ini", 
	2=>"The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form",
	3=>"The uploaded file was only partially uploaded", 
	4=>"No file was uploaded", 
	6=>"Missing a temporary folder"
); 
/* echo "GET: <pre><br />";
print_r($_GET);
echo "</pre><br />POST: <pre><br />";
print_r($_POST);
echo "</pre><br /><hr />"; */

if (isset($_FILES["zip_file"]) && ($_FILES["zip_file"]["error"] == "0") && isset($_POST["pendingCertID"])) {
	$allowedExts    = array(
		"zip"
	);
	$accepted_types = array(
	        'application/zip',
	        'application/x-zip-compressed',
	        'multipart/x-zip',
	        'application/x-compressed'
	);
	$temp           	= explode(".", $_FILES["zip_file"]["name"]);
	$extension      	= end($temp);
	$prefix			= $temp[0];
	$name			= $_FILES["zip_file"]["name"];
	$bogus_type    		= $_FILES["zip_file"]["type"];
	$size          		= $_FILES["zip_file"]["size"];
	$tmp_file		= $_FILES["zip_file"]["tmp_name"];
	$max_size     		= 200000;
	$target_path   		= "ssl-certs/";
	$template		= "vhost-template";
	$template_out_path	= "create-vhost";
	$pendingCertID		= $_POST["pendingCertID"];

	echo "<pre>";
	echo "PendingCertID: " .$_POST["pendingCertID"]. "<br />";
	echo "Create vhost: " .$_POST["createvhost"]. "<br />";
	echo "Upload: " . $_FILES["zip_file"]["name"] . "<br>";
	echo "Browser sent file as: " . $_FILES["zip_file"]["type"] . "<br>";
	echo "Size: " . ($_FILES["zip_file"]["size"] / 1024) . " kB<br>";
	echo "Temp file: " . $_FILES["zip_file"]["tmp_name"] . "<br>";
	// now we find real mimetype as $_FILES["zip_file"]["type"] trusts the browser that sends the file.
	$finfo = finfo_open(FILEINFO_MIME_TYPE);
	$real_type = finfo_file($finfo, $_FILES["zip_file"]["tmp_name"]);
	finfo_close($finfo);
	echo "File MIME type: " . $real_type . "<br />";

	// now we have the file info check the file for further processing
	if (in_array($real_type, $accepted_types) && ($size < $max_size) && in_array($extension, $allowedExts)) {
		echo "Hooray, the file passed mime type, size and ext validation!<br />";
		if (file_exists($target_path . $name)) {
		        echo $name . " already exists. <br />";
		} else {
		        if (move_uploaded_file($tmp_file, $target_path . $name)) {
		                echo "Stored in: " . $target_path . $name . "<br />";
                                // Lets unzip this sucker
                                $za = new ZipArchive();
                                $za->open($target_path.$name, ZipArchive::CHECKCONS);
                                if ($za == TRUE) {
                                        echo "Zipfile opened<br />";
                                        echo "Zipfile contains ".$za->numFiles." files<br />";
                                        for($i = 0; $i < $za->numFiles; $i++) {   
                                                echo '   '.$za->getNameIndex($i) . '<br />';
                                                // Guess the certificate file
                                                if ($za->getNameIndex($i) == $prefix.".cer") {
                                                        $certfile = $za->getNameIndex($i);
                                                        echo "Guessing that the cert file is: " . $certfile . "<br />";
                                                } elseif ($za->getNameIndex($i) == $prefix.".crt") {
                                                        $certfile = $za->getNameIndex($i);
                                                        echo "Guessing that the cert file is: " . $certfile . "<br />";
                                                } else {
                                                        echo "Could not guess the cert filename!<br />Tried ".$prefix.".cer and ".$prefix.".crt<br />";
                                                }
                                        }
                                        if (file_exists($target_path.$prefix)) {
                                                echo "Folder ".$target_path.$prefix." exists, unzip cancelled!<br />";
                                                //echo "Removing Zipfile: ".$name."<br />";
                                                //unlink($target_path.$name);                                        
                                        } else {
                                                echo "Extracting to ".$target_path.$prefix."<br />";
                                                $za->extractTo($target_path.$prefix);
                                                echo "Closing zipfile<br />";
                                                $za->close();
                                                echo "Removing Zipfile: ".$name."<br /><hr>";
                                                unlink($target_path.$name);
                                                // Verify that the uploaded cert and the chosen CSR and key matches
                                                // Open database
                                                require_once("database_conn.php");
                                                // Get CSR and key
                                                list($csr, $key) = get_csr_and_pk_from_db( $mysqli, $pendingCertID );
                                                // Verify Certificate against CSR
                                                if (file_exists($target_path.$prefix."/".$certfile)) {
                                                        $cert = file_get_contents($target_path.$prefix."/".$certfile);
                                                        //print_r($cert);
                                                        // Verify the certificate
                                                        verify($cert, $csr, $key);
                                                        // Print some certificate info
                                                        display_crt_info($cert);
                                                        //Now the three match save the zip id DB
                                                        package_cert($mysqli, $pendingCertID, $target_path, $certfile);
                                                        // Cleanup as we have what we need now.
                                                        $mysqli->close();
                                                } else {
                                                        echo "<br />The Certificate in file: " .$target_path.$prefix."/".$certfile. " does not exist or could not be read!<br />";
                                                }
                                        }
                                } else {
                                        echo "Error opening: ".$target_path.$name."<br />";
                                        print_r($za);
                                }
                                unset($za);
                        } else {
                                echo "Moving file: " . $tmp_file . " to ". $target_path . $name ." failed!<br />";
                        }
                }
	}
	// Now we have a valid cert, we check if we should create a vhost for it on this server
	if (isset($_POST['createvhost']) && $_POST['createvhost'] == 'yes') 
	{
	  echo "Create vhost";
	  // Create(prepare) the vhost from the template
	  $sslproxy_url = "http://hyltvejdk.bsd03.dandomain.dk"; //testdata
	  if (!create_vhost($prefix,$template,$target_path,$template_out_path, $sslproxy_url)) {
	    echo "<br />ERROR: creating vhost!";
	  }
	  
        } else {
          echo "Do not create vhost.";
        }

        echo "</pre>";
        
} else {
        echo "An error occured!<br />";
        if (isset($_FILES["zip_file"])) {
          $error_message = $error_types[$_FILES["zip_file"]["error"]];
          echo "<pre>".$error_message."</pre>";
        } else {
          echo "<pre>No file recieved!</pre>";
        }
        if (!isset($_POST["pendingCertID"])) {
          echo "<pre>No pending cert chosen</pre>";
        }
}

////////////////////// Functions Below  //////////////////////////////////////

function verify($cert, $csr, $key)	// verify modulus md5 of cert, key and csr. Returns true or false depending on result.
{
  echo "<pre>";
  // Extract the data we need

  // the cert
  $cert_get_details = openssl_pkey_get_details(openssl_pkey_get_public($cert));
  if ($cert_get_details !== false) {
    $md5cert = md5($cert_get_details['rsa']['n']);
    //echo "openssl_pkey_get_details on Cert OK<br />";
    //print_r($cert_get_details);
  } else {
    echo "openssl_pkey_get_details on Cert Failed<br />";
    print_r($cert_get_details);
    echo "<br />";
    return false;
  }


  // the csr
  $csr_get_details = openssl_pkey_get_details(openssl_csr_get_public_key($csr));
  if ($csr_get_details !== false) {
    $md5csr = md5($csr_get_details['rsa']['n']);
    //echo "openssl_pkey_get_details on CSR OK<br />";
    //print_r($csr_get_details);
  } else {
    echo "openssl_pkey_get_details on CSR Failed<br />";
    print_r($csr_get_details);
    echo "<br />";
    return false;
  }

  // the key
  $key_priv = openssl_pkey_get_private($key);
  if ($key_priv !== false) {
    
    //echo "openssl_pkey_get_private on KEY OK<br />";
    //print_r($key_priv);
    $key_get_details = openssl_pkey_get_details($key_priv); // Is FALSE if failed else is privkey
    if ($key_get_details !== false) {
      $md5key = md5($key_get_details['rsa']['n']);
      //echo "openssl_pkey_get_details on KEY OK<br />";
      //print_r($key_get_details);
    } else {
      echo "openssl_pkey_get_details on KEY Failed<br />";
      print_r($key_get_details);
      echo "<br />";
      return false;
    }
  } else {
    echo "openssl_pkey_get_private on KEY Failed<br />";
    print_r($key_priv);
    echo "<br />";
    return false;
  }

  echo "</pre>";
  /*
  echo "<b>md5 sums:</b><br />";
  echo "Cert: ".md5($cert_get_details['rsa']['n'])."<br />";
  echo "CSR: ".md5($csr_get_details['rsa']['n'])."<br />";
  echo "KEY: ".md5($key_get_details['rsa']['n'])."<br />";
  */
  if (($md5cert == $md5csr) && ($md5csr == $md5key)) {
    echo "Cert, Key and CSR match!<br />";
    return true;
  } else {
    echo "ERROR: Cert, Key and CSR matching failed!<br />";
    $md5cert = (isset($md5cert) ? $md5cert : "Not Set");
    echo "Cert: ".$md5cert."<br />";
    $md5csr = (isset($md5csr) ? $md5csr : "Not Set");
    echo "CSR: ".$md5csr."<br />";
    $md5key = (isset($md5key) ? $md5key : "Not Set");
    echo "KEY: ".$md5key."<br />";
    // update the uploaddate in the DB as we have a good cert.						TO BE DONE!!!!!
    return false;
  }
}

function get_csr_and_pk_from_db( &$dbconn, $id )	// get the csr and pk from db connection $dbconn entry with id = $id. Returns array with (csr,pk)
{
  echo "<br /><b>Get data back from DB</b><br />";
  if (!($stmt = $dbconn->prepare("SELECT cn,csr,pk FROM `certstor` WHERE `id` = ?"))) {
    echo "<br /><b>Prepare failed: (" . $dbconn->errno . ") " . $dbconn->error . "</b>";
  }	

  if ( !$stmt->bind_param('i',$id) ) {
    echo "<br /><b>bind_param failed: (" . $dbconn->errno . ") " . $dbconn->error . "</b>";
  }
  
  if ( !$stmt->execute() ) {
    echo "<br /><b>execute failed: (" . $dbconn->errno . ") " . $dbconn->error . "</b>";
  }
    
  if ( !$stmt->bind_result($fromdb_cn,$fromdb_csr,$fromdb_pk) ) {
    echo "<br /><b>bind_result failed: (" . $dbconn->errno . ") " . $dbconn->error . "</b>";
  }
  
  while ($stmt->fetch()) {
   /* echo "<br /><p>" .$fromdb_cn."</p>";
    echo "<br /><p>" .$fromdb_csr."</p>";
    echo "<br /><p>" .$fromdb_pk."</p>"; */
  }
  $stmt->close();
  return array($fromdb_csr, $fromdb_pk);
}

function display_crt_info( $crt ) 	// display some certificate info
{
  if ( $data = openssl_x509_parse($crt) ) {
    // print_r($data);
    //subject
    $C = $data['subject']['C'];
    $ST = $data['subject']['ST'];
    $L = $data['subject']['L'];
    $O = $data['subject']['O'];
    $OU = $data['subject']['OU'];
    $CN = $data['subject']['CN'];    
    $emailAddress = $data['subject']['emailAddress'];
    //issuer
    $iC = $data['issuer']['C'];
    $iST = $data['issuer']['ST'];
    $iL = $data['issuer']['L'];
    $iO = $data['issuer']['O'];
    $iOU = $data['issuer']['OU'];
    $iCN = $data['issuer']['CN'];
    $iemailAddress = $data['issuer']['emailAddress'];
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
    echo "Issuer Country: " . $iC . "<br />";
    echo "<hr>";
  }
}
function package_cert(&$dbconn, $id, $root_path, $cert_file_name)	// puts all the parts of the cert into ssl-certs/domain_dk folder. Zips it all and password protects the zip. 
{									// then it inserts the DL url in the DB and inserts the upload_date in the DB
  echo "<br /><hr><b>Pack the cert for download</b>";
  // Get data from DB
  if (!($stmt = $dbconn->prepare("SELECT cn,csr,pk FROM `certstor` WHERE `id` = ?"))) {
    echo "<br /><b>Prepare failed: (" . $dbconn->errno . ") " . $dbconn->error . "</b>";
    return false;
  }

  if ( !$stmt->bind_param('i',$id) ) {
    echo "<br /><b>bind_param failed: (" . $dbconn->errno . ") " . $dbconn->error . "</b>";
    $stmt->close();
    return false;
  }

  if ( !$stmt->execute() ) {
    echo "<br /><b>execute failed: (" . $dbconn->errno . ") " . $dbconn->error . "</b>";
    $stmt->close();
    return false;
  }

  if ( !$stmt->bind_result($fromdb_cn,$fromdb_csr,$fromdb_pk) ) {
    echo "<br /><b>bind_result failed: (" . $dbconn->errno . ") " . $dbconn->error . "</b>";
    $stmt->close();
    return false;
  }

  while ($stmt->fetch()) {
    echo "<br />Packaging cert for " .$fromdb_cn."";
    // echo "<br />" .$fromdb_csr."";
    // echo "<br />" .$fromdb_pk."";
  }
  $stmt->close();
  
  // Verify that we have the cert in the expected path
  $cn_underscored = str_replace(".","_",$fromdb_cn);
  $zip_path = $root_path . $cn_underscored . "/";
  //echo "<br />zip path: " . $zip_path ;
  if ( !file_exists($zip_path.$cert_file_name) ) {
    echo "<br />ERROR: cound not find Certificate at: " . $zip_path.$cert_file_name ;
    return false;
  } else {
    echo "<br />Found Cert :-)";
    // put key in folder
    if ( !openssl_pkey_export_to_file($fromdb_pk, $zip_path.$cn_underscored.".key") ) {
      echo "<br />ERROR: failed to export key to file: " . $zip_path.$cn_underscored.".key";
      return false;
    }
    
    // Zip the files
    $zip_file_name = $cn_underscored . ".zip";
    if ( !zip_files_in_folder($zip_path, $zip_file_name) ) {
      echo "<br />ERROR Zipping files!";
    } else {
      // Update uoloaddate and zipfileurl in DB
      $baseurl = "http" . (($_SERVER['SERVER_PORT']==443) ? "s://" : "://") . $_SERVER['HTTP_HOST'];
      $uploaddate = date("Y-m-d H:i:s");
      $zipfileurl = $zip_path.$zip_file_name;
      echo "<br /><a href=\"".$baseurl."/".$zip_path.$zip_file_name."\">".$baseurl."/".$zip_path.$zip_file_name."</a>";
      
      if (!($stmt = $dbconn->prepare("UPDATE `certstor` SET `uploaddate`= ?,`zipfileurl`=? WHERE `id` = ?"))) {
        echo "<br /><b>Prepare failed: (" . $dbconn->errno . ") " . $dbconn->error . "</b>";
        return false;
      }

      if ( !$stmt->bind_param('ssi',$uploaddate, $zipfileurl, $id) ) {
        echo "<br /><b>bind_param failed: (" . $dbconn->errno . ") " . $dbconn->error . "</b>";
        $stmt->close();
        return false;
      }

      if ( !$stmt->execute() ) {
        echo "<br /><b>execute failed: (" . $dbconn->errno . ") " . $dbconn->error . "</b>";
        $stmt->close();
        return false;
      }
      // log to syslog
      openlog("SSLgen:upload.php", LOG_PID | LOG_PERROR, LOG_LOCAL0);
      syslog(LOG_NOTICE, "Recieved SSL Cert: $zip_file_name at $uploaddate from {$_SERVER['REMOTE_ADDR']} for DBid: $id");
      closelog();
    }
  }
  echo "<hr>";
  
  return true;
}

function zip_files_in_folder($folder, $zip_file_name)
{
  $zip = new ZipArchive();
  $filename = $folder.$zip_file_name;

  if ($zip->open($filename, ZipArchive::CREATE)!==TRUE) {
    echo "<br />cannot open <$filename>";
  }
  // get all files under the path
  echo "<br />looking in $folder for files to add";
  $file = glob($folder . '*');
  foreach ($file as $node) {
    if (is_file($node))  {
      $filename_parts = explode('/', $node);  // Split the filename up by the '/' character
      //print_r($filename_parts);
      //echo "<br />".end($filename_parts);
      $zip->addFile($node, end($filename_parts));
      echo "<br />    Added $node as ".end($filename_parts)." to $filename";
    } 
  }
  $zip->setArchiveComment("Created at DanDomain A/S @ ".date("Y-m-d H:i:s"));
  echo "<br />    numfiles: " . $zip->numFiles;
  echo "<br />    status:" . $zip->status;
  if (!$zip->close() ) {
    echo "<br />ERROR closing zip archive!";
    return false;
  } else {
    echo "<br />Zipfile created";
    if (isset($_POST['createvhost']) && $_POST['createvhost'] !== 'yes')
    {
      foreach ($file as $node) {
        unlink($node);
        echo "<br />deleted $node";
      }
    }    
    return true;
  }
}

function create_vhost($prefix, $vhost_template_file, $base_dir, $out_dir, $proxy_url)
// prepares a vhost template and moves the cert and key to the dir where cronjob looks, $prefix = certificate file name prefix (eg. midgets_dk)
// $vhost_template_file = template to use. $base_dir = where to look for the cert, $out_dir = where to plate the finished template and files
{
  // check that we have all ressources needed
  $cert_folder = $base_dir.$prefix;
  $vhost_tpl_out = $out_dir."/".$prefix.".conf";
  $key_file = $cert_folder."/".$prefix.".key";
  $proxy_url = trim($proxy_url);

  if ( (filter_var($proxy_url, FILTER_VALIDATE_URL) == false) && ( (substr($proxy_url,0,7) == "http://") || (substr($proxy_url,0,8) == "https://") ) ) {
    echo "<br />ERROR:create_vhost proxy_url is not a valid URL !";
    echo "<br /> - $proxy_url ";
    return false;
  }
  if (!file_exists($cert_folder)) {
    echo "<br />ERROR:create_vhost Could not find cert_folder !";
    return false;
  }
  if (!file_exists($vhost_template_file)) {
    echo "<br />ERROR:create_vhost Could not find vhost-template file!";
    return false;
  }
  if (!file_exists($out_dir)) {
    echo "<br />ERROR:create_vhost Could not find out_dir!";
    return false;
  }
  if (!file_exists($key_file)) {
    echo "<br />ERROR:create_vhost Could not find key file!<br />$key_file<br />";
    return false;
  }
  
  //$crt_ending = ( file_exists($cert_folder."/".$prefix.".crt") ? $cert_folder."/".$prefix.".crt" : $cert_folder."/".$prefix.".cer" );	// guessing the crt file
  $crt_ending = ( file_exists($cert_folder."/".$prefix.".crt") ? ".crt" : ".cer" );
  $crt = $cert_folder."/".$prefix.$crt_ending;
  echo "<br />Trying certificate file: &lt;$crt&gt;<br />";
  if (!file_exists($crt)) {
    echo "<br />ERROR:create_vhost Could not find(guess) certificate file!";
    return false;
  }
  
  // parse the cert to extract the commonname and subject alt names
  // example altnames entry for wildcard cert: (CN=*.dandomain.dk)
  // [subjectAltName] => DNS:*.dandomain.dk, DNS:dandomain.dk
  
  $crt_data = file_get_contents($crt);      
  if ( $data = openssl_x509_parse($crt_data) ) {
    // print_r($data);
    //subject
    $commonname = $data['subject']['CN'];
    $altnames = ( isset($data["extensions"]["subjectAltName"]) ? $data["extensions"]["subjectAltName"] : false );
    echo "create_vhost: CommonName: " . $commonname . "<br />";
    //$altnames = "DNS:*.dandomain.dk, DNS:dandomain.dk";  // TEST DATA
    $altNames_message = ( $altnames !== false ) ? "create_vhost: Subject Alternative Names: " . $altnames . "<br />" : "create_vhost: No SAN names found<br />";
    echo $altNames_message;
    $altnames_for_vhost = "";
    if ($altnames !== false) {
      $altnames_replaced = str_replace(" ","", str_replace("DNS:","",trim($altnames)));
      $altnames_exploded = explode(",",$altnames_replaced);
      echo "altnames_replaced: ".$altnames_replaced."<br />";
      $altnames_for_vhost = "";
      foreach($altnames_exploded as $res) {
         echo "--$res--<br />";
         $altnames_for_vhost = $altnames_for_vhost." ".$res;
      }
    }
    //echo "altnames_for_vhost: $altnames_for_vhost<br />";
    
    // now we have common name and possibly SAN names. Now do the replace in the vhost template.
    // replace vars in vhost-template file: 
    //   ServerName template-commonname
    //   SSLCertificateFile /etc/apache2/ssl-certs/template-foldername/template-certname
    //   SSLCertificateKeyFile /etc/apache2/ssl-certs/template-foldername/template-keyname
    //   ProxyPass / template-proxysite-url
    //   ProxyPassReverse / template-proxysite-url
    
    // Servername
    $commonname = $commonname . $altnames_for_vhost;
    $vhost_template = str_replace("template-commonname", $commonname, file_get_contents($vhost_template_file));
    // foldername to certs
    $vhost_template = str_replace("template-foldername", $prefix, $vhost_template);
    // Certificate filename
    // $crt_file = end( explode("/",$crt) );
    $year = date("Y");
    $crt_file_with_year = $prefix."_".$year.$crt_ending;
    // echo "Cert file: $crt_file <br />";
    $vhost_template = str_replace("template-certname", $crt_file_with_year, $vhost_template);
    // Key filename
    // echo "Key file $key_file<br />";
    $key_file_with_year = $prefix."_".$year.".key";
    $vhost_template = str_replace("template-keyname", $key_file_with_year, $vhost_template);
    // Proxy URL
    $vhost_template = str_replace("template-proxysite-url", $proxy_url, $vhost_template);
    // Write date created
    $createdata = "CreateDate: ".date("d-m-Y H:i:s");
    $vhost_template = str_replace("CreateDate", $createdata, $vhost_template);
    echo "<br />$vhost_template<br />";
    // Done, write the vhost template
    if ( file_put_contents($vhost_tpl_out, $vhost_template) == false ){
     echo "ERROR:create_vhost Could not write file: &lt;$vhost_tpl_out&gt;<br />";
     return false;
    }
    
    // Now move the cert and the key to the same location as the vhost-template file
    echo "Moving cert and key to $out_dir:<br />";
    echo " cert: ".$crt."<br />";
    echo " key: ".$key_file."<br />" ;
    if (!rename($crt,$out_dir."/".$crt_file_with_year)) {
     echo "ERROR moving file: $crt<br />";
     return false;
    }
    if (!rename($key_file,$out_dir."/".$key_file_with_year)) {
     echo "ERROR moving file: $key_file<br />";
     return false;
    }
  } else {
    echo "ERROR:create_vhost Could not parse certificate: &lt;$crt&gt;<br />";
    return false;
  }
  // when all is done there should be 3 files in the out dir. A vhost conf and the key and cert. Ready for the cron "engine"
  return true;
}
?>