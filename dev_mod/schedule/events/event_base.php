<?php namespace mod_schedule;
require_once(dirname(__FILE__).'/../inc.php');


abstract class event_base {
    protected $cm;

    protected function __construct($cm) {
        $this->cm = $cm;
    }
    
    abstract public function execute();
}


abstract class event_lesson_base extends event_base {
    protected $lesson;

    protected function __construct($cm, $lesson) {
        parent::__construct($cm);
        $this->lesson = $lesson;
    }
}
