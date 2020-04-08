<?php namespace mod_schedule;

require_once(dirname(__FILE__).'/../../../config.php');
require_once($CFG->dirroot.'/mod/schedule/debug.php');
require_once($CFG->dirroot.'/mod/schedule/actions/action_base.php');


class action_student_unbook_class extends action_base {
    private $student_id;
    private $class_id;


    public function __construct($class_id, $student_id) {
        debug(
            'action_student_unbook_class: '.
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
            null,
            array(
                'id' => $this->class_id,
                'student_id' => $this->student_id
            )
        );

        $class = $DB->get_record(
            $table_name, 
            array(
                'id' => $this->class_id,
                'student_id' => null
            )
        );

        // Return true if class has been booked
        return new action_result(
            is_object($class),
            $class
        );
    }
}


