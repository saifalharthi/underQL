<?php


       //06:54 am


/* database host */
$UNDERQL['db']['host'] = 'localhost';

/* database name */
$UNDERQL['db']['name'] = 'courses';

/* database user name */
$UNDERQL['db']['user'] = 'root';

/* database password */
$UNDERQL['db']['password'] = '';

/* database encoding system for database operations */
$UNDERQL['db']['encoding'] = 'utf8';

/* store some information about every table that you work with for
some internal purposes. */
$UNDERQL['table'] = array( );


$UNDERQL['filter']['prefix'] = 'uql_filter_';


$UNDERQL['checker']['prefix'] = 'uql_checker_';



$UNDERQL['error']['prefix'] = 'UnderQL Error : ';
$UNDERQL['warning']['prefix'] = 'UnderQL Warning : ';


$UNDERQL['rule']['uql_prefix'] = 'uql_rule_';

$UNDERQL['lang']['module'] = 'english';



/*filter input value like insert and update*/
define ('UQL_FILTER_IN',0xAB);
/*filter output value like select*/
define ('UQL_FILTER_OUT',0xAC);



?>