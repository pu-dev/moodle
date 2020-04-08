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
                
                lesson.date,
                lesson.duration,
                lesson.topic,
                lesson.notes

            FROM {schedule_lesson} as lesson

            JOIN {user} as teacher_user
                ON lesson.teacher_id=teacher_user.id
              
            LEFT JOIN {user} as student_user
                ON lesson.student_id=student_user.id
        ";
    }
}
