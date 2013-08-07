<!DOCTYPE html
PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
  <head>
    <title>Certificate Reporting</title>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <style type="text/css">
      #mytable
      {
      border-collapse:collapse;
      font-family:"Trebuchet MS", Arial, Helvetica, sans-serif;
      width:100%;
      }
      #mytable th, #mytable td
      {
      font-size:1em;
      border:1px solid #98bf21;
      padding:3px 7px 2px 7px;
      }
      #mytable th
      {
      font-size:1.1em;
      text-align:left;
      padding-top:5px;
      padding-bottom:4px;
      background-color:#A7C942;
      color:#ffffff;
      }
      #mytable tr.alt td
      {
      color:#000000;
      background-color:#EAF2D3;
      }
      </style>
  </head>
<?php
// Open database
require_once("database_conn.php");

//$pendingstatus = 0;
if (isset($_GET["status"]) && $_GET["status"] == "0") {
    //Pending
    $pendingstatus = "0";
    $stmt = $mysqli->prepare("SELECT id,cn,status,use_on_dsslproxy01,createdate,uploaddate,kayako_ref FROM `certstor` WHERE status = ?");
    $stmt->bind_param('i',$pendingstatus);
  } elseif (isset($_GET["status"]) && $_GET["status"] == "1") {
    //Done
    $pendingstatus = "1";
    $stmt = $mysqli->prepare("SELECT id,cn,status,use_on_dsslproxy01,createdate,uploaddate,kayako_ref FROM `certstor` WHERE status = ?");
    $stmt->bind_param('i',$pendingstatus);
  } else {
    //All
    $stmt = $mysqli->prepare("SELECT id,cn,status,use_on_dsslproxy01,createdate,uploaddate,kayako_ref FROM `certstor`");
}

$stmt->execute();
$stmt->bind_result($id,$cn,$status,$use_on_dsslproxy01,$createdate,$uploaddate,$kayako_ref);
?>
<body>
<h2>Certificate Reporting</h2>
<small><pre>No CSR,Keys or Certs are displayed</pre></small>
<hr />
<select name="status" form="myform">
  <option value="all">All</option>
  <option value="0">Pending</option>
  <option value="1">Done</option>
</select>
<form action="<?php echo $PHP_SELF?>" id="myform">
  <input type="submit" value="Update" />
</form>
<hr />
<table id="mytable">
  <tr>
    <th>Id</th>
    <th>Common Name</th>
    <th>Status</th>
    <th>Use on DSSLPROXY01</th>
    <th>Create date</th>
    <th>Upload date</th>
    <th>Kayako ID</th>
  </tr>
<?php
$i = 0;
while ($stmt->fetch()) {
  $class = ($i == 0) ? "" : "alt";
  echo "<tr class=\"".$class."\">";
  echo "<td>".$id."</td>";
  echo "<td>".$cn."</td>";
  echo "<td>".$status."</td>";
  echo "<td>".$use_on_dsslproxy01."</td>";
  echo "<td>".$createdate."</td>";
  echo "<td>".$uploaddate."</td>";
  echo "<td>".$kayako_ref."</td>";
  echo "</tr>";
  $i = ($i==0) ? 1:0;
}
?>
</table>

</body>
</html>
<?php
// Cleanup
$stmt->close();
$mysqli->close();
?>
