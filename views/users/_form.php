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
    <div class="col-sm-2">
        <div class="form-group">
            <select class="form-control" ng-model="User.show_phone_calls">
                <option value="0">нет оповещий</option>
                <option value="1">уведомления о звонках</option>
            </select>
        </div>

        <div class="form-group">
            <input class="form-control"
                   style="background-color:{{User.color}};color:white;"
                   ng-model="User.color"
                   colorpicker="hex"
                   colorpicker-size="200"
                   placeholder="цвет">
        </div>

        <div class="form-group">
            <select class="form-control" ng-model="User.is_dev" ng-change="User.is_dev == 2 ? User.type = 'SEO' : User.type = 'USER' ">
                <option value="0">менеджер</option>
                <option value="1">разработчик</option>
                <option value="2">seo</option>
            </select>
            <input type="hidden" ng-model="User.type" value="User.type"/>
        </div>
    </div>
    <?php if (User::isDev() || User::isRoot() || User::fromSession()->id == 65) :?>
    <div class="col-sm-2">
        <div class="form-group">
            <input class="form-control" type="number" placeholder="зарплата" ng-model="User.salary">
        </div>
    </div>
    <?php endif ?>
</div>
<!--/user data-->
<!--access settings -->
<div class="row panel-body">
    <div class="col-sm-12">
        <div class="row">
            <label class="ios7-switch" style="font-size: 24px; top: 1px; margin: 0">
                <input type="checkbox" ng-model="User.worktime" ng-true-value="1" ng-false-value="0">
                <span class="switch"></span>
            </label>
            отображать в списке заявок
        </div>
        <div class="row">
            <label class="ios7-switch" style="font-size: 24px; top: 1px; margin: 0">
                <input type="checkbox" ng-model="User.can_approve_tutor" ng-true-value="1">
                <span class="switch"></span>
            </label>
            одобрение репетиторов
        </div>
        <div class="row">
            <label class="ios7-switch" style="font-size: 24px; top: 1px; margin: 0">
                <input type="checkbox" ng-model="User.edit_payment" ng-true-value="1">
                <span class="switch"></span>
            </label>
            просмотр и редактирование остаточного платежа
        </div>
        <div class="row">
            <label class="ios7-switch" style="font-size: 24px; top: 1px; margin: 0">
                <input type="checkbox" ng-model="User.show_summary" ng-true-value="1">
                <span class="switch"></span>
            </label>
            просмотр итогов
        </div>
        <div class="row">
            <label class="ios7-switch" style="font-size: 24px; top: 1px; margin: 0">
                <input type="checkbox" ng-model="User.show_contract" ng-true-value="1">
                <span class="switch"></span>
            </label>
            показать договор
        </div>
        <br/>
        <div class="row">
            <label class="ios7-switch" style="font-size: 24px; top: 1px; margin: 0">
                <input type="checkbox" ng-model="User.show_users" ng-true-value="1">
                <span class="switch"></span>
            </label>
            показать и редактировать пользователей
        </div>

        <div class="row">
            <label class="ios7-switch" style="font-size: 24px; top: 1px; margin: 0">
                <input type="checkbox" ng-model="User.show_tasks" ng-true-value="1">
                <span class="switch"></span>
            </label>
            показать задачи
        </div>
        <div class="row">
            <label class="ios7-switch" style="font-size: 24px; top: 1px; margin: 0">
                <input type="checkbox" ng-model="User.show_debt" ng-true-value="1">
                <span class="switch"></span>
            </label>
            показать пункт "дебет"
        </div>
        <div class="row">
            <label class="ios7-switch" style="font-size: 24px; top: 1px; margin: 0">
                <input type="checkbox" ng-model="User.show_accounts" ng-true-value="1">
                <span class="switch"></span>
            </label>
            показать пункт "расчеты"
        </div>
        <div class="row">
            <label class="ios7-switch" style="font-size: 24px; top: 1px; margin: 0">
                <input type="checkbox" ng-model="User.remove_requests" ng-true-value="1">
                <span class="switch"></span>
            </label>
            удаление заявок в ЕГЭ-Репетиторе
        </div>
        <br>
        <div class="row">
            <label class="ios7-switch" style="font-size: 24px; top: 1px; margin: 0">
                <input type="checkbox" ng-model="User.worldwide_access" ng-true-value="1" ng-false-value="0">
                <span class="switch"></span>
            </label>
            доступ отовсюду
        </div>
        <div class="row">
            <label class="ios7-switch" style="font-size: 24px; top: 1px; margin: 0">
                <input type="checkbox" ng-model="User.banned" ng-true-value="1">
                <span class="switch"></span>
            </label>
            заблокирован в егэ-центре
        </div>
        <div class="row">
            <label class="ios7-switch" style="font-size: 24px; top: 1px; margin: 0">
                <input type="checkbox" ng-model="User.banned_egerep" ng-true-value="1">
                <span class="switch"></span>
            </label>
            заблокирован в егэ-репетиторе
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
</style>