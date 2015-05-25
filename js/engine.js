
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
	
	
	/**
	 * Функция для печати контента элемента.
	 * 
	 */
	function printElem(elem)
    {
        printPopup($(elem).html());
    }

    function printPopup(data) 
    {
        var mywindow = window.open('', '', 'height=400,width=600');
        mywindow.document.write('<html><head><title></title>');
        /*optional stylesheet*/ //mywindow.document.write('<link rel="stylesheet" href="main.css" type="text/css" />');
        mywindow.document.write('</head><body >');
        mywindow.document.write(data);
        mywindow.document.write('</body></html>');

        mywindow.document.close(); // necessary for IE >= 10
        mywindow.focus(); // necessary for IE >= 10

        mywindow.print();
        mywindow.close();

        return true;
    }
    
    function printDiv(id_div) {
            var contents = document.getElementById(id_div).innerHTML;
            var frame1 = document.createElement('iframe');
            frame1.name = "frame1";
            frame1.style.position = "absolute";
            frame1.style.top = "-1000000px";
/*
			frame1.style.position = "fixed";
			frame1.style.top = 0;
			frame1.style.left = 0;
			frame1.style.width = "100%";
			frame1.style.height = "100%";
			frame1.style.background = "white";
			frame1.style.zIndex = 99999;
*/
			
            document.body.appendChild(frame1);
            var frameDoc = frame1.contentWindow ? frame1.contentWindow : frame1.contentDocument.document ? frame1.contentDocument.document : frame1.contentDocument;
            frameDoc.document.open();
            frameDoc.document.write('<html><head><title>ЕГЭ Центр</title>');
			frameDoc.document.write('<link rel="stylesheet" href="css/bootstrap.css" type="text/css">');
            frameDoc.document.write('<link rel="stylesheet" href="css/style.css" type="text/css">');
            frameDoc.document.write('</head><body>');
            frameDoc.document.write(contents);
            frameDoc.document.write('</body></html>');
            frameDoc.document.close();
            setTimeout(function () {
                window.frames["frame1"].focus();
                window.frames["frame1"].print();
                document.body.removeChild(frame1);
            }, 500);
            return false;
        }