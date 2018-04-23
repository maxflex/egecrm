<div id="contracts-list" class="panel panel-primary" ng-app="Contracts" ng-controller="ListCtrl" ng-init="<?= $ang_init_data ?>">
    <div class="panel-heading">Версии договоров</div>
    <div class="panel-body">
        <div class="row" style="margin-bottom: 15px;">
            <div class="col-sm-2">
                <select class="watch-select single-select form-control" ng-model="search.year" ng-change='filter()'>
                    <option value="">все годы</option>
                    <option disabled>────────</option>
                    <option ng-repeat="year in <?= Years::json() ?>"
                        value="{{year}}">{{ yearLabel(year) }}</option>
                </select>
            </div>
            <div class="col-sm-2">
                <select class="watch-select single-select form-control" ng-model="search.version" ng-change='filter()'>
                    <option value="">все версии</option>
                    <option disabled>────────</option>
                    <option value="1">первая в году</option>
                    <option value="2">последняя в году</option>
                </select>
            </div>
        </div>

        <div style="position: relative">
            <div id="frontend-loading" style="height: 100%"></div>
            <table class="table table-hover border-reverse">
                <tr ng-repeat="Contract in Contracts">
                    <td>
                        {{ Contract.id }}. <a href="student/{{ Contract.id_student}}">
                            <span ng-show='Contract.last_name'>{{Contract.last_name}} {{Contract.first_name}} {{Contract.middle_name}}</span>
                            <span ng-hide='Contract.last_name'>имя не указано</span>
                        </a>
                    </td>
                    <td>
                        {{ getContractSum(Contract) | number }} <span class='text-gray' ng-show='Contract.discount > 0'> (с учетом скидки {{ Contract.discount }}%)</span>
                    </td>
                    <td class="text-success">{{ Contract.green }}</td>
                    <td class="text-warning">{{ Contract.yellow }}</td>
                    <td class="text-danger">{{ Contract.red }}</td>
                    <td>{{ Contract.date | date:"dd.MM.yy" }}</td>
                    <td>{{ Contract.year + '-' + (+Contract.year + 1) + ' учебный год' }}</td>
                    <td>{{ Contract.version + 1 + ' версия' }}</td>
                </tr>
            </table>
        </div>

        <pagination
            ng-show='(Contracts && Contracts.length) && (counts.all > <?= Contract::PER_PAGE ?>)'
            ng-model="current_page"
            ng-change="pageChanged()"
            total-items="counts.all"
            max-size="10"
            items-per-page="<?= Contract::PER_PAGE ?>"
            first-text="«"
            last-text="»"
            previous-text="«"
            next-text="»"
        >
        </pagination>


        <div ng-show="Contracts === undefined" style="padding: 100px" class="small half-black center">
            загрузка договоров...
        </div>
        <div ng-show="Contracts === null" style="padding: 100px" class="small half-black center">
            нет договоров
        </div>
    </div>
</div>
