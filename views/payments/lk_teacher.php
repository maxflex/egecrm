<div ng-app="Payments" ng-controller="LkTeacherCtrl" ng-init="<?= $ang_init_data ?>" style="min-height: 500px">
	<div class="row" ng-show="password_correct === false">
		<div class="col-sm-12">
			<h4 class="text-danger center" style="margin: 200px 0">доступ ограничен</h4>
		</div>
	</div>
	
	<div ng-show="password_correct === true">
		<div class="row" style="position: relative">
			<div class="col-sm-12" ng-show="!loaded">
				<div class="center half-black small" style="margin: 200px 0">
					загрузка...
				</div>
			</div>
			<div class="col-sm-12" ng-show="loaded">
				<h4 class="row-header">
					<span ng-show="Data.length">ПРОВЕДЕННЫЕ ЗАНЯТИЯ</span>
					<span ng-show="!Data.length">НЕТ ПРОВЕДЕННЫХ ЗАНЯТИЙ</span>
				</h4>
				<table class="table table-divlike" style="width: 75%" ng-show="Data.length">
					<tr ng-repeat="d in Data">
						<td>
							<a href="groups/edit/{{d.id_group}}/schedule">Группа №{{d.id_group}}</a>
						</td>
						<td>
							{{Subjects[d.id_subject]}}
						</td>
						<td>
							{{d.Group.grade}} класс
						</td>
						<td width="200">
							{{formatDate(d.lesson_date)}} г. в {{formatTime(d.lesson_time)}}
						</td>
						<td>
							ЕГЭ-Центр-{{Branches[d.id_branch]}}
						</td>
						<td>
							{{d.teacher_price | number}} <ng-pluralize count="d.teacher_price" when="{
							'one' : 'рубль',
							'few' : 'рубля',
							'many': 'рублей',
						}"></ng-pluralize>
						</td>
					</tr>
					<tr>
						<td colspan="5" style="padding: 7px"></td>
					</tr>
					<tr>
						<td colspan="3"></td>
						<td colspan="2">
							<b>проведено всего {{Data.length}} <ng-pluralize count="Data.length" when="{
								'one': 'занятие',
								'few': 'занятия',
								'many': 'занятий'
							}"></ng-pluralize> на сумму
							</b>
						</td>
						<td><b>{{totalEarned() | number}} <ng-pluralize count="totalEarned()" when="{
							'one' : 'рубль',
							'few' : 'рубля',
							'many': 'рублей',
						}"></ng-pluralize></b></td>
					</tr>
					<tr>
						<td colspan="3"></td>
						<td colspan="2">
							<b>выплачено преподавателю всего</b>
						</td>
						<td><b>{{totalPaid() | number}} <ng-pluralize count="totalPaid()" when="{
							'one' : 'рубль',
							'few' : 'рубля',
							'many': 'рублей',
						}"></ng-pluralize></b></td>
					</tr>
					<tr>
						<td colspan="3"></td>
						<td colspan="2">
							<b>к выплате</b>
						</td>
						<td><b>{{toBePaid() | number}} <ng-pluralize count="toBePaid()" when="{
							'one' : 'рубль',
							'few' : 'рубля',
							'many': 'рублей',
						}"></ng-pluralize></b></td>
					</tr>
				</table>
	
			</div>
		</div>
		
		<div class="row" ng-show="loaded && payments.length">
			<div class="col-sm-12">
				<h4 class="row-header">ПЛАТЕЖИ</h4>
			    <div class="form-group payment-line">
					<div ng-repeat="payment in payments | reverse" style="margin-bottom: 5px"> 
						<span class="label label-success">
						{{payment_statuses[payment.id_status]}}<span ng-show="payment.id_status == <?= Payment::PAID_CARD ?>">{{payment.card_number ? " *" + payment.card_number.trim() : ""}}</span></span>
						
						<span class="capitalize">{{payment_types[payment.id_type]}}</span>
						Платеж на сумму {{payment.sum}} <ng-pluralize count="payment.sum" when="{
							'one' : 'рубль',
							'few' : 'рубля',
							'many': 'рублей',
						}"></ng-pluralize> от {{payment.date}}
					</div>
			    </div>
			</div>
		</div>
	</div>
	
</div>