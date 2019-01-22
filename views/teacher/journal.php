<div ng-app="Teacher" ng-controller="JournalCtrl" ng-init="<?= $ang_init_data ?>">
	<div class="top-links">
		<span ng-click="setYear(y)" class="link-like" ng-class="{'active': y == year}" ng-repeat="y in <?= Years::json() ?>">{{ yearLabel(y) }}</span>
	</div>
	
	<div>
		<div style='width: 230px; margin-bottom: 30px'>
			<select class="watch-select form-control search-grades" ng-model="grades" ng-change='loadData()' multiple none-selected-text='классы' title='классы' multiple-separator=', '>
				<option ng-hide='grade < 8' ng-repeat="(grade, label) in Grades | toArray" value="{{(grade + 1)}}">{{label}}</option>
			</select>
		</div>
	</div>
	
	<div ng-show="loading" style="padding: 100px" class="small half-black center">
		загрузка...
	</div>
	<div ng-show="!loading && emptyResult()" style="padding: 100px" class="small half-black center">
		нет данных
	</div>
	<table class="table table-journal-students" ng-if="!loading && !emptyResult()">
		<thead>
			<tr>
				<th style="border: none !important"></th>
				<th ng-repeat="date in dates" style="height: 70px; position: relative" ng-class="{'gray-bg': grayMonth(date)}">
					<span>{{formatDate(date)}}</span>
				</th>
			</tr>
		</thead>
		<tbody>
			<tr ng-repeat="student in students">
				<td style="text-align: left; width: 250px">
					<a style="white-space: nowrap" href="student/{{student.id}}" ng-class="{
						'text-danger': name_colors[student.id] == 1,
						'text-warning': name_colors[student.id] == 2,
						'gray-link': name_colors[student.id] == 'grey',
					}">{{ student.name }}</a>
				</td>
				<td ng-repeat="date in dates" ng-class="{
					'gray-bg': grayMonth(date),
					'no-more-dates': noMoreDates(student.id, date),
				}">
					<span class="circle-default"
						ng-class="{
							'circle-red'	: result[student.id][date] === 'red',
							'circle-orange'	: result[student.id][date] === 'orange',
							'invisible'		: result[student.id][date] === undefined,
						}"></span>
				</td>
			</tr>
		</tbody>
<!--
		<tbody>
			<tr ng-repeat="Student in Group.Students">
				<td style="text-align: left; width: 250px">
					<a style="white-space: nowrap" href="student/{{Student.id}}">{{Student.first_name}} {{Student.last_name}}</a>
				</td>
				<td ng-repeat="Lesson in Lessons" ng-class="{'gray-bg': grayMonth(Lesson.lesson_date)}">
					<span class="circle-default"
						ng-class="{
							'circle-red'	: getInfo(Student.id, Lesson).presence == 2,
							'circle-orange'	: getInfo(Student.id, Lesson).presence == 1 && getInfo(Student.id, Lesson).late > 0,
							'invisible'		: getInfo(Student.id, Lesson) === undefined,
						}"></span>
				</td>
			</tr>
			<tr>
				<td></td>
			</tr>
			<tr ng-repeat="Teacher in Teachers">
				<td style="text-align: left; width: 250px">
					<a style="white-space: nowrap" href="teachers/edit/{{Teacher.id}}">{{Teacher.last_name}} {{Teacher.first_name}} {{ Teacher.middle_name }}</a>
				</td>
				<td ng-repeat="Lesson in Lessons" ng-class="{'gray-bg': grayMonth(Lesson.lesson_date)}">
					<span class="circle-default"
						  ng-class="{
							'circle-red'	: getInfo(Teacher.id, Lesson).presence == 2,
							'circle-orange'	: getInfo(Teacher.id, Lesson).presence == 1 && getInfo(Teacher.id, Lesson).late > 0,
							'invisible'		: getInfo(Teacher.id, Lesson) === undefined,
						}"></span>
				</td>
			</tr>
		</tbody>
-->
	</table>
</div>
<style>
	.panel-body {
		overflow: scroll;
	}
	#k_u td {
		padding:5px 10px 5px 0;
	}
	
	.no-more-dates {
		border-right: 1px solid #f3f3f3 !important;
		opacity: .25;
		visibility: hidden;
	}
	
	.table-journal-students {
  width: auto !important; }
  .table-journal-students th {
    font-weight: normal;
    color: black; }
    .table-journal-students th span {
      position: absolute;
      -moz-transform: rotate(-90deg);
      /* FF3.5+ */
      -o-transform: rotate(-90deg);
      /* Opera 10.5 */
      -webkit-transform: rotate(-90deg);
      /* Saf3.1+, Chrome */
      filter: progid:DXImageTransform.Microsoft.BasicImage(rotation=3);
      /* IE6,IE7 */
      -ms-filter: "progid:DXImageTransform.Microsoft.BasicImage(rotation=3)";
      /* IE8 */
      vertical-align: top !important;
      display: block;
      left: -12px;
      top: 27px;
      font-size: 12px; }
    .table-journal-students th .lesson-cancelled-journal {
      position: absolute;
      width: 200px;
      left: -92px;
      top: 125px;
      color: #337ab7;
      font-size: 14px; }
  .table-journal-students td:last-child, .table-journal-students th:last-child {
    border-right: none !important; }
  .table-journal-students .circle-default {
    top: 4px !important; }
  .table-journal-students td, .table-journal-students th {
    height: 24px;
    padding: 0 4px !important;
    text-align: center;
    border: solid 1px #ddd !important; }
  .table-journal-students tr td:first-child {
    border-left: none !important; }
  .table-journal-students th {
    border-top: none !important; }
  .table-journal-students tr:last-child td {
    border-bottom: none !important; }
	
	.table-journal-students .border-top {
		border-top: 2px solid #aaa;
	}
</style>