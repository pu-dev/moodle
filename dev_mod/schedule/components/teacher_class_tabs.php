<?php namespace mod_schedule;
defined('MOODLE_INTERNAL') || die();

require_once(dirname(__FILE__).'/../../../config.php');
require_once($CFG->dirroot.'/mod/schedule/components/tabs_base.php');


class teacher_class_tabs extends tabs_base implements \renderable {
    const TAB_AVAILABILITY_LESSON = 1;
    const TAB_OTHER_LESSON = 2;


    public function __construct($cmid, $current_tab) {
        parent::__construct($cmid, $current_tab);
    }

    public function get_tabs() {
        $tabs = array();

        $tabs[] = $this->create_tab(
            self::TAB_AVAILABILITY_LESSON,
            "views/view_teacher_availability.php",
            "teacher_availability"
        );

        $tabs[] = $this->create_tab(
            self::TAB_OTHER_LESSON,
            "views/view_teacher_other.php",
            "teacher_booked_class"
        );

        return array($tabs);
    }
}