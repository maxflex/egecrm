<div ng-app='Calls' ng-controller='MissedCtrl' ng-init="<?= $ang_init_data ?>">
	<div ng-show="!missed.length" style="padding: 100px" class="small half-black center">
		нет пропущенных вызовов за сегодня
	</div>
	<table class="table border-reverse">
		<tr ng-repeat='m in missed'>
			<td width="200">
				{{ formatTime(m.start) }}
			</td>
			<td width="200">
				<span class="underline-hover inline-block" ng-click="callSip(m.phone_formatted)">{{m.phone_formatted}}</span>
			</td>
			<td>
                <span ng-if="m.caller.type == 'teacher'">преподаватель <a target='_blank' href='teachers/edit/{{m.caller.id}}'>{{ m.caller.name }}</a></span>
                <span ng-if="m.caller.type == 'representative'">представитель <a target='_blank' href='student/{{m.caller.id}}' >{{ m.caller.name }}</a></span>
                <span ng-if="m.caller.type == 'student'">ученик <a target='_blank' href='student/{{m.caller.id}}' >{{ m.caller.name }}</a></span>
                <span ng-if="m.caller.type == 'request'">по заявке <a target='_blank' href='requests/edit/{{m.caller.id}}'>{{ m.caller.name }}</a></span>
                <span ng-if="!m.caller.type">неизвестный номер</span>
			</td>
			<td>
				<span ng-click="deleteCall(m)" class="link-like red" aria-hidden="true">удалить</span>
			</td>
		</tr>
	</table>
</div>