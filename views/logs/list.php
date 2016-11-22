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
            <select class='form-control selectpicker' ng-model='search.type' ng-change='filter()'>
                <option value="" data-subtext="{{ counts.type[''] || '' }}">тип действия</option>
                <option disabled>──────────────</option>
                <option ng-repeat="(id, label) in LogTypes" data-subtext="{{ counts.type[id] || '' }}" value="{{ id }}">{{ label }}</option>
            </select>
        </div>
        <div>
            <select class='form-control selectpicker' ng-model='search.table' ng-change='filter()'>
                <option value="" data-subtext="{{ counts.table[''] || '' }}">таблица</option>
                <option disabled>──────────────</option>
                <option ng-repeat='table in tables' ng-show="counts.table[table]"
                        data-subtext="{{ counts.table[table] || '' }}"
                        value="{{table}}">{{ table }}</option>
            </select>
        </div>
        <div>
            <select class='form-control selectpicker' ng-model='search.column' ng-change='filter()'>
                <option value="" data-subtext="{{ counts.column[''] || '' }}">ячейка</option>
                <option disabled>──────────────</option>
                <option ng-repeat='(column, count) in counts.column' ng-show="count && column"
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
        <?= globalPartial('loading', ['model' => 'logs', 'message' => 'нет логов']) ?>

        <div class="col-sm-12">
            <table class="table reverse-borders" style="font-size: 12px">
                <tr ng-repeat='log in logs'>
                    <td width="8%">
                        {{ log.table }}
                    </td>
                    <td width="8%">
                        {{ LogTypes[log.type] }}
                    </td>
                    <td width="6%">
                        {{ log.row_id }}
                    </td>
                    <td width="120">
                        <span style="color: {{ getUser(log.user_id).color || 'black' }}">{{ getUser(log.user_id).login }}</span>
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
                        {{ formatDateTime(log.created_at) }}
                    </td>
                </tr>
            </table>
        </div>

        <pagination
            ng-show='(logs && logs.length) && (counts.all > <?= Log::PER_PAGE ?>)'
            ng-model="current_page"
            ng-change="pageChanged()"
            total-items="counts.all"
            max-size="10"
            items-per-page="<?= Log::PER_PAGE ?>"
            first-text="«"
            last-text="»"
            previous-text="«"
            next-text="»"
        >
        </pagination>
    </div>
</div>