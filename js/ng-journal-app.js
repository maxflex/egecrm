// Generated by CoffeeScript 1.10.0
var app;

app = angular.module("Journal", []).controller("StudentsCtrl", function($scope) {
  $scope.getGroup = function(id) {
    return _.findWhere($scope.Groups, {
      id: parseInt(id)
    });
  };
  $scope.getJournalGroups = function() {
    return Object.keys(_.chain($scope.Journal).groupBy('id_group').value());
  };
  $scope.getVisitsByGroup = function(id_group) {
    id_group = parseInt(id_group);
    return _.where($scope.Journal, {
      id_group: id_group
    });
  };
  $scope.getScheduleByDate = function(id_group, lesson_date) {
    return _.findWhere($scope.getGroup(id_group).Schedule, {
      date: lesson_date
    });
  };
  $scope.inActiveGroup = function(id_group) {
    id_group = parseInt(id_group);
    return _.where($scope.Groups, {
      id: id_group
    }).length;
  };
  $scope.getMaxVisits = function() {
    var max;
    max = -1;
    $.each($scope.Groups, function(i, group) {
      var count;
      count = $scope.getVisitsByGroup(group.id).length;
      if ($scope.getGroup(group.id).Schedule) {
        count += $scope.getGroup(group.id).Schedule.length;
      }
      if (count > max) {
        max = count;
      }
    });
    return max;
  };
  $scope.toggleMissingNote = function(Schedule) {
    var note;
    note = Schedule.missing_note;
    note++;
    ajaxStart();
    $.post('ajax/MissingNoteToggle', {
      id_student: $scope.student.id,
      id_group: Schedule.id_group,
      date: Schedule.hasOwnProperty('lesson_date') ? Schedule.lesson_date : Schedule.date
    }, (function(response) {
      ajaxEnd();
      Schedule.missing_note = response;
      $scope.$apply();
    }), 'json');
  };
  $scope.formatVisitDate = function(date) {
    return moment(date).format('DD.MM.YY');
  };
  return angular.element(document).ready(function() {
    return set_scope("Journal");
  });
});
