<?php namespace mod_schedule;
defined('MOODLE_INTERNAL') || die();

require_once(dirname(__FILE__).'/../inc.php');
mod_require_once('/tools.php');
mod_require_once('/sql.php');



abstract class class_list_base implements \renderable {

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
        return sql::join_lesson_student_teacher();
    }


    final protected function get_cell_time($class) {
        return \html_writer::tag(
            'nobr', 
            strftime('%H:%M', $class->date)
        );
    }


    final protected function get_cell_duration($class) {
        return \html_writer::tag(
            'nobr', 
            gmdate('H:i', $class->duration)
        );
    }


    final protected function get_cell_date($class) {
         return tools::epoch_to_date($class->date, true);
    }


    final protected function get_cell_topic($class) {
        return $this->__get_txt_cell($class->topic);
    }


    final protected function get_cell_notes($class) {
        return $this->__get_txt_cell($class->notes);
    }

    final private function __get_txt_cell($txt) {
        # todo 
        $blank_msg = "[ blank ]";

        if ( ! isset($txt) ) {
            return $blank_msg;
        }

        if ( strlen($txt) == 0 ) {
            return $blank_msg;
        }

        return nl2br($txt);
    }


    final protected function get_cell_action(
        $label,
        $class=null, 
        $action=null, 
        $url_target=null,
        $url_params=null
    ) {
        $url_params_base = array('id' => $this->cm->id);

        if ( is_null($url_params) ) {
            $url_params = $url_params_base;
        }
        else {
            $url_params = array_merge($url_params, $url_params_base);
        }

        if ( is_null($url_target) ) {
            $url_target = tools::get_self_url($url_params);    
        } else {
            $url_target = tools::get_module_url($url_target, $url_params);  
        }
        
        $html = \html_writer::start_tag(
            'form', 
            array(
                'action' => $url_target,
                'method' => 'post'
            )
        );

        $html .= \html_writer::tag(
            'button', 
            $label,
            array(
                'type'  => 'submit', 
                'name' => 'action'
            )
        );

        if ( ! is_null($action) ) {
            $html .= \html_writer::empty_tag(
                'input', 
                array(
                    'type' => 'hidden',
                    'name' => 'action',
                    'value' => $action
                )
            );
        }

        if ( ! is_null($action) ) {
            $html .= \html_writer::empty_tag(
                'input', 
                array(
                    'type' => 'hidden',
                    'name' => 'class_id',
                    'value' => $class->lesson_id
                )
            );
        }

        $html .= \html_writer::end_tag('form');
        return $html;
    }

    abstract protected function create_table($records);

    abstract protected function get_sql_query();
}
