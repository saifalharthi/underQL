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

require_once('multi/underQL.php');

$tasks_rules = new UQLRule('tasks');

$tasks_rules->id('number');
$tasks_rules->uid('number');
$tasks_rules->text('required');
$tasks_rules->parent('number');
$tasks_rules->duedate('required');
$tasks_rules->status('requried');
$tasks_rules->type('required');

$tasks_rules->addAlias('id','الرقم');
$tasks_rules->addAlias('uid','رقم المستخدم');
$tasks_rules->addAlias('text','المحتوى');
$tasks_rules->addAlias('parent','التصنيف');
$tasks_rules->addAlias('duedate','التاريخ');
$tasks_rules->addAlias('status','الحالة');
$tasks_rules->addAlias('type','النوع');

$tasks = new underQL('tasks');
//$tasks->table('tasks');
$tasks->attachRule($tasks_rules);

$tasks->filter('demo',UQL_FILTER_OUT,'text');

$tasks->select();
$tasks->fetch();

echo $tasks->text;
echo '<br />';
$tasks->fetch();
echo $tasks->text;

?>

</body>

</html>
