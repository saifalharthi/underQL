<?php

function uql_uti_get_rule_error_message($key)
{
   global $UNDERQL;
   $l_list = $UNDERQL['rule']['uql_fail_messages'];
   if(isset($l_list[$key]))
    return $l_list[$key];

   return '';
}

?>