<?php namespace mod_schedule;
require_once(dirname(__FILE__).'/../inc.php');
mod_require_once('/restapi/rest_base.php');
mod_require_once('/actions/action_student_unbook_class.php');


class student_unbook_lesson extends rest_base {
    public function __constructor() {
        parent::__constructor();
    }

    protected function render() {
        $data = $this->get_body_decoded();
        return $this->unbook_lesson($data->lesson_id);
    }

    private function unbook_lesson($lesson_id) {
        $action = new action_student_unbook_class(
            $this->cm,
            $lesson_id);

        $result = $action->execute();

        if ($result->status) {
            $data = array('lesson' => $result->data);
            return $this->response_200_OK($data);
        }

        return $this->response_400_BAD_REQUEST();
    }
}

new student_unbook_lesson();