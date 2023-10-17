<?php

namespace App\Services;

use App\Data\DbAccess;
use App\Utils\AppUtil;
use App\Validations\Validate;
use App\Data\QueryBuilder\PgSelectQueryBuilder;

/**
 * Class DbService
 */
class DbService {

  /**
   * Function selectData
   * @param mixed $params
   * @return array
   */
  public static function selectData(mixed $params): mixed {
    $resp = [];
    $data = [];
    $bind_params = [];
    if(is_array($params) && count($params) > 0) {
      $resp = PgSelectQueryBuilder::build($params);
    } else {
      $resp['msg']['parameters'] = "parameters json array is not valid";
    }
    if (count($resp['msg']) > 0) {
      return $resp['msg'];
    }
    if (strlen($resp['sql_query']) > 0) {
      $bind_params = (array_key_exists('bind_params', $resp))? $resp['bind_params'] : [];
      $data = DbAccess::selectData($resp['sql_query'], $bind_params);
    }
    return $data;
  }

  /**
   * Function insertGetId
   * @param string $table
   * @param mixed $data
   * @param string $primaryKeyName
   * @return mixed
   */
  public static function insertGetId(string $table, mixed $data, string $primaryKeyName='id'): mixed {
    return DbAccess::insertGetId($table, $data, $primaryKeyName);
  }


  /**
   * Function insertData
   * @param string $table
   * @param mixed $data
   * @param bool $ignoreError
   * @return mixed
   */
  public static function insertData(string $table, mixed $data, bool $ignoreError=FALSE): mixed {
    $vMsg = Validate::check($data, $table);
    if (count($vMsg) > 0) {
      return ['validations' => $vMsg];
    }
    return DbAccess::insertData($table, $data, $ignoreError);
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
    $vMsg = Validate::check([$data], $table);
    if (count($vMsg) > 0) {
      return ['validations' => $vMsg];
    }
    return DbAccess::updateData($table, $where, $data, $binding);
  }

  /**
   * Function increment fields
   * @param string $table
   * @param mixed $data
   * @return mixed
   */
  public static function incrementFields(string $table, string $where, array $data, array $other, array $binding=[]): mixed {
    return DbAccess::incrementFields($table, $where, $data, $other, $binding);
  }

  /**
   * Function decrementFields
   * @param string $table
   * @param mixed $data
   * @return mixed
   */
  public static function decrementFields(string $table, string $where, array $data, array $other, array $binding=[]): mixed {
    return DbAccess::decrementFields($table, $where, $data, $other, $binding);
  }

  /**
   * Function deleteData
   * @param string $table
   * @param string $where
   * @param mixed $bindings
   * @return mixed
   */
  public static function deleteData(string $table, string $where, mixed $bindings=[]): mixed {
    return DbAccess::deleteData($table, $where, $bindings);
  }

}
