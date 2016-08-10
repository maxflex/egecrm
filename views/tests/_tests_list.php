<div ng-if="current_tab == 'tests'">
    <div ng-show="tests_loading" style="padding: 100px" class="small half-black center">
        загрузка тестов...
    </div>
    <div ng-show="Tests[current_tab] === false" style="padding: 100px" class="small half-black center">
        нет тестов
    </div>

    <table class="table table-divlike">
        <tr ng-repeat="Test in Tests[current_tab]">
            <td>
                <a href="tests/edit/{{Test.id}}">{{Test.name}}</a>
            </td>
        </tr>
    </table>
</div>