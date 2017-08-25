<div class="lightbox-new lightbox-freetime">
    <!-- @time-refactored @time-checked -->
	<h4 style="text-transform: uppercase; margin-bottom: 25px">ДАТА И ВРЕМЯ ЗАНЯТИЙ
        <span style='font-size: 12px; font-weight: normal; margin-left: 15px; text-transform: lowercase'>
            <input type="checkbox" ng-model="Group.is_dump" ng-true-value='1' ng-false-value='0'> болото
        </span>
    </h4>
        <?php if (! allowed(Shared\Rights::EDIT_GROUPS)) :?>
        <div class='div-blocker'></div>
        <?php endif ?>
        <div ng-show="Group.is_dump">
            <span>
                <select class='branch-cabinet'
                    ng-model="getGroupTime(1, time[1][0]).id_cabinet"
                >
                    <option selected value=''>кабинет</option>
                    <option disabled>──────────────</option>
                    <option ng-repeat='cabinet in all_cabinets' value="{{ cabinet.id }}"
                        style='color: {{ cabinet.color }}'
                        ng-selected="getGroupTime(1, time[1][0]).id_cabinet == cabinet.id">
                        {{ cabinet.label}}
                    </option>
                </select>
            </span>
        </div>
		<table class="table table-divlike" ng-show="!Group.is_dump">
			<tbody>
				<tr ng-repeat='(day, data) in time'>
					<td style="width: 100px"><b>{{ weekdays[day] }}</b></td>
					<td ng-repeat='d in data' style="text-align: left; width: 200px; padding-left: 15px">
						<input type="checkbox"
							ng-checked='timeChecked(day, d)'
							ng-click="timeClick(day, d)"
						>
						{{ d.time }}

						<span ng-show='timeChecked(day, d)'>
							<select class='branch-cabinet'
								ng-model='getGroupTime(day, d).id_cabinet'
							>
								<option selected value=''>кабинет</option>
								<option disabled>──────────────</option>
							  	<option ng-repeat='cabinet in all_cabinets' value="{{ cabinet.id }}"
									ng-class="{'quater-opacity': free_cabinets[d.id][cabinet.id]}"
									style='color: {{ cabinet.color }}'
									ng-selected="getGroupTime(day, d).id_cabinet == cabinet.id">
									{{ cabinet.label}}
								</option>
							</select>
						</span>
					</td>
				</tr>
			</tbody>
		</table>
    <?php if (User::fromSession()->allowed(Shared\Rights::EDIT_GROUPS)) :?>
	<div class="center" style="margin-top: 10px">
		<button class="btn btn-primary" ng-click="saveDayAndTime()">Сохранить</button>
	</div>
    <?php endif ?>
</div>
