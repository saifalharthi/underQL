<?php


function uql_rule_length($rules, $name, $value,$alias = null)
{
      $rule_result = uql_uti_rule_init($rules,'length',$name,$alias);
      if(!$rule_result)
       return UQL_RULE_NOP;

      list($rule_value,$caption) = $rule_result;

      // action here
     if($rule_value < strlen($value))
      return uql_uti_rule_get_error_message('length',$caption,$v);
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

    if(strlen($rule_value) == 0)
      return uql_uti_rule_get_error_message('required',$caption,$v);
    else
      return UQL_RULE_MATCHED;

}


?>