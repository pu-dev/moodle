<?php namespace mod_schedule;
defined('MOODLE_INTERNAL') || die();

require_once(dirname(__FILE__).'/../../../config.php');
require_once($CFG->dirroot.'/mod/schedule/components/tabs_base.php');


class student_class_tabs extends tabs_base implements \renderable {
    public const TAB_BOOK_LESSON = 1;
    public const TAB_NEW_LESSON = 2;
    public const TAB_OLD_LESSON = 3;

    public function __construct($cmid, $current_tab) {
        parent::__construct($cmid, $current_tab);
    }

    public function get_tabs() {
        $tabs = array();

        $tabs[] = $this->create_tab(
            self::TAB_BOOK_LESSON,
            "views/view_student_book_lesson.php",
            # 
            "Book class"
        );

        $tabs[] = $this->create_tab(
            self::TAB_NEW_LESSON,
            "views/view_student_new_lesson.php",
            # Todo 
            "Booked classes"
        );

        $tabs[] = $this->create_tab(
            self::TAB_OLD_LESSON,
            "views/view_student_old_lesson.php",
            "Passed classes"
        );

        return array($tabs);
    }
}
