<div style='position: relative'>
	<div id="frontend-loading"></div>
		<?= globalPartial('reviews') ?>
	</div>

<pagination
	ng-show='(Reviews && Reviews.length) && (counts.all > <?= TeacherReview::PER_PAGE ?>)'
	ng-model="current_page"
	ng-change="pageChanged()"
	total-items="counts.all"
	max-size="10"
	items-per-page="<?= TeacherReview::PER_PAGE ?>"
	first-text="«"
	last-text="»"
	previous-text="«"
	next-text="»"
>
</pagination>

<div ng-show="Reviews === undefined" style="padding: 100px" class="small half-black center">
	загрузка отзывов...
</div>
<div ng-show="Reviews === null" style="padding: 100px" class="small half-black center">
	нет отзывов
</div>