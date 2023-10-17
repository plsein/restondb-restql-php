<?php

namespace App\Utils;

use Illuminate\Http\Response;

if (!function_exists('array_is_list')) {
  /**
   * Function array_is_list
   * @param array $arr
   * @return bool
   */
  function array_is_list(array $arr) {
    if ($arr === []) {
      return true;
    }
    return array_keys($arr) === range(0, count($arr) - 1);
  }
}

/**
 * AppUtil class
 */
class AppUtil {

  public static function makeResponse(mixed $content=[], string $contentType='JSON', int $status=200, array $msg=[]) {
    $resp = [];
    if ($contentType == 'JSON') {
      $content = (is_array($content)) ? $content : [['value'=>$content]];
      $resp['code'] = $status;
      $resp['msg'] = (is_array($msg) && count($msg) > 0) ? $msg : ((intval($status) == 200) ? ['status'=>'ok'] : ['status'=>'error']);
      $resp['data'] = $content;
    } else {
      return $content;
    }
    return $resp;
  }
  
  public static function sendResponse(mixed $content=[], int $status=200, array $msg=[], string $contentType='JSON', mixed $request=NULL) {
    $resp = AppUtil::makeResponse($content, $contentType, $status, $msg);
    if ($contentType == 'JSON') {
      return response()->json($resp, $status);
    } elseif ($contentType == 'JSONP') {
      return response()->json($resp, $status)->setCallback($request->input('callback'));
    }
    return response($content, $status)->header('Content-Type', $contentType);
  }

  public static function download(string $filePath, string $fileName, int $status=200, array $headers=[]) {
    return response(status: $status)->download($filePath, $fileName, $headers);
  }

  public static function escapeString(string $text) {
    // $text = str_replace("\n", "", $text);
    $text = str_replace(";", "&#59;", $text);
    $text = str_replace("--", "- - ", $text);
    $text = str_replace("--", "&minus;&minus;", $text);
    if (substr_count($text, "'") % 2 != 0) {
      $text = str_replace("'","''", $text);
    }
    // $text = addcslashes($text);
    return trim($text); 
  }

  public static function arrayType(array $ary) {
    return (array_is_list($ary))? 'list' : 'dict';
  }
}
