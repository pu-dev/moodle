<?php namespace mod_schedule;

require_once(dirname(__FILE__).'/../inc.php');
mod_require_once('/actions/action_base.php');


class action_update_lesson extends action_base {
    private $lesson_id;
    private $topic;
    private $notes;


    public function __construct($lesson_id, $topic, $notes) {
        $this->lesson_id = $lesson_id;
        $this->topic = $topic;
        $this->notes = $notes;
    }


    public function execute() {
        global $DB;
        $table_name = 'schedule_lesson';

        // Update record in DB
        $lesson = $DB->get_record(
            $table_name,
            array('id' => $this->lesson_id)
        );

        $lesson->topic = $this->topic;
        $lesson->notes = $this->notes;

        $DB->update_record($table_name, $lesson);

        // Consider update always successful, as API
        // always returns true. We could retrieve back
        // lesson from DB and check if it has been updated
        // but another select is just not needed. 
        
        return new action_result(true, $lesson);
    }
}


