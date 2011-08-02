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
$test_rules->name('length',10);
$test_rules->name('number');

$test_rules->addAlias('name','الإسم');

$test = new underQL();
$test->table('test');

$test->filter('demo',UQL_FILTER_IN,'name');
$test->filter('demo',UQL_FILTER_OUT,'name');
$test->attachRule($test_rules);

/*$test->select('*','WHERE id =450');
$test->fetch();

for($i = 0; $i < $test->count(); $i++)
 {
   echo $test->name.'<br />';
   $test->fetch();
 }
*/

$test->name = '45';

if(!$test->isRulesPassed())
 die($test->getRuleError());

$test->insert();


?>

</body>

</html>
