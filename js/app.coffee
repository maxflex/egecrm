app.requires.push 'ngResource'
app
.filter 'formatDateTime', ->
    (date) -> moment(date).format 'DD.MM.YY в HH:mm'

.filter 'byYear', ->
    (items, year) -> _.where items, year: year