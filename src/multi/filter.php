<?php

function uql_filter_xss($value,$inout = UQL_FILTER_IN)
{
     if($inout == UQL_FILTER_IN)
     {
       if(is_string($value))
        return htmlspecialchars($value);
     }

     return $value;
}

function uql_filter_html($value,$inout = UQL_FILTER_IN)
{
   if($inout == UQL_FILTER_IN)
    return strip_tags($value);

   return $value;
}

function uql_filter_space($value,$inout = UQL_FILTER_IN)
{
  if(is_string($value))
    return @trim($value);
  return $value;
}

?>