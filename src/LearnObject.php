<?php namespace bbdn\core;
/**
*  LearnObject
*
*  This class is the base functionality of all REST API actions for "Learn Objects"
*  Regardless of what Learn Object, these methods are the main HTTP protocols that
*  interact with the BbRestAPI"s to it"s respective API route.
*
*  @author Michael Bechtel
*/
class LearnObject {
  private $learnObjects = [
    "announcements",
    "users",
    "courses",
    "contents",
    "datasources",
    "terms",
    "memberships",
    "system",
    "grades"
  ];


  function __construct($options) {
    $this->verbose = $options["--verbose"];
    // if ($this->verbose) {
    //   var_dump(array_keys((array)$options->args));
    // }

    $base_path = "/learn/api/public/v1/";
    $this->auth = $options["auth"];
    $this->target_url =  $options["auth"]->getTargetUrl();

    $this->api_type = "";
    foreach ($options->args as $k=>$v){
      if ($options[$k] == true && in_array($k, $this->learnObjects)) {
        $this->api_type = $k;
      };
    }

    $this->api_path = $base_path . $this->api_type;
    $this->class_name = substr($this->api_type, 0, -1);
    // $this->validator = validators[$this->api_type];
    $this->res = null;
    $this->isPaginated = False;

    # Action controls
    $this->batch = $options["--batch"];
    $this->data = $options["--data"];
    # if a file is provided, override $this->data
    if ($options["--file"] != "None") {
      $tmp = json_encode(file_get_contents($options["--file"]));
      if (!$this->batch) {
        $tmp = json_decode($tmp);
      }
      $this->data = $tmp;
    }

    $this->debug = $options["--debug"];
    $this->enrollments = $options["--enrollments"];

    $this->help = $options["--help"];
    $this->method = $options["--method"];
    $this->page = $options["--get-page"];

    $default_params = $options["auth"]->getDefaultParams($this->api_type);
    $this->params = null;
    try {
      if($options["--params"]) {
        $this->default_params = json_decode($options["--params"]);
      } else {
        $this->params = json_decode($this->default_params);
      }
    } catch (Exception $e) {
      $this->params = json_decode($this->default_params);
    } finally {
      $this->params = json_decode($this->default_params);
    }

    $this->announcements = $options["announcements"];
    $this->contents = $options["contents"];
    $this->courses = $options["courses"];
    $this->datasources = $options["datasources"];
    $this->grades = $options["grades"];
    $this->groups = $options["groups"];
    $this->memberships = $options["memberships"];
    $this->system = $options["system"];
    $this->terms = $options["terms"];
    $this->users = $options["users"];

    $this->type = explode(",", $options["--type"]);
    $this->verbose = $options["--verbose"];
    $this->attempts_id = $options["ATTEMPTS-ID"];
    $this->child_course_id = $options["CHILD-COURSE-ID"];
    $this->column_id = $options["COLUMN-ID"];
    $this->column_id = $options["CONTENT-ID"];
    $this->course_id = $options["COURSE-ID"];
    $this->data_source_id = $options["DATA-SOURCE-ID"];
    $this->group_id = $options["GROUP-ID"];
    $this->term_id = $options["TERM-ID"];
    $this->user_id = $options["USER-ID"];
    $this->announcement_id = $options["ANNOUNCEMENT-ID"];

    # batch override id
    $this->current_id = null;
    $this->current_data = null;
  }

  function get(){
    if ($this->verbose){
      echo "[{$this->class_name}:get()] called" . PHP_EOL;
    }

    if ($this->page){
      $url = "https://{$this->target_url}{$this->page}";
      $this->isPaginated = true;
    } else{
      $url = $this->prep_url();
    }

    $this->do_rest($url);
  }


  function prep_url() {
    // $url = "https://{$this->target_url}{$this->api_path}";
    $url = "{$this->target_url}{$this->api_path}";
    if ($this->debug) {
      echo str_repeat("=", 20) . PHP_EOL;
    }

    # Requesting single obj?
    if ($this->api_type == "users") {
      if ($this->current_id || $this->user_id){
        if ($this->debug){
          echo "[prep_url: users] {$this->current_id}, {$this->user_id}" . PHP_EOL;
        }
        // TODO: the or || bails out on sprintf and return 1, fix!
        // $url .= sprintf("/%s:%s", $this->type[0], $this->current_id || $this->user_id);
        $url .= sprintf("/%s:%s", $this->type[0], $this->user_id);

        # check for enrollments?
        if ($this->enrollments) {
          $url .= "/courses";
        }
      }

      if ($this->debug) {
        echo str_repeat("=", 20) . PHP_EOL;
      }

    } else if ($this->api_type == "announcements") {
      if ($this->current_id || $this->announcement_id) {
        $url .= sprintf("/%s:%s", $this->type[0], $this->current_id || $this->announcement_id);

      }
    } else if ($this->api_type == "courses") {
      if ($this->current_id || $this->course_id) {
        $url .= sprintf("/%s:%s", $this->type[0], $this->current_id || $this->course_id);

        # child course(s)
        if ($this->child_course_id) {
          if ($this->child_course_id == "ALL") {
            $url .= "/children";
          } else {
            $url .= sprintf("/children/%s", $this->type[1] || $this->type[0], $this->current_id || $this->course_id);
          }
        }
      }
    } else if ($this->api_type == "contents") {
        # contents was pre-appended: replace with courses
        $url = str_replace("contents", "courses", $url);
        $url .= sprintf("/%s:%s/content", $this->type[0], $this->current_id || $this->course_id);

        # child content(s)
        if ($this->current_id || $this->content_id) {
          if ($this->content_id == "ALL") {
            $url .= sprintf("/children/%s", $this->type[1] || $this->type[0], $this->current_id || $this->course_id);
          } else {
            $url .= sprintf("/children/%s", $this->type[1] || $this->type[0], $this->current_id || $this->child_course_id);
          }
        }
    } else if ($this->api_type == "grades") {
      # groups was pre-appended: replace with courses
      $url = str_replace("grades", "courses", $url);
      $url .= sprintf("/%s:%s/gradebook/columns", $this->type[0], $this->current_id || $this->course_id);
      if ($this->current_id || $this->column_id) {
        if ($this->column_id) {
          $url .= sprintf("/%s:%s", $this->type[1] || $this->type[0], $this->current_id || $this->column_id);

          if ($this->attempts_id) {
            if ($this->attempts_id == "ALL") {
                $url .= "/attempts";
            } else {
                $url .= sprintf("/attempts/%s", $this->type[1] || $this->type[0], $this->current_id || $this->attempts_id);
            }
          }

          if ($this->user_id) {
            if ($this->user_id == "ALL") {
                $url .= "/users";
            } else {
              $url .= sprintf("/users/%s:%s", $this->type[2] || $this->type[1] || $this->type[0], $this->current_id || $this->user_id);
            }
          }
        }
      } else {
        # columns was not supplied but a user was
        if ($this->current_id || $this->user_id) {
          # remove the pre-appended columns with nothing and rebuild url
          $url = str_replace("columns", "", $url);
          $url .= sprintf("/users/%s:%s", $this->type[2] || $this->type[1] || $this->type[0], $this->current_id || $this->user_id);
        }
      }
    } else if ($this->api_type == "groups") {
      # groups was pre-appended: replace with courses
      $url = str_replace("groups", "courses", $url);
      $url .= sprintf("/%s:%s/contents/%s:%s/groups", $this->type[0], $this->type[1] || $this->type[0]);

      if ($this->current_id || $this->group_id) {
        $url .= sprintf("/%s:%s", $this->type[2] || $this->type[1] || $this->type[0], $this->current_id || $this->group_id);
      }
    } else if ($this->api_type == "memberships"){
      # memberships was pre-appended: replace with courses
      if ($this->verbose) {
        echo "[prep_url():memberships] called" . PHP_EOL;
      }

      $url = str_replace("memberships", "courses", $url);

      $url .= "/{}/users";
      $url .= sprintf("/%s:%s/users", $this->type[0], $this->course_id);

      if ($this->verbose) {
        echo $url . PHP_EOL;
      }

      if ($this->user_id || $this->current_id) {
        if ($this->debug || $this->verbose) {
            echo "$this->type, $this->user_id" . PHP_EOL;
        }
        // _type = None
        // try:
        //     _type = $this->type[1] || $this->type[0]
        // except IndexError:
        //     _type = $this->type[0]

        $url .= sprintf("%s:%s", $this->type[1] || $this->type[0], $this->user_id);
      }
      echo $url . PHP_EOL;
      # sys.exit(1)
    } else if ($this->api_type == "datasources") {
      if ($this->current_id || $this->data_source_id) {
        $url .= sprintf("%s:%s",  $this->type[0], $this->current_id || $this->data_source_id);
      }
    } else if ($this->api_type == "system") {
      $url .= "/version";
    } else if ($this->api_type == "terms") {
      if ($this->current_id || $this->term_id) {
        $url .= sprintf("%s:%s",  $this->type[0], $this->current_id || $this->term_id);
      }
    }
    return $url;
  }


  function do_rest($url){
    if ($this->verbose) {
      echo $url .PHP_EOL;
    }
    // session = requests.session()
    // session.verify = False

    // headers = {'Authorization': $this->auth, 'Content-Type': 'application/json'}

    if ($this->isPaginated) {
       $this->params = null;
    }
    // req = requests.Request($this->method.upper(), url, data=$this->current_data or $this->data,
    //                       headers=headers, params=$this->params)
    // prepped = session.prepare_request(req)

    if ($this->verbose){
       echo "[{$this->class_name}:do_rest()] Called" . PHP_EOL;
       echo "[{$this->class_name}:do_rest()] method={$this->method}, url={$url}, data={$this->data}" . PHP_EOL;
       echo "Prepared Request: {$url}" . PHP_EOL;
    }
    # Only set if debugging or in development
    // if ($this->debug){
    //   session.mount('https://', Tls1Adapter());
    // }
    if ($this->verbose){
      echo "[{$this->class_name}:{$this->method}()] " . strtoupper($this->method) ." Request URL: {$url}";
    }

    // r = session.send(prepped)

    if ($this->verbose){
       echo "[{$this->class_name}:{$this->method}()] STATUS CODE: {}" . PHP_EOL;
       echo "[{$this->class_name}:{$this->method}()] RESPONSE:" . PHP_EOL;
    }

    $result = $this->auth->doRest(strtoupper($this->method), $url, $this->data, $this->params);
    echo $result;
    echo PHP_EOL;
    // if r.text:
    //    $this->res = json.dumps(json.loads(r.text))
    //    print($this->res)
    //
    // if $this->verbose:
    //    print(json.dumps($this->res, indent=settings['json_options']['indent'],
    //                     separators=settings['json_options']['separators'],
    //                     default=$this->date_handler))
  }
}
