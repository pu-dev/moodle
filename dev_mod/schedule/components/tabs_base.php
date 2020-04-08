<?php namespace mod_schedule;
defined('MOODLE_INTERNAL') || die();

require_once(dirname(__FILE__).'/../../../config.php');
require_once($CFG->dirroot.'/mod/schedule/tools.php');
require_once($CFG->dirroot.'/mod/schedule/debug.php');


abstract class tabs_base implements \renderable {
    public $cmid;
    public $current_tab;


    public function __construct($cmid, $currnet_tab) {
        $this->cmid = $cmid;
        $this->current_tab = $currnet_tab;
    }

    final protected function create_tab($tab_id, $url_view, $label) {
        $url = tools::get_module_url(
            $url_view,
            array('id' => $this->cmid)
        );

        return new \tabobject(
            $tab_id,
            $url,
            get_string($label, 'schedule')
        );
    }

    abstract public function get_tabs();
}