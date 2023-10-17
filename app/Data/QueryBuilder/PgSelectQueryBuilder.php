<?php

namespace App\Data\QueryBuilder;

use App\Utils\AppUtil;

/**
 * Class SelectQueryBuilder
 */
class PgSelectQueryBuilder extends SelectQueryBuilder {

  /**
   * Property defaultLimit
   * @var int
   */
  protected static int $defaultLimit = 10;

  /**
   * Property selectClauseFnSeq
   * @var array
   */
  protected static array $selectClauseFnSeq = ['select', 'inner', 'left', 'where', 'group', 'having', 'sort', 'limit', 'offset', 'bind'];

  /**
   * Property initialResp
   * @var array
   */
  protected static array $initialResp = ['msg'=>[], 'sql_query'=>''];

  /**
   * Property allTablesNames
   * @var array
   */
  protected static array $allTableNames = [];

  /**
   * Function mergeResp
   * @param mixed $resp
   * @param mixed $res
   * @return array
   */
  protected static function mergeResp($resp, $res): array {
    return parent::mergeResp($resp, $res);
  }

  /**
   * Function all tables
   * @return array
   */
  protected static function tables(): array {
    return parent::tables();
  }

  /**
   * Function restrict tables access
   * @return bool
   */
  protected static function restrictTablesAccess(string $table): bool {
    return parent::restrictTablesAccess($table);
  }
    
  /**
   * Function table
   * @param mixed $sql_table
   * @return array
   */
  public static function table(mixed $sql_table, bool $retTableName=False): array {
    return parent::table($sql_table, $retTableName);
  }

  /**
   * Function select
   * @param mixed $fields
   * @param mixed $table
   * @return array
   */
  public static function select(mixed $fields, string $table): array {
    return parent::select($fields, $table);
  }

  /**
   * Function inner
   * @param mixed $resp
   * @param mixed $inner
   * @return array
   */
  public static function inner(mixed $resp, mixed $inner): array {
    return parent::inner($resp, $inner);
  }

  /**
   * Function left
   * @param mixed $resp
   * @param mixed $left
   * @return array
   */
  public static function left(mixed $resp, mixed $left): array {
    return parent::left($resp, $left);
  }

  /**
   * Function where
   * @param mixed $resp
   * @param mixed $where
   * @return array
   */
  public static function where(mixed $resp, mixed $where): array {
    return parent::where($resp, $where);
  }

  /**
   * Function group
   * @param mixed $resp
   * @param mixed $group
   * @return array
   */
  public static function group(mixed $resp, mixed $group): array {
    return parent::group($resp, $group);
  }

  /**
   * Function having
   * @param mixed $resp
   * @param mixed $having
   * @return array
   */
  public static function having(mixed $resp, mixed $having): array {
    return parent::having($resp, $having);
  }

  /**
   * Function sort
   * @param mixed $resp
   * @param mixed $sort
   * @return array
   */
  public static function sort(mixed $resp, mixed $sort): array {
    return parent::sort($resp, $sort);
  }

  /**
   * Function limit
   * @param mixed $resp
   * @param mixed $limit
   * @return array
   */
  public static function limit(mixed $resp, mixed $limit): array {
    return parent::limit($resp, $limit);
  }

  /**
   * Function offset
   * @param mixed $resp
   * @param mixed $offset
   * @return array  
   */
  public static function offset(mixed $resp, mixed $offset): array {
    return parent::offset($resp, $offset);
  }

  /**
   * Function bind
   * @param mixed $resp
   * @param mixed $offset
   * @return array
   */
  public static function bind(mixed $resp, mixed $bind): array {
    return parent::bind($resp, $bind);
  }

  /**
   * Function builder
   * @param array $params
   * @return array
   */
  public static function build(array $params): array {
    $resp = [];
    foreach(PgSelectQueryBuilder::$selectClauseFnSeq as $fn) {
      if ($fn == 'select') {
        $resp = PgSelectQueryBuilder::$fn($params['fields'], $params['table']);
      } else {
        $resp = PgSelectQueryBuilder::$fn($resp, $params[$fn]);
      }
    }
    return $resp;
  }

}
