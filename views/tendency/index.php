<div ng-app="Tendency" ng-controller="IndexCtrl">
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
    <div style='min-height: 100px' ng-show='count !== undefined'>
        <div>
            <b>найдено заявок: {{ count }}</b>
        </div>
        <div>
            <table class='table table-divlike'>
                <tr>
                    <td>
                        ID договора
                    </td>
                    <td>
                        кол-во заявок
                    </td>
                </tr>
                <tr ng-repeat='(id_contract, cnt) in contracts'>
                    <td>
                        {{ id_contract }}
                    </td>
                    <td>
                        {{ cnt }}
                    </td>
                </tr>
            </table>
        </div>
    </div>
    <b ng-show='count !== undefined'>найдено заявок: {{ count }}</b>
    <div style="padding: 100px"  ng-show="loading" class="small center half-black">
        загрузка...
    </div>
</div>
