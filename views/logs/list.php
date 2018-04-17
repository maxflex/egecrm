<style>
    #year-fix .dropdown-menu:last-child {
        left: -20px;
    }
    .row.flex-list > div {
        width: 10%;
    }
</style>
<link href="https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css" rel="stylesheet" integrity="sha384-wvfXpqpZZVQGK6TAh5PVlGOfQNHSoD2xbE+QkPxCAFlNEevoEH3Sl0sibVcOQVnN" crossorigin="anonymous">
<div ng-app='Logs' ng-controller='ListCtrl' ng-init="<?= $ang_init_data ?>">
    <div class="row flex-list">
        <div>
            <select ng-highlight class="form-control selectpicker" ng-model='search.user_id' ng-change="filter()" id='change-user'>
                <option value=''>пользователь</option>
            	<option disabled>──────────────</option>
            	<option
            		ng-repeat="user in UserService.getActiveInAnySystem()"
            		value="{{ user.id }}"
            		data-content="<span style='color: {{ user.color || 'black' }}'>{{ user.login }}</span>"
            	></option>
            	<option disabled>──────────────</option>
            	<option
                    ng-repeat="user in UserService.getBannedInBothSystems()"
            		value="{{ user.id }}"
            		data-content="<span style='color: black'>{{ user.login }}</span>"
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
                <option ng-repeat='(table, data) in tables'
                        data-subtext="{{ counts.table[table] || '' }}"
                        value="{{table}}">{{ table }}</option>
            </select>
        </div>
        <div>
            <select class='form-control selectpicker' ng-model='search.column' ng-change='filter()'>
                <option value="" data-subtext="{{ counts.column[''] || '' }}">ячейка</option>
                <option disabled>──────────────</option>
                <option ng-repeat='column in tables[search.table]'
                        value="{{ column }}">{{ column }}</option>
            </select>
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
                        {{ LogTypes[log.type] || log.type }}
                    </td>
                    <td width="6%">
                        {{ log.row_id }}
                    </td>
                    <td width="120">
                        <span ng-if="log.user_id !== null">
                            <span ng-show="log.user.type == 'USER'" style="color: {{ UserService.getColor(log.user_id) }}">{{ log.user.login }}</span>
                            <span ng-show="log.user.type == 'REPRESENTATIVE'">{{ log.user.email }}</span>
                            <a ng-show="log.user.type == 'TEACHER'" href='/teachers/edit/{{ log.user.id_entity }}' target="_blank">{{ log.teacher.last_name }} {{ log.teacher.first_name[0] }}. {{ log.teacher.middle_name[0] }}.</a>
                            <a ng-show="log.user.type == 'STUDENT'" href='/student/{{ log.user.id_entity }}' target="_blank">ученик №{{ log.user.id_entity }}</a>
							<i ng-show="log.view_mode_user_id > 0" class="fa fa-eye" aria-hidden="true" title="{{ log.view_mode_user }}"></i>
							<!-- <div ng-show="log.view_mode_user_id > 0" class="text-gray">
								<span>⟶</span>
								<span class="text-gray">{{ log.view_mode_user }}</span>
							</div> -->
                        </span>
                    </td>
                    <td>
                        <table style="font-size: 12px">
                            <tr ng-repeat="(key, data) in log.data track by $index">
                                <td style="vertical-align: top; width: 150px">{{ key }}</td>
                                <td class="text-gray">
                                    <span ng-if="log.row_id">
                                        <span>{{ data[0]  }}</span>
                                        <span>⟶</span>
                                        <span style='color: black'>{{ data[1] }}</span>
                                    </span>
                                    <span ng-if="!log.row_id">
                                        {{ data }}
                                    </span>
                                </td>
                            </tr>
                        </table>
                    </td>
                    <td width="10%">
                        {{ formatDateTime(log.created_at) }}
                    </td>
                </tr>
            </table>
        </div>

        <div class="col-sm-12">
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
</div>
