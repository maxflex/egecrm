<div id="user-form" ng-app="Users" ng-controller="EditCtrl" ng-init="<?= $ang_init_data ?>">

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


    <div class="row">
        <div class="col-sm-2" style="width: 150px;">
            <div class="form-group">
                <div class="tutor-img" style="margin-top: 0!important;" ng-class="{'border-transparent': User.has_photo_cropped}" ng-click="showPhotoEditor()">
                    <div>
                        изменить фото
                    </div>
                    <span class="btn-file"></span>
                    <img src="img/users/{{ User.id && User.has_photo_cropped ? User.id + '.' + User.photo_extension : 'no-profile-img.gif' }}?ver={{ picture_version }}">
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
                <input class="form-control" ng-model="User.agreement" placeholder="Cоглашение">
            </div>
        </div>
        <div class="col-sm-3">
            <div class="form-group">
                <div class="input-group">
                    <input class="form-control" ng-model="User.login" placeholder="Логин">
                      <span id="userBanned" class="input-group-addon pointer" ng-click="User.banned = (User.banned + 1) % 2">
                          <span class="glyphicon glyphicon-lock no-margin-right small" ng-class="{ 'text-danger': User.banned }"></span>
                      </span>
                </div>
            </div>

            <div class="form-group" ng-class="{ 'has-error' : psw_changed && has_pswd_error, 'has-success' : psw_changed && !has_pswd_error && User.new_password }">
                <input type="password"
                       class="form-control"
                       ng-model="User.new_password"
                       placeholder="Пароль">
            </div>

            <div class="form-group" ng-class="{ 'has-error' : psw_changed && has_pswd_error, 'has-success' : psw_changed && !has_pswd_error && User.new_password }">
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
                    <option value="1">звонки ЕГЭ-Центра</option>
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
    </div>
    <div class="row panel-body">
        <div class="col-sm-12">
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
                    <input type="checkbox" ng-model="User.show_tasks" ng-true-value="1">
                    <span class="switch"></span>
                </label>
                показать задачи
            </div>
            <br>
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
    <div class="row">
        <div class="col-sm-12 center">
            <button class="btn btn-primary" ng-click="save()" ng-disabled="!form_changed || User.login.length == 0 || has_pswd_error">
                <span ng-show="form_changed">Сохранить</span>
                <span ng-show="!form_changed">Сохранено</span>
            </button>
        </div>
    </div>
</div>