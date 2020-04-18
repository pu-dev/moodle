<?php namespace mod_schedule;
defined('MOODLE_INTERNAL') || die();

require_once(dirname(__FILE__).'/../inc.php');
mod_require_once('/components/class_list_base.php');


class teacher_booked_lesson_list extends class_list_base {
    public function __construct($cm) {
        parent::__construct($cm);
    }

    protected function get_sql_query() {
        global $USER;

        define('HOURS_COUNT', 1);
        define('SECONDS_IN_HOUR', 3600);

        $time_now = time();
        // We will show class for 'HOURS_COUNT' hours
        // after lesson scheduled finish time. It will 
        // allow teacher to check his booking for example 
        // when student is late. 
        
        $time = $time_now - (HOURS_COUNT * SECONDS_IN_HOUR);
        // $time = $time_now - 24*SECONDS_IN_HOUR*365;

        $sql = $this->get_sql_query_base();
        $sql .= "
            WHERE
                teacher_id = {$USER->id} 
                AND student_id IS NOT NULL
                AND (lesson.date + lesson.duration) > {$time}
                AND cm_id = {$this->cm->id}

            ORDER BY 
                lesson.date ASC
        ";

        return $sql;
    }

    protected function create_table($records) {
        
        $table = new \html_table();

        $table->width = '100%';
        $table->head = array(
            'Student',
            'Date',
            'Time',
            'Duration'
        );

        foreach ($records as $id => $class) {
            $table->data[$id][] = $class->student_name;;
            $table->data[$id][] = $this->get_cell_date($class);
            $table->data[$id][] = $this->get_cell_time($class);
            $table->data[$id][] = $this->get_cell_duration($class);
        }

        return $table;
    }

}
