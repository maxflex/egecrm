<div ng-repeat="request in requests | filter:{adding : 0}" style="margin-bottom: 10px">
	<a class="request-line-link" href="requests/edit/{{request.id}}">Заявка #{{request.id}}</a>
	<span ng-show="request.duplicates.length > 0" class="label label-success">повтор</span>
</div>