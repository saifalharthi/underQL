<?php

define ('UQL_TEMPLATE_DIR','/');
define ('UQL_TEMPLATE_VAR','%');

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

define('UQL_TEMPLATE_PARSER_FILE',100);
define('UQL_TEMPLATE_PARSER_STRING',200);

class UQLTemplateSegmentParser{

 private $segment_rule_expression;
 private $segments;
 private $template;
 private $template_path;
 private $template_source;
 private $parsing_matches;
 private $parsing_matches_count;

 public function __construct($value,$type = UQL_TEMPLATE_PARSER_FILE)
 {
   if($type == UQL_TEMPLATE_PARSER_FILE)
   {
      $this->template = file_get_conents($path);
      $this->template_path = $path;
   }
   else
    {
      $this->template = $value;
      $this->template_path = null;
    }

   $this->segments = array();
   $this->template_source = $type;
   $this->segment_rule_expression = '/<usegment[\s]+name[\s]*=[\s]*"(.+)"[\s]*>[\s]*(.*)[\s]*<[\s]*\/[\s]*usegment[\s]*>/i';//'<[\s]+usegment[\s]+=[\s]+"(.+)"[\s]+>(.+)<[\s]+/usegment[\s]+>';
   $this->parsing_matches = array();
   $this->parsing_matches_count = 0;

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
   // echo $this->parsing_matches_count;
   for($i = 0; $i < $this->parsing_matches_count; $i++)
   {
      $_segment = new UQLTemplateSegment($this->parsing_matches[1][$i]);
      $_segment->setSegmentContent($this->parsing_matches[2][$i]);
      $this->segments[] = $_segment;
   }
    return true;
   }
 }

 public function isSegmentFound()
 {
   return $this->parsing_matches_count;
 }

 public function getSegments()
 {
   return $this->segments;
 }

}

$content =<<<CON
<usegment name = "RapidPHP">
 <h2>\$title</h2><code>\$text</code>
</usegment>

<usegment name ="Zend">www.zend.com</usegment>  <br />
It is very nice template engine that you can attach it with underQL
<usegment name ="underQL">
 under<sup>QL</sup>
</usegment>
CON;

/*$segment = new UQLTemplateSegment('Gamba');
$segment->setSegmentContent($content);
$segment->title = 'Welcome to UQLTemplateSegment !';
$segment->text  = 'It is very nice template engine that you can attach it with underQL';

echo $segment->executeSegment();

*/

$parser = new UQLTemplateSegmentParser($content,UQL_TEMPLATE_PARSER_STRING);
$parser->parseTemplate();
$segs = $parser->getSegments();
$seg = $segs[0];
//echo $seg->getSegmentName();
$seg->title = 'Welcome to UQLTemplateParser';
$seg->text  = 'This is our parser by help of our Loard..!';
//echo $seg->executeSegment();

echo '<pre>';
var_dump($segs);
echo '</pre>';





?>