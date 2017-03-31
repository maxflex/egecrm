<div ng-app="Group" ng-controller="JournalCtrl" ng-init="<?= $ang_init_data ?>">

	<style>
		body {
			overflow: visible !important;
		}
		.content-col {
			width: auto !important;
			position: absolute;
			left: calc(16.66666667% + 10px);
		}
		table {
		  font-family: sans-serif;
		  border-collapse: collapse;
		}

		thead th {
		   -moz-transform: rotate(-90deg);  /* FF3.5+ */
		   -o-transform: rotate(-90deg);  /* Opera 10.5 */
		   -webkit-transform: rotate(-90deg);  /* Saf3.1+, Chrome */
		   filter:  progid:DXImageTransform.Microsoft.BasicImage(rotation=3);  /* IE6,IE7 */
		   -ms-filter: "progid:DXImageTransform.Microsoft.BasicImage(rotation=3)"; /* IE8 */
		   vertical-align: top !important;
		   padding: 25px 0 !important;
		}
		th {
		  font-weight: normal;
		  color: black;
		}
		td, th {
		  padding: 10px;
		  text-align: center;
		  border: solid 1px #ddd !important;
		}
		tr td:first-child {
			border-left: none !important;
		}
		th {
			border-top: none !important;
		}
		td:nth-child(n+2):nth-child(-n+5) {
			background: #EFEFEF;
		}
		tr:last-child td {
			border-bottom: none !important;
		}
		td:last-child, th:last-child {
			border-right: none !important;
		}

	</style>

	<table class="table table-custom">
		<thead>
			<tr>
				<th style="border: none !important"></th>
				<th ng-repeat="Schedule in Group.Schedule">
						{{formatDate(Schedule.date)}}
				</th>
			</tr>
		</thead>
		<tbody>
			<tr ng-repeat="Student in Group.Students">
				<td>
					<a style="white-space: nowrap" href="students/{{Student.id}}">{{Student.first_name}} {{Student.last_name}}</a>
				</td>
				<td ng-repeat="Schedule in Group.Schedule">
					<span class="circle-default" ng-show="getInfo(Student.id, Schedule) !== undefined"
						ng-class="{
							'circle-red'	: getInfo(Student.id, Schedule).presence == 2,
							'circle-orange'	: getInfo(Student.id, Schedule).presence == 1 && getInfo(Student.id, Schedule).late > 0,
						}"></span>
				</td>
			</tr>
		</tbody>
	</table>
</div>
