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
                        <a href="users/edit/{{ User.id }}" ng-class="{'comment-time':(!allowed(User, right) && right)}">{{ User.login }}</a>
                    </td>
                    <td>
                        <label class="ios7-switch" ng-show='right'>
                            <input type="checkbox" ng-click='toggleRights(User, right)' ng-checked='allowed(User, right)'>
                            <span class="switch"></span>
                        </label>
                    </td>
                </tr>
            </table>
        </div>
    </div>
</div>
