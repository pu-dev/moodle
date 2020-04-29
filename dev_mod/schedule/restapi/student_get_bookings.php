<?php namespace mod_schedule;
require_once(dirname(__FILE__).'/../inc.php');
mod_require_once('/sql.php');
mod_require_once('/restapi/rest_base.php');


class student_get_bookings extends rest_base {
    public function __constructor() {
        parent::__constructor();
    }

    protected function render() {
        global $DB;
        global $USER;

        $time = time();
        $sql = sql::join_lesson_student_teacher();
        $sql .= "
            WHERE
                (
                    student_user.id = {$USER->id} OR
                    student_user.id is null
                ) 
                AND date > {$time}
                AND schedule_id = {$this->cm->instance}

            ORDER BY 
                lesson.date ASC,
                lesson.id ASC
        ";

        $lessons = $DB->get_records_sql($sql);
        return $this->response_200_OK(array('lessons' => $lessons));
    }
}

new student_get_bookings();