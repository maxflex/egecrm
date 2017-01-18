app.service 'GroupService', ->
	@getYears = (groups) ->
		if groups
			return _.uniq _.pluck groups, 'year'
		return []

	@