<div ng-app="Users" ng-controller="ListCtrl" ng-init="<?= $ang_init_data ?>">
    <div class="row mb">
        <div class="col-sm-4">
            <select class="watch-select single-select form-control" ng-model="right" ng-change='filter()'>
                <option value=""  data-subtext="{{ getCounts() }}">права доступа</option>
                <option disabled>──────────────</option>
                <option ng-repeat="id_right in Groups['COMMON']" ng-value='id_right' data-subtext="{{ getCounts(id_right) }}">{{ Rights[id_right] }}</option>
                <option ng-repeat="id_right in Groups['EGECRM']" ng-value='id_right' style="color: #337ab7" data-subtext="{{ getCounts(id_right) }}">{{ Rights[id_right] }}</option>
                <option ng-repeat="id_right in Groups['EGEREP']" ng-value='id_right' style="color: #158E51" data-subtext="{{ getCounts(id_right) }}">{{ Rights[id_right] }}</option>
            </select>
        </div>
    </div>
    <div class="row">
        <div class="col-sm-12">
            <table class='table table-hover border-reverse table-small' style='margin-bottom: 0'>
                <tr ng-repeat='User in ActiveUsers'>
                    <td width='300'>
                        <a href="users/edit/{{ User.id }}">{{ User.login }}</a>
                    </td>
                    <td style='padding: 0'>
                        <label class="ios7-switch" ng-show='right' style='margin: 0; font-size: 20px'>
                            <input type="checkbox" ng-click='toggleRights(User, right)' ng-checked='allowed(User, right)'>
                            <span class="switch"></span>
                        </label>
                    </td>
                </tr>
            </table>
            <fieldset ng-hide='show_banned' class="hidden-thoughts" id="hidden-teachers-button" style='margin-top: 26px'>
    		    <legend ng-click="show_banned = true">показать всех</legend>
    		</fieldset>
            <table ng-show='show_banned' class='table table-hover border-reverse table-small'>
                <tr ng-repeat='User in BannedUsers'>
                    <td width='300'>
                        <a href="users/edit/{{ User.id }}" class="comment-time">{{ User.login }}</a>
                    </td>
                    <td style='padding: 0'>
                        <label class="ios7-switch" ng-show='right' style='margin: 0; font-size: 20px'>
                            <input type="checkbox" ng-click='toggleRights(User, right)' ng-checked='allowed(User, right)'>
                            <span class="switch"></span>
                        </label>
                    </td>
                </tr>
            </table>
        </div>
    </div>
</div>
