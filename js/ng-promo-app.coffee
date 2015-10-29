angular.module "Promo", []
	.controller "MainCtrl", ($scope) ->
		$scope.sklName = (person, padej) ->
		  if person == undefined
		    return false
		  person = 
		    first: person.first_name
		    last: person.last_name
		    middle: person.middle_name
		  # склоняем в дательный падеж
		  person = petrovich(person, padej)
		  # возвращаем ФИО
		  person.last + ' ' + person.first
		
		$scope.students_count = 0
		
		angular.element(document).ready ->
			set_scope "Promo"
			
			$('.counter').FlipClock $scope.students_count,
				autoStart: false
				clockFace: 'Counter'
			
			$('.flip.play').first().hide()