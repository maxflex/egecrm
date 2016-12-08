<div class="panel panel-primary" ng-app="Users" ng-controller="EditCtrl" ng-init="<?= $ang_init_data ?>">
	<div class="panel-heading">
        <div class='row'>
            <div class="col-sm-4">
                Редактирование пользователя {{ User.login }}
            </div>
            <div class='col-sm-4 center'>
                <span class='link-like link-white link-reverse' ng-click="save()" ng-hide="User.login.length == 0 || has_pswd_error">
                    <span ng-show="form_changed">cохранить</span>
                    <span ng-show="!form_changed">cохранено</span>
                </span>
            </div>
        </div>
    </div>
    <div class="panel-body">
        <div id="user-form">
            <?= partial("form") ?>
        </div>
    </div>
</div>
