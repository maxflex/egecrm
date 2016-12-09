<div class="row">
    <label class="ios7-switch">
        <input type="checkbox" ng-click='toggleRights(<?= $right ?>)' ng-checked='allowed(<?= $right ?>)'>
        <span class="switch"></span>
        <span class='title'><?= Shared\Rights::$all[$right] ?></span>
    </label>
</div>
