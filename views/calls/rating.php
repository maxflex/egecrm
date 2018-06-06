<div ng-app='Calls' ng-controller='MissedCtrl' ng-init="<?= $ang_init_data ?>">
	<div ng-show="!data.length" style="padding: 100px" class="small half-black center">
		нет данных
	</div>
	<table class="table border-reverse">
		<tr ng-repeat='d in data'>
			<td width="150">
				{{ d.call_date | formatDateTime }}
			</td>
			<td width="150">
				{{ callDuration(d.seconds) }}
			</td>
			<td width="200">
				{{ PhoneService.format(d.number) }}
			</td>
			<td width="200">
				{{ d.user_login }}
			</td>
			<td>
                <span ng-if="d.caller.type == 'teacher'">преподаватель <a target='_blank' href='teachers/edit/{{d.caller.id}}'>{{ d.caller.name }}</a></span>
                <span ng-if="d.caller.type == 'representative'">представитель <a target='_blank' href='student/{{d.caller.id}}' >{{ d.caller.name }}</a></span>
                <span ng-if="d.caller.type == 'student'">ученик <a target='_blank' href='student/{{d.caller.id}}' >{{ d.caller.name }}</a></span>
                <span ng-if="d.caller.type == 'request'">по заявке <a target='_blank' href='requests/edit/{{d.caller.id}}'>{{ d.caller.name }}</a></span>
                <span ng-if="!d.caller.type">неизвестный номер</span>
			</td>
			<td>
				 <span ng-show="!d.rating" class="text-gray">нет оценки</span>
				 <b ng-show="d.rating">{{ d.rating }}</b>
			</td>
		</tr>
	</table>
</div>
