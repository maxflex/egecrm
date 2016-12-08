<!--photo modal-->
<div class="modal modal-fullscreen" tabindex="-1" id='change-photo'>
    <div class="modal-dialog" style="width: 80%; height: 90%; margin: 3% auto">
        <div class="modal-content" style="height: 100%">
            <div class="modal-body" style="height: 100%">
                <div class="row" style="height: 100%">
                    <div class="col-sm-10 image-col-left" style="height: 100%">
                        <div ng-show='User.has_photo_original' style="height: calc(100% - 10px);">
                            <img src="img/users/{{ User.id + '_original.' + User.photo_extension }}?ver={{ picture_version }}" id='photo-edit' style="height: 100%">
                        </div>
                    </div>
                    <div class="col-sm-2 center image-col-right">
                        <div id="image-preview" ng-show='User.has_photo_original'>
                            <div class="form-group img-preview-container">
                                <div class="img-preview"></div>
                            </div>
                        </div>

                        <div class='photo-sizes'>
                            <div ng-show='User.photo_original_size'>
                                {{ formatBytes(User.photo_original_size) }}
                            </div>
                            <div ng-show='User.photo_cropped_size'>
                                {{ formatBytes(User.photo_cropped_size) }}
                            </div>
                        </div>

                        <div class="form-group">
                            <button class="btn btn-primary full-width">Загрузить
                                    <span class="btn-file">
                                        <input name="photo" type="file" id="fileupload" data-url="upload/user">
                                    </span>
                            </button>
                        </div>
                        <div class="form-group">
                            <button class="btn btn-primary full-width" ng-click='saveCropped()' ng-disabled='!User.has_photo_original'>Сохранить</button>
                        </div>
                        <div class="form-group">
                            <button class="btn btn-danger full-width" ng-click='deletePhoto()' ng-disabled='!User.has_photo_original'>Удалить</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<!--/photo modal-->
<!--user info-->
<div class="row">
    <div class="col-sm-2" style="width: 150px;">
        <div class="form-group">
            <div class="tutor-img" ng-hide="User.isNewRecord" style="margin-top: 0!important;" ng-class="{'border-transparent': User.has_photo_cropped}" ng-click="showPhotoEditor()">
                <div>
                    изменить фото
                </div>
                <span class="btn-file"></span>
                <img src="img/users/{{ User.id && User.has_photo_cropped ? User.id + '.' + User.photo_extension : 'no-profile-img.gif' }}?ver={{ picture_version }}">
            </div>
            <div class="user-photo-hint" ng-show="User.isNewRecord">
                установка фото доступна после создания пользователя
            </div>
        </div>
    </div>
    <div class="col-sm-3">
        <div class="form-group">
            <input class="form-control" ng-model="User.last_name" placeholder="Фамилия">
        </div>
        <div class="form-group">
            <input class="form-control" ng-model="User.first_name" placeholder="Имя">
        </div>
        <div class="form-group">
            <input class="form-control" ng-model="User.middle_name" placeholder="Отчество">
        </div>
    </div>
    <div class="col-sm-3">
        <div class="form-group" ng-class="{ 'has-error' : user_exists}">
            <input class="form-control"
                   placeholder="Логин"
                   ng-model="User.login"
                   ng-model-options='{ debounce: 500 }'
                   ng-change="checkExistance()">
        </div>

        <div class="form-group" ng-class="{ 'has-error' : psw_filled && has_pswd_error, 'has-success' : psw_filled && !has_pswd_error && User.new_password }">
            <input type="password"
                   class="form-control"
                   ng-model="User.new_password"
                   placeholder="Пароль">
        </div>
        <div class="form-group" ng-class="{ 'has-error' : psw_filled && has_pswd_error, 'has-success' : psw_filled && !has_pswd_error && User.new_password }">
            <input type="password"
                   class="form-control"
                   ng-model="User.new_password_repeat"
                   placeholder="Повторите пароль">
        </div>
    </div>
    <div class="col-sm-3">
        <div class="form-group">
            <input class="form-control phone-masked" ng-model="User.phone" placeholder="телефон" ng-keyup='form_changed=true'>
        </div>
        <div class="form-group">
            <input class="form-control"
                   style="background-color:{{User.color}};color:white;"
                   ng-model="User.color"
                   colorpicker="hex"
                   colorpicker-size="200"
                   placeholder="цвет">
        </div>
        <!-- @rights-need-to-refactor -->
        <?php if (User::isDev() || User::isRoot() || User::fromSession()->id == 65) :?>
        <div class="form-group">
            <input class="form-control" type="number" placeholder="зарплата" ng-model="User.salary">
        </div>
        <?php endif ?>
    </div>
</div>
<!--/user data-->
<!--access settings -->
<div class="row panel-body">
    <div class="col-sm-4">
        <div class="row">
            <h4 class="row-header">ЕГЭ-Центр</h4>
        </div>
        <div class="row">
            <label class="ios7-switch">
                <input type="checkbox" ng-click='toggleRights(<?= Shared\Rights::EDIT_GROUPS ?>)' ng-checked='allowed(<?= Shared\Rights::EDIT_GROUPS ?>)'>
                <span class="switch"></span>
                <span class='title'>редактирование групп</span>
            </label>
        </div>
        <div class="row">
            <label class="ios7-switch">
                <input type="checkbox" ng-click='toggleRights(<?= Shared\Rights::EDIT_PAYMENTS ?>)' ng-checked='allowed(<?= Shared\Rights::EDIT_PAYMENTS ?>)'>
                <span class="switch"></span>
                <span class='title'>редактирование платежей</span>
            </label>
        </div>
        <div class="row">
            <label class="ios7-switch">
                <input type="checkbox" ng-click='toggleRights(<?= Shared\Rights::SHOW_TASKS ?>)' ng-checked='allowed(<?= Shared\Rights::SHOW_TASKS ?>)'>
                <span class="switch"></span>
                <span class='title'>задачи</span>
            </label>
        </div>
        <div class="row">
            <label class="ios7-switch">
                <input type="checkbox" ng-click='toggleRights(<?= Shared\Rights::SHOW_CALENDAR ?>)' ng-checked='allowed(<?= Shared\Rights::SHOW_CALENDAR ?>)'>
                <span class="switch"></span>
                <span class='title'>календарь</span>
            </label>
        </div>
        <div class="row">
            <label class="ios7-switch">
                <input type="checkbox" ng-click='toggleRights(<?= Shared\Rights::SHOW_FAQ ?>)' ng-checked='allowed(<?= Shared\Rights::SHOW_FAQ ?>)'>
                <span class="switch"></span>
                <span class='title'>FAQ</span>
            </label>
        </div>
        <div class="row">
            <label class="ios7-switch">
                <input type="checkbox" ng-click='toggleRights(<?= Shared\Rights::SHOW_TEMPLATES ?>)' ng-checked='allowed(<?= Shared\Rights::SHOW_TEMPLATES ?>)'>
                <span class="switch"></span>
                <span class='title'>шаблоны</span>
            </label>
        </div>
        <div class="row">
            <label class="ios7-switch">
                <input type="checkbox" ng-click='toggleRights(<?= Shared\Rights::SHOW_USERS ?>)' ng-checked='allowed(<?= Shared\Rights::SHOW_USERS ?>)'>
                <span class="switch"></span>
                <span class='title'>пользователи</span>
            </label>
        </div>
        <div class="row">
            <label class="ios7-switch">
                <input type="checkbox" ng-click='toggleRights(<?= Shared\Rights::SHOW_PAYMENTS ?>)' ng-checked='allowed(<?= Shared\Rights::SHOW_PAYMENTS ?>)'>
                <span class="switch"></span>
                <span class='title'>платежи</span>
            </label>
        </div>
        <div class="row">
            <label class="ios7-switch">
                <input type="checkbox" ng-click='toggleRights(<?= Shared\Rights::SHOW_TEACHER_PAYMENTS ?>)' ng-checked='allowed(<?= Shared\Rights::SHOW_TEACHER_PAYMENTS ?>)'>
                <span class="switch"></span>
                <span class='title'>оплата преподавателей</span>
            </label>
        </div>
        <div class="row">
            <label class="ios7-switch">
                <input type="checkbox" ng-click='toggleRights(<?= Shared\Rights::SHOW_STATS ?>)' ng-checked='allowed(<?= Shared\Rights::SHOW_STATS ?>)'>
                <span class="switch"></span>
                <span class='title'>итоги</span>
            </label>
        </div>
    </div>
    <div class="col-sm-4">
        <div class="row">
            <h4 class="row-header">ЕГЭ-Репетитор</h4>
        </div>
        <div class="row">
            <label class="ios7-switch">
                <input type="checkbox" ng-click='toggleRights(<?= Shared\Rights::ER_REQUEST_DATA ?>)' ng-checked='allowed(<?= Shared\Rights::ER_REQUEST_DATA ?>)'>
                <span class="switch"></span>
                <span class='title'>редактирование клиентов</span>
            </label>
        </div>
        <div class="row">
            <label class="ios7-switch">
                <input type="checkbox" ng-click='toggleRights(<?= Shared\Rights::ER_EDIT_ACCOUNTS ?>)' ng-checked='allowed(<?= Shared\Rights::ER_EDIT_ACCOUNTS ?>)'>
                <span class="switch"></span>
                <span class='title'>редактирование отчетности</span>
            </label>
        </div>
        <div class="row">
            <label class="ios7-switch">
                <input type="checkbox" ng-click='toggleRights(<?= Shared\Rights::ER_DELETE_TUTOR ?>)' ng-checked='allowed(<?= Shared\Rights::ER_DELETE_TUTOR ?>)'>
                <span class="switch"></span>
                <span class='title'>удаление преподавателей</span>
            </label>
        </div>
        <div class="row">
            <label class="ios7-switch">
                <input type="checkbox" ng-click='toggleRights(<?= Shared\Rights::ER_MERGE_TUTOR ?>)' ng-checked='allowed(<?= Shared\Rights::ER_MERGE_TUTOR ?>)'>
                <span class="switch"></span>
                <span class='title'>склейка преподавателей</span>
            </label>
        </div>
        <div class="row">
            <label class="ios7-switch">
                <input type="checkbox" ng-click='toggleRights(<?= Shared\Rights::ER_TUTOR_ACCOUNTS ?>)' ng-checked='allowed(<?= Shared\Rights::ER_TUTOR_ACCOUNTS ?>)'>
                <span class="switch"></span>
                <span class='title'>отчетность</span>
            </label>
        </div>
        <div class="row">
            <label class="ios7-switch">
                <input type="checkbox" ng-click='toggleRights(<?= Shared\Rights::ER_PERIODS ?>)' ng-checked='allowed(<?= Shared\Rights::ER_PERIODS ?>)'>
                <span class="switch"></span>
                <span class='title'>совершенные расчеты</span>
            </label>
        </div>
        <div class="row">
            <label class="ios7-switch">
                <input type="checkbox" ng-click='toggleRights(<?= Shared\Rights::ER_PERIODS_PLANNED ?>)' ng-checked='allowed(<?= Shared\Rights::ER_PERIODS_PLANNED ?>)'>
                <span class="switch"></span>
                <span class='title'>планируемые расчеты</span>
            </label>
        </div>
        <div class="row">
            <label class="ios7-switch">
                <input type="checkbox" ng-click='toggleRights(<?= Shared\Rights::ER_DEBT ?>)' ng-checked='allowed(<?= Shared\Rights::ER_DEBT ?>)'>
                <span class="switch"></span>
                <span class='title'>дебет</span>
            </label>
        </div>
        <div class="row">
            <label class="ios7-switch">
                <input type="checkbox" ng-click='toggleRights(<?= Shared\Rights::ER_SHOW_TUTOR_DEBT ?>)' ng-checked='allowed(<?= Shared\Rights::ER_SHOW_TUTOR_DEBT ?>)'>
                <span class="switch"></span>
                <span class='title'>показать дебет</span>
            </label>
        </div>
        <div class="row">
            <label class="ios7-switch">
                <input type="checkbox" ng-click='toggleRights(<?= Shared\Rights::ER_ATTACHMENT_STATS ?>)' ng-checked='allowed(<?= Shared\Rights::ER_ATTACHMENT_STATS ?>)'>
                <span class="switch"></span>
                <span class='title'>статистика</span>
            </label>
        </div>
        <div class="row">
            <label class="ios7-switch">
                <input type="checkbox" ng-click='toggleRights(<?= Shared\Rights::ER_SUMMARY_USERS ?>)' ng-checked='allowed(<?= Shared\Rights::ER_SUMMARY_USERS ?>)'>
                <span class="switch"></span>
                <span class='title'>эффективность</span>
            </label>
        </div>
        <div class="row">
            <label class="ios7-switch">
                <input type="checkbox" ng-click='toggleRights(<?= Shared\Rights::ER_LOGS ?>)' ng-checked='allowed(<?= Shared\Rights::ER_LOGS ?>)'>
                <span class="switch"></span>
                <span class='title'>логи</span>
            </label>
        </div>
        <div class="row">
            <label class="ios7-switch">
                <input type="checkbox" ng-click='toggleRights(<?= Shared\Rights::ER_SUMMARY ?>)' ng-checked='allowed(<?= Shared\Rights::ER_SUMMARY ?>)'>
                <span class="switch"></span>
                <span class='title'>итоги</span>
            </label>
        </div>
        <div class="row">
            <label class="ios7-switch">
                <input type="checkbox" ng-click='toggleRights(<?= Shared\Rights::ER_TUTOR_STATUSES ?>)' ng-checked='allowed(<?= Shared\Rights::ER_TUTOR_STATUSES ?>)'>
                <span class="switch"></span>
                <span class='title'>переключение статусов преподавателя</span>
            </label>
        </div>
        <div class="row">
            <label class="ios7-switch">
                <input type="checkbox" ng-click='toggleRights(<?= Shared\Rights::ER_REQUEST_STATUSES ?>)' ng-checked='allowed(<?= Shared\Rights::ER_REQUEST_STATUSES ?>)'>
                <span class="switch"></span>
                <span class='title'>переключение статусов заявки</span>
            </label>
        </div>
        <div class="row">
            <label class="ios7-switch">
                <input type="checkbox" ng-click='toggleRights(<?= Shared\Rights::ER_ACCEPT_ACCOUNTS ?>)' ng-checked='allowed(<?= Shared\Rights::ER_ACCEPT_ACCOUNTS ?>)'>
                <span class="switch"></span>
                <span class='title'>одобрение встреч</span>
            </label>
        </div>
    </div>
    <div class="col-sm-4">
        <div class="row">
            <h4 class="row-header">Общее</h4>
        </div>
        <div class="row">
            <label class="ios7-switch">
                <input type="checkbox" ng-click='toggleRights(<?= Shared\Rights::PHONE_NOTIFICATIONS ?>)' ng-checked='allowed(<?= Shared\Rights::PHONE_NOTIFICATIONS ?>)'>
                <span class="switch"></span>
                <span class='title'>уведомления о звонках</span>
            </label>
        </div>
        <div class="row">
            <label class="ios7-switch">
                <input type="checkbox" ng-click='toggleRights(<?= Shared\Rights::IS_DEVELOPER ?>)' ng-checked='allowed(<?= Shared\Rights::IS_DEVELOPER ?>)'>
                <span class="switch"></span>
                <span class='title'>разработчик</span>
            </label>
        </div>
        <div class="row">
            <label class="ios7-switch">
                <input type="checkbox" ng-click='toggleRights(<?= Shared\Rights::SHOW_CONTRACT ?>)' ng-checked='allowed(<?= Shared\Rights::SHOW_CONTRACT ?>)'>
                <span class="switch"></span>
                <span class='title'>договор</span>
            </label>
        </div>
        <div class="row">
            <label class="ios7-switch">
                <input type="checkbox" ng-model="User.banned_egerep" ng-true-value="1">
                <span class="switch"></span>
                <span class="title">заблокирован в ЕГЭ-Центре</span>
            </label>
        </div>
        <div class="row">
            <label class="ios7-switch">
                <input type="checkbox" ng-model="User.banned_egerep" ng-true-value="1">
                <span class="switch"></span>
                <span class="title">заблокирован в ЕГЭ-Репетиторе</span>
            </label>
        </div>
        <div class="row">
            <label class="ios7-switch">
                <input type="checkbox" ng-model="User.banned_egecms" ng-true-value="1">
                <span class="switch"></span>
                <span class="title">заблокирован в CMS ЕГЭ-Репетитора</span>
            </label>
        </div>
    </div>
</div>
<!--/access settings -->
<style>
    input[type=number]::-webkit-inner-spin-button,
    input[type=number]::-webkit-outer-spin-button {
        -webkit-appearance: none;
        margin: 0;
    }
    .ios7-switch {
        font-size: 24px;
        top: 1px;
        margin: 0;
    }

    .ios7-switch .title {
        font-size: 14px;
        font-weight: normal;
        top: -3px;
        position: relative;
    }
</style>
