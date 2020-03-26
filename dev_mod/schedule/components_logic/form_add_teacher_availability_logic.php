<?php
defined('MOODLE_INTERNAL') || die();

require_once('./debug.php');
require_once('./components/form_add_teacher_availability.php');


class mod_schedule_add_teacher_availability_form_logic {

    public function __construct($form_params) {
        log_debug("Constructor: add_teacher_availability_form_logic");

        $this->_cmid = $form_params['cmid'];
        $this->_mform = new mod_schedule_add_teacher_availability_form(null, $form_params);

        if ($this->_mform->is_cancelled()) {
            $this->cancel_form();
        } 
        else if ($this->_mform->get_data()) {
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
        global $DB, $USER;

        define("MINUTES_IN_HOUR", 60);

        $form_data = $this->_mform->get_data();


        // Get time
        $cls_time = $form_data->class_time;
        
        $start_hour = $cls_time['start_hour'];
        $start_minute = $cls_time['start_minute'];
        $end_hour = $cls_time['end_hour'];
        $end_minute = $cls_time['end_minute'];

        $duration = (
            ($end_hour - $start_hour) * MINUTES_IN_HOUR +
            ($end_minute - $start_minute)
        );

        // Get date
        $cls_date = $form_data->class_date;
        
        $day = $cls_date['day'];
        $month = $cls_date['month'];
        $year = $cls_date['year'];

        error_log('day'. $day);
        error_log('month '.$month);
        error_log('year '.$year);

        // date_default_timezone_set('UTC');  // optional
        $epoch_date = mktime($start_hour, $start_minute, 0, $month, $day, $year);

        $class = new stdClass;
        $class->schedule_id=1;
        $class->teacher_id=1;
        $class->student_id=1;
        $class->lesson_date=$epoch_date;
        $class->lesson_duration=$duration;

        $class->id = $DB->insert_record('schedule_lesson', $class);

        // Than display
        $this->_mform->display();
    }


    private function cancel_form() {
        log_debug("cancel form");
    }


    private function validate_form() {
        // Check if to time is after from time
        log_debug("validate_form");
        $this->_mform->display();
        // $this->_mform->test(3);
    }
}
