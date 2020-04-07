<?php
require_once(dirname(__FILE__).'/../../../config.php');
require_once($CFG->dirroot.'/mod/schedule/debug.php');
require_once($CFG->dirroot.'/mod/schedule/actions/action_base.php');

class action_teacher_cancel_class extends action_base {
    private $class_id;


    public function __construct($class_id) {
        debug(
            'action_teacher_cancel_class: '.
            "class_id: {$class_id}"
        );

        $this->class_id = $class_id;
    }


    public function execute() {
        global $DB;
        $table_name = 'schedule_lesson';

        // Delete class from DB
        $DB->delete_records(
            $table_name,
            array(
                'id'=>$this->class_id
            )
        );

        $class = $DB->get_record(
            $table_name, 
            array(
                'id' => $this->class_id,
            )
        );

        // Return true if class has been removed from DB

        return new action_result (
            ! is_object($class),
            $class
        );
    }
}

