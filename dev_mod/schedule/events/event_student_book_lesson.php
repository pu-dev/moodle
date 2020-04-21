<?php namespace mod_schedule;

require_once(dirname(__FILE__).'/../inc.php');
mod_require_once('/events/event_base.php');


class event_student_book_lesson extends event_lesson_base {
    public function __construct($cm, $lesson) {
        parent::__construct($cm, $lesson);
        debug("construct event book");
    }

    public function execute() {
        global $DB, $USER;

        $table = 'user';
        $student_id = $this->lesson->student_id;
        $teacher_id = $this->lesson->teacher_id;

        $users = $DB->get_records_list(
            $table, 'id', 
            [$student_id, $teacher_id],
            '', // sort
            'id, firstname, lastname'
        );

        $this->update_calendar($student_id, $users[$teacher_id]);
        $this->update_calendar($teacher_id, $users[$student_id]);
    }

    private function update_calendar($target_user_id, $second_user) {
        $schedule = schedule_get_schedule($this->cm->instance);

        $event = new \stdClass();

        $UNUSED = 0;
        $event->eventtype = $UNUSED; 
       
        // This is used for events we only want to display on the calendar,
        // and are not needed on the block_myoverview.
        $event->type = CALENDAR_EVENT_TYPE_STANDARD; 
        
        # Todo
        $event->name = "Lesson with {$second_user->firstname} {$second_user->lastname}";
        $event->description = format_module_intro(
            'schedule', $schedule, $this->cm->id
        );
        // Event shown on main page calendar
        $event->courseid = 0; 
        // Event shown only on course calendar 
        // $event->courseid = $schedule->course; 
        $event->groupid = 0;
        $event->userid = $target_user_id;
        $event->modulename = 'schedule';
        $event->instance = $schedule->id;
        $event->timestart = $this->lesson->date;
        $event->visible = true;  //instance_is_visible('schedule', $schedule);
        $event->timeduration = $this->lesson->duration;
         
        $event_ = \calendar_event::create($event, false);

       $this->update_db($event_->id);
    }

    private function update_db($event_id) {
        global $DB;
        
        $table_name = 'schedule_calendar_event';
        $db_event = array(
            'lesson_id' => $this->lesson->id,
            'event_id' => $event_id
        );
        $DB->insert_record($table_name, $db_event);
    }

}


