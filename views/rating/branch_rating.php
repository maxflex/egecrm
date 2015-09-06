<div ng-app="Rating" ng-controller="MainCtrl" ng-init="<?= $ang_init_data ?>">
	<div class="top-links">
		<a ng-repeat="(id_branch, name) in Branches" href="rating/{{id_branch}}"
			ng-class="{'active': id_branch == <?= $id_branch ?>}">{{name}}</a>
	</div>
	<div class="top-links">
		<span class="link-like" ng-click="setRating(1)" ng-class="{'active': rating_type == 1 || !rating_type}">Средневероятностные</span>
		<span class="link-like" ng-click="setRating(2)" ng-class="{'active': rating_type == 2}">Уникальные</span>
		<span class="link-like" ng-click="setRating(3)" ng-class="{'active': rating_type == 3}">Максимально возможные</span>
	</div>
	<hr style="visibility: hidden">
	<table class="table table-hover">
		<thead>
			<tr>
				<td></td>
				<?php for ($i = 9; $i <= 11; $i++): ?>
					<td><?= $i ?> класс</td>
				<?php endfor ?>
			</tr>
		</thead>
		<tbody>
			<?php foreach (Subjects::$all as $id_subject => $name): ?>
				<tr>
					<td onclick="redirect('rating/subject/<?= $id_subject ?>')" class="pointer"><?= $name ?></td>
					<?php for ($i = 9; $i <= 11; $i++): ?>
						<td>
							<span class="score-field"> 
								<span ng-show="data['<?= $i ?>']['<?= $id_subject ?>']">
									{{data['<?= $i ?>']['<?= $id_subject ?>']}}
								</span>
							</span>
							<span ng-repeat="branch_load in BranchLoad[<?= $i ?>][<?= $id_subject ?>] track by $index" 
								<?php if (in_array(User::fromSession()->id, RatingController::$CAN_EDIT_BRANCHLOAD)): ?>
								ng-click="changeLoadFull(<?= $id_branch ?>, <?= $i ?>, <?= $id_subject ?>, $index)"
								<?php endif ?>
								>
								<svg xmlns="http://www.w3.org/2000/svg" version="1.1" class="svg-metro rating">
				            		<circle r="6" cx="7" cy="7" class="branch-load-{{branch_load.color}}"></circle>
								</svg>
							</span>
							<?php if (in_array(User::fromSession()->id, RatingController::$CAN_EDIT_BRANCHLOAD)): ?>
							<svg xmlns="http://www.w3.org/2000/svg" version="1.1" class="svg-metro rating rating-add" 
								ng-hide="BranchLoad[<?= $i ?>][<?= $id_subject ?>].length >= 4">
			            		<circle fill="transparent" r="6" cx="7" cy="7" stroke="#C0C0C0" stroke-width="1" 
			            			ng-click="addLoadFull(<?= $id_branch ?>, <?= $i ?>, <?= $id_subject ?>)"></circle>
							</svg>
							<?php endif ?>
						</td>
					<?php endfor ?>
				</tr>
			<?php endforeach ?>
			<tr>
				<td>всего</td>
				<?php for ($i = 9; $i <= 11; $i++): ?>
					<td>
						{{sum(data['<?= $i ?>'])}}
					</td>
				<?php endfor ?>
			</tr>
		</tbody>
	</table>
</div>