<?php
defined('MOODLE_INTERNAL') || die();
require_once ($CFG->dirroot.'/course/moodleform_mod.php');


/**
 * Form which is display when adding plugin to a course,
 * or when editing plugin settings.
 */
class mod_schedule_mod_form extends moodleform_mod {

    function definition() {
        global $CFG, $SCHEDULE_SHOWRESULTS, $SCHEDULE_PUBLISH, $SCHEDULE_DISPLAY, $DB;

        $this->add_module_name();
        $this->standard_coursemodule_elements();
        $this->add_action_buttons();
    }

    private function add_module_name() {
        $mform    =& $this->_form;
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
}
