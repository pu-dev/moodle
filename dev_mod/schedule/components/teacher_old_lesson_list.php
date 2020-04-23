<?php namespace mod_schedule;
defined('MOODLE_INTERNAL') || die();

require_once(dirname(__FILE__).'/../inc.php');
mod_require_once('/components/teacher_class_list_base.php');


class teacher_old_lesson_list extends teacher_class_list_base {

    public function __construct($cm) {
        parent::__construct($cm);
    }

    protected function get_sql_query() {
        global $USER;

        $time = time();

        $sql = $this->get_sql_query_base();
        $sql .= "
            WHERE
                teacher_id = {$USER->id} 
                AND student_id IS NOT NULL
                AND (lesson.date + lesson.duration) < {$time}
                AND schedule_id = {$this->cm->instance}

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
            # todo
            'Action',
            'Student',
            'Date',
            'Topic',
            'Notes',
        );

        foreach ($records as $id => $class) {
            $table->data[$id][] = $this->get_cell_teacher_action_edit_lesson($class);
            $table->data[$id][] = $class->student_name;
            $table->data[$id][] = $this->get_cell_date($class);
            $table->data[$id][] = $this->get_cell_topic($class);
            $table->data[$id][] = $this->get_cell_notes($class);
        }

        return $table;
    }
}
