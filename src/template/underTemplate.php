<?php

/************************************************************/
/*                       underTemplate                      */
/************************************************************/
/*                   Abdullah E. Almehmadi                  */
/*                 <cs.abdullah@hotmail.com>                */
/*              MPL(Mozilla Public License 1.1)             */
/*        domain registered 6:32 am <www.underql.com>       */
/*                       1.0.0.Beta                         */
/************************************************************/


/* Template files directory path */
define ('UQL_TEMPLATE_DIR','./');
/*
 Represent template segment and hold information that related to
  a specific segment.
*/
class UQLTemplateSegment
{
  /* Segment name */
  private $segment_name;
  /* Segment variables list */
  private $segment_vars;
  /* Segment contents */
  private $segment_content;
  /* Execute buffer which is saving the last executed segment result */
  private $execute_buffer;

  /*
   Initialaize segment.
   $seg_name : segment name.
  */
  public function __construct($seg_name)
  {
    $this->setSegmentName($seg_name);
    $this->segment_vars = array();
    $this->segment_content = null;
    $this->execute_buffer = null;
  }

  /*
  Used to save the variable within a segment.
  $var : variable name.
  $val : variable value.
  */
  public function __set($var,$val)
  {
    $this->segment_vars[$var] = $val;
  }

  /*
  Used to retrieve the variable value.
  $var : variable name.
  */
  public function __get($var)
  {
    if(isset($this->segment_vars[$var]))
     return $this->segment_vars[$var];

    return '';
  }

  /*
   Free and reset the segment.
  */
  public function __destruct()
  {
    $this->segment_name = null;
    $this->segment_vars = null;
    $this->segment_content = null;
    $this->execute_buffer = null;
  }

  /*
   Assign the segment name.
   $seg_name : segment name.
  */
  public function setSegmentName($seg_name)
  {
    $this->segment_name = $seg_name;
  }

  /*
   Retrieve the segment name.
  */
  public function getSegmentName()
  {
    return $this->segment_name;
  }

  /*
   Assign the segment content.
   $content : segment content.
  */
  public function setSegmentContent($content)
  {
    $this->segment_content = $content;
  }

  /*
   Retrieve the segment content.
  */
  public function getSegmentContent()
  {
    return $this->segment_content;
  }

  /*
    Retrieve an array that contains all the variables within the segment
    with their values.
  */
  public function getSegmentVars()
  {
    return $this->segment_vars;
  }

  /*
   When you execute segment then, that last result saved in the execute buffer
   and getExecutedBuffer will retrieve that last executed segment result without
   execute it again.
  */
  public function getExecutedBuffer()
  {
    return $this->execute_buffer;
  }

  /*
   Apply replacment for all variables within the segment with their values.
   However, the original value will stll unchanged.
  */
  public function execute()
  {
    $vars_count = @count($this->segment_vars);
    if($vars_count == 0)
     return $this->segment_content;

    if(empty($this->segment_content))
     return '';
                                      // email emailval
    $this->execute_buffer = null;
    foreach($this->segment_vars as $key => $val)
    {
      $var_name = '\$'.$key;
      if($this->execute_buffer == null)
       $this->execute_buffer = preg_replace('/('.$var_name.')\b/',$val,$this->segment_content);
      else
       $this->execute_buffer = preg_replace('/('.$var_name.')\b/',$val,$this->execute_buffer);
    }

    return $this->execute_buffer;
  }

  /*
    Print out the result of executed segment.
  */
  public function output($ret = false)
  {
    if($ret)
     return $this->execute();

    echo $this->execute();
  }
}

/* Input comes from file */
define('UQL_TEMPLATE_FROM_FILE',100);
/* Input comes from string buffer */
define('UQL_TEMPLATE_FROM_STRING',200);

class UQLTemplateParser{

 /* Regular expression for segment syntax */
 private $segment_rule_expression;
 /* List of parsed segments (UQLTemplateSegment) objects */
 private $segments;
 /* Current template contents (File | String) */
 private $template;
 /* If the $template_source comes from file then it contains the file path,
  otherwise, null */
 private $template_path;
 /* Its value specifying the input stream source and it will take one value of :
  UQL_TEMPLATE_FROM_FILE or UQL_TEMPLATE_FROM_STRING */
 private $template_source;
 /* Array that contains the result of parsing process */
 private $parsing_matches;
 /* Number of parsed segments */
 private $parsing_matches_count;

 /*
  Initialize parser.
  $value : if $type is UQL_TEMPLATE_FROM_FILE, then it will contains the file
   path, otherwise, it will contains a normal PHP string.
  $type : UQL_TEMPLATE_FROM_FILE or UQL_TEMPLATE_FROM_STRING.
 */
 public function __construct($value,$type = UQL_TEMPLATE_FROM_FILE)
 {
   $this->setTemplate($value,$type);
   $this->segment_rule_expression = '/<usegment[\s]+name[\s]*=[\s]*"(.+)"[\s]*>[\s]*(.*)[\s]*<\/usegment>/imsUx';
 }

 /*
  Reset parser internal values
 */
 public function resetParser()
 {
   $this->template = null;
   $this->template_path = null;
   $this->segments = array();
   $this->parsing_matches = array();
   $this->parsing_matches_count = 0;
   $this->template_source = null;
 }

 /*
   This method works exactly like the constructor and it will remove all the
    information that releated to the current template and assign new ones for
    the one that you set.
   $value : if $type is UQL_TEMPLATE_FROM_FILE, then it will contains the file
     path, otherwise, it will contains a normal PHP string.
   $type : UQL_TEMPLATE_FROM_FILE or UQL_TEMPLATE_FROM_STRING.
 */
 public function setTemplate($value, $type = UQL_TEMPLATE_FROM_FILE)
 {
    $this->resetParser();

   if($type == UQL_TEMPLATE_FROM_FILE)
   {
      $this->template = implode("\n",file(UQL_TEMPLATE_DIR.$value));
      $this->template_path = $value;
   }
   else
    {
      $this->template = $value;
      $this->template_path = null;
    }
    $this->template_source = $type;
 }

 /* Do parsing based on the current information */
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

 /* Check if is there any segment and return TRUE when it is find one segment
  at least , otherwise, FALSE */
 public function isThereAnySegment()
 {
   return ($this->parsing_matches_count > 0 );
 }

 /* Retrieve the list of segments (UQLTemplateSegment) objects */
 public function getSegments()
 {
   return $this->segments;
 }

}

/*
 Rpresent template file and contains its parser internally.
*/
class underTemplate
{
  /* UQLTemplateParser object that represent the current template parser */
  private $parser;


  /*
   Initialaize template.
  $value : if $type is UQL_TEMPLATE_FROM_FILE, then it will contains the file
   path, otherwise, it will contains a normal PHP string.
  $type : UQL_TEMPLATE_FROM_FILE or UQL_TEMPLATE_FROM_STRING.
  */
  public function __construct($value,$type = UQL_TEMPLATE_FROM_FILE)
  {
     $this->parser = new UQLTemplateParser($value,$type);
     $this->parser->parseTemplate();
  }

  /* Check if is there any segment and return TRUE when it is find one segment
  at least , otherwise, FALSE */
  public function isThereAnySegment()
  {
    return $this->parser->isThereAnySegment();
  }

  /*
   Get the segment object(UQLTemplateSegment) by its name and if it is not
    exist, then, null.
  */
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

  /* Retrieve the list of segments (UQLTemplateSegment) objects */
  public function getSegments()
  {
    return $this->parser->getSegments();
  }

 /*
   This method works exactly like the constructor and it will remove all the
    information that releated to the current template and assign new ones for
    the one that you set.
   $value : if $type is UQL_TEMPLATE_FROM_FILE, then it will contains the file
     path, otherwise, it will contains a normal PHP string.
   $type : UQL_TEMPLATE_FROM_FILE or UQL_TEMPLATE_FROM_STRING.
 */

  public function setTemplate($value,$type = UQL_TEMPLATE_FROM_FILE)
  {
    $this->parser->setTemplate($value,$type);
  }

  /*
    Read a template file and return its content without any replacement.
  */
  public function includePureTemplate($path)
  {
    return @file_get_contents($path);
  }

  /*
    Used to makes you to calling the segment as a function. However, this method
     will called automatically by PHP.
    $func : segment name.
    $args : segment arguments.
  */
  public function __call($func,$args)
  {
     $segment = $this->findSegment($func);
     if(!($segment instanceof UQLTemplateSegment))
      return null;

     return $segment;
  }

  /*
   Integrating the underQL object and underTemplate object by applying the
    current template on the current data that are comming from underQL.
    $underQL : underQL object.
    $segment_name : segment name that you would to apply underQL data on it.

   NOTE : You MUST call a select method before calling this method becaust
    this method suppose you will set the underQL object withou fetching any
    row from its result.
  */
  public function fromUnderQL($underQL,$segment_name)
  {
    if(($underQL instanceof underQL))
    {
        $segment = $this->findSegment($segment_name);
        $result = '';
        while($underQL->fetch())
        {
           $fields = $underQL->getCurrentQueryFields();
           $fcount = @count($fields);
           if($fcount == 0)
            return '';

           for($i = 0; $i <$fcount; $i++)
              $segment->$fields[$i] = $underQL->$fields[$i];

           $result .= $segment->execute();
        }
        return $result;
    }
    return '';
  }

  /*
    Clean up the template.
  */
  public function __destruct()
  {
    $this->parser->resetParser();
  }
}
 
?>