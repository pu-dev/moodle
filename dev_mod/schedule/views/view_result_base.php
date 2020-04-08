<?php namespace mod_schedule;

abstract class view_result_base {
    public $html;
    public $redirect;
}


class view_result_html extends view_result_base {
    public function __construct($html) {
        $this->html = $html;
    }
}

class view_result_redirect extends view_result_base {
    public function __construct($url) {
        $this->redirect = $url;
    }
}
