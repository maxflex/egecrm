<div class="phone-app" <?= User::fromSession()->show_phone_calls ? '' : 'style="display:none;"' ?> >
    <phone user_id="<?= User::fromSession()->id ?>"></phone>

    <template id="phone-template">
        <!-- ФОРМА ЗВОНКА -->
		<div class="call-popup animated" v-if='show_element'>
			<div class="call-popup-info">
                звонок от {{ number }}<br>
                <span v-if="determined">
                    <span v-if="caller.type == 'teacher'">преподаватель <a target='_blank' href='teachers/edit/{{caller.id}}'>{{ caller.name }}</a></span>
                    <span v-if="caller.type == 'representative'">представитель <a target='_blank' href='student/{{caller.id}}' >{{ caller.name }}</a></span>
                    <span v-if="caller.type == 'student'">ученик <a target='_blank' href='student/{{caller.id}}' >{{ caller.name }}</a></span>
                    <span v-if="caller.type == 'request'">по заявке <a target='_blank' href='requests/edit/{{caller.id}}'>{{ caller.name }}</a></span>
                    <span v-if="!caller.type">неизвестный номер</span>
                    <br/>
                    <span v-if="last_call_data">
                    	<img src="img/calls/{{last_call_data.from_extension ? 'outgoing' : 'incoming'}}.png">
                    	{{ last_call_data.user.login }} {{ formatDateTime(last_call_data.date_start) }}, разговор {{ time(last_call_data.seconds) }}
                    </span>
                </span>
                <span v-else>
                    <span class="text-gray">определение...</span>
                </span>
			</div>
		</div>
        <!-- Звонок -->
    </template>
</div>
