<?php namespace mod_schedule;
defined('MOODLE_INTERNAL') || die();

require_once(dirname(__FILE__).'/../../../config.php');
require_once($CFG->dirroot.'/mod/schedule/debug.php');
require_once($CFG->dirroot.'/mod/schedule/components/class_list_base.php');


class teacher_class_list extends class_list_base {

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
                teacher_id = {$USER->id}

            ORDER BY 
                lesson.date ASC
        ";

        return $sql;
    }


    /**
     *
     */
    protected function create_table($records) {
        
        $table = new \html_table();

        $table->width = '100%';
        $table->head = array(
            'Action',
            'Teacher',
            'Student',
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
            $table->data[$id][] = $class->student_name;

            $table->data[$id][] = $this->get_cell_date($class);
            $table->data[$id][] = $this->get_cell_time($class);
            $table->data[$id][] = $this->get_cell_duration($class);
        }

        return $table;
    }

    private function get_cell_action_button($class) {
        $action = view_teacher_availability_impl::ACTION_CLASS_CANCEL;
        $label = get_string('cancel_class', 'schedule'); 
        
        return $this->get_cell_action($class, $action, $label);
    }
}
