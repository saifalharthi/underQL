<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
    "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">

<html xmlns="http://www.w3.org/1999/xhtml">

<head>
  <title>Hello!</title>
</head>

<body>

<?php

require_once('multi/underQL.php');

$test_rules = new UQLRule('test');
$test_rules->addAlias('id','الرقم');

$test_rules->name('required');
$test_rules->name('length',25);
$test_rules->addAlias('name','الإسم');

$test = new underQL();
$test->table('test');
$test->attachRule($test_rules);

$test->id = 5005;
$test->name = '';

 if(!$test->isRulesPassed())
     die($test->getRuleError());

echo $test->insert();




?>

</body>

</html>
