<div ng-app='GoogleIds' ng-controller='IndexCtrl' ng-init="<?= $ang_init_data ?>">
    <div class="row flex-list">
        <div>
            <input type='text' ng-model="google_ids" placeholder="google ids" class="form-control" />
        </div>
        <div style='margin-right: 0; width: 200px; flex: none'>
            <button class="btn btn-primary full-width" ng-click="show()" ng-disabled="loading">показать</button>
        </div>
    </div>
    <div class="row" style='margin-top: 30px'>
        <div class="col-sm-12">
			<div class="div-blocker loading" ng-if="loading"></div>
			<table class="table reverse-borders" ng-if="data">
				<thead class="table-header">
					<tr>
						<td>
							ID google
						</td>
						<td>
							ID заявок
						</td>
						<td>
							ID учеников
						</td>
						<td>
							платежи
						</td>
					</tr>
				</thead>
				<tr ng-repeat="(id_google, d) in data">
					<td ng-class="{'quater-opacity': !d}" width='300'>
						{{ id_google }}
					</td>
					<td ng-if="d" width='300'>
						<span ng-repeat="id_request in d.requests">
							<a href="/requests/edit/{{ id_request}}">{{ id_request}}</a>{{ $last ? '' : ', '}}
						</span>
					</td>
					<td ng-if="d" width='300'>
						<span ng-repeat="id_student in d.students">
							<a href="/student/{{ id_student}}">{{ id_student}}</a>{{ $last ? '' : ', '}}
						</span>
					</td>
					<td ng-if="d">
						<div ng-repeat="payment in d.payments" style='display: flex; width: 200px'>
							<div ng-class="{
								'text-green': payment.id_type == 1,
								'text-danger': payment.id_type == 2,
								'half-opacity': disabled_payments[payment.id]
							}" style='width: 95px'>
								{{ payment.id_type == 1 ? '+' : '-' }}{{ payment.sum | number }} руб.
							</div>
							<div style='margin-right: 15px' ng-class="{'half-opacity': disabled_payments[payment.id]}">
								{{ payment.date }}
							</div>
							<div>
								<input type="checkbox" ng-model="disabled_payments[payment.id]" />
							</div>
						</div>
					</td>
					<td ng-if="!d" colspan='3'>
					</td>
				</tr>
				<tfoot class="table-footer">
					<tr>
						<td>
							{{ getTotalGoogleIds() }} google ids
						</td>
						<td>
							{{ getTotal('requests') }} заявок
						</td>
						<td>
							{{ getTotal('students') }} клиентов
						</td>
						<td class='text-green'>
							{{ getTotalPayments() | number }} руб.
						</td>
					</tr>
				</tfoot>
			</table>
        </div>
    </div>
</div>
