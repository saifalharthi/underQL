<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
    "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">

<html xmlns="http://www.w3.org/1999/xhtml">

<head>
  <title>Hello!</title>
</head>

<body>

<?php

require_once('../../multi/underQL.php');
require_once('rules.php');

$course->name = $_POST['cname'];
$course->description = $_POST['cdesc'];

if(!$course->isRulesPassed())
 die($course->getRuleError());

$course->insert();

?>

</body>

</html>
