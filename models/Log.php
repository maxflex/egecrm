<?php

class Log extends Model
{
    public static $mysql_table  = "logs";
    public static $row_id  = null;

    protected $loggable = false;

    protected $_json = ["data"];

    const DO_NOT_LOG = ['id', 'created_at', 'updated_at'];
    const PER_PAGE = 30;

    const VERBOSE = false;

    public static function add($model, $action = false)
    {
        $s = microtime(true);
        $dirty_fields = static::_generateData($model);


        if (count($dirty_fields) || $action) {
            if (Log::VERBOSE) var_dump($model->getTable(), 'dirty fields: ' . count($dirty_fields), $dirty_fields);

            $log = parent::add([
                'user_id'   => (($user_id = User::fromSession()->id) ? $user_id : 0),
                'row_id'    => $model->id,
                'data'      => $dirty_fields,
                'table'     => $model->getTable(),
                'type'      => $action ? $action : static::_getType($model),
                'created_at'=> now()
            ]);

            if ($model->isNewRecord) { // to update entity_id afted adding;
                static::$row_id = $log->id;
            }

            if (Log::VERBOSE) var_dump('log ended in ' . (microtime(true) - $s));
        }
    }

    public static function _generateData($model)
    {
        $dirty_fields = [];

        $modelClass = get_class($model);
        $fields = array_diff($model->mysql_vars, $model->log_except, static::DO_NOT_LOG);

        if (! empty($fields)) {
            $originalModel = $modelClass::findById($model->id, true);
            if ($originalModel) { // adding model retrieve fix;
                $originalModel->beforeSave();
            }

            foreach ($fields as $field) {
                if (isset($model->$field)) {
                    // у стрингов бывало пробелы добавлялись в конец автомаов ангуляром наверно, хотя хз.
                    if (trim_strings($model->$field) != trim_strings($originalModel->$field)) {

                        var_dump($field, $model->$field, $originalModel->$field);

                        // сравнение [] с ''
                        if (!(empty($model->$field) && empty($originalModel->$field))) {
                            $dirty_fields[$field] = [$originalModel->$field, $model->$field];
                        }
                    }
                }
            }
        }

        return $dirty_fields;
    }

    public static function _getType($model)
    {
        return $model->isNewRecord ? 'create' : 'update';
    }

    public static function updateField($fields = [])
    {
        if (static::$row_id) {
            $update_vals = '';
            foreach ($fields as $key => $val) {
                $update_vals[] = $key . " = '" . $val . "'";
            }

            static::dbConnection()->query('update logs set ' . implode(',', $update_vals) . ' where id = ' . static::$row_id);
            static::$row_id = null;
        }
    }

    public static function getTables()
    {
        $tables = static::dbConnection()->query('select group_concat(distinct `table` order by `table`) as tables from ' . static::$mysql_table)->fetch_object()->tables;
        return empty($tables) ? [] : explode(',', $tables);
    }

    public static function getTableColumns($tables = [])
    {
        $cols = [];
        foreach ($tables as $table) {
            $cols += Model::_getMysqlVars($table);
        }
        sort($cols);
        return array_unique($cols);
    }

    public static function getData($page)
    {
        if (!$page) {
            $page = 1;
        }
        // С какой записи начинать отображение, по формуле
        $start_from = ($page - 1) * Log::PER_PAGE;

        $search = isset($_COOKIE['logs']) ? json_decode($_COOKIE['logs']) : (object)[];

//        $query = static::_generateQuery($search, ($page == -1 ? "s.id" : "s.id, s.first_name, s.last_name, s.middle_name "));
//        $result = dbConnection()->query($query . ($page == -1 ? "" : " LIMIT {$start_from}, " . Student::PER_PAGE));
//
//        while ($row = $result->fetch_object()) {
//            $data[] = ($page == -1 ? $row->id : $row);
//        }

//        if ($page > 0) {
//            // counts
//            $counts['all'] = static::_count($search);
//
//            foreach(array_merge([""], Years::$all) as $year) {
//                $new_search = clone $search;
//                $new_search->year = $year;
//                $counts['year'][$year] = static::_count($new_search);
//            }
//            foreach(["", 0, 1] as $green) {
//                $new_search = clone $search;
//                $new_search->green = $green;
//                $counts['green'][$green] = static::_count($new_search);
//            }
//            foreach(["", 0, 1] as $yellow) {
//                $new_search = clone $search;
//                $new_search->yellow = $yellow;
//
//                $counts['yellow'][$yellow] = static::_count($new_search);
//            }
//            foreach(["", 0, 1] as $red) {
//                $new_search = clone $search;
//                $new_search->red = $red;
//                $counts['red'][$red] = static::_count($new_search);
//            }
//
//            foreach(array_merge([''], range(0,3)) as $error) {
//                $new_search = clone $search;
//                $new_search->error = $error;
//                $counts['error'][$error] = static::_count($new_search);
//            }
//        }


        $data = static::findAll();
        $counts = (object)[];

        return [
            'data' 	=> $data,
            'counts' => $counts,
        ];
    }
}
