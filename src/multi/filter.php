<?php

/*

underQL uses filters to do something with data before insert or update theme
like clean XSS or trim the value. However, you can write your
own filter by writing the function with the following pattern :

function uql_filter_[filtername]($value){

return $value_after_apply_filter;
}

After that you can apply the filter like the following :

$_->filter('filtername','name','title',...);

The parameters that are coming after the filter name are the fields names
that you intent to apply your filter on their values before insert or update
them.

*/


function uql_filter_xss( $value )
{
      return strip_tags( $value );
}

?>