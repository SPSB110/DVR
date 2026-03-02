<?php

require_once "config.php";
require 'vendor/autoload.php';
getJsons();

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style.css">
    <title>Certificati</title>
</head>

<body>
<?php
foreach ($files as $file) {
    if ($file === '.' || $file === '..') continue;
    echo "<a href='calcoli.php?file=$file'>$file</a><br>";
}
?>
</body>

</html>