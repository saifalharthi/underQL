
<?php

 /*
    UnderQL Project
    By Abdullah E. Almehmadi
     <cs.abdullah@hotmail.com>
   6:25 am 26-08-32 : 2011-07-27
   domain registered 6:32 am <www.underql.com>
   1.0.0.Beta

   License : MPL(Mozilla Public License 1.1)
   */

/* database host */
$UNDERQL['db']['host']     = 'localhost';
/* database name */
$UNDERQL['db']['name']     = 'adeeb';
/* database user name */
$UNDERQL['db']['user']     = 'root';
/* database password */
$UNDERQL['db']['password'] = '';
/* database encoding system for database operations */
$UNDERQL['db']['encoding'] = 'utf8';


/* store some information about every table that you work with for
 some internal purposes. */
$UNDERQL['table'] = array();

/*

underQL uses filters to do something with data before insert or update theme
 like clean XSS, SQL injection or trim the value. However, you can write your
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
// <!-- Filter APIs Begin -->

$UNDERQL['filter']['prefix'] = 'uql_filter_';


function uql_filter_xss($value)
{
   return strip_tags($value);
}

function uql_filter_trim($value)
{
   return trim($value);
}

function uql_filter_sqli($value)
{
   return mysql_escape_string($value);
}


// <!-- Filter APIs END   -->
// <!-- Checker APIs Begin -->
$UNDERQL['checker']['prefix'] = 'uql_checker_';

function uql_checker_email($value)
{

   return filter_var($value,FILTER_VALIDATE_EMAIL);
}


// <!-- Checker APIs END   -->

$UNDERQL['error']['prefix']   = 'UnderQL Error : ';
$UNDERQL['warning']['prefix'] = 'UnderQL Warning : ';

class underQL{

//used by insert instruction
private $data_buffer; // used to stor key/value that entered by user
private $string_fields; // contains the name of all string fields to use it to add single qoute to the value.
private $table_fields_names;
private $table_name; // table name that is accepting all instructions from the object


// DB connectivity

private $db_handle;
private $db_query_result;
private $db_current_object;

// Errors

private $err_message;

/* Initialization */
public function __construct()
{
   global $UNDERQL;

   $this->db_handle = @mysql_connect($UNDERQL['db']['host'],
                                     $UNDERQL['db']['user'],
                                     $UNDERQL['db']['password']);

   if(!$this->db_handle)
    die('UnderQL Error : Unable to connect to DB..!');

   if(!(@mysql_select_db($UNDERQL['db']['name'])))
     {
       @mysql_close($this->db_handle);
       die('UnderQL Error: Unable to select DB..!');
     }

   @mysql_query("SET NAMES '".$UNDERQL['db']['encoding']."'");

   $this->db_current_object = null;
   $this->db_query_result   = false;

   $this->table_fields_names = array();

   $this->clearDataBuffer();

}

private function clearDataBuffer()
{
  $this->data_buffer = array();
  $this->err_message = '';
}

private function error($msg)
{
  global $UNDERQL;
  die('<code><b><font color ="#FF0000">'.$UNDERQL['error']['prefix'].'</font></b></code><code>'.$msg.'</code>');
}

public function table($tname)
{
  global $UNDERQL;

  if(!array_key_exists($tname,$UNDERQL['table']))
    {
      $l_result = @mysql_query('SHOW TABLES FROM `'.$UNDERQL['db']['name'].'`');
      $l_count  = @mysql_num_rows($l_result);
      if($l_count == 0)
        $this->error($tname.' dose not exist. '.mysql_error());

      while($l_t = @mysql_fetch_row($l_result))
      {
          if(strcmp($tname,$l_t[0]) == 0)
           {
             $this->table_name = $tname;
            // $this->string_fields = $UNDERQL['table'][$tname] = array();
             @mysql_free_result($l_result);
             $this->readFields();
             return;
           }
      }
    }
    else
    {
     //$this->string_fields = $UNDERQL['table'][$tname];
     $this->table_name = $tname;
     $this->readFields();
     @mysql_free_result($l_result);
      return;
    }

    @mysql_free_result($l_result);
    $this->error($tname.' dose not exist');

}

public function __invoke($tname,$cols='*',$extra=null)
{
  $this->table($tname);

  return $this->select($cols,$extra);
}


public function __set($key,$val)
{
   $this->data_buffer[$key] = $val;
}

private function quote()
{
  if(@count($this->data_buffer) == 0)
   return;

  foreach($this->data_buffer as $key=>$val)
  {
   if(in_array($key,$this->string_fields))
   $this->data_buffer[$key] = "'".$val."'";
  else
   $this->data_buffer[$key] = $val;
  }
}

private function formatInsertCommand()
{
  $data_buffer_length = @count($this->data_buffer);
  if($data_buffer_length == 0)
   return false;

  $sql = 'INSERT INTO '.$this->table_name.' ';

  $sql_columns = '(';
  $sql_values  = ' VALUE(';
  $this->quote();
  $i = 0;
  foreach($this->data_buffer as $k=>$v)
  {
    $sql_columns .= $k;
    $sql_values  .= $v;
    if(($i + 1) < $data_buffer_length)
    {
        $sql_columns .= ',';
        $sql_values  .= ',';
    }

     $i++;
  }

  $sql_columns .=')';
  $sql_values  .=')';

  $sql .= $sql_columns.$sql_values;

  return $sql;

}

public function insert()
{
  $sql_insert_string = $this->formatInsertCommand();
  if($sql_insert_string == false)
   return false;

  $l_result = $this->query($sql_insert_string);
  $this->clearDataBuffer();
  return $l_result;
}

private function formatUpdateCommand($where = null)
{
  $data_buffer_length = @count($this->data_buffer);
  if($data_buffer_length == 0)
   return false;

  $sql = 'UPDATE '.$this->table_name.' SET ';

  $sql_clues = '';

  $this->qoute();

  $i = 0;
  foreach($this->data_buffer as $k=>$v)
  {
     $sql_clues .= ' '.$k.'='.$v;
    if(($i + 1) < $data_buffer_length)
     $sql_clues .= ',';

     $i++;
  }


  $sql .= $sql_clues;

  if($where != null)
   $sql .= ' WHERE '.$where;

  return $sql;

}


public function update($where = null)
{
  $sql_update_string = $this->formatUpdateCommand($where);
  if($sql_update_string == false)
   return false;

  $l_result = $this->query($sql_update_string);
  $this->clearDataBuffer();
  return $l_result;
}


private function formatDeleteCommand($where = null)
{

  $sql = 'DELETE FROM '.$this->table_name;

  if($where != null)
   $sql .= ' WHERE '.$where;

  return $sql;

}

public function delete($where = null)
{
  $sql_delete_string = $this->formatDeleteCommand($where);

  $l_result = $this->query($sql_delete_string);
  $this->clearDataBuffer();
  return $l_result;
}

private function formatSelectCommand($cols = '*',$extra = null)
{
  $sql = 'SELECT '.$cols.' FROM '.$this->table_name;

  if($extra != null)
   $sql .= ' '.$extra;

  return $sql;

}

private function select($cols = '*',$extra=null)
{
  $sql_select_string = $this->formatSelectCommand($cols,$extra);

   $l_result = $this->query($sql_select_string);
   $this->clearDataBuffer();
 //echo $sql_select_string;
  if(@mysql_num_rows($this->db_query_result) == 0)
     return false;

     $this->fetch(); // get first record
   return $l_result;
}

public function fetch()
{
   if($this->db_query_result)
    $this->db_current_object = @mysql_fetch_object($this->db_query_result);
}

public function count()
{
  if(!$this->db_query_result)
   return 0;

  return @mysql_num_rows($this->db_query_result);
}

public function free()
{
  if($this->db_query_result)
   @mysql_free_result($this->db_query_result);
}

public function filter()
{
  global $UNDERQL;
  $l_args_num = func_num_args();
  if($l_args_num < 2)
   return false;

  $l_filter_callback = $UNDERQL['filter']['prefix'].func_get_arg(0);
  if(!function_exists($l_filter_callback))
   return false;

  for($i = 1; $i < $l_args_num; $i++)
     $this->data_buffer[func_get_arg($i)] = $l_filter_callback($this->data_buffer[func_get_arg($i)]);

  return true;
}

public function checker($checker,$value){

   global $UNDERQL;
   $checker_callback = $UNDERQL['checker']['prefix'].$checker;
   if(!function_exists($checker_callback))
    return false;

   return $checker_callback($this->data_buffer[$value]);

}

/*public function apply(){

}*/

public function query($query)
{
    $this->free();
    $this->db_query_result = @mysql_query($query);

    if($this->db_query_result)
    {
      if(@mysql_num_rows($this->db_query_result) > 0)
        $this->fetch();

      return true;
    }

   return false;
}

public function __get($key)
{
   if($this->db_current_object)
    return $this->db_current_object->$key;

   return '';
}

public function readFields()
{

  global $UNDERQL;


  if(isset($UNDERQL['table'][$this->table_name]))
   return;

  $l_fs = @mysql_list_fields($UNDERQL['db']['name'],$this->table_name);
  $l_fq = @mysql_query('SHOW COLUMNS FROM `'.$this->table_name.'`');
  $l_fc = @mysql_num_rows($l_fq);
  @mysql_free_result($l_fq);
  $i = 0;
  $this->table_fields_names = array();
  $this->string_fields = array();

  while($i < $l_fc)
  {
     $l_f = mysql_fetch_field($l_fs);

     if($l_f->numeric != 1)
     {
         $this->string_fields[@count($this->string_fields)] = $l_f->name;
        $this->table_fields_names [@count($this->table_fields_names)] = $l_f->name;
     }

     $i++;
  }

}

}

 /* underQL instance (object) */
$_ = new underQL();

?>