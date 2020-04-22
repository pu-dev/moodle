<?php namespace mod_schedule;

require_once(dirname(__FILE__).'/../inc.php');


abstract class action_base {
    abstract public function execute();
}


class action_result {
    public $status;
    public $data;

    public function __construct($status, $data=null) {
        $this->status = $status;
        $this->data = $data;
    }
}