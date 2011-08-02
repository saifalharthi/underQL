
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

require_once('config.php');
require_once('langs/'.$UNDERQL['lang']['module'].'.php');
require_once('uti.php');
require_once('rule.php');
require_once('filter.php');
require_once('checker.php');


define ('UQL_RULE_MATCHED',0xE1);
define ('UQL_RULE_NOT_MATCHED',0xE2);
define ('UQL_RULE_NOP',0xE3);
/*This is returned if all rules applied with no problems*/
define ('UQL_RULE_OK',0xE4);
define ('UQL_RULE_FAIL',0xE5); // when rule fail of all rules



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
     // private $table_fields_names_all;
      private $table_name;
      // table name that is accepting all instructions from the object
      // DB connectivity
      private $db_handle;
      private $db_query_result;
      private $db_current_object;

      private $rules_objects_list;

      // (in/out) filters
      private $in_filters;
      private $out_filters;
      //private $is_out_filters_applied;
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
            //$this->table_fields_names_all = array();
            $this->table_name = null;
            $this->rules_objects_list = array();
            $this->in_filters = array();
            $this->out_filters = array();
           // $this->is_out_filters_applied = array();
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

      /*private function getTableFieldsNames()
      {
         if($this->table_name == null)
          return null;

         $l_count = @count($this->table_fields_names_all);
         if(isset($this->table_fields_names_all[$this->table_name]))
          return $this->table_fields_names_all[$this->table_name];

         $this->table_fields_names_all[$this->table_name] = array();

         $sql = sprintf('SHOW COLUMNS FROM `%s`',$this->table_name);
         $sql_result = @mysql_query($sql);
         if(!$sql_result)
          return null;

         $sql_result_count = @mysql_num_rows($sql_result);
         for($i = 0; $i < $sql_result_count; $i++)
         {
           $row = @mysql_fetch_row($sql_result);
           //print_r($row);
           echo $row[3];
           $this->table_fields_names_all[@count($this->table_fields_names_all)] = $row[0];

         }

         @mysql_free_result($sql_result);
         return $this->table_fields_names_all;

      }*/

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

       private function applyInFilter($key,$val)
      {
          global $UNDERQL;
          $value = $val;
                 if(isset($this->data_buffer[$key]))
                    {
                      if((isset($this->in_filters[$this->table_name][$key]))&&
                        (@count($this->in_filters[$this->table_name][$key]) != 0))
                        {

                            // apply out filters here

                          $filters_count = @count($this->in_filters[$this->table_name][$key]);
                          //$value = $this->db_current_object->$key;
                          $filter_callback = $UNDERQL['filter']['prefix'];
                          $filters_list = $this->in_filters[$this->table_name][$key];
                          for($i = 0; $i < $filters_count; $i++)
                          {
                             $filter_callback = $UNDERQL['filter']['prefix'].$filters_list[$i];
                             $value = $filter_callback($this->data_buffer[$key],UQL_FILTER_IN);
                             //$this->is_out_filters_applied[$key] = true;
                          }

                        }

                    }
         return $value;

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


         /*  if(@count($this->data_buffer) == 0) // no fields
             return UQL_RULE_OK; */

           $l_rules_object_count = @count($this->rules_objects_list);
           if($l_rules_object_count == 0)
           {
             $this->data_buffer[$key] = $this->applyInFilter($key,$this->data_buffer[$key]);
              return UQL_RULE_OK;
           }

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

           $this->data_buffer[$key] = $this->applyInFilter($key,$this->data_buffer[$key]);

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
           {
               $this->db_current_object = @ mysql_fetch_object( $this->db_query_result );
               if(!$this->db_current_object)
                return null;

               $l_fields_names = $this->table_fields_names[$this->table_name];//[$this->table_name];// $this->getTableFieldsNames();
               //print_r($l_fields_names);
               if($l_fields_names)
               {
                  foreach ($l_fields_names as $field_index => $field_name)
                 {
                           // echo $this->db_current_object->$field_name.'<br />';
                    if(@isset($this->db_current_object->$field_name))
                    {

                        $this->db_current_object->$field_name =
                               $this->applyOutFilter($field_name);
                    }
                 }
               }
           }
           else
            return null;

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
            if ( $l_args_num < 3 )
                  return false;
            $filter_name = func_get_arg( 0 );

            $l_filter_callback = $UNDERQL['filter']['prefix'] . $filter_name;

            if ( !function_exists( $l_filter_callback ))
                  return false;

            switch(func_get_arg(1))
            {
              case UQL_FILTER_IN:

               for ( $i = 2; $i < $l_args_num; $i++ )
                  {
                    if(!isset($this->in_filters[$this->table_name][func_get_arg( $i )]))
                     $this->in_filters[$this->table_name][func_get_arg( $i )] = array($filter_name);
                    else
                      {
                        $_temp = $this->in_filters[$this->table_name][func_get_arg( $i )];
                       // if(!in_array($filter_name,$_temp))
                          $_temp[@count($_temp)] = $filter_name;


                        $this->in_filters[$this->table_name][func_get_arg( $i )] = $_temp;
                      }
                  }
                  return true;
              case UQL_FILTER_OUT:
               for ( $i = 2; $i < $l_args_num; $i++ )
                  {
                    if(!isset($this->out_filters[$this->table_name][func_get_arg( $i )]))
                    {
                       $this->out_filters[$this->table_name][func_get_arg( $i )] = array($filter_name);
                       //print_r($this->out_filters);
                    }
                    else
                      {
                        $_temp = $this->out_filters[$this->table_name][func_get_arg( $i )];
                        //print_r($_temp);
                        //if(!in_array($filter_name,$_temp))
                          $_temp[@count($_temp)] = $filter_name;

                        $this->out_filters[$this->table_name][func_get_arg( $i )] = $_temp;

                      }
                  }
                  return true;
                  default : return false;
            }
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
                  /*if ( @ mysql_num_rows( $this->db_query_result ) > 0 )
                        $this->fetch( ); */
                  return true;
            }
            return false;
      }


      private function applyOutFilter($key)
      {
          global $UNDERQL;
                   //
            $value = null;
                  //echo $key;

            if ( isset($this->db_current_object) )
              {
                 if(isset($this->db_current_object->$key))
                    {         //echo @count($this->out_filters[$this->table_name][$key]);
                      if((isset($this->out_filters[$this->table_name][$key]))&&
                        (@count($this->out_filters[$this->table_name][$key]) != 0))
                        {          $value = $this->db_current_object->$key;
                            // apply out filters here

                          $filters_count = @count($this->out_filters[$this->table_name][$key]);
                          $value = $this->db_current_object->$key;
                          $filter_callback = $UNDERQL['filter']['prefix'];
                          $filters_list = $this->out_filters[$this->table_name][$key];
                          for($i = 0; $i < $filters_count; $i++)
                          {
                             // echo $this->db_current_object->$key;
                             $filter_callback = $UNDERQL['filter']['prefix'].$filters_list[$i];
                             $value = $filter_callback($this->db_current_object->$key,UQL_FILTER_OUT);
                          }

                        }

                      return $value;
                    }
              }

            return ((isset($this->db_current_object->$key)) ? $this->db_current_object->$key : $value);
      }

      public function __get( $key )
      {
         if(isset($this->db_current_object->$key))
             return $this->db_current_object->$key;
         else
             return null;
         // return $this->applyOutFilter($key);
      }


      public function readFields( )
      {
            global $UNDERQL;
           /* if ( isset ( $UNDERQL['table'][$this->table_name] ))
                  return; */
            $l_fs = @ mysql_list_fields( $UNDERQL['db']['name'], $this->table_name );
            $l_fq = @ mysql_query( 'SHOW COLUMNS FROM `' . $this->table_name . '`' );
            $l_fc = @ mysql_num_rows( $l_fq );
            @ mysql_free_result( $l_fq );
            $i = 0;
            if(!isset($this->table_fields_names[$this->table_name]))
              $this->table_fields_names[$this->table_name] = array( );

            $this->string_fields = array( );
            while ( $i < $l_fc )
            {
                  $l_f = mysql_fetch_field( $l_fs );
                  if ( $l_f->numeric != 1 )
                  {
                        $this->string_fields[@ count( $this->string_fields )] = $l_f->name;
                  }
                   $this->table_fields_names[$this->table_name]
                     [@count($this->table_fields_names[$this->table_name])]  = $l_f->name;
                  $i++;
            }
            //print_r($this->table_fields_names);
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