<table class="table table-hover" ng-app="Rating" ng-controller="MainCtrl" ng-init="<?= $ang_init_data ?>">
	<thead style="font-weight: bold">
		<tr>
			<td style="width: 50%">Филиал</td>
			<td style="width: 35%"></td>
			<td>Рейтинг</td>
		</tr>
	</thead>
	
	<?php foreach($rating as $id_branch => $score): ?>
		<?php if ($rdata[$id_branch]['actual'] || $rdata[$id_branch]['prognoz']): ?>
		<tr>
			<td onclick="redirect('rating/<?= $id_branch ?>')" class="pointer">
				<?= Branches::metroSvg($id_branch) ?><?= Branches::$all[$id_branch] ?>
			</td>
			<td>
				<span ng-repeat="branch_load in BranchLoad[<?= $id_branch ?>] track by $index" ng-click="changeLoad(<?= $id_branch ?>, $index)">
					<svg xmlns="http://www.w3.org/2000/svg" version="1.1" class="svg-metro rating">
	            		<circle r="6" cx="7" cy="7" class="branch-load-{{branch_load.color}}"></circle>
					</svg>
				</span>
				<svg xmlns="http://www.w3.org/2000/svg" version="1.1" class="svg-metro rating" ng-hide="BranchLoad[<?= $id_branch ?>].length >= 4">
            		<circle fill="transparent" r="6" cx="7" cy="7" stroke="#C0C0C0" stroke-width="1" ng-click="addLoad(<?= $id_branch ?>)"></circle>
				</svg>
			</td>
			<td>
				<?= ($rdata[$id_branch]['actual'] ? $rdata[$id_branch]['actual'] : 0) ?>
				<?php if ($rdata[$id_branch]['prognoz']): ?>
					<span class="quater-black"> +
						<?= ($rdata[$id_branch]['prognoz'] ? $rdata[$id_branch]['prognoz'] : 0) ?>
					</span>
				<?php endif ?>
			</td>
		</tr>
		<?php endif ?>
	<?php endforeach; ?>
</table>