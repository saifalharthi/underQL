
<?php

/*
                 UnderQL
           Abdullah E. Almehmadi
         <cs.abdullah@hotmail.com>
        6:25 am 26-08-32 : 2011-07-27
       MPL(Mozilla Public License 1.1)
    domain registered 6:32 am <www.underql.com>
              1.0.0.Beta


*/

/* database host */
$UNDERQL['db']['host'] = 'localhost';

/* database name */
$UNDERQL['db']['name'] = 'underQL';

/* database user name */
$UNDERQL['db']['user'] = 'root';

/* database password */
$UNDERQL['db']['password'] = '';

/* database encoding system for database operations */
$UNDERQL['db']['encoding'] = 'utf8';

/* store some information about every table that you work with for
some internal purposes. */
$UNDERQL['table'] = array( );

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
// <!-- Filter APIs Begin -->
$UNDERQL['filter']['prefix'] = 'uql_filter_';




function uql_filter_xss( $value )
{
      return strip_tags( $value );
}


// <!-- Filter APIs END   -->
// <!-- Checker APIs Begin -->


$UNDERQL['checker']['prefix'] = 'uql_checker_';


function uql_checker_email( $value )
{
      return filter_var( $value, FILTER_VALIDATE_EMAIL );
}
// <!-- Checker APIs END   -->


$UNDERQL['error']['prefix'] = 'UnderQL Error : ';
$UNDERQL['warning']['prefix'] = 'UnderQL Warning : ';


$UNDERQL['rule']['uql_prefix'] = 'uql_rule_';

$UNDERQL['rule']['uql_fail_messages'] = array(

    'length' => 'Length of %s was exceeded the maximum length (%d)',
    'required' => '%s value is required'

);

define ('UQL_RULE_MATCHED',0xE1);
define ('UQL_RULE_NOT_MATCHED',0xE2);
define ('UQL_RULE_NOP',0xE3);
/*This is returned if all rules applied with no problems*/
define ('UQL_RULE_OK',0xE4);
define ('UQL_RULE_FAIL',0xE5); // when rule fail of all rules

function uql_uti_get_rule_error_message($key)
{
   global $UNDERQL;
   $l_list = $UNDERQL['rule']['uql_fail_messages'];
   if(isset($l_list[$key]))
    return $l_list[$key];

   return '';
}

function uql_rule_length($rules, $name, $value,$alias = null)
{

   if(is_array($rules))
   {
     if((!isset($rules[$name])) || (!isset($rules[$name]['length'])))
      return UQL_RULE_NOP;

     $v = (int) $rules[$name]['length'];

     if($alias != null)
      $caption = $alias;
     else
      $caption = $name;

     $error_message = sprintf(uql_uti_get_rule_error_message('length'),$caption,$v);

     //$v += 2; //escape string single quotes
     if($v < strlen($value))
      return $error_message;
     else
      return UQL_RULE_MATCHED;
   }

   return UQL_RULE_NOP;
}
//////////////////////////////////////////////

function uql_rule_required($rules,$name,$value,$alias = null)
{

   if(is_array($rules))
   {
     if((!isset($rules[$name])) || (!isset($rules[$name]['required'])))
      return UQL_RULE_NOP;

     $v = trim($value);

     if($alias != null)
      $caption = $alias;
     else
      $caption = $name;

     $error_message = sprintf(uql_uti_get_rule_error_message('required'),$caption,$v);

     if(strlen($v) == 0)
     return $error_message;
     else
      return UQL_RULE_MATCHED;
   }

   return UQL_RULE_NOP;
}

////////////////////////////////////////////////////

class UQLRule
{
      private $table_name;
      private $rules;
      private $aliases;
      public  $rules_error_flag;
      public  $rules_error_message;

      public function __construct( $tname )
      {
            $this->table_name = $tname;
            $this->rules = array( );
            $this->rules['UQL']['tablename'] = $tname;
            $this->aliases = array( );
            $this->rules_error_flag = false;
            $this->rules_error_message = '';
      }


      public function getTableName( )
      {
            return $this->table_name;
      }


      public function getRules( )
      {
            return $this->rules;
      }


      public function getAliases( )
      {
            return $this->aliases;
      }


      private function addRule( $rule_name, $field, $value )
      {
            if ( !isset ( $this->rules[$field] ))
                  $this->rules[$field] = array( );
            if ( is_array( $value ))
                  array_shift( $value );
            // remove the first element becaust it's contains the rule name.
            $this->rules[$field][$rule_name] = $value;
      }


      public function addAlias( $name, $value )
      {
            $this->aliases[$name] = $value;
      }


      public function __call( $func, $args )
      {
            $l_args_count = @ count( $args );
            if ( $l_args_count == 0 )
                  return;
            else if ( $l_args_count == 1 )
                        $this->addRule( $args[0], $func, true );
                  //args[0] contains the rule name.
            else if ( $l_args_count == 2 )
                              $this->addRule( $args[0], $func, $args[1] );
                        // one value
            else
                     {
                        // array_shift($args);
                              $this->addRule( $args[0], $func, $args );
                              // many values
                     }
      }

      public function applyRule($rule_name,$name,$value)
      {
        global $UNDERQL;
        $l_rule_callback = $UNDERQL['rule']['uql_prefix'].$rule_name;
        if(!function_exists($l_rule_callback))
         return UQL_RULE_NOP;
//         echo $l_rule_callback;
         if(isset($this->aliases[$name]))
            $l_result = $l_rule_callback($this->rules,$name,$value,$this->aliases[$name]);
         else
            $l_result = $l_rule_callback($this->rules,$name,$value);

         if(is_string($l_result)) // catch error
         {
           $this->rules_error_message = $l_result;
           $this->rules_error_flag = true;
           return UQL_RULE_NOT_MATCHED;
         }

         return $l_result;

      }


}

class underQL
{
//used by insert instruction
      private $data_buffer;
      // used to stor key/value that entered by user
      private $string_fields;
      // contains the name of all string fields to use it to add single qoute to the value.
      private $table_fields_names;
      private $table_name;
      // table name that is accepting all instructions from the object
      // DB connectivity
      private $db_handle;
      private $db_query_result;
      private $db_current_object;

      private $rules_objects_list;
      // Errors
      private $err_message;

      /* Initialization */


      public function __construct( $host = null, $user = null, $pass = null, $dbname = null )
      {
            global $UNDERQL;

            $l_host   = ($host   == null) ? $UNDERQL['db']['host']     : $host;
            $l_user   = ($user   == null) ? $UNDERQL['db']['user']     : $user;
            $l_pass   = ($pass   == null) ? $UNDERQL['db']['password'] : $pass;
            $l_dbname = ($dbname == null) ? $UNDERQL['db']['name']     : $dbname;

            $this->db_handle = @ mysql_connect( $UNDERQL['db']['host'], $UNDERQL['db']['user'], $UNDERQL['db']['password'] );
            if ( !$this->db_handle )
                  $this->error( 'Unable to connect to DB..!' );
            if ( !( @ mysql_select_db( $UNDERQL['db']['name'] )))
            {
                  @ mysql_close( $this->db_handle );
                  $this->error( 'Unable to select DB..!' );
            }
            @ mysql_query( "SET NAMES '" . $UNDERQL['db']['encoding'] . "'" );
            $this->db_current_object = null;
            $this->db_query_result = false;
            $this->table_fields_names = array( );
            $this->rules_objects_list = array();
            $this->clearDataBuffer( );
      }


      public function __destruct( )
      {
            $this->finish( );
      }


      private function clearDataBuffer( )
      {
            $this->data_buffer = array( );
            $this->err_message = '';
      }


      private function error( $msg )
      {
            global $UNDERQL;
            die( '<code><b><font color ="#FF0000">' . $UNDERQL['error']['prefix'] . '</font></b></code><code>' . $msg . '</code>' );
      }


      public function table( $tname )
      {
            global $UNDERQL;
            if ( !array_key_exists( $tname, $UNDERQL['table'] ))
            {
                  $l_result = @ mysql_query( 'SHOW TABLES FROM `' . $UNDERQL['db']['name'] . '`' );
                  $l_count = @ mysql_num_rows( $l_result );
                  if ( $l_count == 0 )
                        $this->error( $tname . ' dose not exist. ' . mysql_error( ));
                  while ( $l_t = @ mysql_fetch_row( $l_result ))
                  {
                        if ( strcmp( $tname, $l_t[0] ) == 0 )
                        {
                              $this->table_name = $tname;
                              // $this->string_fields = $UNDERQL['table'][$tname] = array();
                              @ mysql_free_result( $l_result );
                              $this->readFields( );
                              return;
                        }
                  }
            }
            else
            {
            //$this->string_fields = $UNDERQL['table'][$tname];
                  $this->table_name = $tname;
                  $this->readFields( );
                  @ mysql_free_result( $l_result );
                  return;
            }
            @ mysql_free_result( $l_result );
            $this->error( $tname . ' dose not exist' );
      }


      public function __invoke( $tname, $cols = '*', $extra = null )
      {
            $this->table( $tname );
            return $this->select( $cols, $extra );
      }


      public function __set( $key, $val )
      {
            $this->data_buffer[$key] = $val;

            if(isset($this->rules_objects_list[$this->table_name]))
              {
               $l_target = $this->rules_objects_list[$this->table_name];
               if($l_target->rules_error_flag)
                   return UQL_RULE_FAIL;
              }


           if(@count($this->data_buffer) == 0) // no fields
             return UQL_RULE_OK;

           $l_rules_object_count = @count($this->rules_objects_list);
           if($l_rules_object_count == 0)
            return UQL_RULE_OK;

           if(!isset($this->rules_objects_list[$this->table_name]))
             return UQL_RULE_OK;

           $l_target_rule = $this->rules_objects_list[$this->table_name];

           $l_rules = $l_target_rule->getRules();
           if(@count($l_rules) == 0)
            return UQL_RULE_OK;


             if(strcmp($key,'UQL') == 0)
              return UQL_RULE_OK;

             if(!isset($l_rules[$key]))
              return UQL_RULE_OK;

             $rules_list = $l_rules[$key];

             foreach($rules_list as $rule_name =>$rule_value)
             {
               // echo $rule_name.$rule_value.'<br />';
               if(!isset($this->data_buffer[$key]))
                continue;
               // echo $field.' : '.$rule_name.'('.$rule_value.')<br />';
               if($l_target_rule->applyRule($rule_name,$key,$this->data_buffer[$key])
                  == UQL_RULE_NOT_MATCHED)
                return UQL_RULE_FAIL;
             }

           return UQL_RULE_OK;
      }

      public function isRulesPassed()
      {
        if(!isset($this->rules_objects_list[$this->table_name]))
         return true;

        //current table rules object
        $l_target = $this->rules_objects_list[$this->table_name];

        return ($l_target->rules_error_flag == false);
      }

      public function getRuleError()
      {
        if(!isset($this->rules_objects_list[$this->table_name]))
         return '';

        //current table rules object
        $l_target = $this->rules_objects_list[$this->table_name];
        if($l_target->rules_error_flag)
         return $l_target->rules_error_message;

        return '';
      }

      public function attachRule($rule_object)
      {
          if(!($rule_object instanceof UQLRule))
           return false;

          $this->rules_objects_list[$rule_object->getTableName()] = $rule_object;
          return true;
      }

      public function detachRule($tname)
      {
        if(isset($this->rules_objects_list[$tname]))
         $this->rules_objects_list[$tname] = null;
      }


      private function quote( )
      {
            if ( @ count( $this->data_buffer ) == 0 )
                  return;
            foreach ( $this->data_buffer as $key => $val )
            {
                  if ( in_array( $key, $this->string_fields ))
                        $this->data_buffer[$key] = @"'".mysql_real_escape_string($val,$this->db_handle)."'";
                  else
                        $this->data_buffer[$key] = $val;
            }
      }



      private function formatInsertCommand( )
      {
            $data_buffer_length = @ count( $this->data_buffer );
            if ( $data_buffer_length == 0 )
                  return false;
            $sql = 'INSERT INTO ' . $this->table_name . ' ';
            $sql_columns = '(';
            $sql_values = ' VALUE(';
            $this->quote( );
            $i = 0;
            foreach ( $this->data_buffer as $k => $v )
            {
                  $sql_columns .= $k;
                  $sql_values .= $v;
                  if ( ( $i + 1 ) < $data_buffer_length )
                  {
                        $sql_columns .= ',';
                        $sql_values .= ',';
                  }
                  $i++;
            }
            $sql_columns .= ')';
            $sql_values .= ')';
            $sql .= $sql_columns . $sql_values;
            return $sql;
      }


      public function insert( )
      {
            $sql_insert_string = $this->formatInsertCommand( );
            if ( $sql_insert_string == false )
                  return false;

            $l_result = $this->query( $sql_insert_string );
            $this->clearDataBuffer( );
            return $l_result;
      }


      private function formatUpdateCommand( $where = null )
      {
            $data_buffer_length = @ count( $this->data_buffer );
            if ( $data_buffer_length == 0 )
                  return false;
            $sql = 'UPDATE ' . $this->table_name . ' SET ';
            $sql_clues = '';
            $this->quote( );
            $i = 0;
            foreach ( $this->data_buffer as $k => $v )
            {
                  $sql_clues .= ' ' . $k . '=' . $v;
                  if ( ( $i + 1 ) < $data_buffer_length )
                        $sql_clues .= ',';
                  $i++;
            }
            $sql .= $sql_clues;
            if ( $where != null )
                  $sql .= ' WHERE ' . $where;
            return $sql;
      }


      public function update( $where = null )
      {
            $sql_update_string = $this->formatUpdateCommand( $where );
            if ( $sql_update_string == false )
                  return false;

            $l_result = $this->query( $sql_update_string );
            $this->clearDataBuffer( );
            return $l_result;
      }


      private function formatDeleteCommand( $where = null )
      {
            $sql = 'DELETE FROM ' . $this->table_name;
            if ( $where != null )
                  $sql .= ' WHERE ' . $where;
            return $sql;
      }


      public function delete( $where = null )
      {
            $sql_delete_string = $this->formatDeleteCommand( $where );
            $l_result = $this->query( $sql_delete_string );
            $this->clearDataBuffer( );
            return $l_result;
      }


      private function formatSelectCommand( $cols = '*', $extra = null )
      {
            $sql = 'SELECT ' . $cols . ' FROM ' . $this->table_name;
            if ( $extra != null )
                  $sql .= ' ' . $extra;
            return $sql;
      }


      public function select( $cols = '*', $extra = null )
      {
            $sql_select_string = $this->formatSelectCommand( $cols, $extra );
            $this->free( );
            $l_result = $this->query( $sql_select_string );
            $this->clearDataBuffer( );
            //echo $sql_select_string;
            if ( @ mysql_num_rows( $this->db_query_result ) == 0 )
                  return false;
            // $this->fetch();
            // get first record
            return $l_result;
      }


      public function fetch( )
      {
            if ( $this->db_query_result )
                  $this->db_current_object = @ mysql_fetch_object( $this->db_query_result );
      }


      public function count( )
      {
            if ( !$this->db_query_result )
                  return 0;
            return @ mysql_num_rows( $this->db_query_result );
      }


      public function affected( )
      {
            if ( ( !$this->db_handle ))
                  return 0;
            return @ mysql_affected_rows( $this->db_handle );
      }


      public function free( )
      {
            if ( $this->db_query_result )
                  @ mysql_free_result( $this->db_query_result );
      }


      public function filter( )
      {
            global $UNDERQL;
            $l_args_num = func_num_args( );
            if ( $l_args_num < 2 )
                  return false;
            $l_filter_callback = $UNDERQL['filter']['prefix'] . func_get_arg( 0 );
            if ( !function_exists( $l_filter_callback ))
                  return false;
            for ( $i = 1; $i < $l_args_num; $i++ )
                  $this->data_buffer[func_get_arg( $i )] = $l_filter_callback( $this->data_buffer[func_get_arg( $i )] );
            return true;
      }


      public function checker( $checker, $value )
      {
            global $UNDERQL;
            $checker_callback = $UNDERQL['checker']['prefix'] . $checker;
            if ( !function_exists( $checker_callback ))
                  return false;
            return $checker_callback( $this->data_buffer[$value] );
      }

      /*public function apply(){

      }*/


      public function query( $query )
      {
            $this->free( );
            $this->db_query_result = @ mysql_query( $query );
            if ( $this->db_query_result )
            {
                  if ( @ mysql_num_rows( $this->db_query_result ) > 0 )
                        $this->fetch( );
                  return true;
            }
            return false;
      }


      public function __get( $key )
      {
            if ( $this->db_current_object )
              {
                 if(isset($this->db_current_object->$key))
                    return $this->db_current_object->$key;
              }

            return '';
      }


      public function readFields( )
      {
            global $UNDERQL;
            if ( isset ( $UNDERQL['table'][$this->table_name] ))
                  return;
            $l_fs = @ mysql_list_fields( $UNDERQL['db']['name'], $this->table_name );
            $l_fq = @ mysql_query( 'SHOW COLUMNS FROM `' . $this->table_name . '`' );
            $l_fc = @ mysql_num_rows( $l_fq );
            @ mysql_free_result( $l_fq );
            $i = 0;
            $this->table_fields_names = array( );
            $this->string_fields = array( );
            while ( $i < $l_fc )
            {
                  $l_f = mysql_fetch_field( $l_fs );
                  if ( $l_f->numeric != 1 )
                  {
                        $this->string_fields[@ count( $this->string_fields )] = $l_f->name;
                        $this->table_fields_names[@ count( $this->table_fields_names )] = $l_f->name;
                  }
                  $i++;
            }
      }


      public function finish( )
      {
            $this->free( );
            if ( $this->db_handle )
                  @ mysql_close( $this->db_handle );
      }


}

/* underQL instance (object) */


$_ = new underQL( );
?>