var app;

app = angular.module("Tests", ['ngSanitize', 'ui.bootstrap']).filter('unsafe', function($sce) {
  return $sce.trustAsHtml;
}).filter('range', function() {
  return function(input, total) {
    var i, j, ref;
    total = parseInt(total);
    for (i = j = 1, ref = total + 1; j < ref; i = j += 1) {
      input.push(i);
    }
    return input;
  };
}).filter('toArray', function() {
  return function(obj) {
    var arr;
    arr = [];
    $.each(obj, function(index, value) {
      return arr.push(value);
    });
    return arr;
  };
}).controller("StartCtrl", function($scope, $timeout, $interval) {
  var finishTest, saveAnswers;
  $timeout(function() {
    if ($scope.final_score === void 0) {
      $scope.interval = $interval(function() {
        $scope.time--;
        console.log($scope.time);
        if ($scope.time <= 0) {
          return finishTest();
        }
      }, 1000);
    }
    return $scope.current_problem = 0;
  });
  $scope.counter = function() {
    return moment({}).seconds($scope.time).format("mm:ss");
  };
  $scope.nextProblem = function() {
    var last_question;
    last_question = ($scope.Test.Problems.length - $scope.current_problem) === 1;
    saveAnswers(last_question);
    if (!last_question) {
      return $scope.current_problem++;
    }
  };
  $scope.answered = function() {
    var problem_id;
    problem_id = $scope.Test.Problems[$scope.current_problem].id;
    return $scope.answers[problem_id] !== void 0;
  };
  $scope.prevProblem = function() {
    saveAnswers();
    return $scope.current_problem--;
  };
  $scope.setProblem = function(index) {
    return $scope.current_problem = index;
  };
  saveAnswers = function(last_question) {
    return $.post("tests/ajaxSaveAnswers", {
      id: $scope.Test.id,
      answers: $scope.answers
    }, function(response) {
      $scope.server_answers = angular.copy($scope.answers);
      $scope.$apply();
      if (last_question) {
        return finishTest();
      }
    });
  };
  finishTest = function() {
    return $.post("tests/ajaxFinishTest", {
      id: $scope.Test.id
    }, function(final_score) {
      $interval.cancel($scope.interval);
      $scope.final_score = final_score;
      return $scope.$apply();
    });
  };
  $scope.back = function() {
    return redirect("students/tests");
  };
  $scope.notFinished = function() {
    return $scope.final_score === void 0;
  };
  $scope.$watch('current_problem', function(newVal) {
    if (newVal !== void 0) {
      return $scope.Problem = $scope.Test.Problems[newVal];
    }
  });
  return angular.element(document).ready(function() {
    return set_scope("Tests");
  });
}).controller("StudentTestsCtrl", function($scope, $timeout) {
  $scope.getTestStatus = function(Test) {
    return test_statuses[Test.intermediate || 0];
  };
  $scope.getStudentTest = function(id_testing) {
    return _.find($scope.Tests, {
      id: parseInt(id_testing)
    });
  };
  $scope.toggleTestStatus = function(StudentTest) {
    ajaxStart();
    return $.post("tests/ajaxToggleStatus", {
      id_test: StudentTest.id_test,
      id_student: StudentTest.id_student
    }, function(new_status) {
      ajaxEnd();
      StudentTest.intermediate = parseInt(new_status);
      return $scope.$apply();
    });
  };
  $scope.getTestStatus = function(StudentTest) {
    return test_statuses[StudentTest.intermediate || 0];
  };
  $scope.timeLeft = function(StudentTest) {
    var timestamp_end;
    timestamp_end = moment(StudentTest.date_start).add(30, 'minutes').unix();
    return moment({}).seconds(timestamp_end - moment().unix()).format("mm:ss");
  };
  $scope.formatTestDate = function(StudentTest) {
    if (StudentTest) {
      return moment(StudentTest.date_start).format('DD.MM.YY в HH:mm');
    }
  };
  $scope.testDisplay = function(StudentTest) {
    return StudentTest && (StudentTest.isFinished || StudentTest.inProgress);
  };
  $scope.getStudentAnswerClass = function(StudentTest, problem_id, correct_answer) {
    if (StudentTest && StudentTest.answers && StudentTest.answers.hasOwnProperty(problem_id)) {
      if (StudentTest.answers[problem_id] === correct_answer) {
        return "";
      } else {
        return "circle-red";
      }
    }
    return "circle-gray";
  };
  $scope.getTestHint = function(StudentTest, problem_id, correct_answer) {
    var answer;
    answer = $scope.getStudentAnswerClass(Problem, StudentTest);
    switch (answer) {
      case 'circle-red':
        return 'ответ неверный';
      case 'circle-gray':
        return 'ответ не указан';
      default:
        return 'ответ верный, ' + Problem.score + ' баллов';
    }
  };
  $scope.deleteTest = function(StudentTest) {
    ajaxStart();
    return $.post("tests/ajaxDeleteStudentTest", {
      id: StudentTest.id
    }, function() {
      ajaxEnd();
      $scope.StudentTests = _.reject($scope.StudentTests, function(e) {
        return e.id === StudentTest.id;
      });
      $scope.Tests = angular.copy($scope.Tests);
      return $scope.$apply();
    });
  };
  $scope.filter = function() {
    $.cookie("tests", JSON.stringify($scope.search), {
      expires: 365,
      path: '/'
    });
    $scope.current_page = 1;
    return $scope.getByPage($scope.current_page);
  };
  $scope.pageChanged = function() {
    var page;
    console.log($scope.current_page);
    page = $scope.current_page > 1 ? '?page=' + $scope.current_page : '';
    window.history.pushState({}, '', 'tests/students' + page);
    return $scope.getByPage($scope.current_page);
  };
  $scope.getByPage = function(page) {
    frontendLoadingStart();
    return $.post("tests/ajax/GetStudentTests", {
      page: page
    }, function(response) {
      frontendLoadingEnd();
      $scope.StudentTests = response.data;
      $scope.item_count = response.item_count;
      return $scope.$apply();
    }, "json");
  };
  angular.element(document).ready(function() {
    set_scope("Tests");
    $scope.search = $.cookie("tests") ? JSON.parse($.cookie("tests")) : {};
    $scope.pageChanged();
    return $(".single-select").selectpicker();
  });
  $scope.formatDate = function(date) {
    return moment(date).format('DD MMMM');
  };
  $scope.getTestStatus = function(Test) {
    return test_statuses[Test.intermediate];
  };
  $scope.timeLeft = function(StudentTest, Test) {
    var seconds, timestamp_end;
    timestamp_end = moment(StudentTest.date_start).add(Test.minutes, 'minutes').unix();
    seconds = timestamp_end - moment().unix();
    return moment({}).seconds(seconds).format("mm:ss");
  };
  setInterval(function() {
    return $scope.$apply();
  }, 1000);
  $scope.testDisplay = function(StudentTest) {
    return StudentTest.isFinished || StudentTest.inProgress;
  };
  $scope.getStudentAnswer = function(Problem, StudentTest) {
    if (StudentTest.answers && (StudentTest.answers[Problem.id] !== void 0)) {
      if (StudentTest.answers[Problem.id] === Problem.correct_answer) {
        return true;
      } else {
        return false;
      }
    }
    return void 0;
  };
  $scope.getTestHint = function(Problem, StudentTest) {
    var answer;
    answer = $scope.getStudentAnswer(Problem, StudentTest);
    if (answer !== void 0) {
      return 'ответ установлен';
    } else {
      return 'ответ не установлен';
    }
  };
  $scope.getCurrentScore = function(Test, StudentTest) {
    var count;
    count = 0;
    $.each(Test.Problems, function(index, Problem) {
      if ($scope.getStudentAnswer(Problem, StudentTest)) {
        return count += parseInt(Problem.score);
      }
    });
    return Math.round(count * 100 / Test.max_score);
  };
  return $scope.formatTestDate = function(StudentTest) {
    return moment(StudentTest.date_start).format('DD.MM.YY в HH:mm');
  };
}).controller("ListCtrl", function($scope) {
  console.log('inited');
  $scope.timeLeft = function(StudentTest) {
    var timestamp_end;
    timestamp_end = moment(StudentTest.date_start).add(30, 'minutes').unix();
    return moment({}).seconds(timestamp_end - moment().unix()).format("mm:ss");
  };
  return angular.element(document).ready(function() {
    return set_scope('Tests');
  });
}).controller("AddCtrl", function($scope, $timeout) {
  $scope.addTest = function(Test) {
    $scope.adding = true;
    ajaxStart();
    return $.post('tests/ajaxAdd', {
      Test: $scope.Test
    }, function(response) {
      return redirect("tests/edit/" + response);
    }, "json");
  };
  $scope.saveTest = function() {
    $scope.saving = true;
    ajaxStart();
    return $.post("tests/ajaxEdit", {
      Test: $scope.Test
    }, function(response) {
      var i, new_problems;
      ajaxEnd();
      new_problems = _.filter($scope.Test.Problems, function(problem) {
        return !problem.id;
      });
      if (new_problems.length === response.length) {
        for (i in response) {
          new_problems[i]['id'] = response[i];
        }
      }
      $scope.saving = false;
      $scope.form_changed = false;
      return $scope.$apply();
    }, "json");
  };
  $scope.deleteTest = function() {
    return bootbox.confirm("Вы уверены, что хотите удалить тест №" + $scope.Test.id + "?", function(result) {
      if (result === true) {
        ajaxStart();
        return $.post("tests/ajaxDeleteTest", {
          id_test: $scope.Test.id
        }, function() {
          return redirect("tests");
        }, function() {
          return ajaxEnd();
        });
      }
    });
  };
  $scope.addProblem = function() {
    $scope.form_changed = true;
    return $scope.Test.Problems.push(angular.copy($scope.NewProblem));
  };
  $scope.editingAnswer = function(parent_index, index) {
    return $scope.editing_answer && $scope.editing_answer[0] === parent_index && $scope.editing_answer[1] === index;
  };
  $scope.addAnswer = function(Problem, parent_index) {
    $scope.form_changed = true;
    Problem.answers.push('текст ответа...');
    return $timeout(function() {
      return $scope.editAnswer(Problem, parent_index, Problem.answers.length - 1);
    });
  };
  $scope.setCorrect = function(Problem, index) {
    if (typeof $scope.a === "object") {
      Problem.answers[index] = $scope.a.getData();
      $scope.a.destroy();
      delete $scope.a;
    }
    $scope.form_changed = true;
    $scope.editing_answer = void 0;
    if (Problem.correct_answer === index) {
      return Problem.correct_answer = -1;
    } else {
      return Problem.correct_answer = index;
    }
  };
  $scope.deleteAnswer = function(Problem, index) {
    if (Problem.correct_answer) {
      if (Problem.correct_answer === index) {
        Problem.correct_answer = -1;
      }
      if (Problem.correct_answer > index) {
        Problem.correct_answer--;
      }
    }
    $scope.a.destroy();
    delete $scope.a;
    $scope.form_changed = true;
    $scope.editing_answer = void 0;
    return Problem.answers.splice(index, 1);
  };
  $scope.deleteProblem = function(Problem, index) {
    $scope.e.destroy();
    delete $scope.e;
    $scope.editing_problem = void 0;
    $scope.Test.Problems.splice(index, 1);
    if (Problem.id) {
      ajaxStart();
      return $.post("tests/ajaxDeleteProblem", {
        id_problem: Problem.id
      }, function() {
        return ajaxEnd();
      });
    }
  };
  $scope.editAnswer = function(Problem, parent_index, index) {
    var answer;
    console.log(parent_index, index);
    answer = Problem.answers[index];
    if ($scope.a) {
      Problem.answers[$scope.editing_answer[1]] = $scope.a.getData();
    }
    $scope.editing_answer = [parent_index, index];
    $scope.old_html = answer;
    if (typeof $scope.a === "object") {
      $scope.a.destroy();
    }
    $scope.a = CKEDITOR.replace("answer-" + parent_index + "-" + index, {
      language: 'ru',
      height: 150,
      title: "testy",
      extraPlugins: 'pastebase64,panel,button,panelbutton,colorbutton'
    });
    $scope.a.setData(answer);
    $scope.a.on('contentDom', function() {
      return $scope.a.document.on('keydown', function(event) {
        event = event.data.$;
        if (event.which === 13 && (event.ctrlKey || event.metaKey) || (event.which === 19)) {
          Problem.answers[index] = $scope.a.getData();
          $scope.a.destroy();
          delete $scope.a;
          $scope.editing_answer = void 0;
          $scope.form_changed = true;
          $scope.$apply();
        }
        if (event.which === 27) {
          Problem.answers[index] += " ";
          $scope.a.destroy();
          delete $scope.a;
          $scope.editing_answer = void 0;
          return $scope.$apply();
        }
      });
    });
    return $scope.a.on('instanceReady', function(event) {
      $scope.a.focus().select;
      return $scope.a.execCommand('selectAll');
    });
  };
  $scope.editIntro = function() {
    $scope.old_html = $scope.Test.intro;
    if (typeof $scope.t === "object") {
      $scope.t.destroy();
    }
    $scope.t = CKEDITOR.replace("test-intro", {
      language: 'ru',
      height: 250,
      title: "testy",
      extraPlugins: 'pastebase64,panel,button,panelbutton,colorbutton'
    });
    $scope.t.setData($scope.Test.intro);
    $scope.t.on('contentDom', function() {
      return $scope.t.document.on('keydown', function(event) {
        event = event.data.$;
        if (event.which === 13 && (event.ctrlKey || event.metaKey) || (event.which === 19)) {
          console.log('hererere');
          $scope.Test.intro = $scope.t.getData();
          $scope.t.destroy();
          delete $scope.t;
          $scope.form_changed = true;
          $scope.$apply();
        }
        if (event.which === 27) {
          $scope.Test.intro += " ";
          $scope.t.destroy();
          delete $scope.t;
          return $scope.$apply();
        }
      });
    });
    return $scope.t.on('instanceReady', function(event) {
      $scope.t.focus().select;
      return $scope.t.execCommand('selectAll');
    });
  };
  $scope.editProblem = function(Problem, index) {
    $scope.editing_problem = Problem;
    $scope.old_html = Problem.problem;
    if (typeof $scope.e === "object") {
      $scope.e.destroy();
    }
    $scope.e = CKEDITOR.replace("problem-" + index, {
      language: 'ru',
      height: 250,
      title: "testy",
      extraPlugins: 'pastebase64,panel,button,panelbutton,colorbutton'
    });
    $scope.e.setData(Problem.problem);
    $scope.e.on('contentDom', function() {
      return $scope.e.document.on('keydown', function(event) {
        event = event.data.$;
        if (event.which === 13 && (event.ctrlKey || event.metaKey) || (event.which === 19)) {
          Problem.problem = $scope.e.getData();
          $scope.e.destroy();
          delete $scope.e;
          $scope.editing_problem = void 0;
          $scope.form_changed = true;
          $scope.$apply();
        }
        if (event.which === 27) {
          Problem.problem += " ";
          $scope.e.destroy();
          delete $scope.e;
          $scope.editing_problem = void 0;
          return $scope.$apply();
        }
      });
    });
    return $scope.e.on('instanceReady', function(event) {
      $scope.e.focus().select;
      return $scope.e.execCommand('selectAll');
    });
  };
  return angular.element(document).ready(function() {
    $(".form-change-control").on('keyup change', 'input, select', function() {
      $scope.form_changed = true;
      return $scope.$apply();
    });
    $timeout(function() {
      return $scope.$broadcast('angucomplete-alt:clearInput');
    });
    if ($("#subjects-select").length) {
      $("#subjects-select").selectpicker({
        noneSelectedText: "предметы"
      });
    }
    $("#grades-select").selectpicker({
      noneSelectedText: "класс",
      multipleSeparator: ", "
    });
    return set_scope('Tests');
  });
});
