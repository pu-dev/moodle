<?php
if (!defined('MOODLE_INTERNAL')) {
    die('Direct access to this script is forbidden.');    ///  It must be included from a Moodle page
}

require_once ($CFG->dirroot.'/course/moodleform_mod.php');


/**
 *
 * Form which is display when adding plugin to a course,
 * or when editing plugin settings.
 *
 */
class mod_schedule_mod_form extends moodleform_mod {

    function definition() {
        global $CFG, $SCHEDULE_SHOWRESULTS, $SCHEDULE_PUBLISH, $SCHEDULE_DISPLAY, $DB;

        $mform    =& $this->_form;

        //-------------------------------------------------------------------------------
        $mform->addElement('header', 'general', get_string('general', 'form'));

        $mform->addElement('text', 'name', get_string('schedulename', 'schedule'), array('size'=>'64'));
        $mform->setDefault('name', 'schedule');
        
        if (!empty($CFG->formatstringstriptags)) {
            $mform->setType('name', PARAM_TEXT);
        } else {
            $mform->setType('name', PARAM_CLEANHTML);
        }
        $mform->addRule('name', null, 'required', null, 'client');
        $mform->addRule('name', get_string('maximumchars', '', 255), 'maxlength', 255, 'client');

        //-------------------------------------------------------------------------------
        $this->standard_coursemodule_elements();
        
        //-------------------------------------------------------------------------------
        $this->add_action_buttons();
    }

    function data_preprocessing(&$default_values){
        global $DB;
        // if (!empty($this->_instance) && ($options = $DB->get_records_menu('schedule_options',array('scheduleid'=>$this->_instance), 'id', 'id,text'))
        //        && ($options2 = $DB->get_records_menu('schedule_options', array('scheduleid'=>$this->_instance), 'id', 'id,maxanswers')) ) {
        //     $scheduleids=array_keys($options);
        //     $options=array_values($options);
        //     $options2=array_values($options2);

        //     foreach (array_keys($options) as $key){
        //         $default_values['option['.$key.']'] = $options[$key];
        //         $default_values['limit['.$key.']'] = $options2[$key];
        //         $default_values['optionid['.$key.']'] = $scheduleids[$key];
        //     }

        // }

    }

    /**
     * Allows module to modify the data returned by form get_data().
     * This method is also called in the bulk activity completion form.
     *
     * Only available on moodleform_mod.
     *
     * @param stdClass $data the form data to be modified.
     */
    public function data_postprocessing($data) {
        parent::data_postprocessing($data);
        // Set up completion section even if checkbox is not ticked
        if (!empty($data->completionunlocked)) {
            if (empty($data->completionsubmit)) {
                $data->completionsubmit = 0;
            }
        }
    }

    /**
     * Enforce validation rules here
     *
     * @param array $data array of ("fieldname"=>value) of submitted data
     * @param array $files array of uploaded files "element_name"=>tmp_file_path
     * @return array
     **/
    public function validation($data, $files) {
        $errors = parent::validation($data, $files);

        // Check open and close times are consistent.
        // if ($data['timeopen'] && $data['timeclose'] &&
        //         $data['timeclose'] < $data['timeopen']) {
        //     $errors['timeclose'] = get_string('closebeforeopen', 'schedule');
        // }

        return $errors;
    }

    function add_completion_rules() {
        $mform =& $this->_form;

        $mform->addElement('checkbox', 'completionsubmit', '', get_string('completionsubmit', 'schedule'));
        // Enable this completion rule by default.
        $mform->setDefault('completionsubmit', 1);
        return array('completionsubmit');
    }

    function completion_rule_enabled($data) {
        return !empty($data['completionsubmit']);
    }
}

