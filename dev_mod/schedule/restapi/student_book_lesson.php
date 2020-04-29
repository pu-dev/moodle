<?php namespace mod_schedule;
require_once(dirname(__FILE__).'/../inc.php');
mod_require_once('/restapi/rest_base.php');
mod_require_once('/actions/action_student_book_class.php');


class student_book_lesson extends rest_base {
    public function __constructor() {
        parent::__constructor();
    }

    protected function render() {
        $data = $this->get_body_decoded();
        return $this->book_lesson($data->lesson_id);
    }

    private function book_lesson($lesson_id) {
        $action = new action_student_book_class(
            $this->cm,
            $lesson_id,
            $this->schedule);

        $result = $action->execute();

        switch ($result->status) {
            case action_student_book_class::RESULT_OK:
                $data = array('lesson' => $result->data);
                return $this->response_200_OK($data);

            case action_student_book_class::RESULT_CLASS_UNAVAILABLE:
                return $this->response_412_PRECONDITION_FAILED();

            case action_student_book_class::RESULT_BOOKED_CLASS_LIMIT:
                return $this->response_412_PRECONDITION_FAILED();

            default:
                return $this->response_500_INTERNAL_SERVER_ERROR();
        }
    }
}

new student_book_lesson();