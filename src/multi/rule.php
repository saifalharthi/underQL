<?php


function uql_rule_length($rules, $name, $value,$alias = null)
{
      $rule_result = uql_uti_rule_init($rules,'length',$name,$alias);
      if(!$rule_result)
       return UQL_RULE_NOP;

      list($rule_value,$caption) = $rule_result;

      $rule_value = (int)$rule_value;

      // action here
     if( $rule_value < strlen($value))
      return uql_uti_rule_get_error_message('length',$caption,$rule_value);
     else
      return UQL_RULE_MATCHED;

}

//////////////////////////////////////////////

function uql_rule_required($rules,$name,$value,$alias = null)
{
    $rule_result = uql_uti_rule_init($rules,'required',$name,$alias);
     if(!$rule_result)
       return UQL_RULE_NOP;

    list($rule_value,$caption) = $rule_result;

    if(strlen(trim($value)) == 0)
      return uql_uti_rule_get_error_message('required',$caption,$rule_value);
    else
      return UQL_RULE_MATCHED;

}

//////////////////////////////////////////////

function uql_rule_number($rules,$name,$value,$alias = null)
{
    $rule_result = uql_uti_rule_init($rules,'number',$name,$alias);
     if(!$rule_result)
       return UQL_RULE_NOP;

    list($rule_value,$caption) = $rule_result;

    if(!ctype_digit($value))
      return uql_uti_rule_get_error_message('number',$caption);
    else
      return UQL_RULE_MATCHED;

}

//////////////////////////////////////////////

function uql_rule_symbol($rules,$name,$value,$alias = null)
{
    $rule_result = uql_uti_rule_init($rules,'symbol',$name,$alias);
     if(!$rule_result)
       return UQL_RULE_NOP;

    list($rule_value,$caption) = $rule_result;

    if(!ctype_punct($value))
      return uql_uti_rule_get_error_message('symbol',$caption);
    else
      return UQL_RULE_MATCHED;

}

function uql_rule_between($rules,$name,$value,$alias = null)
{
    $rule_result = uql_uti_rule_init($rules,'between',$name,$alias);
     if(!$rule_result)
       return UQL_RULE_NOP;

    list($rule_value,$caption) = $rule_result;


    if((!is_array($rule_value)) ||(@count($rule_value) != 2))
      return UQL_RULE_NOP;

    if((!is_string($value)) ||(strlen($value) == 0))
     return UQL_RULE_NOP;

     $value_len = strlen($value);
    list($min,$max) = $rule_value;

    if($max <= 0)
     {
       if($min > 0)
       {
         if($value_len >= $min)
          return UQL_RULE_MATCHED;
         else
           return uql_uti_rule_get_error_message('between',$caption);
       }
       return UQL_RULE_NOP;
     }
     else if($min <= 0)
     {
        if($max > 0)
       {
         if($value_len <= $max)
          return UQL_RULE_MATCHED;
         else
           return uql_uti_rule_get_error_message('between',$caption);

       }
       return UQL_RULE_NOP;
     }

     if($min == $max)
     {
      if($value_len > $max)
       return uql_uti_rule_get_error_message('between',$caption);
      else
       return UQL_RULE_MATCHED;
     }
     else if(($value_len >= $min) && ($value_len <= $max))
       return UQL_RULE_MATCHED;

      return uql_uti_rule_get_error_message('between',$caption);
}
?>