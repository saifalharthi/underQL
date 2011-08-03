<?php

$course_rules = new UQLRule('course');
$course_rules->name('required');
$course_rules->name('length',25);
$course_rules->description('required');

$course_rules->addAlias('name','الاسم');
$course_rules->addAlias('description','الوصف');

$course = new underQL('course');
$course->attachRule($course_rules);

$course->filter('xss',UQL_FILTER_IN,'name','description');
$course->filter('html',UQL_FILTER_IN,'name');



$user_rules = new UQLRule('user');
$user_rules->name('required');
$user_rules->name('length',25);
$user_rules->cid('required');
$user_rules->cid('number');

$user_rules->addAlias('name','الاسم');
$user_rules->addAlias('cid','رقم الدورة');

$user = new underQL('user');
$user->attachRule($user_rules);

$user->filter('xss',UQL_FILTER_IN,'name','description');
$user->filter('html',UQL_FILTER_IN,'name');
?>