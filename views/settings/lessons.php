<div ng-app="Settings" ng-controller="LessonsCtrl" ng-init="<?= $ang_init_data ?>">
<!--
	<div class="row">
		<div class="col-sm-4">
			<span class="day-explain"></span> – дни занятий
		</div>
		<div class="col-sm-4">
			<span class="day-explain was-lesson"></span> – проведенные занятия
		</div>
		<div class="col-sm-4">
			<span class="day-explain first-lesson"></span> – первое занятие в группе
		</div>
	</div>
			
-->
<!--
	<div class="center quater-black" style="padding: 50px 0" ng-show="!Schedule">
		загрузка занятий...
	</div>
-->
	<div class="row" ng-show="Schedule">
		<div class="col-sm-12 cabinet-add">
			<table class="table table-hover">
				<tr ng-repeat="s in Schedule">
					<td width="20">
						<span class="day-explain" ng-class="{
							'was-lesson': s.was_lesson,
							'first-lesson': s.is_first_lesson && !s.was_lesson
						}"></span>
					</td>
					<td><a href="groups/edit/{{s.id_group}}" target="_blank">Группа №{{s.id_group}}</a></td>
					<td>{{formatDate(s.date)}}</td>
					<td>{{s.time}}</td>
					<td>
						<span ng-bind-html="s.Group.branch | to_trusted"></span>
					</td>
				</tr>
			</table>
			
			<pagination
			  ng-model="currentPage"
			  ng-change="pageChanged()"
			  total-items="<?= GroupSchedule::countAll() ?>"
			  max-size="10"
			  items-per-page="<?= GroupSchedule::PER_PAGE ?>"
			  first-text="«"
			  last-text="»"
			  previous-text="«"
			  next-text="»"
			>
			</pagination>

			
		</div>
	</div>
</div>