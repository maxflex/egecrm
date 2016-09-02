<div class="panel panel-primary form-change-control" ng-app="Users" ng-controller="ContractCtrl" ng-init="<?= $ang_init_data ?>">
    <div class="panel-heading">
        Договор
    </div>
    <div class="panel-body">
        <div class="row">
            <div class="col-sm-12 mb">
                Договор от {{ contract_date }}
            </div>
            <div class="col-sm-12">
                {{ contract_html }}
            </div>
        </div>
    </div>
</div>