<?php if (! isset($active)) :?>
<div class="div-blocker"></div>
<?php endif ?>
<div class='ng-hide' ng-repeat="month in months" ng-show='calendarLoaded && displayMonth[month]'>
    <div class='calendar-block'>
        <div class='calendar-block-month'>
            {{ monthName(month) }}
            <div>
                {{ (month <= 8 ? Group.year + 1 : Group.year) }}
            </div>
        </div>
        <div class='calendar-block-calendar'>
            <mwl-calendar ng-if='viewDate[month]'
                view="'month'"
                special-dates="special_dates"
                view-date="viewDate[month]"
                events="events[month]"
                view-title="calendarTitle">
            </mwl-calendar>
        </div>
    </div>
</div>
