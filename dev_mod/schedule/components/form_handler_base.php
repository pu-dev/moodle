<?php namespace mod_schedule;
defined('MOODLE_INTERNAL') || die();

require_once(dirname(__FILE__).'/../inc.php');


abstract class form_handler_base {
    public const FORM_DISPLAYED = 1;
    public const FORM_SAVED = 2;
    public const FORM_CANCELED = 3;

    protected $form;
    protected $cm;

    public function __construct($form, $cm) {
        $this->form = $form;
        $this->cm = $cm;
    }

    public function process_form() {
        if ($this->form->is_cancelled()) {
            debug("Form canceled");
            $this->canceled();
            return self::FORM_CANCELED;
        } 
        else if ($this->form->get_data()) {
            debug("Form saved");
            $this->saved();
            return self::FORM_SAVED;
        } 
        else {
            debug("Form displayed");
            $this->validated(); // Should it be here?
            // this branch is executed if the form is submitted but the data doesn't validate and the form should be redisplayed
            // or on the first display of the form.
            //Set default data (if any)
            // $mform->set_data($toform);
            return self::FORM_DISPLAYED;
        }
    }

    protected function saved() {
        $this->form->set_expanded(false);
    }

    abstract protected function canceled();
    
    abstract protected function validated();
}
