<div ng-app="Group" ng-controller="ListCtrl"
	ng-init="<?= $ang_init_data ?>">
		
	<div class="row" style="position: relative">
		<div id="frontend-loading"></div>
		<div class="col-sm-12">
			
			<table class="table table-divlike">
			<?php foreach ($Groups as $Group): ?>
				<tr>
					<td><a href="groups/edit/<?= $Group->id ?>">Группа №<?= $Group->id ?></a></td>
					<td>
						<?php if ($Group->id_branch): ?>
							<?= Branches::metroSvg($Group->id_branch) . Branches::getById($Group->id_branch) ?>
						<?php endif ?>
					</td>
					<td><?= Subjects::getById($Group->id_subject) ?></td>
					<td>
						<?php if ($Group->id_teacher): ?>
							<?= $Group->Teacher->getInitials() ?>
						<?php endif ?>
					</td>
					<td><?= count($Group->students) ?> <?= pluralize('ученик', 'ученика', 'учеников', count($Group->students)) ?></td>
				</tr>
			<?php endforeach ?>
			</table>
			
		</div>
	</div>
</div>
