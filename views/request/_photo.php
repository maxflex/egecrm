<div ng-show="current_menu == 7">
    <div class="row">
        <?= globalPartial('loading', ['model' => 'student']) ?>
        <div class="col-sm-12 ng-hide" ng-show="student !== undefined">
            <div class="row">
                <!--photo modal--> <!-- // @todo - all modals in one file -->
                <div class="modal modal-fullscreen" tabindex="-1" id='change-photo'>
                    <div class="modal-dialog" style="width: 80%; height: 90%; margin: 3% auto">
                        <div class="modal-content" style="height: 100%">
                            <div class="modal-body" style="height: 100%">
                                <div class="row" style="height: 100%">
                                    <div class="col-sm-10 image-col-left" style="height: 100%">
                                        <div ng-if='student.has_photo_original' style="height: calc(100% - 10px);">
                                            <img src="img/students/{{ student.id + '_original.' + student.photo_extension }}?ver={{ picture_version }}" id='photo-edit' style="height: 100%">
                                        </div>
                                    </div>
                                    <div class="col-sm-2 center image-col-right">
                                        <div id="image-preview" ng-show='student.has_photo_original'>
                                            <div class="form-group img-preview-container">
                                                <div class="img-preview"></div>
                                            </div>
                                        </div>

                                        <div class='photo-sizes'>
                                            <div ng-show='student.photo_original_size'>
                                                {{ formatBytes(student.photo_original_size) }}
                                            </div>
                                            <div ng-show='student.photo_cropped_size'>
                                                {{ formatBytes(student.photo_cropped_size) }}
                                            </div>
                                        </div>

                                        <div class="form-group">
                                            <button class="btn btn-primary full-width">Загрузить
                                                <span class="btn-file">
                                        <input name="photo" type="file" id="photoupload" data-url="upload/student">
                                    </span>
                                            </button>
                                        </div>
                                        <div class="form-group">
                                            <button class="btn btn-primary full-width" ng-click='saveCropped()' ng-disabled='!student.has_photo_original'>Сохранить</button>
                                        </div>
                                        <div class="form-group">
                                            <button class="btn btn-danger full-width" ng-click='deletePhoto()' ng-disabled='!student.has_photo_original'>Удалить</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!--/photo modal-->

                <div class="col-sm-2" style="width: 150px;">
                    <div class="form-group">
                        <div class="tutor-img" ng-hide="student.isNewRecord" style="margin-top: 0!important;" ng-class="{'border-transparent': student.has_photo_cropped}" ng-click="showPhotoEditor()">
                            <div>
                                изменить фото
                            </div>
                            <span class="btn-file"></span>
                            <img src="img/students/{{ student.id && student.has_photo_cropped ? student.id + '.' + student.photo_extension : 'no-profile-img.gif' }}?ver={{ picture_version }}">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>