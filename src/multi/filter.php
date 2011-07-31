<?php

/*filter input value like insert and update*/
define ('UQL_FILTER_IN',0xAB);
/*filter output value like select*/
define ('UQL_FILTER_OUT',0xAC);

function uql_filter_xss($value,$inout = UQL_FILTER_IN)
{
      return strip_tags( $value );
}

?>