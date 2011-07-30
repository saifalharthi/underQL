<?php

  require_once('underQL.php');

  $_->table('test');

  $_->id = 100;
  $_->name = 'www.abdullaheid.net';



  $_->insert();

?>