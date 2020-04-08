<?php namespace mod_schedule;

require_once(dirname(__FILE__).'/../../../config.php');
require_once($CFG->dirroot.'/mod/schedule/debug.php');
require_once($CFG->dirroot.'/mod/schedule/actions/action_base.php');


class action_student_book_class extends action_base {
    private $student_id;
    private $class_id;


    public function __construct($class_id, $student_id) {
        debug(
            'action_student_book_class: '.
            "student_id: {$student_id}, ".
            "class_id: {$class_id}"
        );

        $this->class_id = $class_id;
        $this->student_id = $student_id;
    }


    public function execute() {
        global $DB;
        $table_name = 'schedule_lesson';

        // Update record in DB
        $DB->set_field(
            'schedule_lesson',
            'student_id',
            $this->student_id,
            array(
                'id' => $this->class_id,
                'student_id' => null
            )
        );

        // Check if it was updated, as someone could 
        // update it before executing above 'set_field' call
        $class = $DB->get_record(
            $table_name, 
            array(
                'id' => $this->class_id,
                'student_id' => $this->student_id
            )
        );

        // Return true if class has been booked
        return new action_result(
            is_object($class),
            $class
        );
        
    }
}


