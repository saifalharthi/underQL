<?php


function uql_plugin_toXML($that,$args)
{
  $that->select();
  var_dump($that->getFieldsList());
  return '';
}

?>