<style>
    #year-fix .dropdown-menu:last-child {
        left: -20px;
    }
    .row.flex-list > div {
        width: 10%;
    }
</style>

<div ng-app='Logs' ng-controller='ListCtrl' ng-init="<?= $ang_init_data ?>">
    <div class="row flex-list">
        <div>
            <select class="form-control selectpicker" ng-model='search.user_id' ng-change="filter()" id='change-user'>
                <option value="" data-subtext="{{ counts.user[''] || '' }}">пользователь</option>
                <option disabled>──────────────</option>
                <option
                    ng-repeat="user in users"
                    value="{{ user.id }}"
                    data-content="<span style='color: {{ user.color || 'black' }}'>{{ user.login }}</span><small class='text-muted'>{{ counts.user[user.id] || '' }}</small>"
                ></option>
            </select>
        </div>
        <div>
            <select ng-model='search.type' class='selectpicker' ng-change='filter()'>
                <option value="" data-subtext="{{ counts.type[''] || '' }}">тип действия</option>
                <option disabled>──────────────</option>
                <option ng-repeat="(id, label) in LogTypes" data-subtext="{{ counts.type[id] || '' }}" value="id">{{ label }}</option>
            </select>
        </div>
        <div>
            <select ng-model='search.table' class='selectpicker' ng-change='filter()'>
                <option value="" data-subtext="{{ counts.table[''] || '' }}">таблица</option>
                <option disabled>──────────────</option>
                <option ng-repeat='table in tables' ng-show="counts.table[table]"
                        data-subtext="{{ counts.table[table] || '' }}"
                        value="{{table}}">{{ table }}</option>
            </select>
        </div>
        <div>
            <select ng-model='search.column' class='selectpicker' ng-change='filter()'>
                <option value="" data-subtext="{{ counts.column[''] || '' }}">ячейка</option>
                <option disabled>──────────────</option>
                <option ng-repeat='column in LogColumns' ng-show="counts.column[column]"
                        data-subtext="{{ counts.column[column] || '' }}"
                        value="{{column}}">{{ column }}</option>
            </select>
        </div>
        <div>
            <div class="form-group">
                <div class="input-group custom">
                    <span class="input-group-addon">начало –</span>
                    <input type="text" readonly ng-change='filter()'
                            class="form-control bs-date-clear pointer" ng-model="search.date_start">
                </div>
            </div>
        </div>
        <div>
            <div class="form-group">
                <div class="input-group custom">
                    <span class="input-group-addon">конец –</span>
                    <input type="text" readonly ng-change='filter()'
                           class="form-control bs-date-clear pointer" ng-model="search.date_end">
                </div>
            </div>
        </div>
        <div>
            <div class="form-group">
                <div class="input-group custom">
                    <span class="input-group-addon">ID –</span>
                    <input type="text" ng-keyup='keyFilter($event)' class="form-control" ng-model="search.row_id">
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-sm-12">
            <table class="table reverse-borders" style="font-size: 12px">
                <tr ng-repeat='log in logs'>
                    <td>
                        {{ log.table }}
                    </td>
                    <td>
                        {{ LogTypes[log.type] }}
                    </td>
                    <td width="100">
                        <span style="color: {{ users[log.user_id].color || 'black' }}">{{ users[log.user_id].login }}</span>
                    </td>
                    <td>
                        <table style="font-size: 12px">
                            <tr ng-repeat="(key, data) in log.data track by $index">
                                <td style="vertical-align: top; width: 150px">{{ key }}</td>
                                <td>
                                    <span class="text-gray">{{ data[0]  }}</span>
                                    <span class='text-gray'>⟶</span>
                                    <span>{{ data[1] }}</span>
                                </td>
                            </tr>
                        </table>
                    </td>
                    <td>
                        <span style="white-space: nowrap">{{ formatDateTime(log.created_at) }}</span>
                    </td>
                </tr>
            </table>
        </div>
    </div>
</div>