<?php

function uql_checker_email( $value )
{
   return filter_var( $value, FILTER_VALIDATE_EMAIL );
}

?>