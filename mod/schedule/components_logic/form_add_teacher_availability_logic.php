<?php
defined('MOODLE_INTERNAL') || die();

require_once('./debug.php');
require_once('./components/form_add_teacher_availability.php');


class mod_schedule_add_teacher_availability_form_logic {

    public function __construct($form_params) {
        log_debug("Constructor: add_teacher_availability_form_logic");

        // TODO
        $this->_course_id = 4; 
        $this->_mform = new mod_schedule_add_teacher_availability_form(null, $form_params);

        if ($this->_mform->is_cancelled()) {
            $this->cancel_form();
        } 
        else if ($fromform = $this->_mform->get_data()) {
            $this->save_form();
        } 
        else {
            $this->validate_form();
            // this branch is executed if the form is submitted but the data doesn't validate and the form should be redisplayed
            // or on the first display of the form.
            //Set default data (if any)
            // $mform->set_data($toform);
        }
    }


    private function save_form() {
        log_debug("save form ". $this->_course_id);
        global $DB, $USER;

        
        $schedule = array(
            test => '10',
            name => 'test',
            display => 10
        );

        $schedule_id = $DB->insert_record('schedule', $schedule);

        $url = new moodle_url(
            '/mod/schedule/view.php', 
            array('id' => $this->_course_id));
        redirect($url);
    }


    private function cancel_form() {
        log_debug("cancel form");
    }


    private function validate_form() {
        log_debug("validate_form");
        $this->_mform->display();
        // $this->_mform->test(3);
    }
}
