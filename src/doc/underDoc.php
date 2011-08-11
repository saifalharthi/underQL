<?php

define('UQL_DOC_TEMPLATE','standard');

class underDocFunction
{
   public $function_ref;
   public $name;
   public $parameters;
   public $parameters_count;
   public $required_parameters_count;
   public $is_user_defined;
   public $file_name;
   public $start_line;
   public $end_line;

   public function __construct($fname)
   {
     $this->function_ref = new ReflectionFunction($fname);
     $this->name = $this->function_ref->getName();
     $this->parameters_count = $this->function_ref->getNumberOfParameters();
     $this->required_parameters_count = $this->function_ref->getNumberOfRequiredParameters();
     $this->start_line = $this->function_ref->getStartLine();
     $this->end_line = $this->function_ref->getEndLine();
     $this->file_name = basename($this->function_ref->getFileName());
     $this->function_ref->isUserDefined();
     if($this->parameters_count != 0)
       {
         $parms = $this->function_ref->getParameters();
         for($i = 0; $i < $this->parameters_count; $i++)
            $this->parameters[$parms[$i]->name] = $parms[$i];
       }
   }

 }

 /*
 uql_doc_template_begin(){}
 uql_doc_template_function(){}
 uql_doc_template_method(){}
 uql_doc_template_class_being(){}
 uql_doc_template_class_end(){}
 uql_doc_template_attribute(){}
 uql_doc_template_constant(){}
 uql_doc_template_end(){}
 */

?>