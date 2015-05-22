
	// Основной скрипт
	$(document).ready(function() {
		// Вешаем маски
		rebindMasks()	
	})
	
	// По нажатию ESC во всем приложении закрыть LIGHTBOX
	$(document).keyup(function(e) {
		if (e.keyCode == 27) {
			lightBoxHide()
		}
	});
	
	
	/**
	 * Вызов функции с задержкой в 100 миллисекунд, чтобы успели создаться новые элементы
	 * и на них забиндились нужные события
	 */
	function delayedCall(function_name) {
		setTimeout(function_name(), 100)
	}
	
	/**
	 * Переназначает маски для всех элементов, включая новые
	 * 
	 */
	function rebindMasks() {
		// Немного ждем, чтобы новые элементы успели добавиться в DOM
		setTimeout(function() {
			// Дата
			$('.bs-date').datepicker({
				language	: 'ru',
				orientation	: 'top left',
				autoclose	: true
			})
			
			// Дата, начиная с нынчашнего дня
			$('.bs-date-now').datepicker({
				language	: 'ru',
				orientation	: 'top left',
				startDate: '-0d',
				autoclose	: true
			})
			
			// Дата вверху		
			$(".bs-date-top").datepicker({
				language	: 'ru',
				autoclose	: true,
				orientation	: 'bottom auto',
			})
			
			// REGEX для полей типа "число" и "1-5"
			$(".digits-only-float").inputmask("Regex", {regex: "[0-9]*[.]?[0-9]+"});
			$(".digits-only").inputmask("Regex", {regex: "[0-9]+"});
			
			
			// Маска телефонов
			$(".phone-masked").mask("+7 (999) 999-99-99", { autoclear: false })	
		}, 100)
	}
	
	
	/**
	 * Нотифай с сообщением об ошибке.
	 *
	 */
	function notifyError(message) {
		$.notify({'message': message, icon: "glyphicon glyphicon-remove"}, {
			type : "danger",
			allow_dismiss : false,
			placement: {
				from: "top",
			}
		});
	}
	
	/**
	 * Нотифай с сообщением об успехе.
	 *
	 */
	function notifySuccess(message) {
		$.notify({'message': message, icon: "glyphicon glyphicon-ok"}, {
			type : "success",
			allow_dismiss : false,
			placement: {
				from: "top",
			}
		});
	}
	
	/**
	 * Инициализировать array перед push, если он не установлен, чтобы не было ошибки.
	 * 
	 */
	function initIfNotSet(arr) {
		if (!arr) {
			arr = []
		}
		return arr	
	}
	
	
	/**
	 * Скрываем/показываем лайтбоксы и элементы.
	 * 
	 */
	function lightBoxShow()
	{
		$(".lightbox, .lightbox-element").fadeIn(150)
	}
	
	function lightBoxHide()
	{
		$(".lightbox, .lightbox-element").fadeOut(150)
	}