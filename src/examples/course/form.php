<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
    "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">

<html xmlns="http://www.w3.org/1999/xhtml">

<head>
  <title>Hello!</title>
</head>

<body>

<form action = "csave.php" method = "post">

Course : <input type = "text" name = "cname" />
Description : <input type = "text" name = "cdesc" />
<input type ="submit" value ="save" />

</form>

<?php
      require_once('../../multi/underQL.php');
 $_('course');
 $_->fetch();

 for($i = 0; $i < $_->count(); $i++)
 {
   echo $_->id;
   echo '--';
   echo $_->name;
   echo '<br />';
   $_->fetch();
 }

?>
<form action = "usave.php" method = "post">

Name : <input type = "text" name = "cname" />
CourseID : <input type = "text" name = "cdesc" />
<input type ="submit" value ="save" />

</form>
<?php

 $_('user');
 $_->fetch();

 for($i = 0; $i < $_->count(); $i++)
 {
   echo $_->id;
   echo '--';
   echo $_->name."<a href ='udel.php?id=$_->id'>delete</a>";
   echo '<br />';
   $_->fetch();
 }

?>
</body>

</html>
