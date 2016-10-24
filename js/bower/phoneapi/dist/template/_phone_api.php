<template id="phone-template">
    <!-- ФОРМА ЗВОНКА -->
    <div class="call-popup animated" v-if='show_element'>
        <div class="call-popup-info">
            <div>
                звонок от {{ number }}
            </div>
            <div v-if="type == 'egecrm'">
                <span v-if="caller.type == 'teacher'">преподаватель <a target='_blank' href='teachers/edit/{{caller.id}}'>{{ caller.name }}</a></span>
                <span v-if="caller.type == 'representative'">представитель <a target='_blank' href='student/{{caller.id}}' >{{ caller.name }}</a></span>
                <span v-if="caller.type == 'student'">ученик <a target='_blank' href='student/{{caller.id}}' >{{ caller.name }}</a></span>
                <span v-if="caller.type == 'request'">по заявке <a target='_blank' href='requests/edit/{{caller.id}}'>{{ caller.name }}</a></span>
                <span v-if="!caller.type">неизвестный номер</span>
            </div>

            <div v-if="type == 'egerep'">
                <span v-if="caller.type == 'tutor'">преподаватель <a target='_blank' href='tutors/{{caller.id}}/edit'>{{ caller.name }}</a></span>
                <span v-if="caller.type == 'client'">клиент <a target='_blank' href='client/{{caller.id}}' >{{ caller.name }}</a></span>
                <span v-if="caller.type == 'request'">по заявке <a target='_blank' href='requests/{{caller.id}}/edit'>{{ caller.name }}</a></span>
                <span v-if="!caller.type">неизвестный номер</span>
            </div>

            <div v-if="last_call_data">
            <span class="circle-default" :class="{
                'circle-red':   last_call_data.user_busy == true,
                'circle-green': last_call_data.user_busy == false
            }"></span>
                {{ last_call_data.user_login }} {{ formatDateTime(last_call_data.date_start) }},
                <span v-if='last_call_data.answer > 0'>разговор {{ time(last_call_data.finish - last_call_data.answer) }}</span>
                <span v-else>безуспешный вызов</span>
            </div>
            <div v-if="answered_user">
                ответил {{ answered_user }}
            </div>
        </div>
    </div>
    <!-- Звонок -->
</template>
