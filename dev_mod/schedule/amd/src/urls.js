define(['mod_schedule/urls'], function() {

  let urlBase = `${M.cfg.wwwroot}/mod/schedule/restapi`;

  return {
    studentGetAvailableLessons : `${urlBase}/student_get_bookings.php`,
    studentBookLesson: `${urlBase}/student_book_lesson.php`,
    studentUnbookLesson: `${urlBase}/student_unbook_lesson.php`
  };
});
