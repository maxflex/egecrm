<div ng-show="Lessons">
    <div class="top-links">
        <span ng-click="setYear(year)" class="link-like" ng-class="{'active': year == selected_year}" ng-repeat="year in years">{{ yearLabel(year) }}</span>
    </div>
    <table class="table gray-headers balance table-hover reverse-borders">
        <thead>
            <td>
                дата
            </td>
            <td>
                начисления
            </td>
            <td>
                списания
            </td>
            <td>
                остаток
            </td>
            <td>
                комментарий
            </td>
            <td>
                <?php if ($credentials) :?>
                реквизиты
                <?php endif ?>
            </td>
        </thead>
        <tbody ng-repeat="date in reverseObjKeys(Lessons[selected_year])">
            <tr>
                <td colspan="3"></td>
                <td>{{ totalSum(date) | number}} руб.</td>
                <td colspan="2"></td>
            </tr>
            <tr ng-repeat="item in Lessons[selected_year][date] | orderBy:'date':true" ng-class="{'last-date': $last}">
                <td width='120'>
                    <span ng-show="$last">{{ date | date:'dd.MM.yyyy' }}</span>
                </td>
                <td width='120'>
                    <span ng-show="item.sum >= 0" class="text-success">+{{ item.sum | number }} руб.</span>
                </td>
                <td width='120'>
                    <span ng-show="item.sum < 0" class="text-danger">{{ item.sum | number }} руб.</span>
                </td>
                <td width='120'>

                </td>
                <td>
                    {{ item.comment }}
                </td>
                <td class="text-gray">
                    <?php if ($credentials) :?>
                    <span class="item-credentials">{{ item.credentials }}</span>
                    <?php endif ?>
                </td>
            </tr>
        </tbody>
    </table>
</div>