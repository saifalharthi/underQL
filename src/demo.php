<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
    "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">

<html xmlns="http://www.w3.org/1999/xhtml">

<head>
<meta content="ar-sa" http-equiv="Content-Language" />
<meta content="text/html; charset=utf-8" http-equiv="Content-Type" />
  <title>Hello!</title>
</head>

<body>

<p>$task->name</p>

<?php

require_once('multi/underQL.php');
$a = new underQL('athdak_tasks');
echo $a->toJSON();

?>

</body>

</html>
