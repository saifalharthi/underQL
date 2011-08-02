<?php

function uql_uti_rule_get_error_message()
{
   global $UNDERQL;
   $l_args_num = func_num_args();

   if($l_args_num  < 1)
      return '';

   $l_list = $UNDERQL['rule']['uql_fail_messages'];

   $key = func_get_arg(0);

   if(isset($l_list[$key]))
    {
      $l_msg_val = $l_list[$key];

      $l_args = func_get_args();
      array_shift($l_args);

      if(@count($l_args) == 0)
       return '';

      return @vsprintf($l_msg_val,$l_args);
    }

   return '';
}


function uql_uti_rule_check_params($rules,$rule_name,$name)
{
  if(!(is_array($rules)) || (!isset($rules[$name])) || (!isset($rules[$name][$rule_name])))
   return false;

   return true;
}

function uql_uti_rule_get_alias($name,$alias)
{
  if($alias != null)
     return $alias;

  return $name;

}

function uql_uti_rule_get_value($rules,$rule_name,$name)
{
  return $rules[$name][$rule_name];
}

function uql_uti_rule_init($rules,$rule_name,$name,$alias)
{
    if(!uql_uti_rule_check_params($rules,$rule_name,$name))
      return false;

      $rule_value = uql_uti_rule_get_value($rules,$rule_name,$name);
      $caption    = uql_uti_rule_get_alias($name,$alias);

      return array($rule_value,$caption);
}

?>