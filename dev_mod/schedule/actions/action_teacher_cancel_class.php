<?php namespace mod_schedule;

require_once(dirname(__FILE__).'/../inc.php');
mod_require_once('/actions/action_lesson_base.php');
mod_require_once('/events/event_student_unbook_lesson.php');


class action_teacher_cancel_class extends action_lesson_base {
    private $class_id;


    public function __construct($cm, $class_id) {
        parent::__construct($cm);
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

        if ( ! is_object($class) ) {
            $this->update_calendar($this->class_id);
        }

        // Return true if class has been removed from DB
        return new action_result (
            ! is_object($class),
            $class
        );
    }

    public function update_calendar($lesson_id) {
        $lesson = new \stdClass;
        $lesson->id = $lesson_id;

        $event = new event_student_unbook_lesson($this->cm, $lesson);
        $event->execute();
    }

}

