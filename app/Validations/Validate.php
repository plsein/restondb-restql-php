<?php

namespace App\Validations;

use App\Data\DbAccess;
use Exception;

/**
 * Class Validate
 */
class Validate {

  /**
   * Variable $rules
   * @var array
   */
  private static $rules = [];

  /**
   * Function getTableRules
   * @param string $table
   * @param string $schema
   * @return array
   */
  public static function getTableRules(string $table, string $schema=''): array {
    if (array_key_exists($table, Validate::$rules) && is_array(Validate::$rules[$table])) {
      return Validate::$rules[$table];
    }
    $comments = DbAccess::columnComments($table, $schema);
    Validate::$rules[$table] = [];
    $v = [];
    if (is_array($comments) && count($comments) > 0) {
      foreach ($comments as $rec) {
        $rec = (array) $rec;
        $v = [];
        try {
          $v = (strlen($rec['column_comment']) > 0)? json_decode($rec['column_comment'], true) : $v;
        } catch (Exception $e) {
          // no validations
        }
        if (array_key_exists('validations', $v)) {
          Validate::$rules[$table][$rec['column_name']] = $v['validations'];
        }
      }
    }
    return Validate::$rules[$table];
  }

  /**
   * Function check
   * @param mixed $data
   * @param string $table
   * @param array $checks
   * @return array
   */
  public static function check(mixed $data, string $table, array $checks=[]): array {
    $msg = [];
    $idx = 0;
    if (is_string($table) && strlen($table) > 0) {
      if (array_key_exists($table, Validate::$rules) && is_array(Validate::$rules[$table])) {
        $checks = Validate::$rules[$table];
      } else {
        $checks = Validate::getTableRules($table);
      }
    }
    if (is_array($data) && count($data) > 0 && is_array($checks) && count($checks) > 0) {
      foreach ($data as $rec) {
        $msg['0'.$idx] = [];
        foreach ($rec as $key => $value) {
          $msg['0'.$idx][$key] = [];
          if (array_key_exists($key, $checks) && is_array($checks[$key]) && count($checks[$key]) > 0) {
            foreach ($checks[$key] as $check => $values) {
              $check_name = __NAMESPACE__.'\\'.$check;
              // $msg[''.$idx][$key][$check] = $check::validate($data, $values);
              $msg['0'.$idx][$key][$check] = call_user_func($check_name.'::validate', $rec, $values);
              if (count($msg['0'.$idx][$key][$check]) < 1) {
                unset($msg['0'.$idx][$key][$check]);
              }
            }
          }
          if (count($msg['0'.$idx][$key]) < 1) {
            unset($msg['0'.$idx][$key]);
          }
          if (count($msg['0'.$idx]) < 1) {
            unset($msg['0'.$idx]);
          }
        }
        $idx = $idx + 1;
      }
    }
    return $msg;
  }

}
