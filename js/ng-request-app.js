	var test;
	var test2;

	angular.module("Request", ["ngAnimate", "ngMap", "ui.bootstrap"])
		.filter('hideZero', function() {
		  return function(item) {
		    if (item > 0) {
		      return item;
		    } else {
		      return null;
		    }
		  };
		})
		.config( [
		    '$compileProvider',
		    function( $compileProvider )
		    {
		        $compileProvider.aHrefSanitizationWhitelist(/^\s*(https?|ftp|mailto|chrome-extension|sip):/);
		        // Angular before v1.2 uses $compileProvider.urlSanitizationWhitelist(...)
		    }
		])
		.filter('range', function() {
		  return function(input, total) {
		    total = parseInt(total);
		    for (var i=0; i<total; i++)
		      input.push(i);
		    return input;
		  };
		})
		.filter('reverse', function() {
			return function(items) {
				if (items) {
					return items.slice().reverse();
				}
			};
		})
		.filter('to_trusted', ['$sce', function($sce){
	        return function(text) {
	            return $sce.trustAsHtml(text);
	        };
	    }])
		/*

			Контроллер списка заявок

		*/
		.controller("ListCtrl", function($scope, $log) {
			// хэндл псевдо-истории
			window.addEventListener("popstate", function(e) {
				// анфокус
				$(".list-link").blur()

				// меняем список
				if (e.state === null) {
					$scope.changeList($scope.request_statuses[0], false)
				} else {
					$scope.changeList(e.state, false)
				}
			})
			
			$scope.toggleUser = function() {
	            new_user_id = $scope.responsible_user.id == $scope.user.id ? 0 : $scope.user.id;
	            $.post("ajax/changeRequestUser", {'id_request' : $scope.id_request, 'id_user_new' : new_user_id}, function(){
	                $scope.responsible_user = _.findWhere($scope.users, {id : new_user_id});
	                $scope.$apply();
	            });
	        }
	        
			$scope.pickUser = function(request, id_user) {
				if (request.id_user > 0) {
					id_user_new = 0
				} else {
					id_user_new = id_user
				}
				$.post("ajax/changeRequestUser", {"id_request" : request.id, "id_user_new" : id_user_new}, function() {
					request.id_user = id_user_new
					$scope.$apply()	
				})
			}
			
			selectNextUser = function(id_user) {
				user = _.find($scope.users, function(user) {
					return user.id > id_user	
				})
				return ((user && user.id <= 112) ? user.id : 0)
			}
			
			// проверить номер телефона
		    $scope.isMobilePhone = function(phone) {
			    // пустой номер телефона – это тоже правильный номер телефона
			    if (!phone) {
				    return false
			    }
				
				return !phone.indexOf("+7 (9")
		    }

			$scope.smsDialog = smsDialog;
			
			$scope.sipNumber = function(number) {
				return "sip:" + number.replace(/[^0-9]/g, '')
			}

			$scope.callSip = function(number) {
				number = $scope.sipNumber(number)
				location.href = number;
			}

			$scope.getTimeClass = function(request) {
				// подсвечивать время нужно только в невыполненных
				if (request.id_status != 0) {
					return
				}
				
				timestamp = request.date_timestamp
				
				hour = 60 * 60 * 1000;

				// если больше 2 часов
				if (Date.now() - timestamp >= (hour * 2)) {
					return 'label-red'
				}

				if (Date.now() - timestamp >= hour) {
					return 'label-yellow'
				}
			}
			
			$(document).ready(function() {
				// draggable only from main requests list (not relevant)
				if ($scope.request_statuses_count) {
					bindDraggable()
				} else {
					// relevant page
					$("#group-branch-filter").selectpicker('render')
				}
			})
			
			bindDraggable = function() {
				$scope.dragging = false
				
				$(".request-main-list").draggable({
					connectToSortable: ".request-main-list",
					start: function() {
						$scope.dragging = true
						$scope.$apply()
					},
					stop: function() {
						$scope.dragging = false
						$scope.$apply()
					},
					revert: 'invalid',
				})
				
				$(".request-status-li").droppable({
					tolerance: 'pointer',
					hoverClass: "request-status-drop-hover",
					drop: function(event, ui) {
						id_request_status = $(this).data("id")
						id_request = $(ui.draggable).data("id")
						
						$scope.request_statuses_count[$scope.chosen_list]--
						$scope.request_statuses_count[id_request_status]++
						$scope.$apply()
						
						$.post("requests/ajax/changeStatus", {id_request_status: id_request_status, id_request: id_request})
						
						ui.draggable.remove()
					}
				})
				
				$(".delete-request-li").droppable({
					tolerance: 'pointer',
					hoverClass: "request-status-drop-hover-delete",
					drop: function(event, ui) {
						id_request = $(ui.draggable).data("id")
						$scope.dragging = false
						$scope.request_statuses_count[$scope.chosen_list]--
						$scope.$apply()
						$.post("ajax/deleteRequest", {"id_request": id_request})
						
						ui.draggable.remove()
					}
				})
			}
			
			// Выбрать список
			$scope.changeList = function(request_status, push_history) {
				//  Если нажимаем на один и тот же список -- ничего не делаем
				/*
				if (request_status.id == $scope.chosen_list) {
					return
				}*/

				// Устанавливаем список
				$scope.chosen_list = request_status.id

				if (push_history) {
					window.history.pushState(request_status, '', 'requests/' + request_status.constant.toLowerCase());
				}

				// Получаем первую страницу задач списка
				$scope.getByPage(1)
			}

			// Страница изменилась
			$scope.pageChanged = function() {
				// request_status = $scope.request_statuses[$scope.chosen_list]
				request_status = _.where($scope.request_statuses, {id: $scope.chosen_list})[0];
				console.log($scope.chosen_list, request_status)
				window.history.pushState(request_status, '', 'requests/' + request_status.constant.toLowerCase() + '/' + $scope.currentPage)
				// Получаем задачи, соответствующие странице и списку
				$scope.getByPage($scope.currentPage)
			}

			// Получаем задачи, соответствующие странице и списку
			$scope.getByPage = function(page) {
				ajaxStart()
				frontendLoadingStart()
				$.get("requests/ajax/GetByPage", {
					'page'		: page,
					'id_status'	: $scope.chosen_list
				}, function(response) {
					ajaxEnd()
					frontendLoadingEnd()
					$scope.requests = response
					$scope.$apply()
					bindUserColorControl()
					bindDraggable()
					initComments()
				}, "json")
			}
			
			
			// Страница изменилась
			$scope.pageChangedRelevant = function() {
				// Получаем задачи, соответствующие странице и списку
				$scope.getByPageRelevant($scope.currentPage)
			}

			// Получаем задачи, соответствующие странице и списку
			$scope.getByPageRelevant = function(page) {
				ajaxStart()
				frontendLoadingStart()
				$.get("requests/ajax/GetByPageRelevant", {
					'page'		: page,
					'grade'		: $scope.search.grade,
					'id_branch' : $scope.search.id_branch,
					'id_subject': $scope.search.id_subject,
				}, function(response) {
					ajaxEnd()
					frontendLoadingEnd()
					console.log(response)
					$scope.requests = response.requests
					$scope.requests_count = response.requests_count
					$scope.$apply()
					bindUserColorControl()
					initComments()
				}, "json")
			}

		})
		.controller("EditCtrl", function ($scope, $log, $timeout) {

				$scope.week_count = function (programm) {
					c = _.max(programm, function(v){ return v.count; }).count;
					c += _.max(programm, function(v){ return v.count2; }).count2;
					return c;
				}

				$scope.timeLeft = function(StudentTest, Test) {
					if (StudentTest && StudentTest.inProgress) {
						timestamp_end = moment(StudentTest.date_start).add(Test.minutes, 'minutes').unix()
						seconds = timestamp_end - moment().unix()
						if (seconds > 0) {
							return moment({}).seconds(seconds).format("mm:ss")
						} else {
							// Test = _.find($scope.StudentTests, {id: StudentTest.id})
							// Test.isFinished = true
							// $scope.Tests = angular.copy($scope.Tests)
						}
					}
				}
				
				$scope.formatTestDate = function(StudentTest) {
					if (StudentTest) {
						return moment(StudentTest.date_start).format('DD.MM.YY в HH:mm')	
					}
				}
				
				$scope.getTestHint = function(Problem, StudentTest) {
					answer = $scope.getStudentAnswer(Problem, StudentTest)
					switch(answer) {
						case 'circle-red': {
							return 'ответ неверный'
						}
						case 'circle-gray': {
							return 'ответ не указан'
						}
						default: {
							return 'ответ верный, ' + Problem.score + ' баллов'
						}
					}
				}
				
				$scope.deleteTest = function(StudentTest) {
					$.post("tests/ajaxDeleteStudentTest", {id: StudentTest.id}, function() {
						$scope.StudentTests = _.reject($scope.StudentTests, function(e) {
							return e.id == StudentTest.id
						})
						$scope.Tests = angular.copy($scope.Tests)
						$scope.$apply()
					})

				}
				
				$scope.testDisplay = function(StudentTest) {
					return (StudentTest && (StudentTest.isFinished || StudentTest.inProgress))
				}
				
				$scope.getStudentAnswer = function(Problem, StudentTest) {
					if (StudentTest && StudentTest.answers && StudentTest.answers[Problem.id] !== undefined) {
						if (StudentTest.answers[Problem.id] == Problem.correct_answer) {
							return ""
						} else {
							return "circle-red"
						}
					}
					return "circle-gray";
				}
				
				$scope.getCurrentScore = function(Test, StudentTest) {
					count = 0
					$.each(Test.Problems, function(index, Problem) {
						if (! $scope.getStudentAnswer(Problem, StudentTest)) {
							count += parseInt(Problem.score)
						}
					})
					return Math.round(count * 100 / Test.max_score)
				}
				
				$scope.toggleUser = function() {
						new_user_id = $scope.responsible_user.id == $scope.user.id ? 0 : $scope.user.id;
						$.post("ajax/changeRequestUser", {'id_request' : $scope.id_request, 'id_user_new' : new_user_id}, function(){
								$scope.responsible_user = _.findWhere($scope.users, {id : new_user_id});
								$scope.$apply();
						});
				}
				$scope.toggleReviewUser = function() {
						new_user_id = $scope.id_user_review == $scope.user.id ? 0 : $scope.user.id;
						$.post("ajax/UpdateStudentReviewUser", {'id_student' : $scope.id_student, 'id_user_new' : new_user_id}, function(){
								$scope.id_user_review = new_user_id
								$scope.$apply();
						});
				}
				$scope.findUser = function(id) {
					return _.findWhere($scope.users, {id : id});
				}


			// значение "Платежи" по умолчанию (иначе подставляет пустое значение)
			$scope.new_payment = {id_status : 0}
			$scope.current_contract = {subjects : []}

			// сохранение
			$scope.saving = false

			// Маркеры
			$scope.markers 	= [];
			// ID маркера
			$scope.marker_id= 1;
			
			$scope.sipNumber = function(number) {
				return "sip:" + number.replace(/[^0-9]/g, '')
			}

			$scope.callSip = function(element) {
				number = $("#" + element).val()
				number = $scope.sipNumber(number)
				location.href = number;
			}
			
			// OUTDATED: ID свежеиспеченного договора (у новых отрицательный ID,  потом на серваке
			// отрицательные IDшники создаются, а положительные обновляются (положительные -- уже существующие)
			// $scope.new_contract_id = -1;

			// анимация загрузки RENDER ANGULAR
			angular.element(document).ready(function() {
				$scope.setMode($scope.mode)
				setTimeout(function() {
					$(".phone-masked").keyup()
				}, 100)
			})
			
			$scope.getGroup = function(id) {
				return _.findWhere($scope.Groups, {id: parseInt(id)})
			}
			
			$scope.getJournalGroup = function(id) {
				return _.findWhere($scope.JournalGroups, {id: parseInt(id)})
			}
			
			// Пытается найти сначала существующую группу, если нет, то пытаетя найти группу из журнала
			$scope.getAnyGroup = function(id) {
				Group = $scope.getGroup(id)
				if (!Group) {
					Group = $scope.getJournalGroup(id)
				}
				return Group
			}
			
			// получить группы из журнала
			$scope.getJournalGroups = function() {
				return Object.keys(_.chain($scope.Journal).groupBy('id_group').value())
			}
			
			$scope.getStudentGroups = function() {
				group_ids = $scope.getJournalGroups()
				
				$.each($scope.Groups, function(index, Group) {
					if ($.inArray(Group.id.toString(), group_ids) < 0) {
						group_ids.push(Group.id)
					}
				})
				
				return group_ids
			}
			
			$scope.getVisitsByGroup = function(id_group) {
				id_group = parseInt(id_group)
				return _.where($scope.Journal, {id_group: id_group})
			}
			
			$scope.getVisit = function(id_group, date) {
				id_group = parseInt(id_group)
				return _.findWhere($scope.Journal, {id_group: id_group, lesson_date: date})
			}
			
			$scope.getVisitBoolean = function(id_group, date) {
				id_group = parseInt(id_group)
				return _.findWhere($scope.Journal, {id_group: id_group, lesson_date: date}) !== undefined
			}
			
			
			$scope.inActiveGroup = function(id_group) {
				id_group = parseInt(id_group)
				return _.where($scope.Groups, {id: id_group}).length
			}
			
			$scope.getMaxVisits = function() {
				max = -1;
				$.each($scope.Groups, function(i, group) {
					count = $scope.getVisitsByGroup(group.id).length 
					if ($scope.getGroup(group.id).Schedule) {
						count += $scope.getGroup(group.id).Schedule.length	
					}
					if (count > max) {
						max = count
					}
				});
				return max;
			}


			$scope.formatVisitDate = function (date) {
				return moment(date).format("DD.MM.YY")
			}
			
			$scope.toggleSubject = function(id_subject) {
				// если предметы не установлены
				$scope.current_contract.subjects = initIfNotSet($scope.current_contract.subjects)
				
				if ($("#checkbox-subject-" + id_subject).is(":checked") == false) {
					delete $scope.current_contract.subjects[id_subject]
				} else {
					$scope.current_contract.subjects[id_subject] = {'id_subject': id_subject, 'name' : $scope.Subjects[id_subject], 'count' : '' }
				}
				// console.log($scope.current_contract.subjects[id_subject]);
				setTimeout(function(){
					$scope.$apply()
				}, 100);
			}
			
			// проверка на корректность емайл
			$scope.emailFull = function(email) {
				re = /^([\w-]+(?:\.[\w-]+)*)@((?:[\w-]+\.)*\w[\w-]{0,66})\.([a-z]{2,6}(?:\.[a-z]{2})?)$/i
				return re.test(email)
			}
			
			$scope.emailDialog = function(email) {
				$("#email-history").html('<center class="text-gray">загрузка истории сообщений...</center>')
				
				html = ""
				
				$.post("ajax/emailHistory", {"email": email}, function(response) {
					console.log(response);
					if (response != false) {
						$.each(response, function(i, v) {
							files_html = ""
							$.each(v.files, function(i, file) {
								files_html += '<div class="sms-coordinates">\
									<a target="_blank" href="files/email/' + file.name + '" class="link-reverse small">' + file.uploaded_name + '</a>\
									<span> (' + file.size + ')</span>\
									</div>'
							})
							html += '<div class="clear-sms">		\
										<div class="from-them">		\
											' + v.message + ' 		\
											<div class="sms-coordinates">' + v.coordinates + '</div>' + files_html + '\
									    </div>						\
									</div>';	
							})
						$("#email-history").html(html)
					} else {
						$("#email-history").html("")
					}
				}, "json")
				
				$("#email-address").text(email)
				lightBoxShow('email')
			}

			$scope.formatContractDate = function(date) {
				if (date == null) {
					return
				}
				date = date.split('.')
				
				// был баг. месяц делал автоматически +1
				month = date[1] - 1;
				console.log(date[2] + "-" + month + "-" + date[0])
				date = new Date(date[2], month, date[0])
				return moment(date).format("D MMMM YYYY г.")
			}
			
			$scope.objectLength = function(object) {
				if (object !== undefined && object !== null) {
					return Object.keys(object).length
				}
			}
			
			$scope.setMode = function(mode) {
				$scope.mode = mode
				if (mode == 'student') {
					$scope.setMenu($scope.current_menu)
				}
				if ($scope.request_comments === undefined && mode == 'request') {
				    $.post("requests/ajax/LoadRequest", {id_request: $scope.id_request}, function(response) {
						['request_comments', 'responsible_user', 'user', 'users', 'request_duplicates', 'request_phone_level'].forEach(function(field) {
							$scope[field] = response[field]
						})
						$scope.$apply()
						$timeout(function() {
							rebindMasks()
						}, 100)
					}, "json")

			    }
			}

			/**
			 * Склеить заявки
			 *
			 */
			$scope.glue = function(delete_student) {
				ajaxStart()
				$.post("requests/ajax/GlueRequest", {
					id_request: $scope.id_request, 
					id_student: $scope.id_student_glue,
					delete_student: delete_student
				}, function(response) {
					// если склеилось, то обновляем страницу
					if (response === true) {
						location.href = "requests/edit/" + $scope.id_request;
					} else {
						notifyError("Не удалось склеить")
					}
				}, "json")
			}

			/**
			 * Получить ученика для склейки
			 *
			 */
			$scope.findStudent = function() {
				console.log($("#id-student-glue").val())
				// если есть ID в поле, то ищем ученика по ID
				if ($scope.id_student_glue) {
					$.get("requests/ajax/getStudent", {id: $scope.id_student_glue}, function(response) {
						$scope.GlueStudent = response
						$scope.$apply()
					}, "json")
				} else {
				//  иначе обнуляем ученика
					$scope.GlueStudent = null
				}
			}
			
			$scope.getFirstVersionDate = function(contract)
			{
				if (!contract.History) {
					return contract.date
				} else {
					return contract.History[0].date
				}
			}

			$scope.id_user_print = ''
			/**
			 * Выбрать ID контракта для последующей печати договора
			 *
			 */
			$scope.printContract = function(id_contract) {
				$scope.print_mode = 'contract'
				$scope.id_contract_print = id_contract
				$scope.id_user_print = 0
				html = $("#contract-print-" + $scope.id_contract_print).html()
				$scope.editBeforePrint(html)
			}
			
            $scope.printContractLicenced = function(id_contract) {
				$scope.print_mode = 'contract-licenced'
				$scope.id_contract_print = id_contract
				$scope.id_user_print = 0
				html = $("#contract-licenced-print-" + $scope.id_contract_print).html()
				$scope.editBeforePrint(html)
			}
			
			$scope.printContractAdditional = function(contract) {
				$scope.print_mode = 'agreement'
				$scope.contract_additional = contract
				$scope.id_contract_print = contract.id
				html = $("#agreement-print-" + $scope.id_contract_print).html()
				$scope.editBeforePrint(html)
			}
			
			$scope.printAct = function(contract) {
				$scope.print_mode = 'act'
				$scope.contract_act = contract
				$scope.id_contract_print = contract.id
				html = $("#act-print-" + $scope.id_contract_print).html()
				$scope.editBeforePrint(html)
			}
			
			$scope.getLastLessonDate = function() {
        date = '0000-00-00'
        // если есть активные группы
        if ($scope.Groups && $scope.Groups.length) {
          $.each($scope.Groups, function(index, Group) {
            new_date = _.last(Group.Schedule).date
            if (new_date > date) {
              date = new_date
            }
          })
        } else {
          // иначе берем группы которые были посещены
          $.each($scope.getStudentGroups(), function(index, id_group) {
            var last_lesson = _.last($scope.getVisitsByGroup(id_group));
            new_date = last_lesson.lesson_date
            if (new_date > date) {
              date = new_date;
            }
          })
        }
        return $scope.textDate(date)
			}
			
			$scope.todayDate = function() {
				return moment().format("DD.MM.YYYY");
			}
			
			$scope.textDate = function(date) {
				return moment(date).format("DD MMMM YYYY")
			}
			
			$scope.editBeforePrint = function(html) {
				$("#contract-manual-edit").val(html)
				
				if (CKEDITOR.instances['contract-manual-edit'] != undefined) {
					CKEDITOR.instances['contract-manual-edit'].destroy(true)	
				}
				
				if (CKEDITOR.instances['contract-manual-edit'] == undefined) {
					editor = CKEDITOR.replace('contract-manual-edit', {
						fullPage: true,
						allowedContent: true,
						language: 'ru',
						height: 600
					})
				}
				
				lightBoxHide()
				lightBoxShow('manualedit')
			}
			
			
			$scope.printBill = function(payment) {
				$scope.print_mode = 'bill'
				$scope.PrintPayment = payment 
				$scope.$apply()
				printDiv($scope.print_mode + "-print")
			}

			$scope.printPKO = function(payment) {
				$scope.print_mode = 'pko'
				$scope.PrintPayment = payment
				$scope.Representative = $scope.representative
				$scope.$apply()
				printDiv($scope.print_mode + "-print")
			}
			
			/**
			 * Запустить печать договора
			 *
			 */
			$scope.runPrint = function() {
				printDiv($scope.print_mode + "-print-" + $scope.id_contract_print)
				lightBoxHide()
			}
			
			$scope.runPrintManual = function() {
				html = CKEDITOR.instances['contract-manual-edit'].getData()
				$("#contract-manual-div").html(html)
				printDiv('contract-manual-div')
				lightBoxHide()
			}
			

			// Возвращаем структурированные данные по маркерам
			// для передачи на сохранение
			$scope.markerData = function() {
				if ($scope.markers.length) {
					marker_data = [] // инициалицазия
					// генерируем данные
					$.each($scope.markers, function(index, marker) {
						marker_data.push({
							"lat" 	: marker.position.lat(),
							"lng" 	: marker.position.lng(),
							"type"	: marker.type
						});
					})

					return marker_data
				} else {
					return ""
				}
			}
			
			$scope.saveMarkersToServer = function() {
				$.post("requests/ajax/saveMarkers", {markers: $scope.markerData(), id_student: $scope.student.id})
				lightBoxHide()
			}
			
			// Показать карту
			$scope.showMap = function() {
				// устанавливаем тип метки
				// $scope.marker_type = type

				
				// Показываем карту
				lightBoxShow('map')
				google.maps.event.trigger($scope.gmap, 'resize')
				
				// Зум и центр карты по умолчанию
				$scope.gmap.setCenter(new google.maps.LatLng(55.7387, 37.6032))
				$scope.gmap.setZoom(10)
				
				// Обнуляем значение поиска
				$("#map-search").val("")
				
				// Удаляем все маркеры поиска
				if ($scope.search_markers && $scope.search_markers.length) {
					$.each($scope.search_markers, function(i, marker) {
						$log.log(marker)
						marker.setMap(null)
					})
					$scope.search_markers = []
				}
				
				// Если уже есть добавленные маркеры
				if ($scope.markers.length) {
					// отображать только метки с выбранным типом
					bounds = new google.maps.LatLngBounds()
					// есть отображаемые маркеры
					markers_count = 0
					// отображаем маркеры по одному
					$.each($scope.markers, function(index, marker) {
					//	if (marker.type != type) {
					//		marker.setVisible(false)
					//	} else {
							markers_count++ // отображаемые маркеры есть
							marker.setVisible(true)
							bounds.extend(marker.position) // границы карты в зависимости от поставленных меток
					//	}
					})

					// если отображаемые маркеры есть, делаем зум на них
					if (markers_count > 0) {
						$scope.gmap.fitBounds(bounds)
						$scope.gmap.panToBounds(bounds)
						$scope.gmap.setZoom(12)
					}
				}
			}

			// Добавляем ивент удаления маркера
			$scope.bindMarkerDelete = function(marker) {
				google.maps.event.addListener(marker, "dblclick", function(event) {
					t = this
					
					// удаляем маркер с карты
					t.setMap(null)
					
					
					// удаляем маркер из коллекции
					$.each($scope.markers, function(index, m) {
						if (t.id == m.id) {
							$scope.markers.splice(index, 1)
						}
					})
				})
			}
			
			$scope.bindMarkerChangeType = function(marker) {
				google.maps.event.addListener(marker, "click", function(event) {
					if (this.type == 'home') {
						this.type = 'school'
						this.setIcon(ICON_SCHOOL)
					} else {
						this.type = 'home'
						this.setIcon(ICON_HOME)
					}
				})
			}

			// Загрузить маркеры, уже сохраненные на серваке и загруженные оттуда
			$scope.loadServerMarkers = function() {
				$.each($scope.server_markers, function(index, marker) {
					// Создаем маркер
					marker = newMarker($scope.marker_id++, new google.maps.LatLng(marker.lat, marker.lng), $scope.map, marker.type)

					// Добавляем маркер в маркеры
					$scope.markers.push(marker)

					// Добавляем маркер на карту
					marker.setMap($scope.map)

					// Добавляем ивент удаления маркера
					$scope.bindMarkerDelete(marker)
					$scope.bindMarkerChangeType(marker)
				})

				// применяем изменения (ОБЯЗАТЕЛЬНО, иначе слетят метки без изменений)
				$scope.$apply()
			}
			
			$scope.gmapAddMarker = function(event) {
				// Создаем маркер
				// var marker = newMarker($scope.marker_id++, $scope.marker_type, event.latLng)
				marker = newMarker($scope.marker_id++, event.latLng, $scope.map)
				
				// Добавляем маркер в маркеры
				$scope.markers.push(marker)
				
				// Добавляем маркер на карту
				marker.setMap($scope.gmap)

				// Добавляем ивент удаления маркера
				$scope.bindMarkerDelete(marker)
				//
				$scope.bindMarkerChangeType(marker)
			}
			
			// ПОСЛЕ ЗАГРУЗКИ КАРТЫ
			$scope.$on('mapInitialized', function(event, map) {
				// Запоминаем карту после инициалицации
				$scope.gmap = map
				
				test = map
				
				// generate recommended search bounds
				INIT_COORDS = {lat: 55.7387, lng: 37.6032};
				$scope.RECOM_BOUNDS = new google.maps.LatLngBounds(
		            new google.maps.LatLng(INIT_COORDS.lat-0.5, INIT_COORDS.lng-0.5), 
		            new google.maps.LatLng(INIT_COORDS.lat+0.5, INIT_COORDS.lng+0.5)
		        );        
				$scope.geocoder = new google.maps.Geocoder();
		    });
		    
		    // Поиск по карте
		    $scope.searchMap = function(address) {
				$scope.geocoder.geocode({
					address: address + ", московская область",
//					componentRestrictions: {
//						locality: "Moscow",
//				    },
					bounds: $scope.RECOM_BOUNDS,
				}, function(results, status) {
				    if (status == google.maps.GeocoderStatus.OK) {
					    
					    // максимальное кол-во результатов
					    max_results = 3
					    
					    // масштаб поиска
						search_result_bounds = new google.maps.LatLngBounds()
					    
					    $.each(results, function(i, result) {
						    if (i >= max_results) {
							    return
						    }
						    
							search_result_bounds.extend(result.geometry.location) // границы карты в зависимости от поставленных меток
					        
							search_marker = new google.maps.Marker({
							    map: $scope.map,
							    position: result.geometry.location,
							    icon: ICON_SEARCH,
							});
							
							google.maps.event.addListener(search_marker, 'click', function(event) {
								this.setMap(null)	
								$scope.gmapAddMarker(event)
							})
							
							$scope.search_markers = initIfNotSet($scope.search_markers)
//							$log.log($scope.search_markers)
							$scope.search_markers.push(search_marker)  
					    })
					    
					    // если отображаемые маркеры есть, делаем зум на них
						if (results.length > 0) {
							$scope.gmap.fitBounds(search_result_bounds)
							$scope.gmap.panToBounds(search_result_bounds)
							if (results.length == 1) {
								$scope.gmap.setZoom(12)	
							}
						
						}
				    } else {
						$("#map-search").addClass("has-error").focus()
				    }
				});
			}
			
			// Запуск поиска по карте
			$scope.gmapsSearch = function($event) {
				if ($event.keyCode == 13 || $event.type == "click") {
					// prevent empty
					if ($("#map-search").val() == "") {
						$("#map-search").addClass("has-error").focus()
						return
					} else {
						$("#map-search").removeClass("has-error")
					}
					
					$scope.searchMap($("#map-search").val())
				}
			}

			$scope.addAndRedirect = function() {
				$scope.redirect_after_save = $scope.id_request
				$scope.save()
			}

			// Получить общее количество предметов (для печати договора)
			$scope.subjectCount = function(contract) {
				count = 0
				$.each(contract.subjects, function(i, subject) {
					if (subject != undefined) {
						cnt1 = parseInt(subject.count)
						if (!isNaN(cnt1)) {
							count += cnt1
						}
						
						cnt2 = parseInt(subject.count2)
						if (!isNaN(cnt2)) {
							count += cnt2
						}
					}
				})
				return count
			}

			// Передаем функция numToText() в SCOPE
			$scope.numToText = numToText;
			
			// Первая часть суммы для печати в договоре
			$scope.contractFirstPart = function(contract) {
				count = 0
				$.each(contract.subjects, function(i, subject) {
					if (subject != undefined) {
						cnt1 = parseInt(subject.count)
						if (!isNaN(cnt1)) {
							count += cnt1
						}
					}
				})
				
				// сколько процентов составляет первая часть предметов
				percentage = count / $scope.subjectCount(contract)
				
				return Math.round(contract.sum * percentage)
			}
			
			// Первая часть суммы для печати в договоре
			$scope.contractSecondPart = function(contract) {
				count = 0
				$.each(contract.subjects, function(i, subject) {
					if (subject != undefined) {
						cnt2 = parseInt(subject.count2)
						if (!isNaN(cnt2)) {
							count += cnt2
						}
					}
				})
				
				// сколько процентов составляет первая часть предметов
				percentage = count / $scope.subjectCount(contract)
				
				return Math.round(contract.sum * percentage)
			}

			// Рекомендуемая цена договора
			$scope.recommendedPrice = function(contract) {
				count = $scope.subjectCount(contract)
				if (contract.grade == 11) {
					return Math.round(count * 1500)
				} else {
					return Math.round(count * 1350)
				}
			}
			
			// Склонять имя в дательном падеже
			// https://github.com/petrovich/petrovich-js
			$scope.contractPrintName = function(person, padej) {
				if (person === undefined) {
					return false
				}
				var person = {
					first	: person.first_name,
					last	: person.last_name,
					middle	: person.middle_name,
				};

				// склоняем в дательный падеж
				person = petrovich(person, padej);

				// возвращаем ФИО
				return person.last + " " + person.first + " " + person.middle;
			}

			// Добавить предмет к договору
			$scope.addSubject = function(contract) {
				// HTML-элементы предмета и кол-ва предметов
				subject 		= $("#subjects-select" + contract.id)
				subject_count	= $("#add-subject-count" + contract.id)

				// Если предмет или кол-во не установлено
				if (!subject.val()) {
					subject.focus().addClass("has-error")
					return false
				} else {
					subject.removeClass("has-error")
				}

				if (!subject_count.val()) {
					subject_count.focus().addClass("has-error")
					return false
				} else {
					subject_count.removeClass("has-error")
				}

				// Инициализируем, если не установлено
				contract.subjects = initIfNotSet(contract.subjects)

				// Добавляем предмет
				contract.subjects.push({
					"id_subject": subject.val(),
					"name"		: $("#subjects-select" + contract.id  + " option:selected").text(),
					"count"		: subject_count.val()
				})


				// Обнуляем значения
				$('#subjects-select' + contract.id  + ' option:first-child').attr("selected", "selected")
				subject_count.val("")
			}

			// Удалить предмет из договора
			$scope.removeSubject = function(contract, index) {
				contract.subjects.splice(index, 1);
			}


			/**
			 * Проверка на пустой договор. Если пустой, появляется функционал удаления
			 *
			 */
			$scope.emptyContract = function(contract) {
				if (contract.subjects.length || contract.sum || contract.date || (contract.files && contract.files.length)) {
					return false
				} else {
					return true
				}
			}

			// Добавление по нажатию ENTER
			$scope.watchEnter = function($event) {
				// получаем ID элемента
				item_id = $($event.currentTarget).attr("item")

				if ($event.keyCode == 13) {
					$event.currentTarget.blur();

					// выполняем функцию в зависимости от ID
					switch ($event.currentTarget.id) {
						case "add-subject-count" + item_id: {
							// добавление предмета
							$.each($scope.contracts, function(index, contract) {
								if (contract.id == item_id) {
									$scope.addSubject(contract)
								}
							})

							break
						}
						case "payment-sum": {
							// добавление платежа
							$scope.addPayment()

							break
						}
					}
				}
			}
			
			// для позднего обновления
			$scope.lateApply = function() {
				setTimeout(function() {
					$scope.$apply()
				}, 100)
			}
			
			// для позднего обновления
			$scope.lateApplyShort = function() {
				setTimeout(function() {
					$scope.$apply()
				}, 30)
			}


			$scope.addContractNew = function() {
				// валидация параметров договора
				if (!$scope.current_contract.sum) {
					$("#contract-sum").addClass("has-error").focus()
					return false
				} else {
					$("#contract-sum").removeClass("has-error")
				}

				if (!$scope.current_contract.date) {
					$("#contract-date").addClass("has-error").focus()
					return false
				} else {
					$("#contract-date").removeClass("has-error")
				}

				if (!$scope.current_contract.grade) {
					$("select[name='grades']").addClass("has-error").focus()
					return false
				} else {
					$("select[name='grades']").removeClass("has-error")
				}

				// количество активных, но незаполненных полей "кол-во занятий"
				count = $(".contract-lessons").filter(function() {
					if (!$(this).val() && $(this).is(":visible")) {
						$(this).addClass("has-error").focus()
					} else {
						$(this).removeClass("has-error")
					}
					return ($(this).val() == "" && $(this).is(":visible"))
				}).length

				// если есть незаполненные поля, то валидация не пройдена
				if (count > 0) {
					return
				}

				if (!$scope.current_contract.history_edit) {
					$scope.current_contract.id_student = $scope.student.id
				}

				if ($scope.current_contract.id) {
					ajaxStart('contract')
					$.post("ajax/contractEdit", $scope.current_contract, function(response) {
						console.log(response)
						test = response
						
						$scope.current_contract.user_login 	= response.user_login
						$scope.current_contract.date_changed= response.date_changed
							
							if ($scope.current_contract.history_edit) {
								console.log($scope.current_contract)
								parent_contract		= _.findWhere($scope.contracts, {id: $scope.current_contract.id_contract})
								
								$.each(parent_contract.History, function(index, contract) {
									if (contract.id == $scope.current_contract.id) {
										parent_contract.History[index] = $scope.current_contract
										// баг - вкладка сбивается
										setTimeout(function() {
											$('#contract_history_li_' + parent_contract.id + '_' + $scope.current_contract.id + ' a').click()
										}, 50)
										return
									}
								})
							} else {
								angular.forEach($scope.contracts, function(contract, i) {
									if (contract.id == $scope.current_contract.id) {
// 										old_contract = $scope.contracts[i]
										old_contract = response.History[response.History.length - 1]
										$scope.contracts[i] = $scope.current_contract
										// если создалась новая версия, пушим в историю
										if (!$scope.current_contract.no_version_control) {
											$scope.contracts[i].History = initIfNotSet($scope.contracts[i].History)
											$scope.contracts[i].History.push(old_contract)
										}
									}
								})								
							}
							
						$scope.$apply()
						ajaxEnd('contract')
						lightBoxHide()
					}, "json")
				} else {
					// сохраняем догавар
					ajaxStart('contract')
					$.post("ajax/contractSave", $scope.current_contract, function(response) {
						ajaxEnd('contract')
						lightBoxHide()

						$scope.current_contract.id 			= response.id
						$scope.current_contract.user_login 	= response.user_login
						$scope.current_contract.date_changed= response.date_changed
						
						//new_contract = angular.copy($scope.current_contract)
						//new_contract.subjects = angular.copy($scope.current_contract.subjects)
						new_contract = $.extend(true, {}, $scope.current_contract)
						
						
						new_contract.subjects = new_contract.subjects.filter(function(e){return e})
						
						new_contract.subjects.sort(function(a, b) {
							return a.id_subject - b.id_subject
						})
						
						$scope.contracts = initIfNotSet($scope.contracts)
						$scope.contracts.push(new_contract)
						$scope.$apply()

					}, "json");
				}
			}
			
			$scope.subjectChecked = function(id_subject) {
// 				return _.findWhere($scope.current_contract.subjects, {id_subject: id_subject}) !== undefined
				checked = false
				angular.forEach($scope.current_contract.subjects, function(subject) {
					if (subject.id_subject == id_subject) {
						checked = true
						return
					}
				})

				return checked
			}

			$scope.getIndexByIdSubject = function(id_subject) {
				res = false
				angular.forEach($scope.current_contract.subjects, function(subject, i) {
//					console.log(subject, i)
					if (subject.id_subject == id_subject) {
						res = i
						return
					}
				})

				return res
			}
			
			$scope.subjectHandle = function(id_subject) {
				subjects 	= $scope.current_contract.subjects
				subject 	= subjects[id_subject]
				
// 				subject.status = $("#checkbox-subject-" + id_subject).val()					
				console.log('changed', subject.status, $("#checkbox-subject-" + id_subject).val()) 
				
				if (subject.status != 0) {
					if (!subject.id_subject) {
						subject.id_subject = id_subject
						subject.name 	= $scope.SubjectsFull[id_subject]
						subject.count 	= ''
						subject.count2 	= ''
						subject.score 	= ''
					}
				} else {
					delete subjects[id_subject]
				}
				$scope.lateApplyShort()
			}
			
			// вызывает окно редактирования контракта
			$scope.callContractEdit = function(contract)
			{
				$scope.current_contract = angular.copy(contract)

				if ($scope.current_contract.grade === null) {
					$scope.current_contract.grade = ""
				}
				
				lightBoxShow('addcontract')
				$("select[name='grades']").removeClass("has-error")
				$scope.lateApply()
		
				setTimeout(function() {
// 					$('.triple-switch').slider('reset')
					$('.triple-switch').each(function(index, e) {
						val = $(e).attr('data-slider-value');
// 						console.log(val);
						$(e).slider('setValue', parseInt(val));
					})
				}, 100)
			}

			// Окно редактирования договора
			$scope.editContract = function(contract) {
				contract.no_version_control = 0
				contract.history_edit = 0
				contract.date = moment().format("DD.MM.YYYY")
				
				$scope.callContractEdit(contract)
			}


			// без проводки
			$scope.editContractWithoutVersionControl = function(contract) {
				contract.no_version_control = 1
				contract.history_edit = 0
				
				$scope.callContractEdit(contract)
			}
			
			$scope.editHistoryContract = function(contract) {
				contract.no_version_control = 1
				contract.history_edit = 1
				
				$scope.callContractEdit(contract)
			}

			// Показать окно добавления платежа
			$scope.addContractDialog = function() {
				$scope.current_contract = {subjects : []}
				$scope.current_contract.date = moment().format("DD.MM.YYYY")
				
				$('.triple-switch').slider('setValue', 0)	
				
				lightBoxShow('addcontract')
				$("select[name='grades']").removeClass("has-error")
				$scope.lateApply()
			}

			// Добавить контракт
			$scope.addContract = function() {
				// Новый контракт
				new_contract = {"id" : $scope.new_contract_id--}

				// Добавляем в коллекцию контрактов
				$scope.contracts.push(new_contract)

				// Вешаем маску даты на новый элемент
				rebindMasks()

				// Вешаем файлаплоад на новый элемент
				setTimeout(function() {
					$scope.bindFileUpload(new_contract)
				}, 100)
			}

			// Удалить контракт
			$scope.deleteContract = function(contract) {
				bootbox.confirm("Вы уверены, что хотите удалить договор?", function(result) {
					if (result === true) {
						$.post("ajax/contractDelete", {"id_contract": contract.id})

						$.each($scope.contracts, function(i, v) {
							if (v !== undefined) {
								if (v.id == contract.id) {
									$scope.contracts.splice(i, 1)
									$scope.$apply()
									return
								}
							}
						})
					}
				})
			}

			// Удалить контракт из истории
			$scope.deleteContractHistory = function(contract, contract_history, index) {
				bootbox.confirm("Вы уверены, что хотите удалить версию договора из истории?", function(result) {
					if (result === true) {
						$.post("ajax/contractDeleteHistory", {"id_contract": contract_history.id})

						$("#contract_history_" + contract.id).addClass("active");
						$("#contract_history_li_" + contract.id).addClass("active");

						$.each($scope.contracts, function(i, v) {
							if (v.id == contract.id) {
								$scope.contracts[i].History.splice(index, 1)
								$scope.$apply()
								return
							}
						})
					}
				})
			}
			
			$scope.confirmPayment = function(payment) {
				bootbox.prompt({
					title: "Введите пароль",
					className: "modal-password",
					callback: function(result) {
					  if (result === null) {}
						else if (hex_md5(result) === payments_hash) {
							payment.confirmed = payment.confirmed ? 0 : 1
							$.post("ajax/confirmPayment", {id: payment.id, confirmed: payment.confirmed})
							$scope.$apply()
						} else if (result != null) {
							$('.bootbox-form').addClass('has-error').children().first().focus()
							$('.bootbox-input-text').on('keydown', function() {
								$(this).parent().removeClass('has-error')	
							})
							return false
						}
					},
					buttons: {
						confirm: {
							label: "Подтвердить"
						},
						cancel: {
							className: "display-none"
						}		
					}
				})
			}
			
			// Окно редактирования платежа
			$scope.editPayment = function(payment) {
				if (!payment.confirmed) {
					$scope.new_payment = angular.copy(payment)
					$scope.$apply()
					lightBoxShow('addpayment')
					return
				}
				bootbox.prompt({
					title: "Введите пароль",
					className: "modal-password",
					callback: function(result) {
            if (result === null) {}
						else if (hex_md5(result) === payments_hash) {
							$scope.new_payment = angular.copy(payment)
							$scope.$apply()
							lightBoxShow('addpayment')		
						} else if (result != null) {
							$('.bootbox-form').addClass('has-error').children().first().focus()
							$('.bootbox-input-text').on('keydown', function() {
								$(this).parent().removeClass('has-error')	
							})
							return false
						}	
					},
					buttons: {
						confirm: {
							label: "Подтвердить"
						},
						cancel: {
							className: "display-none"
						}		
					}
				})
			}

			// Показать окно добавления платежа
			$scope.addPaymentDialog = function() {
				$scope.new_payment = {id_status : 0}
				lightBoxShow('addpayment')	
			}

			// Добавить платеж
			$scope.addPayment = function() {
				// Получаем элементы (я знаю, что по-хорошему нужно получить их один раз вне функции
				// а не каждый раз, когда функция вызывается, искать их заново. Но забей. Хочу их внутри когда
				payment_date	= $("#payment-date")
				payment_sum 	= $("#payment-sum")
				payment_select	= $("#payment-select")
				payment_type	= $("#paymenttypes-select")

				// Установлен ли способ оплаты
				if (!$scope.new_payment.id_status) {
					payment_select.focus().parent().addClass("has-error")
					return
				} else {
					payment_select.parent().removeClass("has-error")
				}

				// Установлен ли тип платежа?
				if (!$scope.new_payment.id_type) {
					payment_type.focus().parent().addClass("has-error")
					return
				} else {
					payment_type.parent().removeClass("has-error")
				}

				// Установлена ли сумма платежа?
				if (!$scope.new_payment.sum) {
					payment_sum.focus().parent().addClass("has-error")
					return
				} else {
					payment_sum.parent().removeClass("has-error")
				}

				// Установлена ли дата платежа?
				if (!$scope.new_payment.date) {
					payment_date.focus().parent().addClass("has-error")
					return
				} else {
					payment_date.parent().removeClass("has-error")
				}

				// редактирование платежа, если есть ID
				if ($scope.new_payment.id) {
					ajaxStart('payment')
					$.post("ajax/paymentEdit", $scope.new_payment, function(response) {
						angular.forEach($scope.payments, function(payment, i) {
							if (payment.id == $scope.new_payment.id) {
								$scope.payments[i] = $scope.new_payment
								$scope.$apply()
							}
						})
						ajaxEnd('payment')
						lightBoxHide()
					})
				} else {
				// иначе сохранение плтежа
					// Добавляем дополнительные данные в new_payment
					$scope.new_payment.user_login		= $scope.user.login
					$scope.new_payment.first_save_date	= moment().format('YYYY-MM-DD HH:mm:ss')
					$scope.new_payment.entity_id		= $scope.student.id
					$scope.new_payment.entity_type	= 'STUDENT'
					$scope.new_payment.id_user			= $scope.user.id

					ajaxStart('payment')
					$.post("ajax/paymentAdd", $scope.new_payment, function(response) {
						$scope.new_payment.id = response.id;
						$scope.new_payment.document_number = response.document_number;

						// Инициализация если не установлено
						$scope.payments = initIfNotSet($scope.payments);

						$scope.payments.push($scope.new_payment)

						$scope.new_payment = {id_status : 0}

						$scope.$apply()

						ajaxEnd('payment')
						lightBoxHide()
					}, 'json')
				}
			}
			
			// Удалить платеж
			$scope.deletePayment = function(index, payment) {
				if (!payment.confirmed) {
					bootbox.confirm("Вы уверены, что хотите удалить платеж?", function(result) {
						if (result === true) {
							$.post("ajax/deletePayment", {"id_payment": payment.id})
							$scope.payments.splice(index, 1)
							$scope.$apply()
						}
					})
				} else {
					bootbox.prompt({
						title: "Введите пароль",
						className: "modal-password",
						callback: function(result) {
              if (result === null) {}
							else if (hex_md5(result) === payments_hash) {
								bootbox.confirm("Вы уверены, что хотите удалить платеж?", function(result) {
									if (result === true) {
										$.post("ajax/deletePayment", {"id_payment": payment.id})
										$scope.payments.splice(index, 1)
										$scope.$apply()
									}
								})
							} else if (result != null) {
								$('.bootbox-form').addClass('has-error').children().first().focus()
								$('.bootbox-input-text').on('keydown', function() {
									$(this).parent().removeClass('has-error')	
								})
								return false
							}	
						},
						buttons: {
							confirm: {
								label: "Подтвердить"
							},
							cancel: {
								className: "display-none"
							}		
						}
					})
				}	
			}
			
			$scope.dateToStart = function(date) {
				var D;
				date = date.split(".");
				date = date.reverse();
				date = date.join("-");
				D = new Date(date);
				return moment().to(D);
			};

			// форматировать дату
			$scope.formatDate = function(date){
		        var dateOut = new Date(date);
		        return dateOut;
		    };

		    // Удалить файл из догавара
		    $scope.deleteContractFile = function (contract, id) {
			    contract.files.splice(id, 1)
			    $.post("ajax/UploadFiles", {
					"id_contract":	contract.id,
					"files":		contract.files
				});
		    }

		    // проверить номер телефона
		    $scope.phoneCorrect = phoneCorrect	
		    	    
			// проверить номер телефона
		    $scope.isMobilePhone = isMobilePhone
		    
		    // Превратить в файлаплоад
		    $scope.bindFileUpload = function(contract) {
				// загрузка файла договора
				$('#fileupload' + contract.id).fileupload({
					dataType: 'json',
					maxFileSize: 10000000, // 10 MB
					// начало загрузки
					send: function() {
						NProgress.configure({ showSpinner: true })
					},
					// во время загрузки
					progress: function (e, data) {
			            NProgress.set(data.loaded / data.total)
			        },
			        // всегда по окончании загрузки (неважно, ошибка или успех)
			        always: function() {
				        NProgress.configure({ showSpinner: false })
				        ajaxEnd()
			        },
			        done: function (i, response) {
						if (response.result !== "ERROR") {
							contract.files = initIfNotSet(contract.files)
							contract.files.push(response.result)
							$.post("ajax/UploadFiles", {
								"id_contract":	contract.id,
								"files":		contract.files
							});
							$scope.$apply()
						} else {
							notifyError("Ошибка загрузки")
						}
			        },
			        fail: function (e, data) {
						$.each(data.messages, function (index, error) {
							notifyError(error)
						})
			        }
			    })
		    }
		    
		    $scope.groupsFilter = function(Group) {
			    if ($scope.academic_year > 0) {
				    return Group.year == $scope.academic_year
				} else {
					return true
				}
		    }
		    
			$scope.hasHiddenGroups = function() {
				if ($scope.Groups) {
					return $scope.$eval('Groups | filter:groupsFilter').length != $scope.Groups.length
				} else {
					return false
				}
			}
			
			$scope.showHiddenGroups = function() {
				// перезаписываем фильтр
				$scope.groupsFilter = function() { return true }
			}
			
			$scope.signUpForTest = function(Test) {
				$.post("tests/ajaxSignUp", {Test: Test, id_student: $scope.id_student})
			}
			
			
/*
			$scope.signedUpForTest = function(Test) {
				return $scope.getStudentTest(Test.id) !== undefined
			}
*/
			
			$scope.getStudentTest = function(id) {
				return _.find($scope.StudentTests, {id_test: id})
			}
			
			$scope.getTestStatus = function(Test) {
				if (Test !== undefined) {
					return test_statuses[Test.intermediate || 0]
				}
			}
			
			$scope.toggleTestStatus = function(Test) {
				$.post("tests/ajaxToggleStatus", {
					id_test: (Test.hasOwnProperty('id_test') ? Test.id_test : Test.id), 
					id_student: $scope.id_student}
				, function(new_status) {
					Test.intermediate = parseInt(new_status)
					$scope.$apply()
				})
			}
		    
		    $scope.setMenu = function(menu) {
			    if ($scope.student === undefined && menu == 0 && $scope.mode == 'student') {
				    $.post("requests/ajax/LoadStudent", {id_student: $scope.id_student}, function(response) {
						['Subjects', 'SubjectsFull', 'SubjectsFull2', 'server_markers', 'contracts', 'student', 'Groups', 'academic_year', 'student_phone_level', 
							'branches_brick', 'time', 'representative_phone_level', 'representative'].forEach(function(field) {
							$scope[field] = response[field]
						})
						
						$scope.weekdays = [
							{"short" : "ПН", "full" : "Понедельник", 	"schedule": ["", "", $scope.time[1], $scope.time[2]]},
							{"short" : "ВТ", "full" : "Вторник", 		"schedule": ["", "", $scope.time[1], $scope.time[2]]},
							{"short" : "СР", "full" : "Среда", 			"schedule": ["", "", $scope.time[1], $scope.time[2]]},
							{"short" : "ЧТ", "full" : "Четверг", 		"schedule": ["", "", $scope.time[1], $scope.time[2]]},
							{"short" : "ПТ", "full" : "Пятница", 		"schedule": ["", "", $scope.time[1], $scope.time[2]]},
							{"short" : "СБ", "full" : "Суббота", 		"schedule": [$scope.time[3], $scope.time[4], $scope.time[5], $scope.time[6]]},
							{"short" : "ВС", "full" : "Воскресенье",	"schedule": [$scope.time[3], $scope.time[4], $scope.time[5], $scope.time[6]]}
						]
						
						$.each($scope.student.branches, function(index, branch) {
							$scope.student.branches[index] = branch.toString();
						});
						
						$scope.$apply()
						
						$timeout(function() {
							// photo edit
							bindCropper();
							bindPhotoUpload();

							// ios-like triple switch
							$('.triple-switch').slider({
								tooltip: 'hide',
							});
							rebindMasks()
							
							// Добавляем существующие метки
							$scope.loadServerMarkers();
							
							// События добавления меток
							google.maps.event.addListener($scope.gmap, 'click', function(event) {
								$scope.gmapAddMarker(event)
							})
						}, 100)
					}, "json")
			    }
			    if ($scope.payments === undefined && menu == 1) {
					$.post("requests/ajax/LoadPayments", {id_student: $scope.id_student}, function(response) {
						['user', 'payments', 'payment_types', 'payment_statuses'].forEach(function(field) {
							$scope[field] = response[field]
						})
						$scope.$apply()
					}, "json")
			    }
			    if ($scope.Journal === undefined && menu == 2) {
					$.post("requests/ajax/LoadJournal", {id_student: $scope.id_student}, function(response) {
						['Subjects', 'Journal', 'Groups'].forEach(function(field) {
							$scope[field] = response[field]
						})
						$scope.$apply()
					}, "json")
			    }
			    if ($scope.Reviews === undefined && menu == 3) {
				    $scope.enum = review_statuses
					
					$.post("requests/ajax/LoadReviews", {id_student: $scope.id_student}, function(response) {
						['Reviews', 'id_user_review', 'user', 'users'].forEach(function(field) {
							$scope[field] = response[field]
						})
						$scope.$apply()
					}, "json")
			    }
			    if ($scope.Reports === undefined && menu == 4) {
					$.post("requests/ajax/LoadReports", {id_student: $scope.id_student}, function(response) {
						$scope.Reports = response
						$scope.$apply()
					}, "json")
			    }
			    if ($scope.student_comments === undefined && menu == 5) {
					$.post("requests/ajax/LoadStudentComments", {id_student: $scope.id_student}, function(response) {
						$scope.student_comments = response
						$scope.$apply()
					}, "json")
			    }
				if ($scope.Tests === undefined && menu == 6) {
					$.post("requests/ajax/LoadStudentTests", {id_student: $scope.id_student}, function(response) {
						['Tests', 'StudentTests'].forEach(function(field) {
							$scope[field] = response[field]
						})
						$scope.$apply()
					}, "json")
					$scope.tests_interval = setInterval(function() {
						$scope.$apply()
					}, 1000)
			    } else {
				    clearInterval($scope.tests_interval)
			    }
			    $scope.current_menu = menu
		    }

			$(document).ready(function() {
				console.log('here')

				// $('.bs-date input, input.bs-date').inputmask({ alias: 'date'});
				switch(window.location.hash) {
					case '#payments': {
						$scope.setMenu(1)
						break;
					}
					case '#visits': {
						$scope.setMenu(2)
						break;
					}
					case '#reviews': {
						$scope.setMenu(3)
						break;
					}
					case '#reports': {
						$scope.setMenu(4)
						break;
					}
					case '#comments': {
						$scope.setMenu(5)
						break;
					}
					case '#tests': {
						$scope.setMenu(6)
						break;
					}
					case '#phtoto': {
						$scope.setMenu(7)
						break;
					}
					default: {
						$scope.setMenu(0)
					}
				}
				$("#request-subjects").selectpicker({noneSelectedText: "предметы", multipleSeparator: "+"})
				
				$("#request-edit").on('keyup change', 'input, select, textarea', function(){
			        $scope.form_changed = true
			        $scope.$apply()
			    })
			    
				// код подразделения
				$("#code-podr").mask("999-999");

				// генерируем массив цифр посещаемости
				$scope.visit_data_counts = {}
				$.each($scope.getStudentGroups(), function (index, id_group) {
					if ($scope.getGroup(id_group)) {
						$scope.visit_data_counts[id_group] = {}
						change_index = 0
						$.each($scope.getGroup(id_group).Schedule, function (index, Visit) {
							if (index > 0) {
								if ($scope.getVisitBoolean(id_group, $scope.getGroup(id_group).Schedule[index - 1].date) != $scope.getVisitBoolean(id_group, Visit.date)) {
									$scope.visit_data_counts[id_group][index] = index - change_index
									change_index = index
								}
							}
						})
						$scope.visit_data_counts[id_group]['last'] = $scope.getGroup(id_group).Schedule.length - change_index
					}
				})
			    
			  // promo-code-loading
				$(".map-save-button, .bs-datetime").on("click", function() {
				    $scope.form_changed = true
			        $scope.$apply()
			    })
				
				// Биндим загрузку к уже имеющимся дагаварам
				$.each($scope.contracts, function(index, contract) {
					$scope.bindFileUpload(contract)
				})

				// дополнительный apply on document.ready
				$scope.$apply()
			})
			
			$scope.save = function() {
				// Проверяем все ли номера телефона заполнены
				has_errors = false
				
				$(".phone-masked").filter(function() {
					// если есть нижнее подчеркивание, то номер заполнен не полностью
					not_filled = $(this).val().match(/_/)

					if (not_filled !== null) {
						$(this).addClass("has-error").focus()
						notifyError("Номер телефона указан неполностью")
						has_errors = true
						return false
					} else {
						$(this).removeClass("has-error")
					}
				})
				
				
				$(".email").filter(function() {
					if ($(this).val() != '' && !validateEmail($(this).val())) {
						$(this).addClass("has-error").focus()
						has_errors = true
						return false
					} else {
						$(this).removeClass("has-error")
					}
				})

				// если в предварительной проверке были ошибки
				if (has_errors) {
					return false
				}

				ajaxStart()
				$scope.saving = true
				$scope.$apply()

				data = $("#request-edit").serializeArray()
				console.log($scope.representative, data)
				$.post("requests/ajax/Save", data)
					.success(function() {
						if ($scope.redirect_after_save) {
							redirect("requests/edit/" + $scope.redirect_after_save)
						} else {
						//	notifySuccess("Данные сохранены")
						}
					})
					.error(function() {
						notifyError("Ошибка сохранения")
					})
					.complete(function() {
						if (!$scope.redirect_after_save) {
							$scope.saving = false
							$scope.form_changed = false
							$scope.$apply()
							ajaxEnd()
						}
					})
			}

			// photo functions
				$scope.picture_version = 1;
				$scope.dialog = function(id) {
					$("#" + id).modal('show');
				};
				$scope.closeDialog = function(id) {
					$("#" + id).modal('hide');
				};
				$scope.deletePhoto = function() {
					return bootbox.confirm('Удалить фото?', function(result) {
						if (result === true) {
							ajaxStart();
							return $.post("students/ajax/deletePhoto", {
								student_id: $scope.student.id
							}, function() {
								ajaxEnd();
								$scope.student.has_photo_cropped = false;
								$scope.student.has_photo_original = false;
								$scope.student.photo_cropped_size = 0;
								$scope.student.photo_original_size = 0;
								return $scope.$apply();
							});
						}
					});
				};
				$scope.formatBytes = function(bytes) {
					if (bytes < 1024) {
						return bytes + ' Bytes';
					} else if (bytes < 1048576) {
						return (bytes / 1024).toFixed(1) + ' KB';
					} else if (bytes < 1073741824) {
						return (bytes / 1048576).toFixed(1) + ' MB';
					} else {
						return (bytes / 1073741824).toFixed(1) + ' GB';
					}
				};
				$scope.saveCropped = function() {
				return $('#photo-edit').cropper('getCroppedCanvas').toBlob(function(blob) {
					var formData;
					formData = new FormData;
					formData.append('croppedImage', blob);
					formData.append('student_id', $scope.student.id);
					ajaxStart();
					return $.ajax('upload/croppedStudent', {
						method: 'POST',
						data: formData,
						processData: false,
						contentType: false,
						dataType: 'json',
						success: function(response) {
							ajaxEnd();
							$scope.student.has_photo_cropped = true;
							$scope.student.photo_cropped_size = response;
							$scope.picture_version++;
							$scope.$apply();
							return $scope.closeDialog('change-photo');
						}
					});
				});
			};
				bindCropper = function() {
					$('#photo-edit').cropper('destroy');
					return $('#photo-edit').cropper({
						aspectRatio: 4 / 5,
						minContainerHeight: 700,
						minContainerWidth: 700,
						minCropBoxWidth: 240,
						minCropBoxHeight: 300,
						preview: '.img-preview',
						viewMode: 1,
						crop: function(e) {
							var width;
							width = $('#photo-edit').cropper('getCropBoxData').width;
							if (width >= 240) {
								return $('.cropper-line, .cropper-point').css('background-color', '#158E51');
							} else {
								return $('.cropper-line, .cropper-point').css('background-color', '#D9534F');
							}
						}
					});
				};
				bindPhotoUpload = function() {
					$('#photoupload').fileupload({
						formData: {
							student_id: $scope.student.id,
							maxFileSize: 10000000
						},
						send: function() {
							return NProgress.configure({
								showSpinner: true
							});
						},
						progress: function(e, data) {
							return NProgress.set(data.loaded / data.total);
						},
						always: function() {
							NProgress.configure({
								showSpinner: false
							});
							return ajaxEnd();
						},
						done: function(i, response) {
							response.result = JSON.parse(response.result);
							$scope.student.photo_extension = response.result.extension;
							$scope.student.photo_original_size = response.result.size;
							$scope.student.photo_cropped_size = 0;
							$scope.student.has_photo_original = true;
							$scope.student.has_photo_cropped = false;
							$scope.picture_version++;
							$scope.$apply();
							return bindCropper();
						}
					});
				};
				$scope.showPhotoEditor = function() {
					$scope.dialog('change-photo');
					return $timeout(function() {
						return $('#photo-edit').cropper('resize');
					}, 100);
				};
			// photo functions
		})