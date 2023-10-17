<?php

namespace App\Validations;

/**
 * Class StringLengthValidation
 */
class StringLengthValidation implements ValidationInterface {

  /**
   * Function validate
   * @param array $data
   * @param array $rules
   * @return array
   */
  public static function validate(array $data, array $rules): array {
    $valid = true;
    if ($valid) {
      return [];
    }
    return ["valid"=>$valid, "msg"=>$rules['msg']];
  }

}
