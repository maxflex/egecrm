<div ng-app="Tendency" ng-controller="IndexCtrl" style='min-height: 300px'>
    <div class="row">
        <div class="col-sm-3">
            <div class="form-group">
                <?= Branches::buildMultiSelector($Request->branches, [
                    "id" 	=> "request-branches",
                    "ng-model"	=> "search.branches",
                ], "филиалы") ?>
            </div>
        </div>
        <div class="col-sm-3">
            <?= Subjects::buildMultiSelector($Request->subjects, ["id" => "subjects", "ng-model" => "search.subjects"], 'three_letters') ?>
        </div>
        <div class="col-sm-3">
            <?= Grades::buildMultiSelector(false, ['id' => 'grades', 'ng-model' => 'search.grades']) ?>
        </div>
        <div class="col-sm-3">
            <div class="btn full-width btn-primary" ng-click="go()">ОК</div>
        </div>
    </div>
    <div ng-show='count !== undefined' style='margin-top: 50px'>
        <div>
            <b>найдено заявок: {{ count }}</b>
        </div>
        <div>
            <b>кол-во договоров: {{ contracts_count }}</b>
        </div>
        <div>
            <b>сумма платежей: {{ payments_sum | number }} руб.</b>
        </div>
    </div>
    <div style="padding: 100px"  ng-show="loading" class="small center half-black">
        загрузка...
    </div>
</div>
