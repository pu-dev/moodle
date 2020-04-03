<?php
require_once(dirname(__FILE__).'/../../../config.php');
require_once($CFG->dirroot.'/mod/schedule/tools.php');


class mod_schedule_student_class_tabs implements renderable {
    const TAB_BOOK_LESSON = 1;
    const TAB_VIEW_LESSON = 2;

    public $current_tab;

    public $cmid;


    public function __construct($cmid, $currnet_tab) {
        $this->cmid = $cmid;
        $this->current_tab = $currnet_tab;
    }


    public function get_tabs() {
        $tabs = array();

        $tabs[] = $this->create_tab(
            self::TAB_BOOK_LESSON,
            "views/view_student_book_lesson.php",
            "book_class"
        );

        $tabs[] = $this->create_tab(
            self::TAB_VIEW_LESSON,
            "views/view_student_view_lesson.php",
            "view_class"
        );

        return array($tabs);
    }


    private function create_tab($tab_id, $url, $label) {
        $fullurl = mod_schedule_tools::get_module_url() . "/" . $url;

        return new tabobject(
            $tab_id,
            new moodle_url(
                $fullurl,
                array('id' => $this->cmid)),
            get_string($label, 'schedule')
        );
    }
}