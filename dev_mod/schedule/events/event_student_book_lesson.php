<?php namespace mod_schedule;

require_once(dirname(__FILE__).'/../inc.php');
mod_require_once('/events/event_base.php');


class event_student_book_lesson extends event_lesson_base {
    public function __construct($cm, $lesson) {
        parent::__construct($cm, $lesson);
        debug("construct event book");
    }

    public function execute() {
        $lesson =& $this->lesson;
        $this->update_calendar($lesson->student_id);
        $this->update_calendar($lesson->teacher_id);
    }

    private function update_calendar($user_id) {
                $schedule = schedule_get_schedule($this->cm->instance);

        $event = new \stdClass();

        $UNUSED = 0;
        $event->eventtype = $UNUSED; 
       
        // This is used for events we only want to display on the calendar,
        // and are not needed on the block_myoverview.
        $event->type = CALENDAR_EVENT_TYPE_STANDARD; 
        
        # Todo
        $event->name = "Lesson";
        $event->description = format_module_intro(
            'schedule', $schedule, $this->cm->id
        );
        $event->courseid = 0; //$schedule->course;
        $event->groupid = 0;
        $event->userid = $user_id;
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


