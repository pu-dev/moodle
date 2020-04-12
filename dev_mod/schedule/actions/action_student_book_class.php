<?php namespace mod_schedule;

require_once(dirname(__FILE__).'/../inc.php');
mod_require_once('/actions/action_lesson_base.php');
mod_require_once('/events/event_student_book_lesson.php');


class action_student_book_class extends action_lesson_base {
    private $student_id;
    private $class_id;


    public function __construct($cm, $class_id, $student_id) {
        debug("Construct action_student_book_class");
       
        parent::__construct($cm);
        $this->class_id = $class_id;
        $this->student_id = $student_id;
    }


    public function execute() {
        $action_result = $this->update_db();
        if ( $action_result->ok ) {
            $this->update_calendar($action_result->data);
        }
        return $action_result;
    }


    public function update_db() {
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
     

    public function update_calendar($lesson) {
        $event = new event_student_book_lesson($this->cm, $lesson);
        $event->execute();
    }
}


