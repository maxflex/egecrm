123<select ng-model="Schedule.cabinet" style="width: 130px" ng-change="changeCabinet(Schedule)">
    <option selected value="">выберите кабинет</option>
    <option disabled>──────────────</option>
    <option ng-repeat="Cabinet in Cabinets" value="{{Cabinet.id}}" ng-selected="Cabinet.id == Schedule.cabinet">
        {{Cabinet.number}}
    </option>
</select>