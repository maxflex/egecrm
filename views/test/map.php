<div ng-app="Test" ng-controller="MapCtrl">
	<map zoom="10" disable-default-u-i="true" scale-control="true" zoom-control="true" zoom-control-options="{style:'SMALL'}" style="height: 500px">
		<transit-layer></transit-layer>
		<custom-control position="TOP_RIGHT" index="1">
		<div class="input-group gmap-search-control">
	      <input type="text" id="map-search" class="form-control" ng-keyup="gmapsSearch($event)" placeholder="Поиск...">
	      <span class="input-group-btn">
		    <button class="btn btn-default" ng-click="gmapsSearch($event)">
		    <span class="glyphicon glyphicon-search no-margin-right"></span>
		    </button>
		  </span>
		</div>
	    </custom-control>
	</map>
</div>