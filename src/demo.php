<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
    "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">

<html xmlns="http://www.w3.org/1999/xhtml">

<head>
<meta content="ar-sa" http-equiv="Content-Language" />
<meta content="text/html; charset=utf-8" http-equiv="Content-Type" />
  <title>Hello!</title>
</head>

<body>

<?php



require_once('single/underQL.php');

$tasks = new underQL('tasks');

$xml = $tasks->toXML('MAX(id)');
if(is_string($xml))
 {
   $fd = fopen('to.xml','w');
   fwrite($fd,$xml);
   fclose($fd);
 }

?>

</body>

</html>
