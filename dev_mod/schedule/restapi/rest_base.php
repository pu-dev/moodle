<?php namespace mod_schedule;
defined('MOODLE_INTERNAL') || die();

require_once(dirname(__FILE__).'/../inc.php');
mod_require_once('/lib.php');


abstract class rest_base {
    protected $cm;
    protected $schedule;

    public function __construct() {
        header('Content-type: application/json');
        $cmid = required_param('id', PARAM_INT);

        if (! $this->cm = get_coursemodule_from_id('schedule', $cmid)) {
            print_error('invalidcoursemodule');
        }

        require_login($this->cm->course, false, $this->cm);

        if (! $this->schedule = schedule_get_schedule($this->cm->instance)) {
            print_error('invalidcoursemodule');
        }

        // if (! $this->schedule = \schedule_get_schedule($this->cm->instance)) {
        //     print_error('invalidcoursemodule');
        // }

        echo $this->render();
    }

    protected function get_body_decoded() {
        $requstBody = file_get_contents('php://input');
        return json_decode($requstBody);
    }

    protected function response($status, $status_message, $data = null)
    {
        header("HTTP/1.1 ".$status);
    
        $response['status'] = $status;
        $response['status_message'] = $status_message;
        if ( ! is_null($data) ) {
            $response['data'] = $data;
        }
    
        $json_response = json_encode($response);
        return $json_response;
    }


    // Response codes
    protected function response_200_OK($data = null) 
    {
        return $this->response(200, 'OK', $data);
    }

    protected function response_400_BAD_REQUEST($data = null) 
    {
        return $this->response(400, 'Client error - bad request', $data);
    }

    protected function response_409_CONFLICT($data = null) 
    {
        return $this->response(409, 'Client error - conflict', $data);
    }

    protected function response_412_PRECONDITION_FAILED($data = null)
    {
        return $this->response(412, 'Client error - precondition failed', $data);
    }

    protected function response_500_INTERNAL_SERVER_ERROR($data = null)
    {
        return $this->response(500, 'Server error - internal server error', $data);
    }

    abstract protected function render();
}
