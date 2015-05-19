
	// Основной скрипт
	$(document).ready(function() {
		// Дата
		$('.bs-date').datepicker({
			language	: 'ru',
			orientation	: 'top left',
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