<div ng-app='Map' ng-controller='IndexCtrl' ng-init="<?= $ang_init_data ?>">
    <div class="row flex-list">
        <div>
            <?= Branches::buildSvgSelector([], [
                "ng-model" => "search.include_branches",
                "id" => "include-branches",
            ], true) ?>
        </div>
        <div>
            <?= Branches::buildSvgSelector([], [
                "ng-model" => "search.exclude_branches",
                "id" => "exclude-branches",
            ], true) ?>
        </div>
        <div>
            <select id='subjects-select' multiple class="watch-select form-control single-select" ng-model="search.subjects">
                <option
                    ng-repeat="(id_subject, name) in Subjects"
                    value="{{id_subject}}">{{ name }}</option>
            </select>
        </div>
        <div>
            <select class="watch-select form-control search-grades" ng-model="search.grades" multiple none-selected-text='классы'>
                <option ng-hide='grade < 8' ng-repeat="(grade, label) in Grades | toArray" value="{{(grade + 1)}}">{{label}}</option>
            </select>
        </div>
        <div>
            <select class="watch-select single-select form-control" ng-model="search.year" >
                <option ng-repeat="year in <?= Years::json() ?>"
                    data-subtext="{{ counts.year[year] || '' }}"
                    value="{{year}}">{{ yearLabel(year) }}</option>
            </select>
        </div>
        <div style='margin-right: 0'>
            <button class="btn btn-primary full-width" ng-click="initMap()">показать</button>
        </div>
    </div>
    <div class="row">
        <div class="col-sm-12">
            <div id='gmap'></div>
        </div>

    </div>
</div>
<style>
#gmap {
    height: 700px;
    width: 100%;
    margin-top: 20px;
}
</style>