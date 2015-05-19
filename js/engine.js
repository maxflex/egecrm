
	// Основной скрипт
	$(document).ready(function() {
		// Дата
		$('.bs-date').datepicker({
			language	: 'ru',
			orientation	: 'top left',
			autoclose	: true
		})
		
		// Маска телефонов
		$(".phone-masked").mask("+7 (999) 999-99-99", { autoclear: false })
	})