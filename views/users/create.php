<div class="panel panel-primary" ng-app="Users" ng-controller="CreateCtrl" ng-init="<?= $ang_init_data ?>">
	<div class="panel-heading">
        <div class="row">
            <div class="col-sm-4">
                Добавление нового пользователя
            </div>
            <div class="col-sm-4 center">
                <span class="btn btn-primary" ng-click="save()" ng-hide="!requiredFilled()">
                    добавить
                </span>
            </div>
            <div class="col-sm-4">
                <a href="users" class="pull-right link-reverse link-white">к списку пользователей</a>
            </div>
        </div>
    </div>
    <div class="panel-body">
        <div id="user-form" >
            <?= partial("form") ?>
        </div>
    </div>
</div>
