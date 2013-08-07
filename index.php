<!doctype html>
<?php
// Open database
require_once("database_conn.php");
$pendingstatus = 0;	// Pending = 0, Done = 1
$stmt = $mysqli->prepare("SELECT id,cn,kayako_ref FROM `certstor` WHERE status = ?");
$stmt->bind_param('i',$pendingstatus);
$stmt->execute();
$stmt->bind_result($id, $cn, $kanonr);
?>
<html lang="en">
  <head>
    <title>DanDomain A/S SSL Cert Machine</title>
    <!-- Meta Tags -->
    <meta http-equiv="content-type" content="application/xhtml+xml; charset=utf-8" />
    <meta name="description" content="SSL Cert Machine">
    <!-- CSS -->
    <link rel="stylesheet" href="/css/style.css" media="screen,projection" type="text/css" />
    <!-- Google Web Fonts -->
    <link href='https://fonts.googleapis.com/css?family=Droid+Sans' rel='stylesheet' type='text/css'>
  </head>
  <body>
    <div id="wrapper">
      <header>
        
        <h1>DanDomain SSL Certificate Machine</h1>
        <h2>DanDomain A/S - Alsikevej 31 - DK-8920 Randers NV - Tlf: (+45) 87 77 90 45 - <a href="mailto:info@dandomain.dk?Subject=Contact%20from%20https://sslgen.dandomain.dk" target="_top">info@dandomain.dk</a> No <a href="https://www.google.com/chrome">IE</a></h2>
      </header>
      <div id="content">
        
<form id="csrform" action="" method="POST">
  <ol>
    <li>
      <label for="countryName">Country:<span class="small">Two letter abbreviation</span></label>
      <input class="jq_watermark" type="text" title="DK" name="countryName" required maxlength="2" autofocus pattern="[A-Z]{2}" />
    </li>
    <li>
      <label for="stateOrProvinceName">State:<span class="small">Full state name</span></label>
      <input class="jq_watermark" type="text" title="" name="stateOrProvinceName" required />
    </li>
    <li>
      <label for="localityName">Locality:<span class="small">Full city name</span></label>
      <input class="jq_watermark" type="text" title="Randers" name="localityName" required />
    </li>
    <li>
      <label for="organizationName">Organization:<span class="small">Full legal company or personal name</span></label>
      <input class="jq_watermark" type="text" title="DanDomain A/S" name="organizationName" required />
    </li>
    <li>
      <label for="organizationalUnitName">Organizational Unit:<span class="small">Branch of organization</span></label>
      <input class="jq_watermark" type="text" title="IT" name="organizationalUnitName" />
    </li>
    <li>
      <label for="commonName">Common Name:<span class="small">The FQDN for your domain</span></label>
      <input class="jq_watermark" type="text" title="dandomain.com" name="commonName" required />
    </li>
    <li>
      <label for="emailAddress">Email Address:<span class="small">Contact Email</span></label>
      <input class="jq_watermark" type="text" title="support@dandomain.com" name="emailAddress" required />
    </li>
    <li>
      <label for="kayakoRef">Kayako ID:<span class="small">Kayako Reference ID</span></label>
      <input class="jq_watermark" type="text" title="HVZ-824-17228" name="kayakoRef" maxlength="13" pattern="[A-Za-z]{3}\-[0-9]{3}\-[0-9]{5}" />
    </li>    
  </ol>
  <br/>
  <input type="submit" value="Generate CSR" />
</form>
<form id="uploadform" action="upload.php" method="POST" enctype="multipart/form-data">
  <ol>
    <li>
      <label for="pendingCert">Pending Certifikate:<span class="small">Choose Common Name(KayakoID)</span></label>
      <select name="pendingCertID" form="uploadform" id="pendingCertDropdown">
      <?php
       while ($stmt->fetch()) {
          echo "<option value=\"$id\">$cn ($kanonr)</option>";
       }
       $stmt->close();
       $mysqli->close();
      ?>
      </select>
    </li>
    <input type="hidden" name="MAX_FILE_SIZE" value="200000">
    <li>
      <label for="zip_file">Certificate Filname:<span class="small">Zip containing Certificate</span></label>
      <input type=file name="zip_file" id="zip_file">
    </li>
    <li>
      <label for="createvhost">Create vhost:<span class="small">Create vhost on <?php echo $_SERVER['SERVER_NAME'] ?></span></label>
      <input type="checkbox" name="createvhost" id="createvhost" value="yes" align="">
    </li>
  <ol>
  <br />
  <input type=submit name="upload" value="Upload">
  </form>
<div id="postgenerate">
  <textarea id="csr" rows="47" cols="65" readonly="readonly"> </textarea>
</div> <!-- End postgenerate div -->
    </div> <!-- End content -->
    <img src="/images/shadow.png" width="648" height="49" />
    </div> <!-- End wrapper -->

    <!-- JavaScript -->
    <script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/1.6.0/jquery.min.js"></script>
    <script type="text/javascript" src="/js/jquery.watermark.m.js"></script>
    <script type="text/javascript" src="/js/main.js"></script>
  </body>
</html>
