<?php

namespace App\Validations;

/**
 * Interface Validation
 */
interface ValidationInterface {

  /**
   * Function validate
   * @param array $data
   * @param array $rules
   * @return array
   */
  public static function validate(array $data, array $rules): array;

}
