<?php
require_once(dirname(__FILE__).'/../../../config.php');
require_once($CFG->dirroot.'/mod/schedule/components/tabs_base.php');


class mod_schedule_student_class_tabs extends mod_schedule_tabs_base implements renderable {
    public const TAB_BOOK_LESSON = 1;
    public const TAB_VIEW_LESSON = 2;

    public function __construct($cmid, $current_tab) {
        parent::__construct($cmid, $current_tab);
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
}