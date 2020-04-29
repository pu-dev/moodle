define([
  'jquery',
  'core/str',
  'core/notification',
  'core/loadingicon',
  'core/modal_factory',
  'mod_schedule/calendar_config',
  'mod_schedule/urls',
  'mod_schedule/http_response_code',
], function(
  $,
  str,
  notification,
  LoadingIcon,
  ModalFactory,
  CalendarConfig,
  Urls,
  HTTP_response
) { 

function epochToDate(seconds) {
  const date = new Date(0);
  date.setUTCSeconds(seconds);
  return date;
}

function epochToDateFormatted(seconds) {
  const date = epochToDate(seconds);

  const ye = new Intl.DateTimeFormat('en', { year: 'numeric' }).format(date);
  const mo = new Intl.DateTimeFormat('en', { month: 'long' }).format(date);
  const da = new Intl.DateTimeFormat('en', { day: '2-digit' }).format(date);
  const h = new  Intl.DateTimeFormat('en', { hour: '2-digit',  hour12: false }).format(date);
  const m = new Intl.DateTimeFormat('en', { minute: '2-digit', hour12: false }).format(date);

  const mi = m.length == 1 ? `0${m}`: m;
  return `${h}:${mi}, ${da}-${mo}`;
}

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
      right: 'prev,today,next '
    },
    displayEventTime: true,
    displayEventEnd: true,
    editable: false,
    eventClick: function(info) {
      self.lessonOnClick(info.event.extendedProps.lesson, info.event);
    },
    eventMouseEnter: function(info) {
      self.lessonOnMouseEnter(info);
    },
    eventMouseLeave: function(info) {
      self.lessonOnMouseLeave(info);
    },
    eventTimeFormat: {
      hour: 'numeric',
      minute: '2-digit',
      meridiem: false
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
  let dateStart = epochToDate(lesson.date);
  let dateStop = epochToDate(lesson.end_date);

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
    event.backgroundColor = CalendarConfig.bookedLessonBackgroundColor;
  } 
  else {
    event.borderColor = CalendarConfig.availableLessonBorderColor;
    event.backgroundColor = CalendarConfig.availableLessonBackgroundColor;
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
    // notification.alert('Error', error, 'OK');
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
  event.setProp('backgroundColor', CalendarConfig.bookedLessonBackgroundColor);
  event.setExtendedProp('lesson', lessonUpdated);

  const date = epochToDateFormatted(lesson.date);
  notification.alert('Lesson booked', 
    `Booked lesson on ${date}`, 'OK');

}

Calendar.prototype.unbookLesson = function(lesson) {
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
    // notification.alert('Error', error, 'OK');
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
  event.setProp('backgroundColor', CalendarConfig.availableLessonBackgroundColor);
  event.setExtendedProp('lesson', lessonUpdated);

  const date = epochToDateFormatted(lesson.date);
  notification.alert(
    'Lesson unbooked', 
    `Unbooked lesson on ${date}`, 
    'OK');
}

Calendar.prototype.loadAvailableLessons = function() {
  let url = this.getUrl(Urls.studentGetAvailableLessons);

  let loadPromise = fetch(url)
    .then(res => res.json())
    .then(res => {
      this.addLessons(res.data.lessons);
      this.render();
    })
    .catch(function(error) {
      console.error(error);
    });

  var container = $('#calendar');
  LoadingIcon.addIconToContainerRemoveOnCompletion(container, loadPromise);
}

Calendar.prototype.lessonOnMouseEnter = function(info) {
}

Calendar.prototype.lessonOnMouseLeave = function(info) {
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