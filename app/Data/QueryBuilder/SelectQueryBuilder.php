<?php

namespace App\Data\QueryBuilder;

use App\Data\DbAccess;
use App\Utils\AppUtil;

/**
 * Class SelectQueryBuilder
 */
class SelectQueryBuilder 
{

  /**
   * Property restrictTables
   * @var array
   */
  protected static bool $restrictTables = True;

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
    if (is_array($resp) && is_array($res)) {
      if(array_key_exists('msg', $resp) && array_key_exists('msg', $res)) {
        $resp['msg'] = array_merge($resp['msg'], $res['msg']);
      }
      if(array_key_exists('sql_query', $resp) && array_key_exists('sql_query', $res)) {
        $resp['sql_query'] = $resp['sql_query'] . ' ' . $res['sql_query'];
      }
      return $resp;
    }
    return [];
  }

  /**
   * Function all tables
   * @return array
   */
  protected static function tables(): array {
    if (count(SelectQueryBuilder::$allTableNames) > 0) {
      return SelectQueryBuilder::$allTableNames;
    } else {
      $allTables = DbAccess::allTableNames();
      SelectQueryBuilder::$allTableNames = array_map(function ($rec) { return $rec->table_name; }, (array) $allTables);
      return SelectQueryBuilder::$allTableNames;
    }
  }

  /**
   * Function restrict tables access
   * @return bool
   */
  protected static function restrictTablesAccess(string $table): bool {
    $table = (substr_count($table,' '))? trim(substr(trim($table), 0, strpos($table, ' '))) : trim($table);
    if (strlen($table) > 0) {
      if (SelectQueryBuilder::$restrictTables) {
        $tableNames = SelectQueryBuilder::tables();
        return in_array($table, $tableNames);
      }
      return True;
    }
    return False;
  }

  /**
   * Function table
   * @param mixed $sql_table
   * @return array
   */
  protected static function table(mixed $sql_table, bool $retTableName=False): array {
    $resp = SelectQueryBuilder::$initialResp;
    if(is_string($sql_table)) {
      $sql_table = AppUtil::escapeString($sql_table);
      if (!empty($sql_table) && strlen(trim($sql_table)) > 0) {
        if (!SelectQueryBuilder::restrictTablesAccess($sql_table)) {
          $resp['msg']['table'] = "can't access internal table";
        }
        if($retTableName) {
          return [$sql_table];
        }
        $resp['sql_query'] = "SELECT * FROM " . $sql_table;
      } else {
        $resp['msg']['table'] = "table must be a valid string";
      }
    } else {
      $resp['msg']['table'] = "table must be a string";
    }
    return $resp;
  }

  /**
   * Function select
   * @param mixed $fields
   * @param mixed $table
   * @return array
   */
  protected static function select(mixed $fields, string $table): array {
    $resp = SelectQueryBuilder::$initialResp;
    $res = SelectQueryBuilder::table($table);
    $resp = SelectQueryBuilder::mergeResp($resp, $res);
    $table_name = SelectQueryBuilder::table($table, TRUE)[0];
    $sql_table = (array_key_exists('table', $resp['msg']))? $table: $table_name;
    if(!is_array($fields)) {
      $resp['msg']['fields'] = "fields must be an array";
    } elseif (count($fields) > 0) {
      $sql_fields = AppUtil::escapeString(implode(', ', $fields));
      if (empty(trim(str_replace(',', '', $sql_fields)))) {
        $sql_fields = ' * ';
      }
      $resp['sql_query'] = "SELECT " . $sql_fields . " FROM " . $sql_table;
    }
    return $resp;
  }

  /**
   * Function inner
   * @param mixed $resp
   * @param mixed $inner
   * @return array
   */
  protected static function inner(mixed $resp, mixed $inner): array {
    if (!is_array($inner)) {
      $resp['msg']['inner'] = "inner must be an array";
    } elseif (count($inner) > 0) {
      $sql_inner = " ";  // AppUtil::escapeString(implode(' ', $inner));
      foreach ($inner as $inner_table) {
        if (is_array($inner_table) && array_key_exists('table', $inner_table) && array_key_exists('relation', $inner_table)) {
          $inner_table_name = SelectQueryBuilder::table($inner_table['table'], True)[0];
          $inner_table_rel = AppUtil::escapeString($inner_table['relation']);
          if (strlen($inner_table_name) > 0 && strlen($inner_table_rel) > 0) {
            $sql_inner = $sql_inner . " INNER JOIN " . $inner_table_name . " ON " . $inner_table_rel;
          }
        } else {
          $resp['msg']['inner'] = "inner must have a valid table and relation";
        }
      }
      if (!empty($sql_inner) && strlen(trim($sql_inner)) > 0) {
        $resp['sql_query'] = $resp['sql_query'] . $sql_inner;
      } else {
        $resp['msg']['inner'] = "inner must be a valid array";
      }
    }
    return $resp;
  }

  /**
   * Function left
   * @param mixed $resp
   * @param mixed $left
   * @return array
   */
  protected static function left(mixed $resp, mixed $left): array {
    if (!is_array($left)) {
      $resp['msg']['left'] = "left must be an array";
    } elseif (count($left) > 0) {
      $sql_left = " ";
      foreach ($left as $left_table) {
        if (is_array($left_table) && array_key_exists('table', $left_table) && array_key_exists('relation', $left_table)) {
          $left_table_name = SelectQueryBuilder::table($left_table['table'], True)[0];
          $left_table_rel = AppUtil::escapeString($left_table['relation']);
          if (strlen($left_table_name) > 0 && strlen($left_table_rel) > 0) {
            $sql_left = $sql_left . " LEFT JOIN " . $left_table_name . " ON " . $left_table_rel;
          }
        } else {
          $resp['msg']['left'] = "left must have a valid table and relation";
        }
      }
      if (!empty($sql_left) && strlen(trim($sql_left)) > 0) {
        $resp['sql_query'] = $resp['sql_query'] . $sql_left;
      } else {
        $resp['msg']['left'] = "left must be a valid array";
      }
    }
    return $resp;
  }

  /**
   * Function where
   * @param mixed $resp
   * @param mixed $where
   * @return array
   */
  protected static function where(mixed $resp, mixed $where): array {
    if (!is_string($where)) {
      $resp['msg']['where'] = "where must be a string";
    } elseif (!empty($where) && strlen(trim($where)) > 0) {
      $sql_where = AppUtil::escapeString($where);
      if (!empty($sql_where) && strlen(trim($sql_where)) > 0) {
        $resp['sql_query'] = $resp['sql_query'] . " WHERE " . $sql_where;
      } else {
        $resp['msg']['where'] = "where must be a valid string";
      }
    }
    return $resp;
  }

  /**
   * Function group
   * @param mixed $resp
   * @param mixed $group
   * @return array
   */
  protected static function group(mixed $resp, mixed $group): array {
    if (!is_array($group)) {
      $resp['msg']['group'] = "group must be an array";
    } elseif (count($group) > 0) {
      $sql_group = AppUtil::escapeString(implode(', ', $group));
      if (!empty($sql_group) && strlen(trim($sql_group)) > 0) {
        $resp['sql_query'] = $resp['sql_query'] . " GROUP BY " . $sql_group;
      }
    }
    return $resp;
  }

  /**
   * Function having
   * @param mixed $resp
   * @param mixed $having
   * @return array
   */
  protected static function having(mixed $resp, mixed $having): array {
    if (!is_string($having)) {
      $resp['msg']['having'] = "having must be a string";
    } elseif (!empty($having) && strlen(trim($having)) > 0) {
      $sql_having = AppUtil::escapeString($having);
      if (!empty($sql_having) && strlen(trim($sql_having)) > 0) {
        $resp['sql_query'] = $resp['sql_query'] . " HAVING " . $sql_having;
      }
    }
    return $resp;
  }

  /**
   * Function sort
   * @param mixed $resp
   * @param mixed $sort
   * @return array
   */
  protected static function sort(mixed $resp, mixed $sort): array {
    if (!is_array($sort)) {
      $resp['msg']['sort'] = "sort must be an array";
    } elseif (count($sort) > 0) {
      $sql_sort = AppUtil::escapeString(implode(', ', $sort));
      if (!empty($sql_sort) && strlen(trim($sql_sort)) > 0) {
        $resp['sql_query'] = $resp['sql_query'] . " ORDER BY " . $sql_sort;
      }
    }
    return $resp;
  }

  /**
   * Function limit
   * @param mixed $resp
   * @param mixed $limit
   * @return array
   */
  protected static function limit(mixed $resp, mixed $limit): array {
    $limit = (intval($limit) > 0)? $limit : SelectQueryBuilder::$defaultLimit;
    if(intval($limit) > 0) {
      $sql_limit = intval($limit);
      $resp['sql_query'] = $resp['sql_query'] . " LIMIT " . $sql_limit;
    } else {
      $resp['msg']['limit'] = "limit must be an integer";
    }
    return $resp;
  }

  /**
   * Function offset
   * @param mixed $resp
   * @param mixed $offset
   * @return array  
   */
  protected static function offset(mixed $resp, mixed $offset): array {
    if (intval($offset) >= 0) {
      $sql_offset = intval($offset);
      $resp['sql_query'] = $resp['sql_query'] . " OFFSET " . $sql_offset;
    } else {
      $resp['msg']['offset'] = "offset must be an integer";
    }
    return $resp;
  }

  /**
   * Function bind
   * @param mixed $resp
   * @param mixed $offset
   * @return array
   */
  protected static function bind(mixed $resp, mixed $bind): array {
    if (!is_array($bind)) {
      $resp['msg']['bind'] = "bind must be an array";
    } elseif (count($bind) > 0) {
      $resp['bind_params'] = $bind;
      // Mostly not needed but uncomment if required
      // $resp['bind_params'] = array_map('AppUtil::escapeString', $bind);
    }
    return $resp;
  }

  /**
   * Function builder
   * @param array $params
   * @return array
   */
  protected static function build(array $params): array {
    $resp = [];
    foreach(SelectQueryBuilder::$selectClauseFnSeq as $fn) {
      if ($fn == 'select') {
        $resp = SelectQueryBuilder::$fn($params['fields'], $params['table']);
      } else {
        $resp = SelectQueryBuilder::$fn($resp, $params[$fn]);
      }
    }
    return $resp;
  }

}
