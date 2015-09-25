<!-- ЛАЙТБОКС ОТПРАВКА EMAIL -->
<div class="lightbox-new lightbox-email-group">
	<h4 style="text-align: center" id="email-address">
		<span class="text-danger">email не установлен!</span>
	</h4>
	<div class="row">
		<div class="col-sm-12" id="email-history">
		</div>
	</div>
	<div class="row">
		<div class="col-sm-12" style="text-align: center">
			<div class="form-group">
				<input class="form-control" placeholder="Тема сообщения" id="email-subject">
			</div>
			<div class="form-group">
				<textarea rows="8" class="form-control" style="width: 100%" placeholder="Текст сообщения" id="email-message"></textarea>
				<div class="sms-template-list" style="float: left">
					<span onclick="generateEmailTemplate()">шаблон для тестов</span>
				</div>
				<div class="small" style="text-align: right">
					<span class="btn-file link-like link-reverse small">
						<span>добавить файл</span>
						<input id="email-files" data-url="upload/email/" type="file" name="email_file">
					</span>
					<div id="email-files-list">
					</div>
<!--
					<span style="color: black">файл {{$index + 1}}</span> <span ng-show="file.size && file.coords">({{file.size}}, {{file.coords}})</span>
						<a target="_blank" href="files/contracts/{{file.name}}" class="link-reverse small">скачать</a>
					<span class="link-like link-reverse small" ng-click="deleteContractFile(contract, $index)">удалить</span>
-->
	
				</div>
			</div>
			<button class="btn btn-primary ajax-email-group-button" onclick="sendEmail()">Отправить</button>
		</div>
	</div>
</div>
<!-- /ЛАЙТБОКС ОТПРАВКА EMAIL -->