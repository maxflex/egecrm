<div ng-app="Users" ng-controller="ListCtrl" ng-init="<?= $ang_init_data ?>">
<!--	<table class="table table-divlike" id="user-list">-->
<!--        <tr class="row">-->
<!--            <td colspan="5"></td>-->
<!--            <td align="center"><span class="glyphicon glyphicon-earphone"></span></td>-->
<!--            <td align="center"><span class="glyphicon glyphicon-ban-circle"></span></td>-->
<!--        </tr>-->
<!--		<tr ng-repeat="User in Users" class="row">-->
<!--			<td>-->
<!--                <a href="users/edit/{{ User.id }}">{{ User.login }}</a>-->
<!--			</td>-->
<!--			<td>-->
<!--				<input class="form-control" ng-model="User.login" placeholder="логин">-->
<!--			</td>-->
<!--			<td>-->
<!--				<input class="form-control" placeholder="пароль" type="password" ng-model="User.new_password">-->
<!--			</td>-->
<!--			<td>-->
<!--				<input class="form-control" ng-model="User.color" style="background-color: {{User.color}}; color: white" placeholder="цвет">-->
<!--			</td>-->
<!--			<td>-->
<!--				<input class="form-control" ng-model="User.agreement" placeholder="соглашение">-->
<!--			</td>-->
<!--<!---->
<!--			<td>-->
<!--				<label class="ios7-switch" style="font-size: 24px; top: 1px; margin: 0">-->
<!--					<input type="checkbox" ng-model="User.worktime" ng-true-value="1">-->
<!--					<span class="switch"></span>-->
<!--				</label>-->
<!--			</td>-->
<!---->
<!--            <td align="center">-->
<!--                <label class="green-switch ios7-switch" style="font-size: 24px; top: 1px; margin: 0">-->
<!--                    <input type="checkbox" ng-model="User.show_phone_calls" ng-true-value="1">-->
<!--                    <span class="switch"></span>-->
<!--                </label>-->
<!--			</td>-->
<!--			<td align="center">-->
<!--				<label class="red-switch ios7-switch" style="font-size: 24px; top: 1px; margin: 0">-->
<!--					<input type="checkbox" ng-model="User.banned" ng-true-value="1">-->
<!--					<span class="switch"></span>-->
<!--				</label>-->
<!--			</td>-->
<!--		</tr>-->
<!--	</table>-->
<!--	<div class="row">-->
<!--		<div class="col-sm-12 center">-->
<!--			<button class="btn btn-primary" ng-click="save()" ng-disabled="!form_changed">-->
<!--				<span ng-show="form_changed">Сохранить</span>-->
<!--				<span ng-show="!form_changed">Сохранено</span>-->
<!--			</button>-->
<!--		</div>-->
<!--	</div>-->

    <table class="table table-divlike" id="user-list">
        <tr class="row first">
            <td colspan="3"><b>Активные пользователи</b></td>
        </tr>
        <tr ng-repeat="User in Users | filter:isnot_banned" class="row">
            <td colspan="2"></td>
            <td>
                <a href="users/edit/{{ User.id }}">{{ User.login }}</a>
            </td>
        </tr>
        <tr class="row">
            <td colspan="3"><b>Заблокированные пользователи</b></td>
        </tr>
        <tr ng-repeat="User in Users | filter:is_banned" class="row">
            <td class="ban-ico egecrm-banned">
                <span class="glyphicon glyphicon-lock small" ng-show="User.banned"></span>
            <td class="ban-ico egerep-banned">
                <span class="glyphicon glyphicon-lock small" ng-show="User.banned_egerep"></span>
            </td>
            <td>
                <a href="users/edit/{{ User.id }}">{{ User.login }}</a>
            </td>
        </tr>
    </table>
</div>

<style>

	.table-divlike tr.first td b {
        padding: 5px 0 10px!important;
    }
	.table-divlike tr td b {
        padding: 25px 0 10px!important;
        display: inline-block;
    }
	.table-divlike tr td {
        padding: 2px 15px!important;
        line-height: 1.42857143;
	    vertical-align: top;
	}
</style>