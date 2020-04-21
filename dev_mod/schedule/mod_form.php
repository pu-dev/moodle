<?php
defined('MOODLE_INTERNAL') || die();
require_once ($CFG->dirroot.'/course/moodleform_mod.php');

/**
 * Form which is display when adding plugin to a course,
 * or when editing plugin settings.
 */
class mod_schedule_mod_form extends moodleform_mod {

    function definition() {
        $this->add_module_name();
        $this->add_student_limits();
        $this->standard_coursemodule_elements();
        $this->add_action_buttons();
    }

    private function add_module_name() {
        $mform =& $this->_form;
        $mform->addElement('header', 'general', get_string('general', 'form'));
        $mform->addElement('text', 'name', get_string('schedulename', 'schedule'), array('size'=>'64'));
        $mform->setDefault('name', 'Schedule');

        if (!empty($CFG->formatstringstriptags)) {
            $mform->setType('name', PARAM_TEXT);
        } else {
            $mform->setType('name', PARAM_CLEANHTML);
        }

        $mform->addRule('name', null, 'required', null, 'client');
        $mform->addRule('name', get_string('maximumchars', '', 255), 'maxlength', 255, 'client');
    }

    private function add_student_limits() {
        $mform =& $this->_form;
        $mform->addElement('header', 'limits', 'Lesson limits');
        $mform->setExpanded('limits', true);

        // Limit value
        $limit_value = $mform->addElement(
            'text', 
            'lesson_limit_value', 
            'Lesson count', 
            array('size'=>'8')
        );
        $mform->setType('lesson_limit_value', PARAM_INT);
        $mform->setDefault('lesson_limit_value', 0);

        // $mform->addHelpButton('lesson_limit_value', 'todo_fix_me','schedule');

        // Limit period
        $limit_period = $mform->addElement(
            'select', 
            'lesson_limit_period', 
            'Time period', 
            array(
                'month' => 'month',
                'fortnight' => 'fortnight',
                'week' => 'week'
            )
        );
        $mform->setDefault('lesson_limit_period', '1');

        $mform->addRule('lesson_limit_value', 'value required', 'required', null, 'client');
        $mform->addRule('lesson_limit_value', get_string('maximumchars', '', 2), 'maxlength', 2, 'client');
        $mform->addRule('lesson_limit_value', 'use only numbers', 'numeric', null, 'client');

        // $mform->addHelpButton('lesson_limit_period', 'todo_fix_me','schedule');
    }
}
