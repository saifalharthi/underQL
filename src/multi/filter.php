<?php

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

function uql_filter_xss($value,$inout = UQL_FILTER_IN)
{
     if($inout == UQL_FILTER_IN)
     {
       if(is_string($value))
        return htmlspecialchars($value);
     }

     return $value;
}

function uql_filter_nohtml($value,$inout = UQL_FILTER_IN)
{
   if($inout == UQL_FILTER_IN)
    return strip_tags($value);

   return $value;
}
?>