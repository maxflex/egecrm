app.directive 'comments', ->
    restrict: 'E'
    templateUrl: 'directives/comment'
    scope:
        user: '='
        entityId: '='
        trackLoading: '='
        entityType: '@'
    controller: ($rootScope, $scope, $timeout, UserService) ->
        $scope.UserService = UserService
        $scope.show_max = 4                 # сколько комментов показывать в свернутом режиме
        $scope.show_all_comments = false    # показать все комментарии?


        $scope.showAllComments = ->
            $scope.show_all_comments = true
            focusModal()

        $scope.getComments = ->
            if $scope.comments
                if ($scope.show_all_comments or $scope.comments.length <= $scope.show_max) then $scope.comments else _.last($scope.comments, $scope.show_max - 1)
            else
                []

        $scope.$watch 'entityId', (newVal, oldVal) ->
            if $scope.entityType and $scope.entityId
                $.post "get/comments/#{$scope.entityType}/#{$scope.entityId}"
                , {}
                , (response) ->
                    $scope.comments = response
                    $rootScope.loaded_comments++ if $scope.trackLoading
                    $rootScope[$scope.entityType.toLowerCase() + '_comments_loaded'] = true
                , 'json'

        $scope.formatDateTime = (date) ->
            moment(date).format "DD.MM.YY в HH:mm"

        $scope.startCommenting = (event) ->
            $scope.start_commenting = true
            $timeout ->
                $(event.target).parent().find('input').focus()

        $scope.endCommenting = ->
            $scope.comment = ''
            $scope.start_commenting = false

        $scope.remove = (comment_id) ->
            $.post "ajax/DeleteComment", {"id" : comment_id}
            , ->
                $scope.comments = _.without($scope.comments, _.findWhere($scope.comments, {id: comment_id}))

        $scope.edit = (comment, event) ->
            old_text    = comment.comment
            element     = $(event.target)
            # комментарий редактируется...
            comment.is_being_edited = true

            element.unbind('keydown').unbind('blur')

            element.attr('contenteditable', 'true').focus()
            .on 'keydown', (e) ->
                if e.keyCode is 13
                    $(@).removeAttr('contenteditable').blur()
                    comment.comment = $(@).text()
                    $.post 'ajax/EditComment',
                        id: comment.id,
                        comment: comment.comment

                if e.keyCode is 27
                    $(@).blur()

            .on 'blur', (e) ->
                $timeout ->
                    _.find($scope.comments, {id: comment.id}).is_being_edited = false
                    $scope.$apply()
                , 100
                if element.attr 'contenteditable'
                    element.removeAttr('contenteditable').html old_text
            return
        $scope.submitComment = (event) ->
            if event.keyCode is 13
                event.preventDefault()

                $.post 'ajax/AddComment',
                    comment: $scope.comment
                    id_user: $scope.user.id
                    id_place: $scope.entityId
                    place: $scope.entityType
                , (response) ->
                    $scope.comments.push response
                    , 400
                , 'json'
                $scope.endCommenting()
                focusModal();

            if event.keyCode is 27
                $(event.target).blur()

        focusModal = ->
            $('.modal:visible').focus() if $('.modal:visible').length
            return
