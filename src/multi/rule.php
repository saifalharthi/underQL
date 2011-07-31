<?php


function uql_rule_length($rules, $name, $value,$alias = null)
{

   if(is_array($rules))
   {
     if((!isset($rules[$name])) || (!isset($rules[$name]['length'])))
      return UQL_RULE_NOP;

     $v = (int) $rules[$name]['length'];

     if($alias != null)
      $caption = $alias;
     else
      $caption = $name;

     $error_message = sprintf(uql_uti_get_rule_error_message('length'),$caption,$v);

     //$v += 2; //escape string single quotes
     if($v < strlen($value))
      return $error_message;
     else
      return UQL_RULE_MATCHED;
   }

   return UQL_RULE_NOP;
}


function uql_rule_number($rules,$name,$value,$alias = null)
{
     if(is_array($rules))
    {
     if((!isset($rules[$name])) || (!isset($rules[$name]['number'])))
      return UQL_RULE_NOP;

     if($alias != null)
      $caption = $alias;
     else
      $caption = $name;

     $error_message = sprintf(uql_uti_get_rule_error_message('number'),$caption);

     if(!ctype_digit($value))
      return $error_message;
     else
      return UQL_RULE_MATCHED;
   }

   return UQL_RULE_NOP;
}
//////////////////////////////////////////////

function uql_rule_required($rules,$name,$value,$alias = null)
{

   if(is_array($rules))
   {
     if((!isset($rules[$name])) || (!isset($rules[$name]['required'])))
      return UQL_RULE_NOP;

     $v = trim($value);

     if($alias != null)
      $caption = $alias;
     else
      $caption = $name;

     $error_message = sprintf(uql_uti_get_rule_error_message('required'),$caption,$v);

     if(strlen($v) == 0)
     return $error_message;
     else
      return UQL_RULE_MATCHED;
   }

   return UQL_RULE_NOP;
}




?>