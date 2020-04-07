<?php
defined('MOODLE_INTERNAL') || die();

require_once(dirname(__FILE__).'/../../../config.php');
require_once($CFG->dirroot.'/mod/schedule/debug.php');


abstract class mod_schedule_class_list_base implements renderable {

    protected $class_table;
    protected $cm;

    protected function __construct($cm) {
        global $DB;
        $this->cm = $cm;

        $sql = $this->get_sql_query();
        $records = $DB->get_records_sql($sql);
        
        $this->class_table = $this->create_table($records);

    }

    final public function get_class_table() {
        return $this->class_table;
    }

    final protected function get_sql_query_base() {
        $sql = "
            SELECT 
                lesson.id as lesson_id,
                
                lesson.teacher_id as teacher_id,
                teacher_user.username as teacher_name,
                
                lesson.student_id as student_id,
                student_user.username as student_name,
                
                lesson.lesson_date,
                lesson.lesson_duration
              
            FROM {schedule_lesson} as lesson

            JOIN {user} as teacher_user
                ON lesson.teacher_id=teacher_user.id
              
            LEFT JOIN {user} as student_user
                ON lesson.student_id=student_user.id
        ";

        return $sql;
    }

    final protected function get_cell_time($class) {
        return html_writer::tag(
            'nobr', 
            strftime('%H:%M', $class->lesson_date)
        );
    }

    final protected function get_cell_duration($class) {
        return html_writer::tag(
            'nobr', 
            gmdate('H:i', $class->lesson_duration)
        );
    }

    final protected function get_cell_date($class) {
         return userdate($class->lesson_date, '<nobr>%a %d %b %Y</nobr>');
    }

    final protected function get_cell_action($class, $action, $label) {
        $url_params = array('id' => $this->cm->id);
        $url_action = mod_schedule_tools::get_self_url($url_params);

        $html = html_writer::start_tag(
            'form', 
            array(
                'action' => $url_action,
                'method' => 'post'
            )
        );

        $html .= html_writer::tag(
            'button', 
            $label,
            array(
                'type'  => 'submit', 
                'name' => 'action'
            )
        );

        $html .= html_writer::empty_tag(
            'input', 
            array(
                'type' => 'hidden',
                'name' => 'action',
                'value' => $action
            )
        );

        $html .= html_writer::empty_tag(
            'input', 
            array(
                'type' => 'hidden',
                'name' => 'class_id',
                'value' => $class->lesson_id
            )
        );

        $html .= html_writer::end_tag('form');
        return $html;
    }

    abstract protected function create_table($records);

    abstract protected function get_sql_query();
}
