<!DOCTYPE html>
<html>
<body>
<head>
<title>Title of the document</title>
</head>
<?php
// Open database
require_once("database_conn.php");
if (isset($_GET["dropdown"])) {
  $arg = $_GET["dropdown"];
  echo "Recieved: " . $arg . "<br /><hr />";
}
$pendingstatus = 0;
$stmt = $mysqli->prepare("SELECT id,cn,createdate,kayako_ref FROM `certstor` WHERE status = ?");
$stmt->bind_param('i',$pendingstatus);
$stmt->execute();
$stmt->bind_result($id, $cn, $crdate, $kanonr);
?>
<pre>dropdown format: commonname createdate kayako_ref</pre><br />

<select name="dropdown" form="myform">
<?php
while ($stmt->fetch()) {
  echo "<option value=\"$id\">$cn $crdate $kanonr</option>";
  }
echo "</select>";
  
$stmt->close();
$mysqli->close();
?>
<form action="<?php echo $PHP_SELF?>" id="myform">
  <input type="submit">
</form>
</body>
</html> 