<div id="contract-print-{{contract.id}}" class="printable"> 
    <h1 style="text-align: center">Договор</h1>
    <div>
    	<b>Имя ученика:</b> {{student.last_name}} {{student.first_name}} {{student.middle_name}}
    </div>
    <div>
    	<b>Дата заключения:</b> {{contract.date}}
    </div>
    <div>
    	<b>Сумма:</b> {{contract.sum}} руб.
    </div>
    
	<table style="width: 100%; margin-top: 20px">
		<thead>
			<tr>
				<td style="font-weight: bold" width="300px">предмет</td>
				<td style="font-weight: bold">занятий</td>
			</tr>
		</thead>
		<tbody>
			<tr ng-repeat="subject in contract.subjects">
				<td>{{subject.name}}</td>
				<td>{{subject.count}}</td>
			</tr>
		</tbody>
	</table>
</div>