<table class="table table-hover" ng-app="Rating" ng-controller="MainCtrl" ng-init="<?= $ang_init_data ?>">
	<thead style="font-weight: bold">
		<tr>
			<td style="width: 80%">Филиал</td>
			<td>Рейтинг</td>
		</tr>
	</thead>
	
	<?php foreach($rating as $id_branch => $score): ?>
		<?php if ($score): ?>
		<tr>
			<td onclick="redirect('rating/<?= $id_branch ?>')" class="pointer">
				<?= Branches::metroSvg($id_branch) ?><?= Branches::$all[$id_branch] ?>
			</td>
			<td>
				<span class="score-field"><?= $score ?></span>
				<span ng-repeat="branch_load in BranchLoad[<?= $id_branch ?>] track by $index" 
				<?php if (in_array(User::fromSession()->id, RatingController::$CAN_EDIT_BRANCHLOAD)): ?>
				ng-click="changeLoad(<?= $id_branch ?>, $index)"
				<?php endif ?>
				>
					<svg xmlns="http://www.w3.org/2000/svg" version="1.1" class="svg-metro rating">
	            		<circle r="6" cx="7" cy="7" class="branch-load-{{branch_load.color}}"></circle>
					</svg>
				</span>
				<?php if (in_array(User::fromSession()->id, RatingController::$CAN_EDIT_BRANCHLOAD)): ?>
				<svg xmlns="http://www.w3.org/2000/svg" version="1.1" class="svg-metro rating rating-add" ng-hide="BranchLoad[<?= $id_branch ?>].length >= 4">
            		<circle fill="transparent" r="6" cx="7" cy="7" stroke="#C0C0C0" stroke-width="1" ng-click="addLoad(<?= $id_branch ?>)"></circle>
				</svg>
				<?php endif ?>
			</td>
		</tr>
		<?php endif ?>
	<?php endforeach; ?>
</table>