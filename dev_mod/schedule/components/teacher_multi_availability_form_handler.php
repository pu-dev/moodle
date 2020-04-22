<?php namespace mod_schedule;
defined('MOODLE_INTERNAL') || die();

require_once(dirname(__FILE__).'/../inc.php');
mod_require_once('/components/form_handler_base.php');


class teacher_multi_availability_form_handler extends form_handler_base {
    public function __construct($form, $cm) {
        parent::__construct($form, $cm);
    }

    protected function saved() {
        global $DB, $USER;
        parent::saved();

        $data = $this->form->get_data();

        // Get time
        $cls_time = $data->class_time;

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


       $day_len = 24*60*60;
       $days_selected = $data->sdays;

       $classes = array();
       for (
            $date = $data->date_start; 
            $date <= $data->date_end; 
            $date += $day_len
        ) {
            $day_no = date('N', $date);
            
            if ( array_key_exists($day_no, $days_selected) ) {
                $class = new \stdClass;
                $class->schedule_id = $this->cm->instance;
                $class->teacher_id = $USER->id;
                $class->student_id = null;
                $class->date = tools::get_epoch_date($date, $start_hour, $start_minute);
                $class->duration = $duration;
                array_push($classes, $class);
            }
        }

        $table = 'schedule_lesson';
        $DB->insert_records($table, $classes);
    }

    protected function canceled() {
    }

    protected function validated() {
    }
}
