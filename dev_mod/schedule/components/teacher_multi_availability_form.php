<?php namespace mod_schedule;
defined('MOODLE_INTERNAL') || die();

require_once(dirname(__FILE__).'/../inc.php');
mod_require_once('/components/form_base.php');


class teacher_multi_availability_form extends form_base {

    public function definition() {
        $this->create_header('Multiple sessions', 'header_multi_class');
        $this->create_week_days('sdays');
        $this->create_time_selector('class_time');
        $this->create_date_selector();
        $this->create_save_button();
    }

    public function validation($data, $files) {
        $errors = parent::validation($data, $files);
        $this->validate_class_days($errors, $data, 'sdays');
        $this->validate_class_start_date($errors, $data, 'date_start', 'class_time');
        $this->validate_class_end_date($errors, $data, 'date_start', 'date_end');
        $this->validate_class_time($errors, $data, 'class_time');
        return $errors;
    }

    private function create_date_selector () {
        $mform =& $this->_form;
        $mform->addElement(
            'date_selector', 
            'date_start',
            # todo
            'Start date');

        $mform->addElement(
            'date_selector', 
            'date_end',
            # todo
            'End date');
    }
}