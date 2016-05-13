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
<!--                    <img src="{{ User.photo_url }}?ver={{ picture_version }}">-->
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

            <td>
                <input class="form-control"
                       style="background-color:{{User.color}};color:white;"
                       ng-model="User.color"
                       colorpicker="hex"
                       colorpicker-size="200"
                       placeholder="цвет">
            </td>
        </div>
    </div>
    <div class="row">
        <div class="col-sm-12 center">
            <button class="btn btn-primary" ng-click="save()" ng-disabled="!form_changed || has_pswd_error">
                <span ng-show="form_changed">Сохранить</span>
                <span ng-show="!form_changed">Сохранено</span>
            </button>
        </div>
    </div>
</div>