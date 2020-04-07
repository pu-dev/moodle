<?php
require_once(dirname(__FILE__).'/../../../config.php');
require_once($CFG->dirroot.'/mod/schedule/debug.php');


abstract class action_base {
    abstract public function execute();
}


class action_result {
    public $ok;
    public $data;

    public function __construct($ok, $data) {
        $this->ok = $ok;
        $this->data = $data;
    }
}