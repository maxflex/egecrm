<div class="phone-app">
    <phone user_id="<?= User::fromSession()->id ?>"></phone>

    <template id="phone-template">
        <!-- ФОРМА ЗВОНКА -->
		<div class="call-popup animated" :class="{'fadeInDown': show_element, 'fadeOutRight': hide_element}" v-if='show_element'>
			<span class="glyphicon glyphicon-remove" id="close-call" @click='hide_element = true'></span>
			<div class="call-popup-ava">
				<div class="ava-call"
                    style="background-image: url('{{(caller && caller.type == 'teacher') ? 'img/teachers/' + caller.id + '_2x.jpg' : 'img/phone/no_user_pic.jpg'}}')">
                </div>
			</div>

			<div class="call-popup-info">
                <b v-if="caller">
                    <a target='_blank' href='teachers/edit/{{caller.id}}' v-if="caller.type == 'teacher'">{{ caller.name }}</a>
                    <a target='_blank' href='requests/edit/{{caller.id}}' v-if="caller.type == 'request'">{{ caller.name }}</a>
                    <a target='_blank' href='student/{{caller.id}}' v-if="caller.type == 'student' || caller.type == 'representative'">{{ caller.name }}</a>
                </b>
				<b v-else>{{ number }}</b>
				<div id="call-description">
                    <span v-if='!connected'>
                        <span v-if='caller'>
                            <span v-if="caller.type == 'teacher'">Преподаватель</span>
                            <span v-if="caller.type == 'client'">Ученик</span>
                            <span v-if="caller.type == 'representative'">Представитель</span>
                            <span v-if="caller.type == 'request'">Заявка</span>
                        </span>
                        <span v-else>
                            <span v-if="determined">Неизвестно</span>
                            <span v-else class="text-gray">определение...</span>
                        </span>
                    </span>
                    <span v-if='connected'>{{ call_length }}</span>
                </div>
				<span id="additional-buttons"></span>
				<img src="img/phone/decline.jpg" class="phone-control" @click="hangup">
			</div>
		</div>
        <!-- Звонок -->
    </template>
</div>
