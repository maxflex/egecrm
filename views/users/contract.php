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
                <div class="contract-content" ng-bind-html="contract_html"></div>
            </div>
        </div>
    </div>
</div>
<style>
    .contract-content {
        text-indent: 20px; }

    .contract-table td {
        padding:5px;
        width:17%;
        border: 1px solid black;
        border-collapse: collapse;
    }
    .contract-table td:first-child {
        width:35%;
    }
    .contract-table .head td {
        background:#63b2de;
    }
</style>