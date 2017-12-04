<link href="https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css" rel="stylesheet" integrity="sha384-wvfXpqpZZVQGK6TAh5PVlGOfQNHSoD2xbE+QkPxCAFlNEevoEH3Sl0sibVcOQVnN" crossorigin="anonymous">
<div class="panel panel-primary" ng-app="Clients" ng-controller="SubjectsCtrl" ng-init="<?= $ang_init_data ?>">
	<div class="panel-heading">
		Клиенты по предметам
		<div class="pull-right">
			<a href="clients">по клиентам</a>
		</div>
	</div>
	<div class="panel-body">
		<div class="row" style="margin-bottom: 15px">
			<div class="col-sm-2" style='width: 250px'>
				<select class="watch-select single-select form-control" ng-model="search.year" ng-change='filter()'>
					<option value="" data-subtext="{{ counts.year[''] || '' }}">все годы</option>
					<option disabled>────────</option>
					<option ng-repeat="year in <?= Years::json() ?>"
							data-subtext="{{ counts.year[year] || '' }}"
							value="{{year}}">{{ yearLabel(year) }}</option>
				</select>
			</div>
			<div class="col-sm-2" style='width: 250px'>
				<select class="watch-select single-select form-control" ng-model="search.status" ng-change='filter()'>
					<option value=""  data-subtext="{{ counts.status[''] || '' }}">все</option>
					<option disabled>───────</option>
					<option value="3">в работе</option>
					<option value="2">к расторжению</option>
					<option value="1">расторгнут</option>
				</select>
	        </div>
			<div class="col-sm-2" style='width: 250px'>
				<select multiple class="watch-select single-select form-control" ng-model="search.payment_statuses" ng-change='filter()' title='статус платежа' data-multiple-separator=', '>
					<?php foreach(StudentPaymentStatuses::$all as $id => $label) :?>
						<option value="<?= $id ?>"><?= $label ?></option>
					<?php endforeach ?>
				</select>
			</div>
		</div>

		<div style="position: relative">
			<div id="frontend-loading" style="height: 100%"></div>
			<table class="table small table-hover border-reverse gray-headers">
				<tr ng-repeat="cs in contract_subjects">
					<td width='350'>
						{{getNumber($index)}}. <a href="student/{{cs.id_student}}" ng-class="{
							'text-danger': cs.status == 1,
							'text-warning': cs.status == 2,
						}">
							<span ng-show='cs.student_name'>{{cs.student_name}}</span>
							<span ng-hide='cs.student_name'>имя не указано</span>
						</a>
					</td>
					<td width='100'>
						{{ Subjects[cs.id_subject]}}-{{ Grades[cs.grade] }}
					</td>
					<td width='100'>
						{{ cs.count }}
					</td>
					<td width='100'>
						{{ cs.subject_sum | number }}
					</td>
					<td width='100'>
						{{ cs.deposit | number }}
					</td>
					<td>
						{{ student_payment_statuses[cs.payment_status] }}
					</td>
					<td>
						<div class="pull-right">
							<div class="visit-div-circle" ng-repeat="v in visits[cs.id_student][cs.id_subject] track by $index">
								<span class="circle-default"
								ng-class="{
									'circle-red'	: v == 1,
									'circle-orange'	: v == 2
								}"></span>
							</div>
						</div>
					</td>
				</tr>
				<tr class="last-row">
					<td colspan="2">
						<b>итого на странице</b>
					</td>
					<td>
						<b>{{ total('count') | number }}</b>
					</td>
					<td>

					</td>
					<td colspan='3'>
						<b>{{ total('subject_sum') | number }}</b>
					</td>
				</tr>
			</table>
		</div>

		<div ng-show="contract_subjects === undefined" style="padding: 100px" class="small half-black center">
			загрузка клиентов...
		</div>
		<div ng-show="contract_subjects === null" style="padding: 100px" class="small half-black center">
			нет клиентов
		</div>
	</div>
</div>