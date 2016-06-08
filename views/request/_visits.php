<div class="row" ng-show="getStudentGroups().length > 0">
    <div class="col-sm-12">
	     <h4 class="row-header">ПОСЕЩАЕМОСТЬ</h4>
            <table>
                <tr ng-repeat="id_group in getStudentGroups()" class="visit-div">
                    <td class="visit-div-group">
                        <a href="groups/edit/{{id_group}}" style="display:inline-block;width:90px;">Группа №{{id_group}}</a>
                        <!--<span>{{Subjects[getAnyGroup(id_group).id_subject]}}{{getAnyGroup(id_group).grade ? '-' + getAnyGroup(id_group).grade : ''}}</span>-->
                        <!-- показываем данные группы из журнала посещений, а не из данных групп, потому что там нет групп, в которых ученик перестал ходит -->
                        <span>{{Subjects[getVisitsByGroup(id_group)[0].id_subject]}}{{getVisitsByGroup(id_group)[0].grade ? '-' + getVisitsByGroup(id_group)[0].grade : ''}}</span>
                    </td>
                    <td>
                        <span ng-if="!getGroup(id_group)">
                            <div ng-repeat="Visit in getVisitsByGroup(id_group)"
                                 class="visit-div-circle">
                                <span class="circle-default" title="{{formatVisitDate(Visit.lesson_date)}}{{(Visit.presence == 1 && Visit.late > 0) ? ', опоздание ' + Visit.late + ' мин.' : ''}}"
                                      ng-class="{
                                    'circle-red'	: Visit.presence == 2,
                                    'circle-orange'	: Visit.presence == 1 && Visit.late > 0
                                }"></span>
                            </div>
                            <span class="visit-between-number">{{getVisitsByGroup(id_group).length}}</span>
                        </span>
                    </td>
                    <td>
                        <div ng-repeat="Visit in getGroup(id_group).Schedule" class="visit-div-circle">
                            <span class="visit-between-number" ng-show="visit_data_counts[id_group][$index]">{{ visit_data_counts[id_group][$index] }}</span>
                            <!-- Занятия нет -->
                            <span ng-if="!getVisit(id_group, Visit.date)">
                                <span class="circle-default circle-future" title="{{formatVisitDate(Visit.date)}}"></span>
                            </span>
                            <!-- Занятие есть -->
                            <span ng-if="getVisit(id_group, Visit.date)">
                                <span class="circle-default" title="{{formatVisitDate(getVisit(id_group, Visit.date).lesson_date)}}{{(getVisit(id_group, Visit.date).presence == 1 && getVisit(id_group, Visit.date).late > 0) ? ', опоздание ' + getVisit(id_group, Visit.date).late + ' мин.' : ''}}"
                                      ng-class="{
                                    'circle-red'	: getVisit(id_group, Visit.date).presence == 2,
                                    'circle-orange'	: getVisit(id_group, Visit.date).presence == 1 && getVisit(id_group, Visit.date).late > 0
                                }"></span>
                            </span>
                        </div>
                        <span class="visit-between-number" ng-show="visit_data_counts[id_group]['last']">{{ visit_data_counts[id_group]['last'] }}</span>
                        <span class="visit-between-number" style="width: auto">итого {{ getGroup(id_group) ? getGroup(id_group).Schedule.length : getVisitsByGroup(id_group).length }} <ng-pluralize count="getGroup(id_group) ? getGroup(id_group).Schedule.length : getVisitsByGroup(id_group).length" when="{
                             'one': 'занятие',
                             'few': 'занятия',
                             'many': 'занятий',
                        }"></ng-pluralize></span>
                    </td>
                </tr>
            </table>
    </div>
</div>