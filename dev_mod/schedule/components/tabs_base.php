<?php
require_once(dirname(__FILE__).'/../../../config.php');
require_once($CFG->dirroot.'/mod/schedule/tools.php');
require_once($CFG->dirroot.'/mod/schedule/debug.php');


abstract class mod_schedule_tabs_base implements renderable {
    public $cmid;
    public $current_tab;


    public function __construct($cmid, $currnet_tab) {
        $this->cmid = $cmid;
        $this->current_tab = $currnet_tab;
    }

    final protected function create_tab($tab_id, $url_view, $label) {
        debug($url_view);
        $url = mod_schedule_tools::get_module_url(
            $url_view,
            array('id' => $this->cmid)
        );
        // $url = mod_schedule_tools::get_self_url();

        return new tabobject(
            $tab_id,
            $url,
            get_string($label, 'schedule')
        );
    }

    abstract public function get_tabs();
}