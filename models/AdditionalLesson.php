<?php

/**
 * Дополнительное (внеплановое) занятие
 */
class AdditionalLesson
{
	public static function add($data)
	{
		extract($data);

		$Group = Group::add(array_merge(compact(
			'teacher_price',
			'id_subject',
			'grade',
			'year',
			'id_teacher',
			'students'
		), [
			'is_unplanned' => 1
		]));

		VisitJournal::add(array_merge(compact(
			'lesson_date',
			'lesson_time',
			'cabinet'
		), [
			'id_group' => $Group->id
		]));

		return self::get($Group->id);
	}

	public static function updateById($id, $data)
	{
		extract($data);

		$Lesson = VisitJournal::findById($id);

		$Lesson->update(compact(
			'lesson_date',
			'lesson_time',
			'cabinet'
		));

		Group::updateById($Lesson->id_group, compact(
			'teacher_price',
			'id_subject',
			'grade',
			'year',
			'students'
		));

		return self::get($Lesson->id_group);
	}

	public static function getByEntity($type_entity, $id_entity)
	{
		$Groups = Group::findAll([
			'condition' => "is_unplanned=1 AND "
				. ($type_entity == Teacher::USER_TYPE ? "id_teacher={$id_entity}" : "FIND_IN_SET({$id_entity}, students)")
		]);

		$return = [];

		foreach($Groups as $Group) {
			$return[] = self::get($Group);
		}

		return $return;
	}

	public static function deleteById($id)
	{
		$Lesson = VisitJournal::findById($id);
		Group::deleteById($Lesson->id_group);
		$Lesson->delete();
	}

	/**
	 * $Group – id_group or Group::class
	 */
	public static function get($Group)
	{
		if (is_int($Group) || is_string($Group)) {
			$Group = Group::findById($Group, true);
		}
		$Lesson = VisitJournal::getGroupLessons($Group->id, 'find');
		error_log(json_encode($Lesson));
 		return [
			'id' => $Lesson->id,
			'teacher_price' => $Group->teacher_price,
			'id_subject' => $Lesson->id_subject,
			'grade' => $Lesson->grade,
			'year' => $Lesson->year,
			'id_teacher' => $Lesson->id_teacher,
			'students' => $Group->students,
			'cabinet' => $Lesson->cabinet,
			'lesson_date' => $Lesson->lesson_date,
			'lesson_time' => $Lesson->lesson_time,
			'id_group' => $Lesson->id_group,
			'grade' => $Lesson->grade,
			'grade_short' => $Lesson->grade_short,
			'is_planned' => $Lesson->grade_short,
			'cancelled' => $Lesson->cancelled,
			'is_conducted' => $Lesson->is_conducted,
			'lesson_date_formatted' => date_format(date_create($Lesson->lesson_date), "d.m.y")
		];
	}
}
