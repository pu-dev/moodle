<?php
require_once(dirname(__FILE__).'/../../../../config.php');
require_once($CFG->dirroot.'/mod/schedule/debug.php');
require_once($CFG->dirroot.'/mod/schedule/tools.php');
require_once($CFG->dirroot.'/mod/schedule/views/impl/view_student_base_impl.php');
require_once($CFG->dirroot.'/mod/schedule/components/student_book_class_list.php');
require_once($CFG->dirroot.'/mod/schedule/actions/action_student_book_class.php');
require_once($CFG->dirroot.'/mod/schedule/actions/action_student_unbook_class.php');


class view_student_book_lesson_impl extends view_student_base_impl {
    public const ACTION_NONE = 10;
    public const ACTION_BOOK_CLASS = 11;
    public const ACTION_UNBOOK_CLASS = 12;

    public function __construct() {
        parent::__construct(
            mod_schedule_student_class_tabs::TAB_BOOK_LESSON
        );
    }

    protected function display() {
        parent::display();
        $this->process_action();
        $this->display_student_book_class();
    }

    private function display_student_book_class() {
        $class_list = new mod_schedule_student_book_class_list($this->cm);
        echo html_writer::table($class_list->get_class_table());
    }

    private function process_action() {
        $action = optional_param('action', self::ACTION_NONE, PARAM_INT);
        debug('Action to process: {$action}');
        
        switch ($action) {
            case self::ACTION_NONE:
                // Do nothing here
                break;

            case self::ACTION_BOOK_CLASS:
                $this->action_book_class();    
                break;

            case self::ACTION_UNBOOK_CLASS:
                $this->action_unbook_class();    
                break;

            default:
                print_error("Invalid action: {$action}");
        }
    }

    private function action_book_class() {
        global $USER;
        global $OUTPUT;

        $class_id = required_param('class_id', PARAM_INT);
        $action = new action_student_book_class($class_id, $USER->id);
        $result = $action->execute();
        $class_date = mod_schedule_tools::epoch_to_date($result->data->lesson_date);

        if ($result->ok) {
            $msg = get_string('class_booked_ok', 'schedule', $class_date);
            $this->alert_success($msg);
        }
        else {
            $msg = get_string('class_booked_failed', 'schedule', $class_date);
            $this->alert_error($msg);
        }
    }

    private function action_unbook_class() {
        global $USER;

        $class_id = required_param('class_id', PARAM_INT);
        $action = new action_student_unbook_class($class_id, $USER->id);
        $result = $action->execute();
        $class_date = mod_schedule_tools::epoch_to_date($result->data->lesson_date);

        if ($result->ok) {
            $msg = get_string('class_ubbooked_ok', 'schedule', $class_date);
            $this->alert_success($msg);
        }
        else {
            $msg = get_string('class_unbooked_failed', 'schedule');
            $this->alert_success($msg);
        }
    }
}

