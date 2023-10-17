<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use App\Utils\AppUtil;
use App\Services\DbService;
use App\Security\JwtAuth;

class RestApiController extends ApiController
{

    /**
     * Create a new REST API controller instance.
     *
     * @return void
     */
    public function __construct(Request $request)
    {
        parent::__construct($request);
        $this->middleware('auth', ['except' => ['index', 'token']]);
    }

    /**
     * Function index
     *
     * @return string
     */
    public function index(): JsonResponse
    {
        $name = $this->request->input('name');
        Log::channel('stack')->info('Parameter: {name}', ['name' => $name]);
        return AppUtil::sendResponse(['value'=>$name]);
    }

    /**
     * Function token
     *
     * @return string
     */
    public function token()
    {
        $key = $this->request->input('key');
        $secret = $this->request->input('secret');
        return AppUtil::sendResponse(['token'=>JwtAuth::token($key, $secret)]);
    }

    /**
     * Function select
     * Sample JSON input: {
     *   "fields": ["sum(z.zone_id) as sum_zone_id", "sum(z.zone_id)/:div as half_sum_zone_id", "cr.region_name as region_name", "csp.provider_name"],
     *   "table": "zones z",
     *   "inner": ["cloud_regions cr on cr.region_id = z.region_id"],
     *   "left": ["cloud_service_providers csp on csp.provider_id = cr.provider_id"],
     *   "where": "z.zone_id > :zoneId and cr.region_name like :regionName",
     *   "group": ["z.zone_id", "cr.region_name", "csp.provider_name"],
     *   "having": "sum(z.zone_id) > :zoneIdSum",
     *   "sort": ["z.zone_name asc"],
     *   "bind": {"div":2, "zoneId":0, "regionName": "%north%", "zoneIdSum": 100},
     *   "limit": 1,
     *   "offset": 0
     * } 
     * @return string
     */
    public function select()
    {
        $parameters = array();
        $params = $this->request->json();
        
        $parameters['fields'] = $params->get('fields', '*');
        $parameters['table'] = $params->get('table', '');
        $parameters['inner'] = $params->get('inner', []);
        $parameters['left'] = $params->get('left', []);
        $parameters['where'] = $params->get('where', '');
        $parameters['group'] = $params->get('group', []);
        $parameters['having'] = $params->get('having', '');
        $parameters['sort'] = $params->get('sort', []);
        $parameters['limit'] = $params->get('limit', '');
        $parameters['offset'] = $params->get('offset', '');
        $parameters['bind'] = $params->get('bind', []);

        $result = DbService::selectData($parameters);
        return AppUtil::sendResponse($result);
    }

    /**
     * Function insertGetId
     *
     * @return string
     */
    public function insertGetId()
    {
        $params = array();
        $params = $this->request->json();

        $table = $params->get('table', '');
        $data = $params->get('data', []);
        $primaryKeyName = $params->get('primaryKeyName', 'id');

        $result = DbService::insertGetId($table, $data, $primaryKeyName);
        return AppUtil::sendResponse($result);
    }

    /**
     * Function insertData
     * Sample JSON input: {
     *   "table": "zones",
     *   "records": [{
     *     "zone_name": "test zone 104",
     *     "region_id": 41
     *   },{
     *     "zone_name": "test zone 105",
     *     "region_id": 41
     *   }]
     * }
     * @return string
     */
    public function insertData()
    {
        $msg = ['validations'=>[]];
        $params = array();
        $params = $this->request->json();
        
        $table = $params->get('table', '');
        $records = $params->get('records', []);
        $ignoreError = $params->get('ignoreError', FALSE);

        $result = DbService::insertData($table, $records, $ignoreError);
        if(is_array($result) && array_key_exists('validations', $result) && count($result['validations']) > 0) {
            $msg['validations'] = $result['validations'];
        }
        if (is_array($msg) && array_key_exists('validations', $msg) && count($msg['validations']) > 0) {
            return AppUtil::sendResponse([], 400, $msg);
        }
        return AppUtil::sendResponse($result);
    }

    /**
     * Function updateData
     * Sample JSON input: {
     *   "objects": [{
     *     "table": "zones",
     *     "where": "zone_id=?",
     *     "bindings": [144],
     *     "data":{
     *       "zone_name": "test zone 106",
     *       "region_id": 41
     *     }
     *   },{
     *     "table": "zones",
     *     "where": "zone_id=?",
     *     "bindings": [145],
     *     "data": {
     *       "zone_name": "test zone 107"
     *     }
     *   }]
     * }
     * @return string
     */
    public function updateData()
    {
        $params = array();
        $params = $this->request->json();
        $objects = $params->get('objects', []);
        $msg = ['validations'=>[]];
        $idx = 0;
        if(is_array($objects) && count($objects)>0) {
            foreach($objects as $obj) {
                if(is_array($obj) && count($obj)>0) {
                    $table = (array_key_exists('table', $obj) && is_string($obj['table']))? trim($obj['table']): '';
                    $where = (array_key_exists('where', $obj))? trim($obj['where']): '';
                    $bindings = (array_key_exists('bindings', $obj) && is_array($obj['bindings']))? $obj['bindings']: [];
                    $data = (array_key_exists('data', $obj) && is_array($obj['data']))? $obj['data']: [];
                    // $bindings = array_map('AppUtil::escapeString', $bindings);
                    if (strlen($table)>0 && strlen($where)>0 && count($data)>0) {
                        $result = DbService::updateData($table, $where, $data, $bindings);
                        if(is_array($result) && array_key_exists('validations', $result) && count($result['validations']) > 0) {
                            $msg['validations']['0'.$idx] = [];
                            $msg['validations']['0'.$idx] = $result['validations']['00'];
                        }
                    }
                }
                $idx = $idx + 1;
            }
        }
        if (is_array($msg) && array_key_exists('validations', $msg) && count($msg['validations']) > 0) {
            return AppUtil::sendResponse([], 400, $msg);
        }
        return AppUtil::sendResponse([]);
    }

    /**
     * Function incrementFields
     * Sample JSON input: {
     *   "table": "zones",
     *   "where": "zone_id=?",
     *   "bindings": [144],
     *   "data": {
     *     "votes": 5,
     *     "balance": 100
     *   },
     *   "other": {
     *     "type": "paid",
     *     "country_code": "US" 
     *   }
     * }
     * @return string
     */
    public function incrementFields()
    {
        $params = array();
        $params = $this->request->json();

        $table = $params->get('table', '');
        $where = $params->get('where', '');
        $data = $params->get('data', []);
        $other = $params->get('other', []);
        $bindings = $params->get('bindings', []);

        $result = DbService::incrementFields($table, $where, $data, $other, $bindings);
        return AppUtil::sendResponse($result);
    }

    /**
     * Function decrementFields
     *
     * @return string
     */
    public function decrementFields()
    {
        $params = array();
        $params = $this->request->json();

        $table = $params->get('table', '');
        $where = $params->get('where', '');
        $data = $params->get('data', []);
        $other = $params->get('other', []);
        $bindings = $params->get('bindings', []);

        $result = DbService::decrementFields($table, $where, $data, $other, $bindings);
        return AppUtil::sendResponse($result);
    }

    /**
     * Function deleteData
     * Sample JSON input: {
     *   "objects": [{
     *     "table": "zones",
     *     "where": "zone_id=:zoneId",
     *     "bindings": {"zoneId": 142}
     *   },{
     *     "table": "zones",
     *     "where": "zone_id=:zoneId",
     *     "bindings": {"zoneId": 143}
     *   }]
     * }
     * @return string
     */
    public function deleteData()
    {
        $params = array();
        $params = $this->request->json();
       
        $objects = $params->get('objects', []);
        if(is_array($objects) && count($objects)>0) {
            foreach($objects as $obj) {
                if(is_array($obj) && count($obj)>0) {
                    $table = (array_key_exists('table', $obj) && is_string($obj['table']))? trim($obj['table']): '';
                    $where = (array_key_exists('where', $obj))? trim($obj['where']): '';
                    $bindings = (array_key_exists('bindings', $obj) && is_array($obj['bindings']))? $obj['bindings']: [];
                    // $bindings = array_map('AppUtil::escapeString', $bindings);
                    if (strlen($table)>0 && strlen($where)>0) {
                        $result = DbService::deleteData($table, $where, $bindings);
                    }
                }
            }
        }
        return AppUtil::sendResponse([]);
    }

}
