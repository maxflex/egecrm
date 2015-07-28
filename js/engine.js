	moment.lang('ru-RU');
	var ang_scope;
	
	// Основной скрипт
	$(document).ready(function() {
		// ангуляровский scope
		ang_scope = angular.element("[ng-app='Request']").scope()
		
		// Вешаем маски
		rebindMasks()
		
		// закрываем лайтбокс
		$(".lightbox").on("click", function() {
			lightBoxHide();
		})
		
		// Предотвращаем пустой поиск
		$("#global-search").submit(function() {
			if (!$("#global-search-text").val()) {
				return false
			}
		})
	})
	
	
	function smsDialog(elem) {
		var html = ""
		
		// если начинается с плюса, то это сразу номер телефона
		if (elem[0] == "+") {
			number = elem;
		} else {
			number = $('#' + elem).val()
		}
		
		$("#sms-history").html('<center class="text-gray">загрузка истории сообщений...</center>')
		
		$.post("ajax/smsHistory", {"number": number}, function(response) {
			console.log(response);
			if (response != false) {
				$.each(response, function(i, v) {
					html += '<div class="clear-sms">		\
								<div class="from-them">		\
									' + v.message + ' 		\
									<div class="sms-coordinates">' + v.coordinates + '</div>\
							    </div>						\
							</div>';	
					})
				$("#sms-history").html(html)
			} else {
			//	$("#sms-history").html("<div class='text-gray' style='text-align: center'>история сообщений пуста</div>");
				$("#sms-history").html("")
			}
		}, "json")
		
		$("#sms-number").text(number)
		lightBoxShow('sms')
	}
	
	function sendSms() {
		message = $("#sms-message");
		number	= $("#sms-number").text();
		
		if (message.val().trim() == "") {
			message.addClass("has-error").focus()
			return
		} else {
			message.removeClass("has-error")
		}
		
		$.post("ajax/sendSms", {
			"message": message.val().trim(),
			"number": number,
		}, function(response) {
			html = '\
			<div class="clear-sms">		\
					<div class="from-them">		\
						' + response.message + ' 		\
						<div class="sms-coordinates">' + response.coordinates + '</div>\
				    </div>						\
				</div>';	
			$("#sms-history").prepend(html).animate({ scrollTop: 0 }, "fast");
			message.val("")
		}, "json");
	}
	
	// По нажатию ESC во всем приложении закрыть LIGHTBOX
	$(document).keyup(function(e) {
		if (e.keyCode == 27) {
			lightBoxHide()
		}
	});
	
	function redirect(url) {
		window.location = url
	}
	
	// Удалить заявку по ID
	function deleteRequest(id_request) {
		bootbox.confirm("Вы уверены, что хотите удалить заявку #" + id_request, function(result) {
			if (result === true) {
				ajaxStart()
				$.post("ajax/deleteRequest", {"id_request": id_request}, function() {
					window.history.go(-1)	
				})
			}
		})
	}
	
	// Удалить ученика по ID
	function deleteStudent(id_student) {
		bootbox.confirm("Вы уверены, что хотите удалить профиль ученика №" + id_student, function(result) {
			if (result === true) {
				ajaxStart()
				$.post("ajax/deleteStudent", {"id_student": id_student}, function() {
					window.history.go(-1)	
				})
			}
		})
	}
	
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
			
			$(".bs-datetime").datetimepicker({
				format: 'YYYY-MM-DD HH:mm',
				locale: 'ru',
			})
			
			// REGEX для полей типа "число" и "1-5"
			$(".digits-only-float").inputmask("Regex", {regex: "[0-9]*[.]?[0-9]+"});
			$(".digits-only").inputmask("Regex", {regex: "[0-9]*"});
			
			
			// Маска телефонов
			$(".phone-masked")
				.mask("+7 (999) 999-99-99", { autoclear: false })
				.on("keyup", phoneInLoop)
				
			function phoneInLoop() {
				// если есть нижнее подчеркивание, то номер заполнен не полностью
				not_filled = $(this).val().match(/_/)
				
				t = $(this)
				// если номер полностью заполнен
				if (!not_filled) {
					$.post("ajax/checkPhone", {'phone': $(this).val(), 'id_request': ang_scope.id_request}, function(response) {
						if (response != "null") {
							ang_scope.phone_duplicate = response
							t.addClass("has-error-bold")
							//console.log(response)
							//t.parent().find("button span").removeClass("glyphicon-plus").addClass("glyphicon-random")
							// $("<h2>herererer</h2>").insertAfter(t)
						} else {
							ang_scope.phone_duplicate = null
							t.removeClass("has-error-bold")
						}
						ang_scope.$apply()
					})
				} else {
					t.removeClass("has-error-bold")
					ang_scope.phone_duplicate = null
					ang_scope.$apply()
				}
			}
			
			// FLOAT-LABEL
			$(".floatlabel").floatlabel();
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
	 * Получить цвет метро по названию.
	 * 
	 */
	function getColorByName(name) {
		var metro
		
		$.each(metro_data.stations, function (i, v) {
			if (v.name == name) {
				metro = v
				return
			}	
		})
		
		line = metro_data.lines[metro.lineId]
		
		
		return line.color
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
	 * Инициализировать array перед push, если он не установлен, чтобы не было ошибки.
	 * 
	 */
	function initIfNotSetObject(obj) {
		if (!obj) {
			obj = {}
		}
		return obj	
	}
	
	
	/**
	 * Скрываем/показываем лайтбоксы и элементы.
	 * 
	 */
	function lightBoxShow(element)
	{
		if (element == "addcontract") {
			setTimeout(function(){$(".ios7-switch.transition-control").removeClass("no-transition")}, 300)
		}
		
		$(".lightbox, .lightbox-" + element).fadeIn(150)
	}
	
	function lightBoxHide()
	{
		$(".ios7-switch.transition-control").addClass("no-transition")
		
		$(".lightbox, div[class^='lightbox-']").fadeOut(150)
	}
	
	
	/**
	 * Анимация аякса.
	 * 
	 */
	function ajaxStart()
	{
		NProgress.start()
	}
	function ajaxEnd()
	{
		NProgress.done()
	}
    
    /**
     * Печать дива.
     * 
     */
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
        frameDoc.document.write("<style type='text/css'>\
        	h4 {text-align: center}\
        	p {text-indent: 50px; margin: 0}\
		  </style>"
		);
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
	
	// ЧИСЛО БУКВАМИ
	var cifir_ru= new Array("од","дв","три","четыр","пят","шест","сем","восем","девят");
	var sotN_ru=new Array("сто","двести","триста","четыреста","пятьсот","шестьсот","семьмот","восемьсот","девятьсот");
	var milion_ru=new Array("триллион","миллиард","миллион","тысяч");
	var anDan_ru =new Array("","","","сорок","","","","","девяносто");
	
	function SPR(x){
		var sumprop = new SPRU(x);
		document.form1.check.value=sumprop.XS
	}
	
	function SPRU(XS){
		(XS>0? this.XS=sumPROPRU(Math.floor(XS),Math.round((XS-Math.floor(XS))*100)) : this.XS="Нулевое значение!" );
		return this;
	}
	
	function numToText(xx){
		var scet=4;
		var cifR='';
		var cfR='';
		var oboR=new Array(0);
		//==========================
		if (xx>999999999999999) { cfR="Пусто!"; return cfR; }
		while(xx/1000>0){
			yy=Math.floor(xx/1000);
			delen=Math.round((xx/1000-yy)*1000);
			//-------------------------------
			sot=Math.floor(delen/100)*100;
			des=(Math.floor(delen-sot)>9?Math.floor((delen-sot)/10)*10:0);
			ed=Math.floor(delen-sot)-Math.floor((delen-sot)/10)*10;
			//-------------------------------
			forDes=(des/10==2?'а':'')
			forEd=(ed==1?'ин': (ed==2?'е':'') );
			ffD=(ed>4?'ь': (ed==1 || scet<3? (scet<3 && ed<2?'ин': (scet==3?'на': (scet<4? (ed==2?'а':( ed==4?'е':'')) :'на') ) ) : (ed==2?'а':( ed==4?'е':'') ) ) );
			forTys=(des/10==1? (scet<3?'ов':'') : (scet<3? (ed==1?'': (ed>1 && ed<5?'а':'ов') ) : (ed==1?'а': (ed>1 && ed<5?'и':'') )) );
			//===============================
				oprSot=(sotN_ru[sot/100-1]!=null?sotN_ru[sot/100-1]:'');
				oprDes=' '+(cifir_ru[des/10-1]!=null? (des/10==1?'': (des/10==4 || des/10==9?anDan_ru[des/10-1]:(des/10==2 || des/10==3?cifir_ru[des/10-1]+forDes+'дцать':cifir_ru[des/10-1]+'ьдесят') ) ) :'');
				oprEd=' '+(cifir_ru[ed-1]!=null? cifir_ru[ed-1]+(des/10==1?forEd+'надцать' : ffD ) : (des==10?'десять':'') );
				oprTys=' '+(milion_ru[scet]!=null && delen>0 ?milion_ru[scet]+forTys:'');
			//-------------------------------
			cifR=(oprSot.length>1?oprSot:'')+
				 (oprDes.length>1?oprDes:'')+
	             (oprEd.length>1?oprEd:'')+
				 (oprTys.length>1?oprTys:'');
			oboR[oboR.length]=cifR;
			xx=Math.floor(xx/1000);
			scet-=1;
			if ( Math.floor(xx)<1 ) {	break;	}
		}
			oboR.reverse();
			for (i=0; i<oboR.length; i++){
				cfR+=oboR[i]+' ';
			}
			(cfR.length<3?cfR='ноль ':cfR);
			return cfR.replace('  ',' ').replace(/^\s\s*/, '').replace(/\s\s*$/, '');
	}
	
	// \КОНЕЦ ЧИСЛО БУКВАМИ