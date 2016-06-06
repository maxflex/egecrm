<div id="user-form" ng-app="Users" ng-controller="CreateCtrl" ng-init="<?= $ang_init_data ?>">
    <?= partial("form") ?>
    <div class="row">
        <div class="col-sm-12 center">
            <button class="btn btn-primary" ng-click="save()" ng-disabled="!requiredFilled()">
                Добавить
            </button>
        </div>
    </div>
</div>