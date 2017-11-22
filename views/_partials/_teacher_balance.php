<div ng-show="Lessons">
    <div class="top-links">
        <span ng-click="setYear(year)" class="link-like" ng-class="{'active': year == selected_year}" ng-repeat="year in years">{{ yearLabel(year) }}</span>
    </div>
    <table class="table balance table-hover reverse-borders">
        <tbody ng-repeat="(date, items) in Lessons[selected_year]">
            <tr>
                <td colspan="3"></td>
                <td>{{ daySum(items) | number}} руб.</td>
                <td>

                </td>
            </tr>
            <tr ng-repeat="item in items" ng-class="{'last-date': $last}">
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
            </tr>
        </tbody>
    </table>
</div>