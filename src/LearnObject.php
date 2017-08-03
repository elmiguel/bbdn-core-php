<?php namespace bbdn\core;
/**
*  LearnObject
*
*  This class is the base functionality of all REST API actions for "Learn Objects"
*  Regardless of what Learn Object, these methods are the main HTTP protocols that
*  interact with the BbRestAPI's to it's respective API route.
*
*  @author Michael Bechtel
*/
class LearnObject {
  private $learnObjects = [
    'announcements',
    'users',
    'courses',
    'contents',
    'datasources',
    'terms',
    'memberships',
    'system',
    'grades'
  ];


  function __construct($options) {
    $this->verbose = $options['--verbose'];
    // if ($this->verbose) {
    //   var_dump(array_keys((array)$options->args));
    // }

    $base_path = '/learn/api/public/v1/';
    $this->auth = "Bearer " . $options['auth']->getToken();
    $this->target_url =  $options['auth']->getTargetUrl();
    // $this->api_type = [k for k, v in validators.items() if $options[k]][0];
    $this->api_type = '';
    foreach ($options->args as $k=>$v){
      if ($options[$k] == true && in_array($k, $this->learnObjects)) {
        $this->api_type = $k;
      };
    }
    // echo "$ this - >api_type: $this->api_type\n";

    $this->api_path = $base_path . $this->api_type;
    // $this->class_name = $this->api_type.title()[:-1];
    // $this->validator = validators[$this->api_type];
    $this->res = null;
    $this->isPaginated = False;

    # Action controls
    $this->batch = $options['--batch'];
    $this->data = $options['--data'];
    # if a file is provided, override $this->data
    if ($options['--file'] != "None") {
      $tmp = json_encode(file_get_contents($options['--file']));
      if (!$this->batch) {
        $tmp = json_decode($tmp);
      }
      $this->data = $tmp;
    }
        // with open($options['--file']) as f:
        //     tmp = json.loads(f.read());
        //     if not $this->batch:
        //         tmp = json.dumps(tmp);
        //
        //     $this->data = tmp;

    $this->debug = $options['--debug'];
    $this->enrollments = $options['--enrollments'];

    $this->help = $options['--help'];
    $this->method = $options['--method'];
    $this->page = $options['--get-page'];

    $default_params = $options['auth']->getDefaultParams($this->api_type);

    // TODO: re-implement param overrides
    // try:
    //     override_params = json.loads($options['--params']);
    //     default_params.update(override_params) if $options['--params'] else default_params
    //     $this->params = default_params.copy();
    // except ValueError:
    //     $this->params = default_params.copy();
    // except json.decoder.JSONDecodeError:
    //     $this->params = default_params.copy();

    $this->announcements = $options['announcements'];
    $this->contents = $options['contents'];
    $this->courses = $options['courses'];
    $this->datasources = $options['datasources'];
    $this->grades = $options['grades'];
    $this->groups = $options['groups'];
    $this->memberships = $options['memberships'];
    $this->system = $options['system'];
    $this->terms = $options['terms'];
    $this->users = $options['users'];

    // $this->type = $options['--type'].split(',');
    $this->verbose = $options['--verbose'];
    $this->attempts_id = $options['ATTEMPTS-ID'];
    $this->child_course_id = $options['CHILD-COURSE-ID'];
    $this->column_id = $options['COLUMN-ID'];
    $this->column_id = $options['CONTENT-ID'];
    $this->course_id = $options['COURSE-ID'];
    $this->data_source_id = $options['DATA-SOURCE-ID'];
    $this->group_id = $options['GROUP-ID'];
    $this->term_id = $options['TERM-ID'];
    $this->user_id = $options['USER-ID'];
    $this->announcement_id = $options['ANNOUNCEMENT-ID'];

    # batch override id
    $this->current_id = null;
    $this->current_data = null;
  }
}
