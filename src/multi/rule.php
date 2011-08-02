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


?>