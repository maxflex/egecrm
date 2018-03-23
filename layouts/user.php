<!-- @rights-refactored -->
<!-- ЛАЙТБОКС ОТПРАВКА EMAIL -->
<div class="lightbox-new lightbox-email">
	<input type="hidden" id="email-mode" value="1">
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
				<div class="email-template-list sms-template-list" style="float: left">
					<span onclick="generateEmailTemplate()">шаблон для тестов</span>
				</div>
				<div class="email-group-controls" style="float: left; display: none">
					<span style="margin-right: 7px">
						<input type="checkbox" onclick="ang_scope.to_students = !ang_scope.to_students; ang_scope.$apply()" checked> ученикам
					</span>
					<span>
						<input type="checkbox" onclick="ang_scope.to_representatives = !ang_scope.to_representatives; ang_scope.$apply()"> представителям
					</span>
				</div>
				<div class="small" style="text-align: right">
					<span class="btn-file link-like link-reverse small">
						<span>добавить файл</span>
						<input id="email-files" data-url="upload/email/" type="file" name="email_file">
					</span>
					<div id="email-files-list">
					</div>
				</div>
			</div>
			<button class="btn btn-primary ajax-email-button" onclick="sendEmail()">Отправить</button>
		</div>
	</div>
</div>
<!-- /ЛАЙТБОКС ОТПРАВКА EMAIL -->


<?= globalPartial('phone_api'); ?>
<!-- форма поиска -->
<div class="modal" id="searchModal" tabindex="-1">
	<div class="modal-dialog">
		<div class="modal-content">
			<input type="text" placeholder="искать" id="searchQueryInput" v-on:keyup="keyup" v-on:keydown.up.prevent  v-model="query">
			<!--<input type="text" ng-model="query" ng-keyup="key($event)" ng-keydown="stoper($event)" placeholder="искать" id="searchQueryInput">-->
			<div id="searchResult">
				<div class="searchResultWraper" v-if="query!='' && !loading && results == 0">
					<div class="notFound" v-if="!error">cовпадений нет</div>
				</div>
				<div v-if="results > 0" v-for="(index, row) in lists" class="resultRow" v-bind:class="{active : ((index+1) ==  active)}">
					<div v-if="row.type == 'students'">
						<a v-bind:href="row.link">{{ row.last_name }} {{ row.first_name }} {{ row.middle_name }}</a>  - ученик
					</div>

					<div v-if="row.type == 'representatives'">
						<a v-bind:href="row.link">{{ row.last_name }} {{ row.first_name }} {{ row.middle_name }}</a>  - представитель
					</div>

					<div v-if="row.type == 'tutors'">
						<a v-bind:href="row.link">{{ row.last_name }} {{ row.first_name }} {{ row.middle_name }}</a>  - преподаватель
					</div>

					<div v-if="row.type == 'requests'">
						<a v-bind:href="row.link">{{ row.name || 'имя не указано' }}</a>  - заявка
					</div>

                    <div v-if="row.type == 'contracts'">
                        <a v-bind:href="row.link">№{{ row.id_contract }}</a>  - договор
                    </div>
				</div>
			</div>
		</div>
	</div>
</div>
<!-- конец формы поиска -->

<div class="row">
	<div class="col-sm-2" style="margin-left: 10px">
    	<div>
    		<?= globalPartial('menu') ?>
    	</div>
    </div>
    <div class="col-sm-9 content-col" style="padding: 0; width: 80.6%;">
    	<?php if (!$this->_custom_panel) { ?>
    		<div class="panel panel-primary">
    		<div class="panel-heading">
    			<?= $this->tabTitle() ?>
    		</div>
    		<div class="panel-body">
    	<?php } ?>
