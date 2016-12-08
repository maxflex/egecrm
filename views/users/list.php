<div ng-app="Users" ng-controller="ListCtrl" ng-init="<?= $ang_init_data ?>">
    <div class="row mb">
        <div class="col-sm-4">
            <select class="watch-select single-select form-control" ng-model="right" ng-change='filter()'>
				<option value=""  data-subtext="{{ getCounts() }}">права доступа</option>
				<option disabled>──────────────</option>
				<option ng-repeat='(id_right, title) in Rights' ng-value='id_right' data-subtext="{{ getCounts(id_right) }}">
                    {{ title }}
                </option>
			</select>
        </div>
    </div>
    <div class="row">
        <div class="col-sm-12">
            <table class='table table-hover border-reverse'>
                <tr ng-repeat='User in Users'>
                    <td width='300'>
                        <a href="users/edit/{{ User.id }}">{{ User.login }}</a>
                    </td>
                    <td>
                        <span ng-show='right' ng-click='toggleRights(User, right)'>
                            <span class='link-like'   ng-show='allowed(User, right)'>да</span>
                            <span class='link-like text-danger' ng-show='!allowed(User, right)'>нет</span>
                        </span>
                    </td>
                </tr>
            </table>
        </div>
    </div>
</div>
