var pu;

define([
  'core/str',
  'core/notification',
  'core/loadingicon',
  'mod_schedule/calendar_config',
  'mod_schedule/urls',
  'mod_schedule/http_response_code'
], function(
  str,
  notification,
  LoadingIcon,
  CalendarConfig,
  Urls,
  HTTP_response
) { 



function Calendar(phpOpts) {
  let self = this;

  this.phpOpts = phpOpts;

  this.calendarEl = document.getElementById('calendar');
  
  this.calendar = new FullCalendar.Calendar(this.calendarEl, {
    plugins: [ 'interaction', 'dayGrid', 'timeGrid', 'list' ],
    defaultView: 'dayGridMonth',
    // selectable: true,
    header: {
      left: 'dayGridDay,dayGridWeek,dayGridMonth',
      center: 'title',
      right:  'prev,today,next '
    },
    eventClick: function(info) {
      self.lessonOnClick(info.event.extendedProps.lesson, info.event);
    },
    dateClick: function(info) {
    }
  });
}

Calendar.prototype.render = function() {
  this.calendar.render();
}

Calendar.prototype.raw = function() {
  return this.calendar;
}

Calendar.prototype.addLesson = function(lesson) {
  let dateStart = new Date(0);
  dateStart.setUTCSeconds(lesson.date);

  let dateStop = new Date(0);
  dateStop.setUTCSeconds(lesson.end_date);

  let event = {
    id: lesson.lesson_id,
    title: lesson.teacher_name,
    start: dateStart,
    end: dateStop,
    editable: true,
    startEditable: true,
    durationEditable: true,
    lesson: lesson
  };

  if ( lesson.student_id !== null ) {
    event.borderColor = CalendarConfig.bookedLessonBorderColor;
  } 
  else {
    event.borderColor = CalendarConfig.availableLessonBorderColor;
  }

  this.calendar.addEvent(event);
}

Calendar.prototype.addLessons = function(lessons) {
  let keys = Object.keys(lessons);
  for (key of keys) {
    this.addLesson(lessons[key]);
  }
}

Calendar.prototype.lessonOnClick = function(lesson, event) {
  if (lesson.student_id === null ) {
    this.bookLesson(lesson);
  }
  else {
    this.unbookLesson(lesson);
  }
}

Calendar.prototype.bookLesson = function(lesson) {
  console.log("book lesson");
  let self = this;
  let url = this.getUrl(Urls.studentBookLesson);

  fetch(url, {
    method: 'POST',
    headers: {'Content-Type': 'application/json'},
    body: JSON.stringify({
      lesson_id: lesson.lesson_id,
    })
  })
  .then(response => {
    if ( ! response.ok || response.status != HTTP_response.OK ) {
      throw `Error while booking class. Response status: ${response.status}`;
      // error 
    }
    return response.json();
  })
  .then(json => {
    this.lessonBooked(json.data.lesson);
  })
  .catch(function(error){
    // Todo message about failuer
    console.error(error);
  });
}


Calendar.prototype.lessonBooked = function(lesson) {
  let event = this.calendar.getEventById(lesson.id);
  let extProps = event.extendedProps;

  let lessonUpdated = extProps.lesson;
  lessonUpdated.student_id = lesson.student_id;
  // todo 
  // lessonUpdated.student_name = 
  
  event.setProp('borderColor', CalendarConfig.bookedLessonBorderColor);
  event.setExtendedProp('lesson', lessonUpdated);
}

Calendar.prototype.unbookLesson = function(lesson) {
  console.log("unbook lesson");
  console.log(lesson);
  let self = this;
  let url = this.getUrl(Urls.studentUnbookLesson);

  fetch(url, {
    method: 'POST',
    headers: {'Content-Type': 'application/json'},
    body: JSON.stringify({
      lesson_id: lesson.lesson_id,
    })
  })
  .then(response => {
    if ( ! response.ok || response.status != HTTP_response.OK ) {
      throw `Error while unbooking class. Response status: ${response.status}`;
    }
    return response.json();
  })
  .then(json => {
    this.lessonUnbooked(json.data.lesson);
  })
  .catch(function(error){
    console.error(error);
  });
}

Calendar.prototype.lessonUnbooked = function(lesson) {
  let event = this.calendar.getEventById(lesson.id);
  let extProps = event.extendedProps;

  let lessonUpdated = extProps.lesson;
  lessonUpdated.student_id = null;
  lessonUpdated.student_name = null;
  
  event.setProp('borderColor', CalendarConfig.availableLessonBorderColor);
  event.setExtendedProp('lesson', lessonUpdated);
}

Calendar.prototype.loadAvailableLessons = function() {
  let url = this.getUrl(Urls.studentGetAvailableLessons);

  fetch(url)
    .then(res => res.json())
    .then(res => {
      console.log(res);
      this.addLessons(res.data.lessons);
      this.render();
    })
    .catch(function(error) {
      console.error(error);
    });
}

Calendar.prototype.getUrl = function (urlBase, urlParams) {
  const urlParamsFixed = {
    id: this.phpOpts.cmid,
    sesskey: M.cfg.sesskey
  };

  let urlParamsTmp = null;
  if ( urlParams === 'undefined' ) {
    urlParamsTmp = urlParamsFixed;
  }
  else {
    urlParamsTmp = {...urlParamsFixed, ...urlParams};
  }

  let url = new URL(urlBase);
  url.search = new URLSearchParams(urlParamsTmp);
  return url;
}


return Calendar;

}); //define