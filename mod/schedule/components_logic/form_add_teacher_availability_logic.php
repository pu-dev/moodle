<?php
require_once('./debug.php');
require_once('./components/form_add_teacher_availability.php');


class mod_schedule_add_teacher_availability_form_logic {

    public function __construct($form_params) {
        log_debug("Constructor: add_teacher_availability_form_logic");

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
        log_debug("save form");
        
        $url = new moodle_url(
            '/mod/schedule/view.php', 
            array('id' => $course_id));
        redirect($url);
    }


    private function cancel_form() {
        log_debug("cancel form");
    }


    private function validate_form() {
        log_debug("validate_form");
        $this->_mform->display();
    }
}
