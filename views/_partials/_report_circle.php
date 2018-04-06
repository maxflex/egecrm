<span class="report-grade-circle" ng-class="{
	'active': Report.<?= $field ?> == 5,
	'bg-orange': Report.<?= $field ?> == 4,
	'bg-red': Report.<?= $field ?> <= 3 && Report.<?= $field ?> >= 1,
	'undefined': !Report.<?= $field ?>
}">
	<span>{{ Report.<?= $field ?> ? Report.<?= $field ?> : '?'}}</span>
</span>
