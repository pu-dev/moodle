<?php
defined('MOODLE_INTERNAL') || die();

require_once(dirname(__FILE__).'/../../../config.php');
require_once($CFG->dirroot.'/mod/schedule/debug.php');
require_once($CFG->dirroot.'/mod/schedule/components/form_handler_base.php');


class mod_schedule_teacher_availability_form_handler extends mod_schedule_form_handler_base {
    public function __construct($form) {
        parent::__construct($form);
    }


    protected function save_form() {
        global $DB, $USER;

        define("MINUTES_IN_HOUR", 60);
        define("SECONDS_IN_MINUTE", 60);

        $form_data = $this->form->get_data();


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
        $epoch_date = $form_data->class_date;
        $epoch_date += (
            $start_hour * MINUTES_IN_HOUR * SECONDS_IN_MINUTE +
            $start_minute * SECONDS_IN_MINUTE
        );

        // Create 'class' representation
        $class = new stdClass;
        $class->schedule_id = 1; # TODO
        $class->teacher_id = $USER->id;
        $class->student_id = null;
        $class->lesson_date = $epoch_date;
        $class->lesson_duration = $duration;

        $class->id = $DB->insert_record('schedule_lesson', $class);

        debug("Teachers availability saved");
        // Than display
        // $this->form->display();
    }


    protected function cancel_form() {
        // log_debug("cancel form");
    }


    protected function validate_form() {
        // Check if to time is after from time
        // log_debug("validate_form");
        // $this->form->display();
        // $this->_mform->test(3);
    }
}
