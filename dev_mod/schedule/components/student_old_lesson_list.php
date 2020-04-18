<?php namespace mod_schedule;
defined('MOODLE_INTERNAL') || die();

require_once(dirname(__FILE__).'/../inc.php');
mod_require_once('/components/class_list_base.php');


class student_old_lesson_list extends class_list_base {

    public function __construct($cm) {
        parent::__construct($cm);
    }

    protected function get_sql_query() {
        global $USER;

        $time = time();

        $sql = $this->get_sql_query_base();
        $sql .= "
            WHERE
                student_user.id = {$USER->id} 
                AND (lesson.date + lesson.duration) < {$time}
                AND cm_id = {$this->cm->id}

            ORDER BY 
                lesson.date ASC,
                lesson.id ASC
        ";

        return $sql;
    }

    protected function create_table($records) {
        $table = new \html_table();

        $table->width = '100%';

        $table->head = array(
            'Action',
            'Topic',
            'Notes',
            'Teacher',
            'Date',
        );

        foreach ($records as $id => $class) {
            $table->data[$id][] = $this->get_cell_action_button($class);
            $table->data[$id][] = $this->get_cell_topic($class);
            $table->data[$id][] = $this->get_cell_notes($class);
            $table->data[$id][] = $class->teacher_name;
            $table->data[$id][] = $this->get_cell_date($class);
        }

        return $table;
    }

    private function get_cell_action_button($class) {
        # todo
        $label = 'Edit';
        $action = null;
        $url = 'views/view_student_edit_lesson.php';
        $url_params = [
            'lesson_id' => $class->lesson_id
        ];

        return $this->get_cell_action($label, $class, $action, $url, $url_params);
    }
}
