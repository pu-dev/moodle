<?php namespace mod_schedule;
defined('MOODLE_INTERNAL') || die();

require_once(dirname(__FILE__).'/inc.php');


class sql {
 
    public static function join_lesson_student_teacher() {
        return "
            SELECT 
                lesson.id as lesson_id,
                
                lesson.teacher_id as teacher_id,
                teacher_user.username as teacher_name,
                
                lesson.student_id as student_id,
                student_user.username as student_name,
                
                lesson.schedule_id as schedule_id,

                lesson.date,
                lesson.duration,
                (lesson.date + lesson.duration) as end_date,
                lesson.topic,
                lesson.notes

            FROM {schedule_lesson} as lesson

            JOIN {user} as teacher_user
                ON lesson.teacher_id=teacher_user.id
              
            LEFT JOIN {user} as student_user
                ON lesson.student_id=student_user.id
        ";
    }

    public static function get_user_lesson_count(
        $user, $schedule_id, $start_date, $stop_date) {
        global $DB;

        $table = 'schedule_lesson';
        $where = "
            student_id = {$user->id}
            AND schedule_id = {$schedule_id}
            AND date >= {$start_date}
            AND date < {$stop_date}
        ";

        return $DB->count_records_select($table, $where);
    }
}
