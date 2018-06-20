<div ng-app="Stream" ng-controller="Main" ng-init="<?= $ang_init_data ?>">
    <div class="row flex-list">
        <div>
            <select ng-model='search.mobile' class='selectpicker' ng-change='filter()'>
                <option value="" data-subtext="{{ counts.mobile[''] || '' }}">версия</option>
                <option disabled>──────────────</option>
                <option data-subtext="{{ counts.mobile[0] || '' }}" value="0">стационарная</option>
                <option data-subtext="{{ counts.mobile[1] || '' }}" value="1">мобильная</option>
            </select>
        </div>
        <div>
            <select ng-model='search.action' class='selectpicker' ng-change='filter()'>
                <option value="" data-subtext="{{ counts.action[''] || '' }}">действие</option>
                <option disabled>──────────────</option>
                <option ng-repeat='action in actions'
                    data-subtext="{{ counts.action[action] || '' }}"
                    value="{{action}}">{{ action }}</option>
            </select>
        </div>
        <div>
            <select ng-model='search.type' class='selectpicker' ng-change='filter()' none-selected-text='тип действия'>
                <option data-subtext="{{ counts.type[''] || '' }}" value=''>тип действия</option>
                <option disabled>──────────────</option>
                <option ng-repeat='t in types'
                    data-subtext="{{ counts.type[t] || '' }}"
                    value="{{ t }}">{{ t }}</option>
            </select>
        </div>
        <div>
            <div class="form-group">
                <div class="input-group custom">
                  <span class="input-group-addon">клиент –</span>
                  <input type="text" ng-keyup='keyFilter($event)' class="form-control" ng-model="search.google_id">
                </div>
            </div>
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
    </div>


<div class="row">
    <div class="col-sm-12">
        <div class='fe-loading' ng-show="frontend_loading"></div>
        <table id="stream-data" class="table" style="font-size: 0.8em;">
            <thead>
                <td></td>
                <td>клиент</td>
                <td>действие</td>
                <td>тип</td>
                <td>время</td>
            </thead>
            <tbody>
                <tr ng-repeat="s in data.stream">
                    <td class="mobile-cell">
                        <div style='display: inline-block; width: 5px'>
                            <span ng-show="s.mobile" class="glyphicon glyphicon-phone" style='font-size: 12px; top: -1px; position: relative'></span>
                        </div>
                    </td>
                    <td width='15%'>
                        {{ s.google_id }}
                    </td>
                    <td width='15%'>
						<span ng-show="['client_request', 'client_request_attempt'].indexOf(s.action) !== -1" class="glyphicon glyphicon-envelope"></span>
                        <span ng-show='!s.href'>{{ s.action }}</span>
                        <a ng-show='s.href' href='{{ s.href}}' target="_blank">{{ s.action }}</a>
                    </td>
                    <td width='60%'>
                        {{ s.type }}
                    </td>
                    <td width='10%'>
                        {{ formatDate(s.created_at) }}
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

    <pagination style="margin-top: 30px"
        ng-show='data.count && (data.count > data.per_page)'
        ng-model="current_page"
        ng-change="pageChanged()"
        total-items="data.count"
        max-size="10"
        items-per-page="data.per_page"
        first-text="«"
        last-text="»"
        previous-text="«"
        next-text="»"
    ></pagination>

</div>
