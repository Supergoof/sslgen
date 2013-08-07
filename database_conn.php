<?php
  $mysqli = new mysqli("localhost", "sslgensqluser", "RsuaZEx5tHKXSNWM", "sslgen");
  // check connection
  if ($mysqli->connect_error) {
    trigger_error('Database connection failed: '  . $conn->connect_error, E_USER_ERROR);
  }
?>
