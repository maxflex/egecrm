<div ng-app="StudentProfile" ng-controller="PhotoCtrl" ng-init="<?= $ang_init_data ?>">
    <div class="row">
        <div class="col-sm-12">
            <span class="glyphicon glyphicon-hand-right pull-left" style="height: 50px; vertical-align: middle; top: 1px; margin-right: 14px; font-size: 28px"></span>
            <div style="line-height: 14px">
                для идентификации вас преподавателем пожалуйста добавьте ваше фото. Желательно использовать качественные фото высокого разрешения. Допускаются файлы до 10мб формата jpg/jpeg, png, gif.
            </div>
        </div>

        <!--photo modal-->
        <div class="modal modal-fullscreen" tabindex="-1" id='change-photo'>
            <div class="modal-dialog" style="width: 80%; height: 90%; margin: 3% auto">
                <div class="modal-content" style="height: 100%">
                    <div class="modal-body" style="height: 100%">
                        <div class="row" style="height: 100%">
                            <div class="col-sm-10 image-col-left" style="height: 100%">
                                <div ng-if='Student.has_photo_original' style="height: calc(100% - 10px);">
                                    <img ng-src="img/students/{{ Student.id + '_original.' + Student.photo_extension }}?ver={{ picture_version }}" id='photo-edit' style="height: 100%">
                                </div>
                            </div>
                            <div class="col-sm-2 center image-col-right">
                                <div id="image-preview" ng-show='Student.has_photo_original'>
                                    <div class="form-group img-preview-container">
                                        <div class="img-preview"></div>
                                    </div>
                                </div>

                                <div class='photo-sizes'>
                                    <div ng-show='Student.photo_original_size'>
                                        {{ formatBytes(Student.photo_original_size) }}
                                    </div>
                                    <div ng-show='Student.photo_cropped_size'>
                                        {{ formatBytes(Student.photo_cropped_size) }}
                                    </div>
                                </div>

                                <div class="form-group">
                                    <div class="btn btn-primary full-width">Загрузить
                                        <span class="btn-file">
                                            <input name="photo" type="file" id="fileupload" data-url="upload/student">
                                        </span>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <button class="btn btn-primary full-width" ng-click='saveCropped()' ng-disabled='!Student.has_photo_original'>Сохранить</button>
                                </div>
                                <div class="form-group">
                                    <button class="btn btn-danger full-width" ng-click='deletePhoto()' ng-disabled='!Student.has_photo_original'>Удалить</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!--/photo modal-->

        <div class="col-sm-12">
            <div class="col-sm-2" style="width: 150px;">
                <div class="form-group">
                    <div class="tutor-img" ng-hide="Student.isNewRecord" style="margin-top: 0!important;" ng-class="{'border-transparent': Student.has_photo_cropped}" ng-click="showPhotoEditor()">
                        <div>
                            изменить фото
                        </div>
                        <span class="btn-file"></span>
                        <img ng-src="img/students/{{ Student.id && Student.has_photo_cropped ? Student.id + '.' + Student.photo_extension : 'no-profile-img.gif' }}?ver={{ picture_version }}">
                    </div>
                </div>
            </div>

        </div>
</div>