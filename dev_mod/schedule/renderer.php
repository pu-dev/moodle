<?php
require_once('./debug.php');
require_once('./components_logic/form_add_teacher_availability_logic.php');
require_once('./components/class_list.php');


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

    public function render_mod_schedule_class_list(mod_schedule_class_list $class_list) {
        global $DB;

        $classes = $DB->get_records('schedule_lesson');

        $table = new html_table();

        $table->width = '100%';
        $table->head = array(
            'Teacher',
            'Student',
            'Date',
            'Time',
            'Duration'
        );

        foreach ($classes as $id => $class) {
            
            $student_name = 'todo';
            // $student_name = get_string('no_student', 'schedule');
            if ($class->student_id != 0) {
                // $student_name = 'name TODO';
            }

            $teacher_name = $class->teacher_id;


            $table->data[$id][] = $teacher_name;
            $table->data[$id][] = $student_name;

            # TODO
            # get_string('date_format', '<nobr>%a %d %b %Y</nobr>'); 
            $table->data[$id][] = userdate($class->lesson_date, '<nobr>%a %d %b %Y</nobr>');


            // Get time
            //
            $table->data[$id][] = html_writer::tag(
                'nobr', 
                strftime('%H:%M', $class->lesson_date));

            // Get duration
            //
            $table->data[$id][] = html_writer::tag(
                'nobr', 
                gmdate('H:i', $class->lesson_duration)
            );
        }

        return html_writer::table($table);
    }
}
