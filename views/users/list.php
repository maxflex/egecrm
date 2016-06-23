<div ng-app="Users" ng-controller="ListCtrl" ng-init="<?= $ang_init_data ?>">
    <table class="table table-divlike" id="user-list">
        <tr class="row first">
            <td colspan="3"><b>Активные пользователи</b></td>
        </tr>
        <tr ng-repeat="User in Users | filter:isnot_banned" class="row">
            <td colspan="2"></td>
            <td style="padding-left: 10px !important">
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
            <td style="padding-left: 10px !important">
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