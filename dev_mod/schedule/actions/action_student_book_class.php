<?php namespace mod_schedule;

require_once(dirname(__FILE__).'/../inc.php');
mod_require_once('/sql.php');
mod_require_once('/actions/action_lesson_base.php');
mod_require_once('/events/event_student_book_lesson.php');


class action_student_book_class extends action_lesson_base {
    public const RESULT_OK = 10;
    public const RESULT_CLASS_UNAVAILABLE = 11;
    public const RESULT_BOOKED_CLASS_LIMIT = 12;

    private $class_id;
    private $student_id;
    private $schedule;

    public function __construct($cm, $class_id, $schedule) {
        global $USER;       
        parent::__construct($cm);
        $this->class_id = $class_id;
        $this->student_id = $USER->id;
        $this->schedule = $schedule;
    }


    public function execute() {
        if ( $this->class_limit_reached() ) {
            return new action_result(self::RESULT_BOOKED_CLASS_LIMIT);
        }

        $action_result = $this->update_db();
        if ( $action_result->status == self::RESULT_OK ) {
            $this->update_calendar($action_result->data);
        }
        return $action_result;
    }


    private function class_limit_reached() {
        global $DB;

        $lesson = $DB->get_record(
            'schedule_lesson',
            array('id' => $this->class_id));

        $limit_period = strtolower($this->schedule->lesson_limit_period);
        $limit_value = $this->schedule->lesson_limit_value;

        // 0 means not limit
        if ( $limit_value == 0 ) {
            return false;
        }

        switch ($limit_period) {
            case 'month':
                $booked_class_count = $this->get_booked_class_count_month($lesson);
                break;
            
            case 'week':
                $booked_class_count = $this->get_booked_class_count_week($lesson);
                break;

            default:
                # code...
                break;
        }

        if ( $limit_value != 0 && $limit_value <= $booked_class_count ) {
            return true;
        }

        return false;
    }

    private function get_booked_class_count_month($lesson) {
        $lesson_date = \date('Y-m', $lesson->date);

        $period_start = new \DateTime("first day of {$lesson_date}");
        $period_end = new \DateTime("last day of {$lesson_date}");

        $period_start = $period_start->format('U');
        $period_end = $period_end->format('U') + 24 * 60 * 60; // move it to next month

        return $this->get_user_lesson_count($period_start, $period_end);
    }

    private function get_booked_class_count_week($lesson) {
        $lesson_date = \date('Y-m-d', $lesson->date);
        $period_start = null;

        // Check if the lesson is on monday
        if ( \date('N', $lesson->date) == 1 ) {
            $period_start = $lesson->date;
        }
        else {
            $period_start = new \DateTime("{$lesson_date} last Monday");
            $period_start = $period_start->format('U');
        }
        
        $period_end = new \DateTime("{$lesson_date} next Monday");
        $period_end = $period_end->format('U');
        return $this->get_user_lesson_count($period_start, $period_end);
    }

    private function get_user_lesson_count($period_start, $period_end) {
        global $USER;
        return sql::get_user_lesson_count(
            $USER,
            $this->cm->instance,
            $period_start,
            $period_end);
    }

    public function update_db() {
        global $DB;
        $table = 'schedule_lesson';

        // Update record in DB
        $DB->set_field(
            $table,
            'student_id',
            $this->student_id,
            array(
                'id' => $this->class_id,
                'student_id' => null
            )
        );

        // Check if it was updated, as someone could 
        // update it before executing above 'set_field' call
        $class = $DB->get_record(
            $table, 
            array(
                'id' => $this->class_id,
                'student_id' => $this->student_id
            )
        );

        if ( is_object($class) ) {
            return new action_result(self::RESULT_OK, $class);
        }

        return new action_result(self::RESULT_CLASS_UNAVAILABLE);
    }

    public function update_calendar($lesson) {
        $event = new event_student_book_lesson($this->cm, $lesson);
        $event->execute();
    }
}


