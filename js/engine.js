	moment.locale('ru-RU');
	var ang_scope;
	var email_uploaded_files = [];
	var email_uploaded_file_id = 1;
	
	// Основной скрипт
	$(document).ready(function() {
		// ангуляровский scope по умолчанию
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
		
		// Убираем мега баг. Такой крутой,  что даже можно запостить на стаковерфлоу
		$("option[value^='?']").remove()
		
		// кол-во символов смс
		$("#sms-message").on("keyup", function() {
			res = SmsCounter.count($(this).val())
			$("#sms-counter").html(res.messages + " СМС")
		})
		
		
		// загрузка файла договора
		$('#email-files').fileupload({
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
					file = response.result
					file.email_uploaded_file_id = email_uploaded_file_id

					email_uploaded_files.push(file)
					$("#email-files-list").append('<div id="email-file-' + email_uploaded_file_id + '" class="loaded-file">\
						<span style="color: black">' + file.uploaded_name + '</span>\
							<a target="_blank" href="files/email/' + file.name + '" class="link-reverse small">скачать</a>\
						<span class="link-like link-reverse small" onclick="emailRemoveFile(' + email_uploaded_file_id + ')">удалить</span>\
						</div>')
					email_uploaded_file_id++
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

		
	})
	
	function emailRemoveFile(id) {
		$("#email-file-" + id).slideUp(300, function() {
			$.each(email_uploaded_files, function (index, file) {
				if (file.email_uploaded_file_id == id) {
					email_uploaded_files.splice(index, 1)
				}	
			})
			$(this).remove()
		})
	}
	
	function showFullSms(id) {
		$("#sms-short-" + id).hide(0)
		$("#sms-full-" + id).show(0)
	}
	
	function smsTemplate(id_template) {
		template = $("#sms-template-" + id_template).text().trim()
		$("#sms-message").val(template).keyup()
	}
	
	
	function generateEmailTemplate()
	{
		subject = "Тест определения уровня знаний (ЕГЭ-Центр-Москва)"
		body = "{student_first_middle_name}, здравствуйте.\n\n\
В прикрепленных файлах тесты, которые необходимо выполнить до {today_plus_5}. Просьба ответ прислать в ответном письме по электронной почте, с указанием фамилии и предмета.\n\n\
С уважением, {user_first_last}, ответственный по тестированию (ЕГЭ-Центр-Москва), +7 (495) 646-85-92"
		
		body = body.replace('{student_first_middle_name}', ang_scope.student.first_name + ' ' + ang_scope.student.middle_name)
		body = body.replace('{today_plus_5}', moment().add('days', 5).format('DD.MM'))
		body = body.replace('{user_first_last}', ang_scope.user.first_name + ' ' + ang_scope.user.last_name)
		
		contract = ang_scope.contracts[ang_scope.contracts.length - 1]
		
		if (contract) {
			email_uploaded_files = []
			$("#email-files-list").html("")
			$.each(contract.subjects, function(i, subject) {
				file_name = i + '_' + contract.grade + '.pdf'
				
				$.get("ajax/getFile", {file_name: "files/email/" + file_name}, function(response) {
					console.log(response)
					if (response != false) {
						file = {
							uploaded_name: subject.name + ', ' + contract.grade + ' класс',
							name: i + '_' + contract.grade + '.pdf',
							size: response,
						}
						file.email_uploaded_file_id = email_uploaded_file_id
			
						email_uploaded_files.push(file)
						$("#email-files-list").append('<div id="email-file-' + email_uploaded_file_id + '" class="loaded-file">\
							<span style="color: black">' + file.uploaded_name + '</span>\
								<a target="_blank" href="files/email/' + file.name + '" class="link-reverse small">скачать</a>\
							<span class="link-like link-reverse small" onclick="emailRemoveFile(' + email_uploaded_file_id + ')">удалить</span>\
							</div>')
						email_uploaded_file_id++						
					}
				}, "json")
			})
		}

		$("#email-subject").val(subject)
		$("#email-message").val(body)
	}
	
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
	
	function sendEmail() {
		message = $("#email-message");
		email	= $("#email-address").text();
		subject = $("#email-subject")
		
		if (subject.val().trim() == "") {
			subject.addClass("has-error").focus()
			return
		} else {
			subject.removeClass("has-error")
		}
		
		if (message.val().trim() == "") {
			message.addClass("has-error").focus()
			return
		} else {
			message.removeClass("has-error")
		}
		
		ajaxStart('email')
		$.post("ajax/sendEmail", {
			"message": message.val().trim(),
			"subject": subject.val().trim(),
			"email": email,
			"files": email_uploaded_files,
		}, function(response) {
			$("#email-files-list").html("")
			ajaxEnd('email')
			
			files_html = ""
			$.each(email_uploaded_files, function(i, file) {
				files_html += '<div class="sms-coordinates">\
					<a target="_blank" href="files/email/' + file.name + '" class="link-reverse small">' + file.uploaded_name + '</a>\
					<span> (' + file.size + ')</span>\
					</div>'
			})
			
			html = '\
			<div class="clear-sms">		\
					<div class="from-them">		\
						' + response.message + ' 		\
						<div class="sms-coordinates">' + response.coordinates + '</div>' + files_html + '\
				    </div>						\
				</div>';	
			$("#email-history").prepend(html).animate({ scrollTop: 0 }, "fast");
			message.val("")
			subject.val("")
			email_uploaded_files = []
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
			
			
			$.mask.definitions['H'] = "[0-2]";
		    $.mask.definitions['h'] = "[0-9]";
		    $.mask.definitions['M'] = "[0-5]";
		    $.mask.definitions['m'] = "[0-9]";
			$(".timemask").mask("Hh:Mm", {clearIfNotMatch: true});
			
			// Маска телефонов
			$(".phone-masked")
				.mask("+7 (999) 999-99-99", { autoclear: false })
				.on("keyup", function() {
					t = $(this)
					
					// если номер не заполнен -- выйти
					if (!t.val()) {
						return
					}
					
					// если есть нижнее подчеркивание, то номер заполнен не полностью
					not_filled = t.val().match(/_/)
					
					// если номер полностью заполнен
					if (!not_filled) {
						$.ajax({
							type: "POST",
							url: "ajax/checkPhone",
							data: {'phone': t.val(), 'id_request': ang_scope.id_request},
							success: function(response) {
									if (response == "true") {
										ang_scope.phone_duplicate = response
										t.addClass("has-error-bold")
									} else {
										ang_scope.phone_duplicate = null
										t.removeClass("has-error-bold")
									}
									ang_scope.$apply()
								},
							async: false
						})
					} else {
						t.removeClass("has-error-bold")
						ang_scope.phone_duplicate = null
						ang_scope.$apply()
					}
				})
			
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
	function frontendLoadingStart()
	{
		$("#frontend-loading").fadeIn(300)
	}
	function frontendLoadingEnd()
	{
		$("#frontend-loading").hide()
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
	
	// кол-во смс
	!function(){var $,SmsCounter;window.SmsCounter=SmsCounter=function(){function SmsCounter(){}SmsCounter.gsm7bitChars="@£$¥èéùìòÇ\\nØø\\rÅåΔ_ΦΓΛΩΠΨΣΘΞÆæßÉ !\\\"#¤%&'()*+,-./0123456789:;<=>?¡ABCDEFGHIJKLMNOPQRSTUVWXYZÄÖÑÜ§¿abcdefghijklmnopqrstuvwxyzäöñüà";SmsCounter.gsm7bitExChar="\\^{}\\\\\\[~\\]|€";SmsCounter.gsm7bitRegExp=RegExp("^["+SmsCounter.gsm7bitChars+"]*$");SmsCounter.gsm7bitExRegExp=RegExp("^["+SmsCounter.gsm7bitChars+SmsCounter.gsm7bitExChar+"]*$");SmsCounter.gsm7bitExOnlyRegExp=RegExp("^[\\"+SmsCounter.gsm7bitExChar+"]*$");SmsCounter.GSM_7BIT="GSM_7BIT";SmsCounter.GSM_7BIT_EX="GSM_7BIT_EX";SmsCounter.UTF16="UTF16";SmsCounter.messageLength={GSM_7BIT:160,GSM_7BIT_EX:160,UTF16:70};SmsCounter.multiMessageLength={GSM_7BIT:153,GSM_7BIT_EX:153,UTF16:67};SmsCounter.count=function(text){var count,encoding,length,messages,per_message,remaining;encoding=this.detectEncoding(text);length=text.length;if(encoding===this.GSM_7BIT_EX){length+=this.countGsm7bitEx(text)}per_message=this.messageLength[encoding];if(length>per_message){per_message=this.multiMessageLength[encoding]}messages=Math.ceil(length/per_message);remaining=per_message*messages-length;if(remaining == 0 && messages == 0){remaining = per_message; }return count={encoding:encoding,length:length,per_message:per_message,remaining:remaining,messages:messages}};SmsCounter.detectEncoding=function(text){switch(false){case text.match(this.gsm7bitRegExp)==null:return this.GSM_7BIT;case text.match(this.gsm7bitExRegExp)==null:return this.GSM_7BIT_EX;default:return this.UTF16}};SmsCounter.countGsm7bitEx=function(text){var char2,chars;chars=function(){var _i,_len,_results;_results=[];for(_i=0,_len=text.length;_i<_len;_i++){char2=text[_i];if(char2.match(this.gsm7bitExOnlyRegExp)!=null){_results.push(char2)}}return _results}.call(this);return chars.length};return SmsCounter}();if(typeof jQuery!=="undefined"&&jQuery!==null){$=jQuery;$.fn.countSms=function(target){var count_sms,input;input=this;target=$(target);count_sms=function(){var count,k,v,_results;count=SmsCounter.count(input.val());_results=[];for(k in count){v=count[k];_results.push(target.find("."+k).text(v))}return _results};this.on("keyup",count_sms);return count_sms()}}}.call(this);