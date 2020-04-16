<?php namespace mod_schedule;
defined('MOODLE_INTERNAL') || die();

require_once(dirname(__FILE__).'/../inc.php');
mod_require_once('/components/form_base.php');


class teacher_availability_form extends form_base {
    public function definition() {
        $this->create_header('Add single session', 'header_single_class');
        $this->create_time_selector('class_time');
        $this->create_date_selector();
        $this->create_save_button();
    }


    public function validation($data, $files) {
        $errors = parent::validation($data, $files);
        $this->validate_class_start_date($errors, $data, 'class_date', 'class_time');
        $this->validate_class_time($errors, $data, 'class_time');
        return $errors;
    }

    private function create_date_selector () {
        $mform =& $this->_form;
        $mform->addElement(
            'date_selector', 
            'class_date', 
            # todo
            'Date');
    }
}
