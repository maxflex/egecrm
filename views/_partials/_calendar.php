<?php if (! isset($active)) :?>
<div class="div-blocker"></div>
<?php endif ?>
<div class='ng-hide' ng-repeat="month in months" ng-show='calendarLoaded'>
    <h4>{{ monthName(month) }}</h4>
    <mwl-calendar ng-if='viewDate[month]'
        view="'month'"
        vacation-dates="vocation_dates"
        view-date="viewDate[month]"
        events="events[month]"
        view-title="calendarTitle">
    </mwl-calendar>
</div>
