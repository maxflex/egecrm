app.requires.push 'ngResource'
app
.filter 'formatDateTime', ->
    (date) -> moment(date).format 'DD.MM.YY в HH:mm'

.filter 'byYear', ->
    (items, year) -> _.where items, year: year

.filter 'orderObjectBy', ->
	(items, field, reverse) ->
		filtered = []
		angular.forEach items, (item) -> filtered.push(item)
		filtered.sort (a, b) -> (a[field] > b[field] ? 1 : -1)
		filtered.reverse() if (reverse)
		filtered