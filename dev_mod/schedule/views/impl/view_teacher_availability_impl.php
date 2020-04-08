<?php namespace mod_schedule;
defined('MOODLE_INTERNAL') || die();

require_once(dirname(__FILE__).'/../../../../config.php');
require_once($CFG->dirroot.'/mod/schedule/tools.php');
require_once($CFG->dirroot.'/mod/schedule/debug.php');
require_once($CFG->dirroot.'/mod/schedule/views/impl/view_teacher_base_impl.php');
require_once($CFG->dirroot.'/mod/schedule/components/teacher_availability_form.php');
require_once($CFG->dirroot.'/mod/schedule/components/teacher_availability_form_handler.php');
require_once($CFG->dirroot.'/mod/schedule/components/teacher_class_list.php');
require_once($CFG->dirroot.'/mod/schedule/actions/action_teacher_cancel_class.php');


class view_teacher_availability_impl extends view_teacher_base_impl {
    public const ACTION_NONE = 10;
    public const ACTION_CLASS_CANCEL = 11;
    // public const ACTION_UNBOOK_CLASS = 12;


    public function __construct() {
        parent::__construct(
            teacher_class_tabs::TAB_AVAILABILITY_LESSON
        );
    }


    protected function display() {
        parent::display();
        $this->process_action();
        $this->display_teacher_form();
        $this->display_teacher_class();
    }


    private function display_teacher_form() {
        $form_params = array('id' => $this->cm->id);
        $url_params = $form_params;

        $url = tools::get_self_url($url_params);
        debug("Form target url: {$url}");

        $teacher_form = new teacher_availability_form($url, $form_params);
        $teacher_form_handler = new teacher_availability_form_handler($teacher_form);

        $teacher_form_handler->process_form();
        $teacher_form->display();
    }


    private function display_teacher_class() {
        $class_list = new teacher_class_list($this->cm);
        echo \html_writer::table($class_list->get_class_table());
    }


    private function process_action() {
        $action = optional_param('action', self::ACTION_NONE, PARAM_INT);
        
        switch ($action) {
            case self::ACTION_NONE:
                // Do nothing here
                break;

            case self::ACTION_CLASS_CANCEL:
                $this->action_class_cancel();    
                break;
            default:
                print_error("Invalid action: {$action}");
        }
    }

    private function action_class_cancel() {
        $class_id = required_param('class_id', PARAM_INT);
        $action = new action_teacher_cancel_class($class_id);
        $result = $action->execute();

        if ($result->ok) {
            $msg = get_string('class_canceled_ok', 'schedule');
            $this->alert_success($msg);
        }
        else {
            $msg = get_string('class_canceled_failed', 'schedule');
            $this->alert_success($msg);
        }        
    }
}
