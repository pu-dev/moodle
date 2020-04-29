define(['mod_schedule/calendar'], function(Calendar)
{
  return {
    init: function(phpOpts) {
      let calendar = new Calendar(phpOpts);
      calendar.loadAvailableLessons();
    } // init
  }; // return
});
