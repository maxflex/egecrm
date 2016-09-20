<div class="lightbox-new lightbox-freetime">
	<h4 style="text-transform: uppercase; margin-bottom: 25px">ДАТА И ВРЕМЯ ЗАНЯТИЙ</h4>
		<table class="table table-divlike">
			<tbody>
				<tr ng-repeat='(day, data) in time'>
					<td><b>{{ weekdays[day] }}</b></td>
					<td ng-repeat='d in data' style="text-align: center">
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
									ng-class="{'half-opacity': free_cabinets[d.id][cabinet.id]}"
									style='color: {{ cabinet.color }}'
									ng-selected="getGroupTime(day, d).id_cabinet == cabinet.id">
									{{ cabinet.label}}
								</option>
							</select>
						</span>

						<!-- заглушка -->
						<span ng-show='!timeChecked(day, d)'>
							<select class='branch-cabinet' disabled>
								<option selected value=''>кабинет</option>
							</select>
						</span>
					</td>
				</tr>
			</tbody>
		</table>
	<div class="center" style="margin-top: 10px">
		<button class="btn btn-primary" ng-click="saveDayAndTime()">Сохранить</button>
	</div>
</div>
