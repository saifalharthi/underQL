<?php

/*filter input value like insert and update*/
define ('UQL_FILTER_IN',0xAB);
/*filter output value like select*/
define ('UQL_FILTER_OUT',0xAC);

function uql_filter_demo($value,$inout = UQL_FILTER_IN)
{
      switch($inout)
      {
        case UQL_FILTER_IN:
          return 'IN('.$value.')';
         break;
        case UQL_FILTER_OUT:
          return 'OUT('.$value.')'; 
         break;
        default : return $value;
      }
}

?>