<?php

namespace App\Data;

use App\Utils\AppUtil;
use Illuminate\Support\Facades\DB;

/**
 * Class DbAccess
 */
class DbAccess {

  /**
   * Function getPdo
   * @return \PDO
   */
  private static function getPdo() {
    return DB::connection()->getPdo();
  }

  /**
   * Function allTableNames
   * @return mixed
   */
  public static function allTableNames(): mixed {
    $db_schemas = env('DB_SCHEMA', 'public');
    $db_schemas = "'".implode("','", explode(',', trim($db_schemas)))."'";
    return DB::select("SELECT table_name FROM information_schema.tables  WHERE table_schema IN (".$db_schemas.")");
  }

  /**
   * Function columnComments
   * @param string $table
   * @param string $schema
   * @return mixed
   */
  public static function columnComments(string $table, string $schema=''): mixed {   // , fields
    $db_schemas = (is_string($schema) && strlen($schema) > 0)? "'" + $schema + "'" : "'".implode("','", explode(',', trim(env('DB_SCHEMA', 'public'))))."'";
    $table_name = AppUtil::escapeString($table);
    // $table_fields = "'" + implode("','", fields) + "'";
    $sql_query = "SELECT table_schema, table_name, column_name, COL_DESCRIPTION((table_schema||'.'||table_name)::regclass::oid, ordinal_position) AS column_comment "
      . " FROM information_schema.columns WHERE table_schema IN (" . $db_schemas . ") AND table_name='" . $table_name . "'"
      . " AND col_description((table_schema||'.'||table_name)::regclass::oid, ordinal_position) IS NOT null";   //  AND column_name IN ("+table_fields+")
    return DB::select($sql_query);
  }

  /**
   * Function selectData
   * @param string $query
   * @param mixed $params
   * @return array
   */
  public static function selectData(string $query, mixed $params): mixed {
    return DB::select($query, $params);
  }

  /**
   * Function insertGetId
   * @param string $table
   * @param mixed $data
   * @param string $primaryKeyName
   * @return mixed
   */
  public static function insertGetId(string $table, mixed $data, string $primaryKeyName='id'): mixed {
    return DB::table($table)->insertGetId($data, $primaryKeyName);
  }

  /**
   * Function insertData
   * @param string $table
   * @param mixed $data
   * @param bool $ignoreError
   * @return mixed
   */
  public static function insertData(string $table, mixed $data, bool $ignoreError=FALSE): mixed {
    if ($ignoreError) {
      return DB::table($table)->insertOrIgnore($data);
    }
    return DB::table($table)->insert($data);
  }

  /**
   * Function updateData
   * @param string $table
   * @param string $where
   * @param array $binding
   * @param mixed $data
   * @return mixed
   */
  public static function updateData(string $table, string $where, mixed $data, array $binding=[]): mixed {
    return DB::table($table)->whereRaw(AppUtil::escapeString($where), $binding)->update($data);
  }

  /**
   * Function increment fields
   * @param string $table
   * @param array $data
   * @param array $other
   * @return mixed
   */
  public static function incrementFields(string $table, string $where, array $data, array $other, array $binding=[]): mixed {
    return DB::table($table)->whereRaw(AppUtil::escapeString($where), $binding)->incrementEach($data, $other);
  }

  /**
   * Function decrementFields
   * @param string $table
   * @param array $data
   * @param array $other
   * @return mixed
   */
  public static function decrementFields(string $table, string $where, mixed $data, array $other, array $binding=[]): mixed {
    return DB::table($table)->whereRaw(AppUtil::escapeString($where), $binding)->decrementEach($data, $other);
  }

  /**
   * Function deleteData
   * @param string $table
   * @param string $where
   * @param array $bindings
   * @return mixed
   */
  public static function deleteData(string $table, string $where, array $bindings=[]): mixed {
    return DB::table($table)->whereRaw(AppUtil::escapeString($where), $bindings)->delete();
  }

}
