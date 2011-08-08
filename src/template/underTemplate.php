<?php

define ('UQL_TEMPLATE_DIR','/');

class UQLTemplateSegment
{
  private $segment_name;
  private $segment_vars;
  private $segment_content;
  private $execute_buffer; // save last executed result

  public function __construct($seg_name)
  {
    $this->setSegmentName($seg_name);
    $this->segment_vars = array();
    $this->segment_content = null;
    $this->execute_buffer = null;
  }

  public function __set($var,$val)
  {
    $this->segment_vars[$var] = $val;
  }

  public function __get($var)
  {
    if(isset($this->segment_vars[$var]))
     return $this->segment_vars[$var];

    return '';
  }

  public function __destruct()
  {
    $this->segment_name = null;
    $this->segment_vars = null;
    $this->segment_content = null;
    $this->execute_buffer = null;
  }

  public function setSegmentName($seg_name)
  {
    $this->segment_name = $seg_name;
  }

  public function getSegmentName()
  {
    return $this->segment_name;
  }

  public function setSegmentContent($content)
  {
    $this->segment_content = $content;
  }

  public function getSegmentContent()
  {
    return $this->segment_content;
  }

  public function getSegmentVars()
  {
    return $this->segment_vars;
  }

  public function getExecutedBuffer()
  {
    return $this->execute_buffer;
  }

  public function executeSegment()
  {
    $vars_count = @count($this->segment_vars);
    if($vars_count == 0)
     return $this->segment_content;

    if(empty($this->segment_content))
     return '';



    $this->execute_buffer = null;
    foreach($this->segment_vars as $key => $val)
    {
      $var_name = '$'.$key;
      if($this->execute_buffer == null)
       $this->execute_buffer = str_replace($var_name,$val,$this->segment_content);
      else
       $this->execute_buffer = str_replace($var_name,$val,$this->execute_buffer);
    }

    return $this->execute_buffer;
  }
}

define('UQL_TEMPLATE_FROM_FILE',100);
define('UQL_TEMPLATE_FROM_STRING',200);

class UQLTemplateParser{

 private $segment_rule_expression;
 private $segments;
 private $template;
 private $template_path;
 private $template_source;
 private $parsing_matches;
 private $parsing_matches_count;

 public function __construct($value,$type = UQL_TEMPLATE_FROM_FILE)
 {
   $this->setTemplate($value,$type);
   $this->segment_rule_expression = '/<usegment[\s]+name[\s]*=[\s]*"(.+)"[\s]*>[\s]*(.*)[\s]*<\/usegment>/imsUx';
 }

 public function resetParser()
 {
   $this->template = null;
   $this->template_path = null;
   $this->segments = array();
   $this->parsing_matches = array();
   $this->parsing_matches_count = 0;
   $this->template_source = null;
 }


 public function setTemplate($value, $type = UQL_TEMPLATE_FROM_FILE)
 {
    $this->resetParser();

   if($type == UQL_TEMPLATE_FROM_FILE)
   {
      $this->template = implode("\n",file($value));
      $this->template_path = $value;
   }
   else
    {
      $this->template = $value;
      $this->template_path = null;
    }
    $this->template_source = $type;
 }

 public function parseTemplate()
 {
   if(@count($this->segments) > 0)
    return true;

   if($this->template)
   {
      $this->parsing_matches_count = @preg_match_all(
                                       $this->segment_rule_expression,$this->template,
                                       $this->parsing_matches
                                       );
   if(!$this->parsing_matches_count)
    return false;

   for($i = 0; $i < $this->parsing_matches_count; $i++)
   {
      $_segment = new UQLTemplateSegment($this->parsing_matches[1][$i]);
      $_segment->setSegmentContent($this->parsing_matches[2][$i]);
      $this->segments[$this->parsing_matches[1][$i]] = $_segment;
   }
    return true;
   }
 }

 public function isThereAnySegment()
 {
   return $this->parsing_matches_count;
 }

 public function getSegments()
 {
   return $this->segments;
 }

}

class underTemplate
{
  private $parser;


  public function __construct($value,$type = UQL_TEMPLATE_FROM_FILE)
  {
     $this->parser = new UQLTemplateParser($value,$type);
     $this->parser->parseTemplate();
  }

  public function isThereAnySegment()
  {
    return $this->parser->isThereAnySegment();
  }

  public function findSegment($name)
  {
    if($this->parser->isThereAnySegment())
     {
       $segments = $this->parser->getSegments();
       if(isset($segments[$name]))
        return $segments[$name];
     }
     return null;
  }

  public function getSegments()
  {
    return $this->parser->getSegments();
  }

  public function setTemplate($value,$type = UQL_TEMPLATE_FROM_FILE)
  {
    $this->parser->setTemplate($value,$type);
  }

  public function __destruct()
  {
    $this->parser->resetParser();
  }
}

 require_once('../multi/underQL.php');
 $athdak = new underQL('athdak_tasks');
 $athdak->select();

$template = new underTemplate('uql_template_demo.html');

if($template->isThereAnySegment())
{
 $header = $template->findSegment('header');
 $header->title = 'underQL & underTemplate';
 echo $header->executeSegment();



 $loop = $template->findSegment('tasks');
 while($athdak->fetch())
 {
   $loop->id = $athdak->id;
   $loop->task = $athdak->task;
   echo $loop->executeSegment();
 }

 $footer = $template->findSegment('footer');
 echo $footer->executeSegment();
}

?>