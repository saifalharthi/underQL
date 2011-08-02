<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
    "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">

<html xmlns="http://www.w3.org/1999/xhtml">

<head>
  <title>Hello!</title>
</head>

<body>

<?php

require_once('multi/underQL.php');

//$_->table('test');
$_('test');
$_->filter('demo',UQL_FILTER_OUT,'name');
$_->fetch();



for($i = 0; $i < $_->count(); $i++)
 {          // echo 'x';
   echo $_->id.'<br />';
   echo $_->name.'<br />';
   $_->fetch();
 }
           /*
$_->name = 'Abdullah';

$_->insert();*/




?>

</body>

</html>
