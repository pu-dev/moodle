<?php namespace mod_schedule;

require_once(dirname(__FILE__).'/../inc.php');
mod_require_once('/events/event_base.php');


class event_student_unbook_lesson extends event_lesson_base {
    public function __construct($cm, $lesson) {
        parent::__construct($cm, $lesson);
        debug("construct event unbook");
    }

    public function execute() {
        global $DB;

        $table_name = 'schedule_calendar_event';
        $conditions = array(
            'lesson_id' => $this->lesson->id,
        );
        $sort = '';
        $fields = '*';

        $events = $DB->get_records(
            $table_name, 
            $conditions,
            $sort,
            $fields);

        foreach ($events as $id => $event) {
            $this->update_calendar($event->event_id);
        }
    }

    private function update_calendar($event_id) {
        debug("remove calendar for event id: ".$event_id);

        // Remove event links from our tables
        global $DB;
        $table_name = 'schedule_calendar_event';
        $conditions = array(
            'event_id' => $event_id,
            'lesson_id' => $this->lesson->id
        );

        $DB->delete_records(
            $table_name,
            $conditions
        );

        // Remove events from calendar
        $event = \calendar_event::load($event_id);
        $event->delete();
    }

}


