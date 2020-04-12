<?php namespace mod_schedule;

require_once(dirname(__FILE__).'/../inc.php');
mod_require_once('/actions/action_base.php');

abstract class action_lesson_base extends action_base {
    protected $cm;
    protected $lesson;

    public function __construct($cm, $lesson = null) {
        $this->cm = $cm;
        // $this->lesson = $lesson;
    }
    
    abstract public function execute();
}
