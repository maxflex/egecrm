<!--photo modal-->
<div class="modal modal-fullscreen" tabindex="-1" id='change-photo'>
    <div class="modal-dialog" style="width: 80%; height: 90%; margin: 3% auto">
        <div class="modal-content" style="height: 100%">
            <div class="modal-body" style="height: 100%">
                <div class="row" style="height: 100%">
                    <div class="col-sm-10 image-col-left" style="height: 100%">
                        <div ng-show='User.has_photo_original' style="height: calc(100% - 10px);">
                            <img ng-src="img/users/{{ User.id + '_original.' + User.photo_extension }}?ver={{ picture_version }}" id='photo-edit' style="height: 100%">
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
                <img ng-src="img/users/{{ User.id && User.has_photo_cropped ? User.id + '.' + User.photo_extension : 'no-profile-img.gif' }}?ver={{ picture_version }}">
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
        <div class="form-group">
            <input class="form-control" ng-model="User.email" placeholder="email">
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
            <phones class="user-phone" entity="User" entity-type="User" without-buttons untrack-duplicate></phones>
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
        <!-- SHOW_SALARY -->
        <?php if (allowed(Shared\Rights::SHOW_SALARY)) :?>
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
        <?= partial('right', ['right' => Shared\Rights::EDIT_GROUPS]) ?>
        <?= partial('right', ['right' => Shared\Rights::EDIT_GROUP_SCHEDULE]) ?>
        <?= partial('right', ['right' => Shared\Rights::EDIT_PAYMENTS]) ?>
        <?= partial('right', ['right' => Shared\Rights::EC_EDIT_GROUP_CONTRACT]) ?>
        <?= partial('right', ['right' => Shared\Rights::SHOW_TASKS]) ?>
        <?= partial('right', ['right' => Shared\Rights::SHOW_CALENDAR]) ?>
        <?= partial('right', ['right' => Shared\Rights::SHOW_FAQ]) ?>
        <?= partial('right', ['right' => Shared\Rights::SHOW_TEMPLATES]) ?>
        <?= partial('right', ['right' => Shared\Rights::SHOW_USERS]) ?>
        <?= partial('right', ['right' => Shared\Rights::SHOW_PAYMENTS]) ?>
        <?= partial('right', ['right' => Shared\Rights::SHOW_TEACHER_PAYMENTS]) ?>
        <?= partial('right', ['right' => Shared\Rights::SHOW_STATS]) ?>
        <?= partial('right', ['right' => Shared\Rights::SHOW_SALARY]) ?>
        <?= partial('right', ['right' => Shared\Rights::LOGS]) ?>
        <?= partial('right', ['right' => Shared\Rights::EC_STREAM]) ?>
        <?= partial('right', ['right' => Shared\Rights::EC_ACTIVITY]) ?>
        <?= partial('right', ['right' => Shared\Rights::EC_CALLS_RATING]) ?>
    </div>
    <div class="col-sm-4">
        <div class="row">
            <h4 class="row-header">ЕГЭ-Репетитор</h4>
        </div>
        <?= partial('right', ['right' => Shared\Rights::ER_DELETE_REQUESTS]) ?>
        <?= partial('right', ['right' => Shared\Rights::ER_DELETE_LISTS]) ?>
        <?= partial('right', ['right' => Shared\Rights::ER_DELETE_ATTACHMENTS]) ?>
        <?= partial('right', ['right' => Shared\Rights::ER_DELETE_ARCHIVES]) ?>
        <?= partial('right', ['right' => Shared\Rights::ER_DELETE_REVIEWS]) ?>
        <?= partial('right', ['right' => Shared\Rights::ER_ATTACHMENT_VISIBILITY]) ?>
        <?= partial('right', ['right' => Shared\Rights::ER_SUMMARY_FIELDS]) ?>
        <?= partial('right', ['right' => Shared\Rights::ER_EDIT_ACCOUNTS]) ?>
        <?= partial('right', ['right' => Shared\Rights::ER_EDIT_PAYMENTS]) ?>
        <?= partial('right', ['right' => Shared\Rights::ER_DELETE_TUTOR]) ?>
        <?= partial('right', ['right' => Shared\Rights::ER_MERGE_TUTOR]) ?>
        <?= partial('right', ['right' => Shared\Rights::ER_TUTOR_ACCOUNTS]) ?>
        <?= partial('right', ['right' => Shared\Rights::ER_PERIODS]) ?>
        <?= partial('right', ['right' => Shared\Rights::ER_PERIODS_PLANNED]) ?>
        <?= partial('right', ['right' => Shared\Rights::ER_DEBT]) ?>
        <?= partial('right', ['right' => Shared\Rights::ER_SHOW_TUTOR_DEBT]) ?>
        <?= partial('right', ['right' => Shared\Rights::ER_ATTACHMENT_STATS]) ?>
        <?= partial('right', ['right' => Shared\Rights::ER_SUMMARY_USERS]) ?>
        <?= partial('right', ['right' => Shared\Rights::ER_SUMMARY_USERS_ALL]) ?>
        <?= partial('right', ['right' => Shared\Rights::ER_LOGS]) ?>
        <?= partial('right', ['right' => Shared\Rights::ER_SUMMARY]) ?>
        <?= partial('right', ['right' => Shared\Rights::ER_TUTOR_STATUSES]) ?>
        <?= partial('right', ['right' => Shared\Rights::ER_REQUEST_STATUSES]) ?>
        <?= partial('right', ['right' => Shared\Rights::ER_ACCEPT_ACCOUNTS]) ?>
        <?= partial('right', ['right' => Shared\Rights::ER_STREAM]) ?>
        <?= partial('right', ['right' => Shared\Rights::ER_TEMPLATES]) ?>
        <?= partial('right', ['right' => Shared\Rights::ER_REQUEST_ERRORS]) ?>
        <?= partial('right', ['right' => Shared\Rights::ER_ATTENDANCE]) ?>
        <?= partial('right', ['right' => Shared\Rights::ER_ACTIVITY]) ?>
        <?= partial('right', ['right' => Shared\Rights::ER_APPROVE_BACKGROUND]) ?>
        <?= partial('right', ['right' => Shared\Rights::SECRET_SMS]) ?>
    </div>
    <div class="col-sm-4">
        <div class="row">
            <h4 class="row-header">Общее</h4>
        </div>
        <?= partial('right', ['right' => Shared\Rights::PHONE_NOTIFICATIONS]) ?>
        <?= partial('right', ['right' => Shared\Rights::IS_DEVELOPER]) ?>
        <?= partial('right', ['right' => Shared\Rights::EC_BANNED]) ?>
        <?= partial('right', ['right' => Shared\Rights::ER_BANNED]) ?>
        <?= partial('right', ['right' => Shared\Rights::ECC_BANNED]) ?>
        <?= partial('right', ['right' => Shared\Rights::ERC_BANNED]) ?>
        <?= partial('right', ['right' => Shared\Rights::WORLDWIDE_ACCESS]) ?>
        <?= partial('right', ['right' => Shared\Rights::EMERGENCY_EXIT]) ?>
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
