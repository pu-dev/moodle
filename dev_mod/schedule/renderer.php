<?php
require_once(dirname(__FILE__).'/../../config.php');
require_once($CFG->dirroot.'/mod/schedule/debug.php');
require_once($CFG->dirroot.'/mod/schedule/components_logic/form_add_teacher_availability_logic.php');
require_once($CFG->dirroot.'/mod/schedule/components/student_class_tabs.php');
require_once($CFG->dirroot.'/mod/schedule/components/teacher_class_tabs.php');


/*
This file creates html
*/

class mod_schedule_renderer extends plugin_renderer_base {

    public function display_add_teacher_availability_form($cmid) {
        $form_params = array(
            'cmid' => $cmid
        );
       
        new mod_schedule_add_teacher_availability_form_logic($form_params);
    }


    /**
     *
     */

    // public function render_mod_schedule_class_list(mod_schedule_class_list $class_list) {
    //     return html_writer::table($class_list->class_table);
    // }


    public function render_mod_schedule_student_class_tabs(mod_schedule_student_class_tabs $view) {
        return print_tabs(
            $view->get_tabs(),
            $view->current_tab, 
            null, 
            null,
            true
        );
    }


    public function render_mod_schedule_teacher_class_tabs(mod_schedule_teacher_class_tabs $view) {
        return print_tabs(
            $view->get_tabs(),
            $view->current_tab, 
            null, 
            null,
            true
        );
    }

}


