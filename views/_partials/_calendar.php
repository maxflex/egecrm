<?php if (! isset($active)) :?>
<?php endif ?>
<span ng-init="_year = Group ? Group.year : current_year"></span>
<div class='ng-hide' ng-repeat="month in months" ng-show='calendarLoaded && displayMonth[month]'>
    <div class='calendar-block'>
        <div class='calendar-block-month'>
            {{ monthName(month) }}
            <div>
                {{ (month <= 8 ? _year + 1 : _year) }}
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
