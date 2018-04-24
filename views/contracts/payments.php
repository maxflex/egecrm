<div ng-app="Contracts" ng-controller="PaymentsCtrl" ng-init="<?= $ang_init_data ?>">
    <div style="position: relative">
        <div id="frontend-loading" style="height: 100%"></div>
        <table class="table table-hover border-reverse">
            <tr ng-repeat="Payment in Payments">
				<td width='300'>
					<a href="student/{{ Payment.id_student }}">
						{{ Payment.last_name }} {{ Payment.first_name }} {{ Payment.middle_name }}
					</a>
				</td>
				<td width='150'>
					{{ Payment.date | date:'dd.MM.yy' }}
				</td>
				<td>
					{{ Payment.sum | number }} руб.
				</td>
            </tr>
        </table>
    </div>

    <pagination
        ng-show='(Payments && Payments.length) && (counts.all > <?= ContractPayment::PER_PAGE ?>)'
        ng-model="current_page"
        ng-change="pageChanged()"
        total-items="counts.all"
        max-size="10"
        items-per-page="<?= ContractPayment::PER_PAGE ?>"
        first-text="«"
        last-text="»"
        previous-text="«"
        next-text="»"
    >
    </pagination>


    <div ng-show="Payments === undefined" style="padding: 100px" class="small half-black center">
        загрузка договоров...
    </div>
    <div ng-show="Payments === null" style="padding: 100px" class="small half-black center">
        нет договоров
    </div>
</div>
