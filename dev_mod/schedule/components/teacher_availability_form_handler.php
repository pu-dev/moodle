<?php namespace mod_schedule;
defined('MOODLE_INTERNAL') || die();

require_once(dirname(__FILE__).'/../inc.php');
mod_require_once('/tools.php');
mod_require_once('/components/form_handler_base.php');


class teacher_availability_form_handler extends form_handler_base {
    public function __construct($form, $cm) {
        parent::__construct($form, $cm);
    }

    protected function saved() {
        global $DB, $USER;
        parent::saved();

        $form_data = $this->form->get_data();

        // Get time
        $cls_time = $form_data->class_time;
        
        $start_hour = $cls_time['start_hour'];
        $start_minute = $cls_time['start_minute'];
        $end_hour = $cls_time['end_hour'];
        $end_minute = $cls_time['end_minute'];

        // Duration in seconds
        $duration = tools::get_duration(
            $start_hour, 
            $start_minute,
            $end_hour, 
            $end_minute
        );

        // Get date
        $epoch_date = tools::get_epoch_date(
            $form_data->class_date,
            $start_hour,
            $start_minute
        );

        // Create 'class' representation
        $class = new \stdClass;
        $class->schedule_id = $this->cm->instance;
        $class->teacher_id = $USER->id;
        $class->student_id = null;
        $class->date = $epoch_date;
        $class->duration = $duration;

        $class->id = $DB->insert_record('schedule_lesson', $class);

        debug("Teachers availability saved");
    }

    protected function canceled() {
    }

    protected function validated() {
    }
}
