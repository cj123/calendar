angular.module("calendar").service("CalendarData", ["moment", function(moment) {
    this.currentDate = moment();
}]);
