<?php
defined('MOODLE_INTERNAL') || die();

require_once(dirname(__FILE__).'/../../../config.php');
require_once($CFG->dirroot.'/mod/schedule/debug.php');
require_once($CFG->dirroot.'/mod/schedule/components/class_list_base.php');


class mod_schedule_student_book_class_list extends mod_schedule_class_list_base {

    /**
     *
     */
    public function __construct($cm) {
        parent::__construct($cm);
    }


    /**
     *
     */
    protected function get_sql_query() {
        global $USER;

        $sql = $this->get_sql_query_base();
        $sql .= "
            WHERE
                student_user.id = {$USER->id} OR
                student_user.id is null

            ORDER BY 
                lesson.lesson_date ASC,
                lesson.id ASC
        ";

        return $sql;
    }


    /**
     *
     */
    protected function create_table($records) {
        $table = new html_table();

        $table->width = '100%';
        # Todo
        $table->head = array(
            'Action',
            'Teacher',
            'Date',
            'Time',
            'Duration'
        );

        foreach ($records as $id => $class) {
            
            $student_name = 'todo';
            // $student_name = get_string('no_student', 'schedule');
            if ($class->student_id != 0) {
                // $student_name = 'name TODO';
            }

            $table->data[$id][] = $this->get_cell_action_button($class);
            $table->data[$id][] = $class->teacher_name;
            $table->data[$id][] = $this->get_cell_date($class);
            $table->data[$id][] = $this->get_cell_time($class);
            $table->data[$id][] = $this->get_cell_duration($class);
        }

        return $table;
    }


    /**
     *
     */
    private function get_cell_action_button($class) {

        if ( $class->student_id !== null) {
            $action = view_student_book_lesson_impl::ACTION_UNBOOK_CLASS;
            $label = get_string('unbook_class', 'schedule'); 
        }
        else {
            $action = view_student_book_lesson_impl::ACTION_BOOK_CLASS;
            $label = get_string('book_class', 'schedule'); 
        }
        
        return $this->get_cell_action($class, $action, $label);
    }
}
