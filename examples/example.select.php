<?php

 require_once('underQL.php');

 $_('test');
 for($i = 0; $i < $_->count(); $i++)
  {
      echo $_->id.'<br />';
      echo $_->name.'<br />';
      $_->fetch();
  }

?>