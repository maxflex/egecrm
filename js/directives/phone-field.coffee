app.directive 'ngPhone', ->
        restrict: 'A'
        link: ($scope, element) ->
            $(element).inputmask("+7 (999) 999-99-99", { autoclear: false, showMaskOnHover: false })
