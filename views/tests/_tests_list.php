    <table class="table table-divlike">
        <tr ng-repeat="Test in Tests">
            <td>
                <a href="tests/edit/{{Test.id}}">{{Test.name}}</a>
            </td>
        </tr>
    </table>
