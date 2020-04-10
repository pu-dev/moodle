<?php namespace mod_schedule;
defined('MOODLE_INTERNAL') || die();

require_once(dirname(__FILE__).'/../../../config.php');
require_once($CFG->dirroot.'/mod/schedule/components/tabs_base.php');


class teacher_class_tabs extends tabs_base implements \renderable {
    const TAB_AVAILABILITY_LESSON = 1;
    const TAB_BOOKED_LESSON = 2;
    const TAB_OLD_LESSON = 3;


    public function __construct($cmid, $current_tab) {
        parent::__construct($cmid, $current_tab);
    }

    public function get_tabs() {
        $tabs = array();

        $tabs[] = $this->create_tab(
            self::TAB_AVAILABILITY_LESSON,
            "views/view_teacher_availability.php",
            # Todo
            "Your availability"
        );

        $tabs[] = $this->create_tab(
            self::TAB_BOOKED_LESSON,
            "views/view_teacher_booked_lesson.php",
            # Todo
            "Booked classes"
        );

        $tabs[] = $this->create_tab(
            self::TAB_OLD_LESSON,
            "views/view_teacher_old_lesson.php",
            # Todo
            "Old classes"
        );
        return array($tabs);
    }
}