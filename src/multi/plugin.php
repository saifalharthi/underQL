<?php

function uql_plugin_toXML($that,$args)
{
  $arg_num = @count($args);
  switch($arg_num)
  {
    case 0:
     $that->select(); break;
    case 1:
     $that->select($args[0]); break;
    case 2:
     $that->select($args[0],$args[1]); break;
    default :
     return UQL_PLUGIN_RETURN;
  }

  if($that->count() == 0)
   return UQL_PLUGIN_RETURN;

  $tname = $that->getTableName();
  $xml  = '<?xml version = "1.0" encoding ="UTF-8" ?>'."\n";
  $xml .= '<'.$tname.'>'."\n";
  $fields = $that->getFieldsList();
  $fields = $that->getCurrentQueryFields();
  $fields_count = @count($fields);
  
  while($that->fetch())
  {
   $xml .= '<record>'."\n";
   for($i = 0; $i < $fields_count; $i++ )
   {
      if($that->$fields[$i] != null)
            $xml .= '<'.$fields[$i].'>'.$that->$fields[$i].'</'.$fields[$i].'>'."\n";
   }
   $xml .= '</record>'."\n";
  }

  $xml .= '</'.$tname.'>'."\n";
  $that->free();
  return $xml;
}

?>