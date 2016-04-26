<div class="phone-app" <?= User::fromSession()->show_phone_calls ? '' : 'style="display:none;"'?>>
    <phone user_id="<?= User::fromSession()->id ?>"></phone>

    <template id="phone-template">
        <!-- ФОРМА ЗВОНКА -->
		<div class="call-popup animated" :class="{'fadeInDown': show_element, 'fadeOutRight': hide_element}" v-if='show_element'>
<!--			<span class="glyphicon glyphicon-remove" id="close-call" @click='hide_element = true'></span>-->
<!--			<div class="call-popup-ava">-->
<!--				<div class="ava-call"-->
<!--                    style="background-image: url('{{(caller && caller.type == 'teacher') ? 'img/teachers/' + caller.id + '_2x.jpg' : 'img/phone/no_user_pic.jpg'}}')">-->
<!--                </div>-->
<!--			</div>-->

			<div class="call-popup-info">
                звонок от {{ number }}<br>
                <span v-if="determined">
                    <span v-if="caller.type == 'teacher'">преподаватель <a target='_blank' href='teachers/edit/{{caller.id}}'>{{ caller.name }}</a></span>
                    <span v-if="caller.type == 'representative'">представитель <a target='_blank' href='student/{{caller.id}}' >{{ caller.name }}</a></span>
                    <span v-if="caller.type == 'student'">ученик <a target='_blank' href='student/{{caller.id}}' >{{ caller.name }}</a></span>
                    <span v-if="caller.type == 'request'">по заявке <a target='_blank' href='requests/edit/{{caller.id}}'>{{ caller.name }}</a></span>
                    <span v-if="!caller.type">неизвестный номер</span>
                    <br/>
                    <span v-if="caller.user">последняя связь с {{caller.user}}</span>
                </span>
                <span v-else>
                    <span class="text-gray">определение...</span>
                </span>

<!--				<div id="call-description">-->
<!--                    <span v-if='!connected'>-->
<!--                        <span v-if='caller'>-->
<!--                            <span v-if="caller.type == 'teacher'">Преподаватель</span>-->
<!--                            <span v-if="caller.type == 'client'">Ученик</span>-->
<!--                            <span v-if="caller.type == 'representative'">Представитель</span>-->
<!--                            <span v-if="caller.type == 'request'">Заявка</span>-->
<!--                        </span>-->
<!--                        <span v-else>-->
<!--                            <span v-if="determined">Неизвестно</span>-->
<!--                            <span v-else class="text-gray">определение...</span>-->
<!--                        </span>-->
<!--                    </span>-->
<!--                    <span v-if='connected'>{{ call_length }}</span>-->
<!--                </div>-->
<!--				<span id="additional-buttons"></span>-->
<!--				<img src="img/phone/decline.jpg" class="phone-control" @click="hangup">-->
			</div>
		</div>
        <!-- Звонок -->
    </template>
</div>
