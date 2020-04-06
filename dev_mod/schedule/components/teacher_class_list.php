<?php

class mod_schedule_teacher_class_list implements renderable {

    private $class_table;

    public function __construct() {
        global $DB;

        // $classes = $DB->get_records('schedule_lesson');

        $sql = "
            SELECT 
                lesson.id,
                
                lesson.teacher_id, 
                teacher_user.username as teacher_name,
                
                lesson.student_id,
                student_user.username as student_name,
                
                lesson.lesson_date,
                lesson.lesson_duration
              
            FROM {schedule_lesson} as lesson

            JOIN {user} as teacher_user
                ON lesson.teacher_id=teacher_user.id
              
            LEFT JOIN {user} as student_user
                ON lesson.student_id=student_user.id

            ORDER BY 
                lesson.lesson_date ASC
        ";

        $classes = $DB->get_records_sql($sql);

        $table = new html_table();

        $table->width = '100%';
        $table->head = array(
            'Teacher',
            'Student',
            'Date',
            'Time',
            'Duration'
        );

        foreach ($classes as $id => $class) {
            
            $student_name = 'todo';
            // $student_name = get_string('no_student', 'schedule');
            if ($class->student_id != 0) {
                // $student_name = 'name TODO';
            }



            $table->data[$id][] = $class->teacher_name;
            $table->data[$id][] = $class->student_name;

            # TODO
            # get_string('date_format', '<nobr>%a %d %b %Y</nobr>'); 
            $table->data[$id][] = userdate($class->lesson_date, '<nobr>%a %d %b %Y</nobr>');


            // Get time
            //
            $table->data[$id][] = html_writer::tag(
                'nobr', 
                strftime('%H:%M', $class->lesson_date));

            // Get duration
            //
            $table->data[$id][] = html_writer::tag(
                'nobr', 
                gmdate('H:i', $class->lesson_duration)
            );
        }

        $this->class_table = $table;
    }

    public function get_class_table() {
        return $this->class_table;
    }
}
