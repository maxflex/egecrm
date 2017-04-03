<?php

class Log extends Model
{
    public static $mysql_table  = "logs";
    public static $row_id  = null;

    protected $loggable = false;
    protected static $types = ['create', 'update', 'delete'];

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

        return false;
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

                        if (Log::VERBOSE) {
                            var_dump($field, $model->$field, $originalModel->$field);
                        }

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

        $data = static::findAll([
            'condition' => static::_generateQuery($search),
            'order'     => 'created_at desc',
            'limit'     => ($page == -1 ? static::PER_PAGE : "{$start_from}, " . static::PER_PAGE),
        ]);
        // $counts = static::counts($search);
        $counts['all'] = static::_count($search); // закомментить, если понадобится counts. верхнее раскомментить

        $query = static::_generateQuery($search);
        return compact('data', 'counts', 'query');
    }

    private static function _generateQuery($search)
    {

        $search = filterParams($search);
        $condition = [];

        if (isset($search->table)) {
            $condition[] = "`table` = '{$search->table}'";
        }

        if (isset($search->row_id)) {
            $condition[] = "row_id = {$search->row_id}";
        }

        if (isset($search->type)) {
            $condition[] = "type = '{$search->type}'";
        }

        if (isset($search->user_id)) {
            $condition[] = "user_id = {$search->user_id}";
        }

        if (isset($search->date_start)) {
            $date = fromDotDate($search->date_start);
            $condition[] = "created_at >= '{$date} 00:00:00'";
        }

        if (isset($search->date_end)) {
            $date = fromDotDate($search->date_end);
            $condition[] = "created_at <= '{$date} 23:59:59'";
        }

        if (isset($search->column)) {
            $condition[] = "data like '%{$search->column}%'";
        }

        return empty($condition) ? '1' : implode(' and ', $condition);
    }

    private static function _count($search)
    {
        return static::count(['condition' => static::_generateQuery($search)]);
//        return [
//            'condition' => static::_generateQuery($search),
//            'value' => static::count(['condition' => static::_generateQuery($search)])
//        ];
    }

    private function counts($search)
    {
        $tables = static::getTables();
        $counts['all'] = static::_count($search);

        foreach(array_merge([''], static::$types) as $type) {
            $new_search = clone $search;
            $new_search->type = $type;
            $counts['type'][$type] = static::_count($new_search);
        }

        foreach(array_merge(['', 0], User::getIds()) as $user_id) {
            $new_search = clone $search;
            $new_search->user_id = $user_id;
            $counts['user'][$user_id] = static::_count($new_search);
        }

        foreach(array_merge([''], $tables) as $table) {
            $new_search = clone $search;
            $new_search->table = $table;

            $counts['table'][$table] = static::_count($new_search);
        }

        foreach(array_merge([''], static::getTableColumns($tables)) as $column) {
            $new_search = clone $search;
            $new_search->column = $column;
            $counts['column'][$column] = static::_count($new_search);
        }

        return $counts;
    }
}
