<?php namespace mod_schedule;
defined('MOODLE_INTERNAL') || die(__FILE__);

require_once(dirname(__FILE__).'/../../inc.php');
mod_require_once('/tools.php');
mod_require_once('/views/impl/view_student_base_impl.php');
mod_require_once('/components/student_book_class_list.php');
mod_require_once('/actions/action_student_book_class.php');
mod_require_once('/actions/action_student_unbook_class.php');


class view_student_book_lesson_impl extends view_student_base_impl {
    public const ACTION_NONE = 10;
    public const ACTION_BOOK_CLASS = 11;
    public const ACTION_UNBOOK_CLASS = 12;

    public function __construct() {
        parent::__construct(student_class_tabs::TAB_BOOK_LESSON);
    }

    protected function render() {
        $html = parent::render();
        $html .= $this->process_action();
        $html .= $this->render_student_book_class();

        return new view_result_html($html);
    }

    private function render_student_book_class() {
        $class_list = new student_book_class_list($this->cm);
        return \html_writer::table($class_list->get_class_table());
    }

    private function process_action() {        
        $html = '';
        $action = optional_param('action', self::ACTION_NONE, PARAM_INT);

        switch ($action) {
            case self::ACTION_NONE:
                // Do nothing here
                break;

            case self::ACTION_BOOK_CLASS:
                $html = $this->action_book_class();    
                break;

            case self::ACTION_UNBOOK_CLASS:
                $html = $this->action_unbook_class();    
                break;

            default:
                print_error("Invalid action: {$action}");
        }
        return $html;
    }

    private function action_book_class() {
        global $USER;
        global $OUTPUT;

        $class_id = required_param('class_id', PARAM_INT);
        $action = new action_student_book_class(
            $this->cm,
            $class_id,
            $this->schedule);
        $result = $action->execute();
        $html = '';

        switch ($result->status) {
            case action_student_book_class::RESULT_OK:
                $class_date = tools::epoch_to_date($result->data->date);
                $msg = get_string('class_booked_ok', 'schedule', $class_date);
                $html = $this->alert_success($msg);
                break;

            case action_student_book_class::RESULT_CLASS_UNAVAILABLE:
                $msg = get_string('class_booked_failed', 'schedule');
                $html = $this->alert_error($msg);
                break;

            case action_student_book_class::RESULT_BOOKED_CLASS_LIMIT:
                $limit_period = strtolower($this->schedule->lesson_limit_period);
                $limit_value = $this->schedule->lesson_limit_value;

                $msg = "Only {$limit_value} class(es) a {$limit_period} can be booked in this course";
                $html = $this->alert_error($msg);
                break;
            
            default:
                error_log('action_book_class: unknown status retured {$result->status}');
                break;
        }

        return $html;
    }

    private function action_unbook_class() {
        global $USER;

        $class_id = required_param('class_id', PARAM_INT);
        $action = new action_student_unbook_class($this->cm, $class_id, $USER->id);
        $result = $action->execute();
        $html = '';

        if ($result->status) {
            $class_date = tools::epoch_to_date($result->data->date);
            $msg = get_string('class_unbooked_ok', 'schedule', $class_date);
            $html = $this->alert_success($msg);
        }
        else {
            $msg = get_string('class_unbooked_failed', 'schedule');
            $html = $this->alert_error($msg);
        }

        return $html;
    }
}

