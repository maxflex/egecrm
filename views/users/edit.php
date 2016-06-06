<div id="user-form" ng-app="Users" ng-controller="EditCtrl" ng-init="<?= $ang_init_data ?>">
    <?= partial("form") ?>
    <div class="row">
        <div class="col-sm-12 center">
            <button class="btn btn-primary" ng-click="save()" ng-disabled="!form_changed || User.login.length == 0 || has_pswd_error">
                <span ng-show="form_changed">Сохранить</span>
                <span ng-show="!form_changed">Сохранено</span>
            </button>
        </div>
    </div>
</div>