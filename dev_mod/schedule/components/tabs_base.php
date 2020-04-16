<?php namespace mod_schedule;
defined('MOODLE_INTERNAL') || die();

require_once(dirname(__FILE__).'/../inc.php');
mod_require_once('/tools.php');


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
            $label
            // get_string($label, 'schedule')
        );
    }

    abstract public function get_tabs();
}
