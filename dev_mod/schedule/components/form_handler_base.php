<?php namespace mod_schedule;
defined('MOODLE_INTERNAL') || die();

require_once(dirname(__FILE__).'/../../../config.php');
require_once($CFG->dirroot.'/mod/schedule/debug.php');


abstract class form_handler_base {
    public const FORM_DISPLAYED = 1;
    public const FORM_SAVED = 2;
    public const FORM_CANCELED = 3;

    protected $form;

    public function __construct($form) {
        $this->form = $form;
    }

    public function process_form() {
        if ($this->form->is_cancelled()) {
            debug("Form canceled");
            $this->cancel_form();
            return self::FORM_CANCELED;
        } 
        else if ($this->form->get_data()) {
            debug("Form saved");
            $this->save_form();
            return self::FORM_SAVED;
        } 
        else {
            debug("Form displayed");
            $this->validate_form(); // Should it be here
            // this branch is executed if the form is submitted but the data doesn't validate and the form should be redisplayed
            // or on the first display of the form.
            //Set default data (if any)
            // $mform->set_data($toform);
            return self::FORM_DISPLAYED;
        }
    }

    abstract protected function save_form();

    abstract protected function cancel_form();
    
    abstract protected function validate_form();
}
