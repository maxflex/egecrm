
	// Основной скрипт
	$(document).ready(function() {
		
		// Дата
		$('.bs-date').datepicker({
			language	: 'ru',
			orientation	: 'top left',
			startDate: '-0d',
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
		
		
		
		// Маска телефонов
		$(".phone-masked").mask("+7 (999) 999-99-99", { autoclear: false })
	})
	
	// По нажатию ESC во всем приложении закрыть LIGHTBOX
	$(document).keyup(function(e) {
		if (e.keyCode == 27) {
			lightBoxHide()
		}
	});
	
	/**
	 * Преобразовать координаты LATLNG в строку.
	 * 
	 */
	function latLngString(event) {
		return event.latLng.lat() + "-" + event.latLng.lng()
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
	
	function lightBoxShow()
	{
		$(".lightbox, .lightbox-element").fadeIn(150)
	}
	
	function lightBoxHide()
	{
		$(".lightbox, .lightbox-element").fadeOut(150)
	}
	
	// Добавить маркер в БД
/*
	function addMarker(even) {
		
	}
*/