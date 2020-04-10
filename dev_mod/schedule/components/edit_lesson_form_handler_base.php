<?php namespace mod_schedule;
defined('MOODLE_INTERNAL') || die();

require_once(dirname(__FILE__).'/../inc.php');
mod_require_once('/tools.php');
mod_require_once('/components/form_handler_base.php');
mod_require_once('/actions/action_update_lesson.php');


abstract class edit_lesson_form_handler_base extends form_handler_base {
    
    public function __construct($form) {
        parent::__construct($form);
    }

    protected function save_form() {
        global $DB;

        $form_data = $this->form->get_data();
        $action = new action_update_lesson(
            $form_data->lesson_id,
            $form_data->topic,
            $form_data->notes
        );

        $action->execute();
    }

    protected function cancel_form() {
    }

    protected function validate_form() {
    }
}
