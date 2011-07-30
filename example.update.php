<?php

  require_once('underQL.php');

  $_->table('test');

  $_->id = 200;
  $_->name = 'www.underql.com';



  $_->update("id = 100");

?>