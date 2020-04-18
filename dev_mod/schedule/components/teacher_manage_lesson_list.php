<?php namespace mod_schedule;
defined('MOODLE_INTERNAL') || die();

require_once(dirname(__FILE__).'/../inc.php');
mod_require_once('/components/class_list_base.php');


class teacher_manage_lesson_list extends class_list_base {

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
                AND cm_id = {$this->cm->id}

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
            // 'Teacher',
            'Student',
            'Date',
            'Time',
            'Duration'
        );

        foreach ($records as $id => $class) {
            # Todo
            $student_name = "[ Free slot ]";

            if ( ! is_null($class->student_id) ) {
                $student_name = $class->student_name;
            }

            $table->data[$id][] = $this->get_cell_action_button($class);
            // $table->data[$id][] = $class->teacher_name;
            $table->data[$id][] = $student_name;

            $table->data[$id][] = $this->get_cell_date($class);
            $table->data[$id][] = $this->get_cell_time($class);
            $table->data[$id][] = $this->get_cell_duration($class);
        }

        return $table;
    }

    private function get_cell_action_button($class) {
        $label = get_string('cancel', 'schedule'); 
        $action = view_teacher_availability_impl::ACTION_CLASS_CANCEL;
        
        return $this->get_cell_action($label, $class, $action);
    }
}
