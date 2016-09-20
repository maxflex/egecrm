<div id="contracts-list" class="panel panel-primary" ng-app="Contracts" ng-controller="ListCtrl" ng-init="<?= $ang_init_data ?>">
    <div class="panel-heading">Версии договоров</div>
    <div class="panel-body">
        <div class="row" style="margin-bottom: 15px;">
            <div class="col-sm-2">
                <div class="input-group custom" style="position: relative">
                    <span class="input-group-addon">начало - </span>
                    <input class="form-control bs-date" ng-model="search.start_date" ng-change="filter()">
                </div>
            </div>
            <div class="col-sm-2">
                <div class="input-group custom" style="position: relative">
                    <span class="input-group-addon">конец - </span>
                    <input class="form-control bs-date" ng-model="search.end_date" ng-change="filter()">
                </div>
            </div>
            <div class="col-sm-2">
                <div class="input-group custom" style="position: relative">
                    <span class="input-group-addon">ID ученика - </span>
                    <input class="digits-only form-control" ng-model="search.id_student" ng-change="filter()" ng-model-options="{ debounce: 500 }">
                </div>
            </div>
        </div>

        <div style="position: relative">
            <div id="frontend-loading" style="height: 100%"></div>
            <table class="table table-hover border-reverse">
                <tr ng-repeat="Contract in Contracts">
                    <td>
                        {{getNumber($index)}}. <a href="student/{{ Contract.id_student}}">
                            <span ng-show='Contract.last_name'>{{Contract.last_name}} {{Contract.first_name}} {{Contract.middle_name}}</span>
                            <span ng-hide='Contract.last_name'>имя не указано</span>
                        </a>
                    </td>
                    <td>{{ Contract.sum }}</td>
                    <td class="text-success">{{ Contract.green }}</td>
                    <td class="text-warning">{{ Contract.yellow }}</td>
                    <td class="text-danger">{{ Contract.red }}</td>
                    <td>{{ Contract.date }}</td>
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
