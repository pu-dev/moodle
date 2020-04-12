<?php namespace mod_schedule;

require_once(dirname(__FILE__).'/../inc.php');


abstract class action_base {
    abstract public function execute();
}


class action_result {
    public $ok;
    public $data;

    public function __construct($ok, $data=null) {
        $this->ok = $ok;
        $this->data = $data;
    }
}