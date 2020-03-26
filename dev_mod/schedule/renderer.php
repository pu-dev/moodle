<?php
require_once('./debug.php');
require_once('./components_logic/form_add_teacher_availability_logic.php');


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
}



