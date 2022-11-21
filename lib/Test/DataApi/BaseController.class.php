<?php

namespace Test\DataApi;

/**
 * Class BaseController
 * @package Test\DataApi
 */
class BaseController
{
    protected $service_url;
    protected $service_token;
    protected $default_timeout = 30;

    protected $errors = [];

    protected $cache_projects_data = null;
    protected $cache_buildings_data = null;
    protected $cache_realty_data = null;
    protected $cache_realty_short_data = null;
    protected $cache_customer_id = null;
    protected $cache_check_customer_questionnaire = null;
    protected $cache_customer_profile = null;
    protected $cache_customer_salutation_notifications = null;
    protected $cache_customer_agreements = null;
    protected $cache_customer_agreements_full = null;
    protected $cache_agreement_realtyobjects = null;
    protected $cache_realtyobject = null;
    protected $cache_agreement_invoices = null;
    protected $cache_agreement_process = null;
    protected $cache_invoice = null;
    protected $cache_customer_booking_invoices = null;
    protected $cache_booked_realty_objects = null;
    protected $cache_customer_preliminary_invoices = null;
    protected $cache_customer_preliminary_invoices_full = null;
    protected $cache_agreement_paymentplans = null;
    protected $cache_paymentplan = null;
    protected $cache_inspection = null;
    protected $cache_inspection_schedule = null;
    protected $cache_questionnaire_attributes_values = null;


    public $general_headers = [
        "Content-type: application/json",
        "Accept: application/json",
        "Cache-Control: no-cache",
        "Pragma: no-cache",
    ];

    public $allow_log = false;

    /**
     * @return static
     */
    public static function instance()
    {
        return new static();
    }

    /**
     * @param $str string
     */
    public function writeLog($str)
    {
        if ($this->allow_log) {
            writeFileLog($str);
        }
    }

    /**
     * @param $additional_header string[]
     * @return string[]
     */
    public function makeHeaders($additional_header = [])
    {
        if ($token = getSessionJWT()) {
            $this->general_headers = [
                'Authorization: Bearer '. $token,
                'Content-Type: application/json'
            ];
        }

        return array_merge($this->general_headers, $additional_header);
    }

    public function prepareCurl($url, $headers, $post_data)
    {
        $post_data = json_encode($post_data);
        if (!$headers) {
            $headers = $this->makeHeaders();
        }
        
        $this->writeLog(sprintf('request = %s', $post_data));
        $local_config_file = dirname(__FILE__) . '/local.config.php';
        if (is_file($local_config_file)) {
            $local_config = include($local_config_file);
        }
        if (is_array($local_config)) {
            if ($local_config['url']) {
                $url = $local_config['url'];
            }
            if ($local_config['headers']) {
                $headers = array_merge($headers, $local_config['headers']);
            }
        }
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, $this->default_timeout);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        return $ch;
    }

    /**
     * @param $url string
     * @return static
     */
    public function setServiceUrl($url)
    {
        $this->service_url = $url;
        return $this;
    }

    /**
     * @param $token string
     * @return static
     */
    public function setServiceToken($token)
    {
        $this->service_token = $token;
        return $this;
    }

    public function isErrors($data = [])
    {
        if ($data['result']['errorCode'] != 0) {
            return true;
        }
        return false;
    }
    

    /**
     * @param $project_ids string[]
     * @param $cacheTtl bool
     * @return array
     */
    public function getDevelopmentProjects($project_ids = [], $cacheTtl = false)
    {
        $result = [];

        $filter = serialize($project_ids);
        if (is_array($this->cache_projects_data[$filter])) {
            return $this->cache_projects_data[$filter];
        }

        $cacheTtl = is_numeric($cacheTtl) ? $cacheTtl : COMPONENTS_CACHE_TTL;
        $obCache = new \CPHPCache();
        if ($obCache->InitCache($cacheTtl, __FUNCTION__ . $filter)) {
            $result = $obCache->GetVars();
        } elseif ($obCache->StartDataCache()) {

            $post_data = [
                'method' => 'getDevelopmentProjects',
                'params' => [
                    'token' => $this->service_token,
                    'developmentprojects' => $project_ids ?: [],
                ],
            ];

            $ch = $this->prepareCurl($this->service_url, false, $post_data);
            $response = curl_exec($ch);
            $error = curl_errno($ch);
            $error_message = curl_error($ch);
            curl_close($ch);
            $this->writeLog('finish getDevelopmentProjects');
            if ($error) {
                $this->errors[] = 'Execute error: ' . $error_message;
                $result = false;
            } else if ($response) {
                $result = json_decode($response, true);
                if (isErrors()) {
                    $this->errors[] = 'Execute error: ' . $result['errorMessage'];
                    $result = false;
                }
            }

            $obCache->EndDataCache($result);
        }

        $this->cache_projects_data[$filter] = $result;

        return $result;
    }

    /**
     * @param $project_id string
     * @param $cacheTtl bool
     * @return array
     */
    public function getBuildingsByProject($project_id = false, $cacheTtl = false)
    {
        $result = [];

        if (!$project_id) {
            return $result;
        }

        $filter = serialize([$project_id]);
        if (is_array($this->cache_projects_data[$filter])) {
            return $this->cache_projects_data[$filter];
        }

        $cacheTtl = is_numeric($cacheTtl) ? $cacheTtl : COMPONENTS_CACHE_TTL;
        $obCache = new \CPHPCache();
        if ($obCache->InitCache($cacheTtl, __FUNCTION__ . $filter)) {
            $result = $obCache->GetVars();
        } elseif ($obCache->StartDataCache()) {

            $post_data = [
                'method' => 'getBuildingsByProject',
                'params' => [
                    'token' => $this->service_token,
                    'developmentprojectid' => $project_id,
                ],
            ];

            $ch = $this->prepareCurl($this->service_url, false, $post_data);
            $response = curl_exec($ch);
            $error = curl_errno($ch);
            $error_message = curl_error($ch);
            curl_close($ch);
            $this->writeLog('finish getBuildingsByProject');
            if ($error) {
                $this->errors[] = 'Execute error: ' . $error_message;
                $result = false;
            } else if ($response) {
                $result = json_decode($response, true);
                if (isErrors()) {
                    $this->errors[] = 'Execute error: ' . $result['errorMessage'];
                    $result = false;
                }
            }

            $obCache->EndDataCache($result);
        }

        $this->cache_projects_data[$filter] = $result;

        return $result;
    }

    public function getBuildingsByProjectMarketplace($project_id = false)
    {
        $result = [];

        if (!$project_id) {
            return $result;
        }

        $post_data = [
            'method' => 'getBuildingsByProjectMarketplace',
            'params' => [
                'token' => $this->service_token,
                'developmentprojectid' => $project_id,
            ],
        ];

        $ch = $this->prepareCurl($this->service_url, false, $post_data);
        $response = curl_exec($ch);
        $error = curl_errno($ch);
        $error_message = curl_error($ch);
        curl_close($ch);
        $this->writeLog('finish getBuildingsByProject');
        if ($error) {
            $this->errors[] = 'Execute error: ' . $error_message;
            $result = false;
        } else if ($response) {
            $result = json_decode($response, true);
            if (isErrors()) {
                $this->errors[] = 'Execute error: ' . $result['errorMessage'];
                $result = false;
            }
        }

        return $result;
    }

    /**
     * @param $buildings string[]
     * @param $developmentPhases string[]
     * @param $developmentProjects string[]
     * @return array
     */
    public function getBuildings($buildings = [], $developmentPhases = [], $developmentProjects = [], $cacheTtl = false)
    {
        $result = [];

        $post_data = [
            'method' => 'getBuildings',
            'params' => [
                'token' => $this->service_token,
                'buildings' => $buildings ?: [],
                'developmentphases' => $developmentPhases ?: [],
                'developmentprojects' => $developmentProjects ?: [],
            ],
        ];

        $ch = $this->prepareCurl($this->service_url, false, $post_data);
        $response = curl_exec($ch);
        $error = curl_errno($ch);
        $error_message = curl_error($ch);
        curl_close($ch);
        $this->writeLog('finish getBuildings');
        if ($error) {
            $this->errors[] = 'Execute error: ' . $error_message;
            $result = false;
        } else if ($response) {
            $result = json_decode($response, true);
            if (isErrors()) {
                $this->errors[] = 'Execute error: ' . $result['errorMessage'];
                $result = false;
            }
        }

        return $result;
    }

    public function getTypicalRealtyObject($developmentProjects = [])
    {
        $url =  $this->service_url . '/getTypicalRealtyObject';
        $post_data = [
            'token' => $this->service_token,
            'developmentprojects' => $developmentProjects
        ];

        $ch = $this->prepareCurl($url, false, $post_data);
        curl_setopt($ch, CURLOPT_TIMEOUT, 1000);
        $response = curl_exec($ch);
        curl_close($ch);
        $result = json_decode($response, true);

        if ($result['result']['typicalRealtyObjects']) {
            return $result['result']['typicalRealtyObjects'];
        } else {
            return false;
        }
    }

    public function getRealtyObjectsMarketplace($realtyObjects = [], $floors = [], $sections = [], $buildings = [],
                                                $developmentPhases = [], $developmentProjects = [], $methodInUrl = false)
    {
        $result = [];
        $url =  $this->service_url;
        $post_data = [
            'method' => 'getRealtyObjectsMarketplace',
            'params' => [
                'token' => $this->service_token,
                'realtyobjects' => $realtyObjects ?: [],
                'developmentProjects' => $developmentProjects ?: [],
            ]
        ];

        if ($methodInUrl) {
            $post_data = [
                'token' => $this->service_token,
                'realtyobjects' => $realtyObjects ?: [],
                'developmentProjects' => $developmentProjects ?: []
            ];
            $url =  $this->service_url.'/getRealtyObjectsMarketplace';
        }

        $ch = $this->prepareCurl($url, false, $post_data);
        curl_setopt($ch, CURLOPT_TIMEOUT, 1000);
        $response = curl_exec($ch);
        $error = curl_errno($ch);
        $error_message = curl_error($ch);
        curl_close($ch);
        $this->writeLog('finish getRealtyObjectsMarketplace');
        if ($error) {
            $this->errors[] = 'Execute error: ' . $error_message;
            return false;
        }

        if ($response) {
            $result = json_decode($response, true);
            if (isErrors()) {
                $this->errors[] = 'Execute error: ' . $result['errorMessage'];
                return false;
            }
        }

        return $result;
    }

    /**
     * @param $realtyObjects string[]
     * @param array $floors string[]
     * @param array $sections string[]
     * @param array $buildings string[]
     * @param array $developmentPhases string[]
     * @param array $developmentProjects string[]
     * @return array
     */
    public function getRealtyObjects($realtyObjects = [], $floors = [], $sections = [], $buildings = [],
                                     $developmentPhases = [], $developmentProjects = [])
    {
        $result = [];

        $filter = serialize([
            'realtyobjects' => $realtyObjects,
            'floors' => $floors,
            'sections' => $sections,
            'buildings' => $buildings,
            'developmentphases' => $developmentPhases,
            'developmentprojects' => $developmentProjects,
        ]);
        if (is_array($this->cache_realty_data[$filter])) {
            return $this->cache_realty_data[$filter];
        }

        $post_data = [
            'method' => 'getRealtyObjects',
            'params' => [
                'token' => $this->service_token,
                'realtyobjects' => $realtyObjects ?: [],
                'floors' => $floors ?: [],
                'sections' => $sections ?: [],
                'buildings' => $buildings ?: [],
                'developmentphases' => $developmentPhases ?: [],
                'developmentprojects' => $developmentProjects ?: [],
            ],
        ];

        $ch = $this->prepareCurl($this->service_url, false, $post_data);
        curl_setopt($ch, CURLOPT_TIMEOUT, 1000);
        $response = curl_exec($ch);
        $error = curl_errno($ch);
        $error_message = curl_error($ch);
        curl_close($ch);
        $this->writeLog('finish getRealtyObjects');
        if ($error) {
            $this->errors[] = 'Execute error: ' . $error_message;
            return false;
        }

        if ($response) {
            $result = json_decode($response, true);
            if (isErrors()) {
                $this->errors[] = 'Execute error: ' . $result['errorMessage'];
                return false;
            }
        }

        $this->cache_realty_data[$filter] = $result;

        return $result;
    }

    /**
     * @param string $developmentprojectId
     * @param array $realtyObjects
     * @return array
     */
    public function getRealtyObjectsByFilters($developmentprojectId = '', $realtyObjects = [])
    {
        $result = [];

        $post_data = [
            'method' => 'getRealtyObjectsByFilters',
            'params' => [
                'token' => $this->service_token,
                'developmentprojectId' => $developmentprojectId,
                'realtyobjects' => $realtyObjects,
            ],
        ];

        $ch = $this->prepareCurl($this->service_url, false, $post_data);
        curl_setopt($ch, CURLOPT_TIMEOUT, 1000);
        $response = curl_exec($ch);

        $error = curl_errno($ch);
        $error_message = curl_error($ch);
        curl_close($ch);
        $this->writeLog('finish getRealtyObjects');
        if ($error) {
            $this->errors[] = 'Execute error: ' . $error_message;
            return false;
        }

        if ($response) {
            $result = json_decode($response, true);
            if (isErrors()) {
                $this->errors[] = 'Execute error: ' . $result['errorMessage'];
                return false;
            }
        }

        return $result;
    }

    public function getRealtyObjectsByFiltersMarketplace($developmentprojectId = '' , $realtyobjectTypes = [])
    {
        $result = [];

        $post_data = [
            'method' => 'getRealtyObjectsByFiltersMarketplace',
            'params' => [
                'token' => $this->service_token,
                'developmentprojectId' => $developmentprojectId,
                'realtyobjectTypes' => $realtyobjectTypes
            ],
        ];

        $ch = $this->prepareCurl($this->service_url, false, $post_data);
        $response = curl_exec($ch);
        curl_close($ch);

        if ($response) {
            $result = json_decode($response, true);
        }

        return $result;
    }


    public function getSections()
    {
        $result = false;
        $post_data = [
            'method' => 'getSections',
            'params' => [
                'token' => $this->service_token,
            ],
        ];
        $ch = $this->prepareCurl($this->service_url, false, $post_data);
        curl_setopt($ch, CURLOPT_TIMEOUT, 1000);
        $response = curl_exec($ch);
        curl_close($ch);

        if ($response) {
            $result = json_decode($response, true);
            if (isErrors()) {
                $this->errors[] = 'Execute error: ' . $result['errorMessage'];
                return false;
            }
        }

        return $result;
    }

    /**
     * @param $realtyObjects string[]
     * @param array $floors string[]
     * @param array $sections string[]
     * @param array $buildings string[]
     * @param array $developmentPhases string[]
     * @param array $developmentProjects string[]
     * @return array
     */
    public function getRealtyObjectsShortInfo($realtyObjects = [], $floors = [], $sections = [], $buildings = [],
                                              $developmentPhases = [], $developmentProjects = [])
    {
        $result = [];

        $filter = serialize([
            'realtyobjects' => $realtyObjects,
            'floors' => $floors,
            'sections' => $sections,
            'buildings' => $buildings,
            'developmentphases' => $developmentPhases,
            'developmentprojects' => $developmentProjects,
        ]);
        if (is_array($this->cache_realty_short_data[$filter])) {
            return $this->cache_realty_short_data[$filter];
        }

        $post_data = [
            'method' => 'getRealtyObjectsShortInfo',
            'params' => [
                'token' => $this->service_token,
                'realtyobjects' => $realtyObjects ?: [],
                'floors' => $floors ?: [],
                'sections' => $sections ?: [],
                'buildings' => $buildings ?: [],
                'developmentphases' => $developmentPhases ?: [],
                'developmentprojects' => $developmentProjects ?: [],
            ],
        ];

        $ch = $this->prepareCurl($this->service_url, false, $post_data);
        curl_setopt($ch, CURLOPT_TIMEOUT, 1000);
        $response = curl_exec($ch);
        $error = curl_errno($ch);
        $error_message = curl_error($ch);
        curl_close($ch);
        $this->writeLog('finish getRealtyObjectsShortInfo');
        if ($error) {
            $this->errors[] = 'Execute error: ' . $error_message;
            return false;
        }

        if ($response) {
            $result = json_decode($response, true);
            if (isErrors()) {
                $this->errors[] = 'Execute error: ' . $result['errorMessage'];
                return false;
            }
        }

        $this->cache_realty_short_data[$filter] = $result;

        return $result;
    }

    /**
     * @param $phone string
     * @return bool
     */
    public function sendSecurityCodeV2($phone, $codeType = false, $memberCode = false)
    {
        if (!$phone) {
            return false;
        }

        $post_data = [
            'params' => [
                'token' => $this->service_token,
                'telephone' => clearPhone($phone),
                'deviceType' => 'Android',
                'deviceName' => 'Android'
            ]
        ];

        if ($codeType) {
            $post_data['params']['codeType'] = $codeType;
            $post_data['params']['creditrequestmemberid'] = $memberCode;
            $url = $this->service_url . '/sendSecurityCode';
        } else {
            $post_data['method'] = 'sendSecurityCode';
            $url = $this->service_url;
        }

        $ch = $this->prepareCurl($url, false, $post_data);
        $response = curl_exec($ch);
        $error = curl_errno($ch);
        $error_message = curl_error($ch);
        curl_close($ch);
        $this->writeLog('finish sendSecurityCode');
        if ($error) {
            $this->errors[] = 'Execute error: ' . $error_message;
            return false;
        }

        if ($response) {
            $result = json_decode($response, true);
            if (isErrors()) {
                $this->errors[] = 'Execute error: ' . $result['errorMessage'];
                return false;
            }
        }

        return true;
    }

    /**
     * @param $phone string
     * @return bool
     */
    public function SendSecurityCodeAddCreditMember($phone, $memberCode)
    {
        if (!$phone) {
            return false;
        }

        $post_data = [
            'params' => [
                'token' => $this->service_token,
                'telephone' => clearPhone($phone),
                'creditRequestMemberId' => $memberCode
            ]
        ];

        $url = $this->service_url . '/SendSecurityCodeAddCreditMember';
        $ch = $this->prepareCurl($url, false, $post_data);
        $response = curl_exec($ch);
        $error = curl_errno($ch);
        $error_message = curl_error($ch);
        curl_close($ch);
        $this->writeLog('finish sendSecurityCode');
        if ($error) {
            $this->errors[] = 'Execute error: ' . $error_message;
            return false;
        }

        if ($response) {
            $result = json_decode($response, true);
            if (isErrors()) {
                $this->errors[] = 'Execute error: ' . $result['errorMessage'];
                return false;
            }
        }

        return true;
    }

    /**
     * @param $phone string
     * @param $security_code string
     * @return bool
     */
    public function checkSecurityCodeV2($phone, $securityCode, $codeType = false, $memberCode = false)
    {
        if (!$phone) {
            return false;
        }

        $post_data = [
            'params' => [
                'token' => $this->service_token,
                'telephone' => clearPhone($phone),
                'securityCode' => $securityCode,
                'deviceType' => 'Android',
                'deviceName' => 'Android'
            ],
        ];

        if ($codeType) {
            $post_data['params']['codeType'] = $codeType;
            $post_data['params']['creditrequestmemberid'] = $memberCode;
            $url = $this->service_url . '/checkSecurityCode';
        } else {
            $post_data['method'] = 'checkSecurityCode';
            $url = $this->service_url;
        }

        $ch = $this->prepareCurl($url, false, $post_data);
        $response = curl_exec($ch);
        $error = curl_errno($ch);
        $error_message = curl_error($ch);

        curl_close($ch);
        $this->writeLog('finish checkSecurityCode');
        if ($error) {
            $this->errors[] = 'Execute error: ' . $error_message;
            return false;
        }

        if ($response) {
            $result = json_decode($response, true);
            $code = $result['result']['code'];
            if ($result['result']['errorCode'] == 0) {
                if (!$codeType && $code) {
                    setSessionValue('code', $code);
                    return $this->authorization($code);
                }
                return true;
            } else {
                $this->errors[] = 'Execute error: ' . $result['errorMessage'];
            }
        }

        return false;
    }

    /**
     * @param $phone string
     * @param $security_code string
     * @return bool
     */
    public function checkSecurityCodeAddCreditMember($phone, $securityCode, $memberCode)
    {
        if (!$phone) {
            return false;
        }

        $post_data = [
            'params' => [
                'token' => $this->service_token,
                'telephone' => clearPhone($phone),
                'securityCode' => $securityCode,
                'creditRequestMemberId' => $memberCode
            ],
        ];

        $url = $this->service_url . '/CheckSecurityCodeAddCreditMember';
        $ch = $this->prepareCurl($url, false, $post_data);
        $response = curl_exec($ch);
        $error = curl_errno($ch);
        $error_message = curl_error($ch);
        curl_close($ch);
        $this->writeLog('finish CheckSecurityCodeAddCreditMember');
        if ($error) {
            $this->errors[] = 'Execute error: ' . $error_message;
            return false;
        }

        if ($response) {
            $result = json_decode($response, true);
            if ($result['result']['errorCode'] == 0) {
                return true;
            } else {
                $this->errors[] = 'Execute error: ' . $result['errorMessage'];
            }
        }

        return false;
    }

    public function updateToken()
    {
        if ($refreshToken = getSessionRT()) {
            //Удаляем старый, что бы вылетелеа авторизация в случае неуспеха
            deleteSessionValue('JWT');
            $headers = [
                'Accept: application/json' ,
                'Content-Type: application/x-www-form-urlencoded'
            ];

            $arr = [
                "grant_type" => "refresh_token",
                "refresh_token" => $refreshToken,
            ];
            $data = http_build_query($arr);

            $url = $this->service_url . '/token';
            $dispatchTime = date("H:i:s d.m.Y");

            $curl = curl_init();
            curl_setopt_array($curl, array(
                CURLOPT_URL => $url,
                CURLOPT_RETURNTRANSFER => true, // return the transfer as a string of the return value
                CURLOPT_TIMEOUT => 0,   // The maximum number of seconds to allow cURL functions to execute.
                CURLOPT_POST => true,   // This line must place before CURLOPT_POSTFIELDS
                CURLOPT_POSTFIELDS => $data // Data that will send
            ));
            // Set Header
            if (!empty($headers)) {
                curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
            }
            $response = curl_exec($curl);
            curl_close($curl);

            $result = json_decode($response, true);
            if ($result['access_token']) {
                setSessionValue('JWT', $result['access_token']);
                setSessionValue('RT', $result['refresh_token']);
                return true;
            }
        }
        return false;
    }

    public function authorization($code = false)
    {
        if (!$code) {
            $code = getSessionCode();
        }

        $headers = [
            'Accept: application/json' ,
            'Content-Type: application/x-www-form-urlencoded'
        ];

        $arr = [
            "grant_type" => "authorization_code",
            "code" => $code,
        ];
        $data = http_build_query($arr);

        $url = $this->service_url . '/token';

        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true, // return the transfer as a string of the return value
            CURLOPT_TIMEOUT => 0,   // The maximum number of seconds to allow cURL functions to execute.
            CURLOPT_POST => true,   // This line must place before CURLOPT_POSTFIELDS
            CURLOPT_POSTFIELDS => $data // Data that will send
        ));
        // Set Header
        if (!empty($headers)) {
            curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        }
        $response = curl_exec($curl);
        $errno = curl_errno($curl);
        if ($errno) {
            return false;
        }
        curl_close($curl);

        $result = json_decode($response, true);
        if ($result['access_token']) {
            setSessionValue('JWT', $result['access_token']);
            setSessionValue('RT', $result['refresh_token']);
            return true;
        }

        return false;
    }

    /**
     * @param $processstageId string
     * @return bool
     */
    public function updateProcessstageStatus($processstageId)
    {
        if (!$processstageId) {
            return false;
        }

        $post_data = [
            'method' => 'updateProcessstageStatus',
            'params' => [
                'token' => $this->service_token,
                'processstageId' => $processstageId,
            ],
        ];

        $ch = $this->prepareCurl($this->service_url, false, $post_data);
        $response = curl_exec($ch);
        $error = curl_errno($ch);
        $error_message = curl_error($ch);
        curl_close($ch);
        $this->writeLog('finish updateProcessstageStatus');
        if ($error) {
            $this->errors[] = 'Execute error: ' . $error_message;
            return false;
        }

        if ($response) {
            $result = json_decode($response, true);
            if ($result['result']['errorCode'] == 0) {
                return true;
            } else {
                $this->errors[] = 'Execute error: ' . $result['errorMessage'];
                return false;
            }
        }

        return false;
    }

    /**
     * @param $telephone string
     * @return array
     */
    public function getCustomerId($telephone)
    {
        $result = [];

        $filter = serialize([
            'telephone' => clearPhone($telephone),
        ]);
        if (is_array($this->cache_customer_id[$filter])) {
            return $this->cache_customer_id[$filter];
        }

        $post_data = [
            'method' => 'getCustomerId',
            'params' => [
                'token' => $this->service_token,
                'telephone' => $telephone,
            ],
        ];

        $ch = $this->prepareCurl($this->service_url, false, $post_data);
        $response = curl_exec($ch);
        $error = curl_errno($ch);
        $error_message = curl_error($ch);
        curl_close($ch);
        $this->writeLog('finish getCustomerId');
        if ($error) {
            $this->errors[] = 'Execute error: ' . $error_message;
            return false;
        }

        if ($response) {
            $result = json_decode($response, true);
            if (isErrors()) {
                $this->errors[] = 'Execute error: ' . $result['errorMessage'];
                return false;
            }
        }

        $this->cache_customer_id[$filter] = $result;

        return $result;
    }


    /**
     * @return bool
     */
    public function checkCustomerShortQuestionnaire()
    {
        $post_data = [
            'method' => 'checkCustomerShortQuestionnaire',
            'params' => [
                'token' => $this->service_token,
            ],
        ];

        $ch = $this->prepareCurl($this->service_url, false, $post_data);
        $response = curl_exec($ch);
        $error = curl_errno($ch);
        $error_message = curl_error($ch);
        curl_close($ch);
        $this->writeLog('finish checkCustomerShortQuestionnaire');
        if ($error) {
            $this->errors[] = 'Execute error: ' . $error_message;
            return false;
        }

        if ($response) {
            $result = json_decode($response, true);
            if (isErrors()) {
                $this->errors[] = 'Execute error: ' . $result['errorMessage'];
                return false;
            }
        }

        return true;
    }


    public function addCustomerShortQuestionnaire($firstname, $middlename, $lastname,
                                             $mobilephone, $emailaddress1, $birthdate = '')
    {
        $post_data = [
            'method' => 'addCustomerShortQuestionnaire',
            'params' => [
                'token' => $this->service_token,
                'firstname' => $firstname,
                'middlename' => $middlename,
                'lastname' => $lastname,
                'mobilephone' => $mobilephone,
                'emailaddress1' => $emailaddress1,
                'birthdate' => $birthdate
            ],
        ];

        $ch = $this->prepareCurl($this->service_url, false, $post_data);
        $response = curl_exec($ch);
        $error = curl_errno($ch);
        $error_message = curl_error($ch);
        curl_close($ch);
        $this->writeLog('finish addCustomerShortQuestionnaire');
        if ($error) {
            $this->errors[] = 'Execute error: ' . $error_message;
            return false;
        }

        if ($response) {
            $result = json_decode($response, true);
            if (isErrors()) {
                $this->errors[] = 'Execute error: ' . $result['errorMessage'];
                return false;
            }
        }

        return true;
    }

    /**
     * @param $cacheTtl bool
     * @return array
     */
    public function getCustomerProfile()
    {
        $result = [];

        $post_data = [
            'method' => 'getCustomerProfile',
            'params' => [
                'token' => $this->service_token,
            ],
        ];

        $ch = $this->prepareCurl($this->service_url, false, $post_data);
        $response = curl_exec($ch);

        $error = curl_errno($ch);
        $error_message = curl_error($ch);
        curl_close($ch);
        $this->writeLog('finish getCustomerProfile');
        if ($error) {
            $this->errors[] = 'Execute error: ' . $error_message;
            $result = false;
        } else if ($response) {
            $result = json_decode($response, true);
            if (isErrors()) {
                $this->errors[] = 'Execute error: ' . $result['errorMessage'];
                $result = false;
            }
        }

        return $result;
    }

    /**
     * @param $cacheTtl bool
     * @return array
     */
    public function getCustomerSalutationNotifications()
    {
        $result = [];

        $post_data = [
            'method' => 'getCustomerSalutationNotifications',
            'params' => [
                'token' => $this->service_token,
            ],
        ];

        $ch = $this->prepareCurl($this->service_url, false, $post_data);
        $response = curl_exec($ch);
        $error = curl_errno($ch);
        $error_message = curl_error($ch);
        curl_close($ch);
        $this->writeLog('finish getCustomerSalutationNotifications');
        if ($error) {
            $this->errors[] = 'Execute error: ' . $error_message;
            $result = false;
        } else if ($response) {
            $result = json_decode($response, true);
            if (isErrors()) {
                $this->errors[] = 'Execute error: ' . $result['errorMessage'];
                $result = false;
            }
        }

        return $result;
    }

    /**
     * @return array
     */
    public function getCustomerAgreements()
    {
        $result = [];

        $post_data = [
            'method' => 'getCustomerAgreements',
            'params' => [
                'token' => $this->service_token,
            ],
        ];

        $ch = $this->prepareCurl($this->service_url, false, $post_data);
        $response = curl_exec($ch);
        $error = curl_errno($ch);
        $error_message = curl_error($ch);
        curl_close($ch);
        $this->writeLog('finish getCustomerAgreements');
        if ($error) {
            $this->errors[] = 'Execute error: ' . $error_message;
            $result = false;
        } else if ($response) {
            $result = json_decode($response, true);
            if (isErrors()) {
                $this->errors[] = 'Execute error: ' . $result['errorMessage'];
                $result = false;
            }
        }

        return $result;
    }

    /**
     * @return array
     */
    public function getCustomerAgreementsFull()
    {
        $result = [];

        $post_data = [
            'method' => 'getCustomerAgreementsFull',
            'params' => [
                'token' => $this->service_token,
            ],
        ];

        $ch = $this->prepareCurl($this->service_url, false, $post_data);
        curl_setopt($ch, CURLOPT_TIMEOUT, 100);
        $response = curl_exec($ch);
        $error = curl_errno($ch);
        $error_message = curl_error($ch);
        curl_close($ch);
        $this->writeLog('finish getCustomerAgreementsFull');
        if ($error) {
            $this->errors[] = 'Execute error: ' . $error_message;
            $result = false;
        } else if ($response) {
            $result = json_decode($response, true);
            if (isErrors()) {
                $this->errors[] = 'Execute error: ' . $result['errorMessage'];
                $result = false;
            }
        }

        return $result;
    }

    /**
     * @param $agreementId string
     * @param $cacheTtl bool
     * @return array
     */
    public function getAgreementRealtyobjects($agreementId, $cacheTtl = false)
    {
        $result = [];

        if (!$agreementId) {
            return false;
        }

        $filter = serialize([
            'agreementId' => $agreementId,
        ]);
        if (is_array($this->cache_agreement_realtyobjects[$filter])) {
            return $this->cache_agreement_realtyobjects[$filter];
        }

        $cacheTtl = is_numeric($cacheTtl) ? $cacheTtl : COMPONENTS_CACHE_5MIN_TTL;
        $obCache = new \CPHPCache();
        if ($obCache->InitCache($cacheTtl, __FUNCTION__ . $filter)) {
            $result = $obCache->GetVars();
        } elseif ($obCache->StartDataCache()) {

            $post_data = [
                'method' => 'getAgreementRealtyobjects',
                'params' => [
                    'token' => $this->service_token,
                    'agreementId' => $agreementId,
                ],
            ];

            $ch = $this->prepareCurl($this->service_url, false, $post_data);
            $response = curl_exec($ch);
            $error = curl_errno($ch);
            $error_message = curl_error($ch);
            curl_close($ch);
            $this->writeLog('finish getAgreementRealtyobjects');
            if ($error) {
                $this->errors[] = 'Execute error: ' . $error_message;
                $result = false;
            } else if ($response) {
                $result = json_decode($response, true);
                if (isErrors()) {
                    $this->errors[] = 'Execute error: ' . $result['errorMessage'];
                    $result = false;
                }
            }

            $obCache->EndDataCache($result);
        }

        $this->cache_agreement_realtyobjects[$filter] = $result;

        return $result;
    }

    /**
     * @param $realtyobjectId string
     * @param $agreementId string
     * @param $cacheTtl bool
     * @return array
     */
    public function getRealtyobject($realtyobjectId, $agreementId, $cacheTtl = false)
    {
        $result = [];

        if (!$realtyobjectId) {
            return false;
        }
        if (!$agreementId) {
            return false;
        }

        $filter = serialize([
            'realtyobjectId' => $realtyobjectId,
            'agreementId' => $agreementId,
        ]);
        if (is_array($this->cache_realtyobject[$filter])) {
            return $this->cache_realtyobject[$filter];
        }

        $cacheTtl = is_numeric($cacheTtl) ? $cacheTtl : COMPONENTS_CACHE_5MIN_TTL;
        $obCache = new \CPHPCache();
        if ($obCache->InitCache($cacheTtl, __FUNCTION__ . $filter)) {
            $result = $obCache->GetVars();
        } elseif ($obCache->StartDataCache()) {

            $post_data = [
                'method' => 'getRealtyobject',
                'params' => [
                    'token' => $this->service_token,
                    'realtyobjectId' => $realtyobjectId,
                    'agreementId' => $agreementId,
                ],
            ];

            $ch = $this->prepareCurl($this->service_url, false, $post_data);
            $response = curl_exec($ch);
            $error = curl_errno($ch);
            $error_message = curl_error($ch);
            curl_close($ch);
            $this->writeLog('finish getRealtyobject');
            if ($error) {
                $this->errors[] = 'Execute error: ' . $error_message;
                $result = false;
            } else if ($response) {
                $result = json_decode($response, true);
                if (isErrors()) {
                    $this->errors[] = 'Execute error: ' . $result['errorMessage'];
                    $result = false;
                }
            }

            $obCache->EndDataCache($result);
        }

        $this->cache_realtyobject[$filter] = $result;

        return $result;
    }

    /**
     * @param $agreementId string
     * @param $cacheTtl bool
     * @return array
     */
    public function getAgreementInvoices($agreementId, $cacheTtl = false)
    {
        $result = [];

        if (!$agreementId) {
            return false;
        }

        $filter = serialize([
            'agreementId' => $agreementId,
        ]);
        if (is_array($this->cache_agreement_invoices[$filter])) {
            return $this->cache_agreement_invoices[$filter];
        }

        $cacheTtl = is_numeric($cacheTtl) ? $cacheTtl : COMPONENTS_CACHE_5MIN_TTL;
        $obCache = new \CPHPCache();
        if ($obCache->InitCache($cacheTtl, __FUNCTION__ . $filter)) {
            $result = $obCache->GetVars();
        } elseif ($obCache->StartDataCache()) {

            $post_data = [
                'method' => 'getAgreementInvoices',
                'params' => [
                    'token' => $this->service_token,
                    'agreementId' => $agreementId,
                ],
            ];

            $ch = $this->prepareCurl($this->service_url, false, $post_data);
            $response = curl_exec($ch);
            $error = curl_errno($ch);
            $error_message = curl_error($ch);
            curl_close($ch);
            $this->writeLog('finish getAgreementInvoices');
            if ($error) {
                $this->errors[] = 'Execute error: ' . $error_message;
                $result = false;
            } else if ($response) {
                $result = json_decode($response, true);
                if (isErrors()) {
                    $this->errors[] = 'Execute error: ' . $result['errorMessage'];
                    $result = false;
                }
            }
            $obCache->EndDataCache($result);
        }

        $this->cache_agreement_invoices[$filter] = $result;

        return $result;
    }

    /**
     * @param $agreementId string
     * @param $cacheTtl bool
     * @return array
     */
    public function getAgreementProcess($agreementId, $cacheTtl = false)
    {
        $result = [];

        if (!$agreementId) {
            return false;
        }

        $filter = serialize([
            'agreementId' => $agreementId,
        ]);
        if (is_array($this->cache_agreement_process[$filter])) {
            return $this->cache_agreement_process[$filter];
        }

        $cacheTtl = is_numeric($cacheTtl) ? $cacheTtl : COMPONENTS_CACHE_5MIN_TTL;
        $obCache = new \CPHPCache();
        if ($obCache->InitCache($cacheTtl, __FUNCTION__ . $filter)) {
            $result = $obCache->GetVars();
        } elseif ($obCache->StartDataCache()) {

            $post_data = [
                'method' => 'getAgreementProcess',
                'params' => [
                    'token' => $this->service_token,
                    'agreementId' => $agreementId,
                ],
            ];

            $ch = $this->prepareCurl($this->service_url, false, $post_data);
            $response = curl_exec($ch);
            $error = curl_errno($ch);
            $error_message = curl_error($ch);
            curl_close($ch);
            $this->writeLog('finish getAgreementProcess');
            if ($error) {
                $this->errors[] = 'Execute error: ' . $error_message;
                $result = false;
            } else if ($response) {
                $result = json_decode($response, true);
                if (isErrors()) {
                    $this->errors[] = 'Execute error: ' . $result['errorMessage'];
                    $result = false;
                }
            }
            $obCache->EndDataCache($result);
        }

        $this->cache_agreement_process[$filter] = $result;

        return $result;
    }

    /**
     * @param $invoiceId string
     * @param $cacheTtl bool
     * @return array
     */
    public function getInvoice($invoiceId, $cacheTtl = false)
    {
        if (!$invoiceId) {
            return false;
        }

        $post_data = [
            'method' => 'getInvoice',
            'params' => [
                'token' => $this->service_token,
                'invoiceId' => $invoiceId,
            ],
        ];

        $ch = $this->prepareCurl($this->service_url, false, $post_data);
        $response = curl_exec($ch);
        curl_close($ch);
        $result = json_decode($response, true);

        if ($formUrl = $result['result']['urls'][0]['formUrl']) {
            $result['result']['formUrl'] = $formUrl;
        }

        return $result;
    }


    /**
     * @return array
     */
    public function getMortgageCalculatorAttributesValues()
    {
        $post_data = [
            'method' => 'getMortgageCalculatorAttributesValues',
            'params' => [
                'token' => $this->service_token,
            ],
        ];

        $ch = $this->prepareCurl($this->service_url, false, $post_data);
        curl_setopt($ch, CURLOPT_TIMEOUT, 60);
        $response = curl_exec($ch);
        curl_close($ch);

        $result = json_decode($response, true);

        return $result;

    }

    /**
     * @return array
     */
    public function createMortgageRequest($developmentprojectid)
    {
        $post_data = [
            'method' => 'createMortgageRequest',
            'params' => [
                'token' => $this->service_token,
                'developmentprojectid' => $developmentprojectid
            ],
        ];

        $ch = $this->prepareCurl($this->service_url, false, $post_data);
        curl_setopt($ch, CURLOPT_TIMEOUT, 60);
        $response = curl_exec($ch);
        curl_close($ch);
        $result = json_decode($response, true);

        return $result;
    }

    public function deleteMortgageWorkPlace($workPlaceId)
    {
        $post_data = [
            'method' => 'deleteMortgageWorkPlace',
            'params' => [
                'token' => $this->service_token,
                'workplaceid' => $workPlaceId
            ],
        ];

        $ch = $this->prepareCurl($this->service_url, false, $post_data);
        curl_setopt($ch, CURLOPT_TIMEOUT, 60);
        $response = curl_exec($ch);
        curl_close($ch);
        $result = json_decode($response, true);

        return $result;
    }

    public function deleteMortgageOwnedProperty($ownedPropertyId)
    {
        $post_data = [
            'method' => 'deleteMortgageOwnedProperty',
            'params' => [
                'token' => $this->service_token,
                'ownedpropertyid' => $ownedPropertyId
            ],
        ];

        $ch = $this->prepareCurl($this->service_url, false, $post_data);
        curl_setopt($ch, CURLOPT_TIMEOUT, 60);
        $response = curl_exec($ch);
        curl_close($ch);
        $result = json_decode($response, true);

        return $result;
    }

    public function deleteMortgageVehicle($vehicleId)
    {
        $post_data = [
            'method' => 'deleteMortgageVehicle',
            'params' => [
                'token' => $this->service_token,
                'vehicleid' => $vehicleId
            ],
        ];

        $post_data = json_encode($post_data);
        $headers = $this->makeHeaders();
        $ch = $this->prepareCurl($this->service_url, $headers, $post_data);
        curl_setopt($ch, CURLOPT_TIMEOUT, 60);
        $response = curl_exec($ch);
        curl_close($ch);
        $result = json_decode($response, true);

        return $result;
    }

    public function deleteFile($url)
    {
        $headers = $this->makeHeaders();
        $url = str_replace(':8826','', $url);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        $result = json_decode($response, true);

        return $result;
    }

    public function getFile($url)
    {
        $headers = $this->makeHeaders();
        $url = str_replace(':8826','', $url);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_VERBOSE, 1);
        curl_setopt($ch, CURLOPT_AUTOREFERER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
        curl_setopt($ch, CURLOPT_HEADER, true);
        $result = curl_exec($ch);
        // Then, after your curl_exec call:
        $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        $body = substr($result, $header_size);

        $header = substr($result, 0, $header_size);
        $ext = $this->getFileExtFromHeader($header);
        $imageData = base64_encode($body);
        curl_close($ch);

        return "data:image/{$ext};base64,{$imageData}";
    }

    public function getFileExtFromHeader($header)
    {
        $fileName = false;
        $data = explode("\n", $header);
        array_shift($data);
        foreach($data as $part){
            $middle=explode(":",$part);
            $headers[trim($middle[0])] = trim($middle[1]);
        }
        if ($headers['Content-Disposition']) {
            $strings = explode(";", $headers['Content-Disposition']);
            foreach ($strings as $string) {
                if (stripos($string, 'filename') !== false)
                    $fileName = explode("=", $string)[1];
            }
        }
        $ext = pathinfo($fileName, PATHINFO_EXTENSION);

        return $ext;
    }

    /**
     * @param $filePath string
     * @param $url string
     * @return array
     */
    public function sendFile($filePath, $url)
    {
        if ($filePath && $url) {
            $url = str_replace(':8826','', $url);
            $name = basename($filePath);
            $cFile = file_get_contents($filePath);
            $token = getSessionJWT();
            if ($token && $cFile && $name) {
                $headers = [
                    'Authorization: Bearer '. $token,
                    'fileName: ' . $name
                ];

                $ch = curl_init ($url);
                curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
                curl_setopt ($ch, CURLOPT_POST, 1);
                curl_setopt ($ch, CURLOPT_POSTFIELDS, $cFile);

                $response = curl_exec($ch);
                curl_close($ch);
                return json_decode($response, true);
            }
        }
        return false;
    }

    /**
     * @param $creditrequestMemberid string
     * @return array
     */
    public function getMortgageRequestMemberDocuments($creditrequestMemberid)
    {
        $post_data = [
            'method' => 'getMortgageRequestMemberDocuments',
            'params' => [
                'token' => $this->service_token,
                'creditrequestMemberid' => $creditrequestMemberid,
            ],
        ];

        $ch = $this->prepareCurl($this->service_url, false, $post_data);
        curl_setopt($ch, CURLOPT_TIMEOUT, 60);
        $response = curl_exec($ch);
        curl_close($ch);
        $result = json_decode($response, true);

        return $result;
    }

    /**
     * @param $creditrequestMemberid string
     * @return array
     */
    public function createMortgageVehicle($creditrequestMemberid)
    {
        $post_data = [
            'method' => 'createMortgageVehicle',
            'params' => [
                'token' => $this->service_token,
                'creditrequestMemberid' => $creditrequestMemberid,
            ],
        ];

        $ch = $this->prepareCurl($this->service_url, false, $post_data);
        curl_setopt($ch, CURLOPT_TIMEOUT, 60);
        $response = curl_exec($ch);
        curl_close($ch);
        $result = json_decode($response, true);

        return $result;
    }

    /**
     * @param $creditrequestMemberid string
     * @return array
     */
    public function createMortgageOwnedProperty($creditrequestMemberid)
    {
        $post_data = [
            'method' => 'createMortgageOwnedProperty',
            'params' => [
                'token' => $this->service_token,
                'creditrequestMemberid' => $creditrequestMemberid,
            ],
        ];

        $ch = $this->prepareCurl($this->service_url, false, $post_data);
        curl_setopt($ch, CURLOPT_TIMEOUT, 60);
        $response = curl_exec($ch);
        curl_close($ch);
        $result = json_decode($response, true);

        return $result;
    }

    /**
     * @param $creditrequestMemberid string
     * @return array
     */
    public function createMortgageWorkplace($creditrequestMemberid)
    {
        $post_data = [
            'method' => 'createMortgageWorkplace',
            'params' => [
                'token' => $this->service_token,
                'creditrequestMemberid' => $creditrequestMemberid,
            ],
        ];

        $post_data = str_replace(array("'", '"'), array("\'", '\"'), $post_data);
        $dispatchTime = date("H:i:s d.m.Y");
    
        $ch = $this->prepareCurl($this->service_url, false, $post_data);
        curl_setopt($ch, CURLOPT_TIMEOUT, 60);
        $response = curl_exec($ch);
        curl_close($ch);
        $result = json_decode($response, true);
        return $result;
    }


    /**
     * @param $relatedEntityId string
     * @param $destination string
     * @param $attributes array
     * @return array
     */
    public function updateMortgageEntityAttributesRequest($relatedEntityId, $destination ,$attributes)
    {
        $post_data = [
            'method' => 'updateMortgageEntityAttributesRequest',
            'params' => [
                'token' => $this->service_token,
                'relatedEntityId' => $relatedEntityId,
                'destination' => $destination,
                'attributes' => $attributes
            ],
        ];

        $post_data = str_replace(array("'", '"'), array("\'", '\"'), $post_data);
        $dispatchTime = date("H:i:s d.m.Y");
        $ch = $this->prepareCurl($this->service_url, false, $post_data);
        curl_setopt($ch, CURLOPT_TIMEOUT, 60);
        $response = curl_exec($ch);
        curl_close($ch);
        $result = json_decode($response, true);

        return $result;
    }

    /**
     * @param $creditRequestId string
     * @return array
     */
    public function sendMortgageRequestToManager($creditRequestId)
    {
        $post_data = [
            'method' => 'sendMortgageRequestToManager',
            'params' => [
                'token' => $this->service_token,
                'creditrequestid' => $creditRequestId,
            ],
        ];

        $ch = $this->prepareCurl($this->service_url, false, $post_data);
        curl_setopt($ch, CURLOPT_TIMEOUT, 60);
        $response = curl_exec($ch);
        curl_close($ch);
        $result = json_decode($response, true);

        return $result;
    }

    /**
     * @param $creditRequestId string
     * @param $banks array
     * @return array
     */
    public function updateMortgageSelectedBanks($creditRequestId, $banks)
    {
        $post_data = [
            'method' => 'updateMortgageSelectedBanks',
            'params' => [
                'token' => $this->service_token,
                'creditrequestid' => $creditRequestId,
                'banks' => $banks
            ],
        ];

        $ch = $this->prepareCurl($this->service_url, false, $post_data);
        curl_setopt($ch, CURLOPT_TIMEOUT, 60);
        $response = curl_exec($ch);
        curl_close($ch);
        $result = json_decode($response, true);

        return $result;
    }

    /**
     * @param $creditRequestId string
     * @return array
     */
    public function getMortgageGetAvailableBanks($creditRequestId)
    {
        $post_data = [
            'method' => 'getMortgageGetAvailableBanks',
            'params' => [
                'token' => $this->service_token,
                'creditrequestid' => $creditRequestId
            ],
        ];

        $ch = $this->prepareCurl($this->service_url, false, $post_data);
        curl_setopt($ch, CURLOPT_TIMEOUT, 60);
        $response = curl_exec($ch);
        curl_close($ch);
        $result = json_decode($response, true);

        return $result;
    }


    /**
     * @return array
     */
    public function createMortgageRequestMember($creditRequestId)
    {
        $post_data = [
            'method' => 'createMortgageRequestMember',
            'params' => [
                'token' => $this->service_token,
                'creditrequestid' => $creditRequestId
            ],
        ];

        $ch = $this->prepareCurl($this->service_url, false, $post_data);
        curl_setopt($ch, CURLOPT_TIMEOUT, 60);
        $response = curl_exec($ch);
        curl_close($ch);
        $result = json_decode($response, true);

        return $result;
    }

    /**
     * @return array
     */
    public function getMortgageQuestionnaireAttributesValues()
    {
        $post_data = [
            'method' => 'getMortgageQuestionnaireAttributesValues',
            'params' => [
                'token' => $this->service_token,
            ],
        ];

        $headers[] = 'Content-Type: application/json';

        $ch = $this->prepareCurl($this->service_url, $headers, $post_data);
        curl_setopt($ch, CURLOPT_TIMEOUT, 60);
        $response = curl_exec($ch);
        curl_close($ch);
        $result = json_decode($response, true);

        return $result;
    }

    /**
     * * @param $projectId string
     * @return array
     */
    public function getMortgageCreditProgramsByProject($projectId = false)
    {
        $post_data = [
            'method' => 'getMortgageCreditProgramsByProject',
            'params' => [
                'token' => $this->service_token,
            ],
        ];

        if ($projectId) {
            $post_data['params']['Developmentprojectid'] = $projectId;
        }

        $ch = $this->prepareCurl($this->service_url, false, $post_data);
        curl_setopt($ch, CURLOPT_TIMEOUT, 60);
        $response = curl_exec($ch);
        curl_close($ch);

        $result = json_decode($response, true);

        return $result;

    }

    /**
     * @param $creditRequestId string
     * @return array
     */
    public function getMortgageCreditRequestPositions($creditRequestId)
    {
        $post_data = [
            'method' => 'getMortgageCreditRequestPositions',
            'params' => [
                'token' => $this->service_token,
                'creditrequestid' => $creditRequestId
            ],
        ];

        $ch = $this->prepareCurl($this->service_url, $headers, $post_data);
        curl_setopt($ch, CURLOPT_TIMEOUT, 60);
        $response = curl_exec($ch);
        curl_close($ch);
        $result = json_decode($response, true);

        return $result;
    }

    /**
     * @param $creditrequestids array
     * @return array
     */
    public function getMortgageCustomerRequests($creditRequestIds = [])
    {
        $post_data = [
            'method' => 'getMortgageCustomerRequests',
            'params' => [
                'token' => $this->service_token,
            ]
        ];

        if ($creditRequestIds) {
            $post_data['params']['creditrequestids'] = $creditRequestIds;
        }

        $ch = $this->prepareCurl($this->service_url, false, $post_data);
        curl_setopt($ch, CURLOPT_TIMEOUT, 60);
        $response = curl_exec($ch);
        curl_close($ch);
        $result = json_decode($response, true);

        return $result;
    }

    public function getInfraCategories()
    {
        $url = $this->service_url . '/infra-categories/';
        $json_string =  $this->getResponse($url, []);
        $response = json_decode($json_string,true);
        return $response;
    }

    public function getResponse($url, $headers = [])
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        $response = curl_exec($ch);

        if ($response === false)
            $response = curl_error($ch);

        curl_close($ch);

        return $response;
    }

    public function getInfraObjects($projectCode)
    {
        $response = [];
        if ($projectCode) {
            $url = $this->service_url . '/infra-objects/?project_slug=' . $projectCode;
            $json_string = $this->getResponse($url, []);
            $response = json_decode($json_string,true);
        }
        return $response;
    }

    public function getProjects()
    {
        $url = $this->service_url . '/project/?limit=100';
        $json_string = $this->getResponse($url, []);
        $decodeArr = json_decode($json_string,true);
        if ($decodeArr['results']) {
            $response = $decodeArr['results'];
        }
        return $response;
    }

    /**
     * @param $realtyobjectId string
     * @return array
     */
    public function addBooking($realtyobjectId)
    {
        if (!$realtyobjectId) {
            return false;
        }

        $post_data = [
            'method' => 'addBooking',
            'params' => [
                'token' => $this->service_token,
                'realtyobjectId' => $realtyobjectId,
            ],
        ];

        $ch = $this->prepareCurl($this->service_url, false, $post_data);
        curl_setopt($ch, CURLOPT_TIMEOUT, 302);
        $response = curl_exec($ch);
        curl_close($ch);
        $this->writeLog('finish addBooking');

        $result = json_decode($response, true);
        return $result;
    }

    /**
     * @return array
     */
    public function getBookedRealtyobjects()
    {
        $result = [];

        $post_data = [
            'method' => 'getBookedRealtyobjects',
            'params' => [
                'token' => $this->service_token,
            ],
        ];

        $ch = $this->prepareCurl($this->service_url, false, $post_data);
        $response = curl_exec($ch);
        $error = curl_errno($ch);
        $error_message = curl_error($ch);
        curl_close($ch);
        $this->writeLog('finish getBookedRealtyobjects');
        if ($error) {
            $this->errors[] = 'Execute error: ' . $error_message;
            return false;
        }

        if ($response) {
            $result = json_decode($response, true);
            if (isErrors()) {
                $this->errors[] = 'Execute error: ' . $result['errorMessage'];
                return false;
            }
        }

        return $result;
    }

    /**
     * @return array
     */
    public function getCustomerPreliminaryInvoices()
    {
        $result = [];
        $post_data = [
            'method' => 'getCustomerPreliminaryInvoices',
            'params' => [
                'token' => $this->service_token
            ],
        ];

        $ch = $this->prepareCurl($this->service_url, false, $post_data);
        $response = curl_exec($ch);
        $error = curl_errno($ch);
        $error_message = curl_error($ch);
        curl_close($ch);
        $this->writeLog('finish getCustomerPreliminaryInvoices');
        if ($error) {
            $this->errors[] = 'Execute error: ' . $error_message;
            $result = false;
        } else if ($response) {
            $result = json_decode($response, true);
            if (isErrors()) {
                $this->errors[] = 'Execute error: ' . $result['errorMessage'];
                $result = false;
            }
        }

        return $result;
    }

    /**
     * @return array
     */
    public function getCustomerPreliminaryInvoicesFull()
    {
        $result = [];

        $post_data = [
            'method' => 'getCustomerPreliminaryInvoicesFull',
            'params' => [
                'token' => $this->service_token,
            ],
        ];

        $ch = $this->prepareCurl($this->service_url, false, $post_data);
        $response = curl_exec($ch);
        $error = curl_errno($ch);
        $error_message = curl_error($ch);
        curl_close($ch);
        $this->writeLog('finish getCustomerPreliminaryInvoicesFull');
        if ($error) {
            $this->errors[] = 'Execute error: ' . $error_message;
            $result = false;
        } else if ($response) {
            $result = json_decode($response, true);
            if (isErrors()) {
                $this->errors[] = 'Execute error: ' . $result['errorMessage'];
                $result = false;
            }
        }

        return $result;
    }

    /**
     * @param $agreementId string
     * @param $cacheTtl bool
     * @return array
     */
    public function getAgreementPaymentplans($agreementId, $cacheTtl = false)
    {
        $result = [];

        if (!$agreementId) {
            return false;
        }

        $filter = serialize([
            'agreementId' => $agreementId,
        ]);
        if (is_array($this->cache_agreement_paymentplans[$filter])) {
            return $this->cache_agreement_paymentplans[$filter];
        }

        $cacheTtl = is_numeric($cacheTtl) ? $cacheTtl : COMPONENTS_CACHE_5MIN_TTL;
        $obCache = new \CPHPCache();
        if ($obCache->InitCache($cacheTtl, __FUNCTION__ . $filter)) {
            $result = $obCache->GetVars();
        } elseif ($obCache->StartDataCache()) {

            $post_data = [
                'method' => 'getAgreementPaymentplans',
                'params' => [
                    'token' => $this->service_token,
                    'agreementId' => $agreementId,
                ],
            ];

            $ch = $this->prepareCurl($this->service_url, false, $post_data);
            $response = curl_exec($ch);
            $error = curl_errno($ch);
            $error_message = curl_error($ch);
            curl_close($ch);
            $this->writeLog('finish getAgreementPaymentplans');
            if ($error) {
                $this->errors[] = 'Execute error: ' . $error_message;
                $result = false;
            } else if ($response) {
                $result = json_decode($response, true);
                if (isErrors()) {
                    $this->errors[] = 'Execute error: ' . $result['errorMessage'];
                    $result = false;
                }
            }

            $obCache->EndDataCache($result);
        }

        $this->cache_agreement_paymentplans[$filter] = $result;

        return $result;
    }

    /**
     * @param $paymentplanId string
     * @param $cacheTtl bool
     * @return array
     */
    public function getPaymentplan($paymentplanId, $cacheTtl = false)
    {
        $result = [];

        if (!$paymentplanId) {
            return false;
        }

        $filter = serialize([
            'paymentplanId' => $paymentplanId,
        ]);
        if (is_array($this->cache_paymentplan[$filter])) {
            return $this->cache_paymentplan[$filter];
        }

        $cacheTtl = is_numeric($cacheTtl) ? $cacheTtl : COMPONENTS_CACHE_5MIN_TTL;
        $obCache = new \CPHPCache();
        if ($obCache->InitCache($cacheTtl, __FUNCTION__ . $filter)) {
            $result = $obCache->GetVars();
        } elseif ($obCache->StartDataCache()) {

            $post_data = [
                'method' => 'getPaymentplan',
                'params' => [
                    'token' => $this->service_token,
                    'paymentplanId' => $paymentplanId,
                ],
            ];

            $ch = $this->prepareCurl($this->service_url, false, $post_data);
            $response = curl_exec($ch);
            $error = curl_errno($ch);
            $error_message = curl_error($ch);
            curl_close($ch);
            $this->writeLog('finish getPaymentplan');
            if ($error) {
                $this->errors[] = 'Execute error: ' . $error_message;
                $result = false;
            } else if ($response) {
                $result = json_decode($response, true);
                if (isErrors()) {
                    $this->errors[] = 'Execute error: ' . $result['errorMessage'];
                    $result = false;
                }
            }

            $obCache->EndDataCache($result);
        }

        $this->cache_paymentplan[$filter] = $result;

        return $result;
    }

    /**
     * @param $inspectionId string
     * @param $cacheTtl bool
     * @return array
     */
    public function getInspection($inspectionId, $cacheTtl = false)
    {
        $result = [];

        if (!$inspectionId) {
            return false;
        }

        $filter = serialize([
            'inspectionId' => $inspectionId,
        ]);
        if (is_array($this->cache_inspection[$filter])) {
            return $this->cache_inspection[$filter];
        }

        $cacheTtl = is_numeric($cacheTtl) ? $cacheTtl : COMPONENTS_CACHE_5MIN_TTL;
        $obCache = new \CPHPCache();
        if ($obCache->InitCache($cacheTtl, __FUNCTION__ . $filter)) {
            $result = $obCache->GetVars();
        } elseif ($obCache->StartDataCache()) {

            $post_data = [
                'method' => 'getInspection',
                'params' => [
                    'token' => $this->service_token,
                    'inspectionId' => $inspectionId,
                ],
            ];

            $ch = $this->prepareCurl($this->service_url, false, $post_data);
            $response = curl_exec($ch);
            $error = curl_errno($ch);
            $error_message = curl_error($ch);
            curl_close($ch);
            $this->writeLog('finish getInspection');
            if ($error) {
                $this->errors[] = 'Execute error: ' . $error_message;
                $result = false;
            } else if ($response) {
                $result = json_decode($response, true);
                if (isErrors()) {
                    $this->errors[] = 'Execute error: ' . $result['errorMessage'];
                    $result = false;
                }
            }

            $obCache->EndDataCache($result);
        }

        $this->cache_inspection[$filter] = $result;

        return $result;
    }

    /**
     * @param $agreementId string
     * @param $realtyobjectId string
     * @param $dateFrom string
     * @param $dateTill string
     * @param $cacheTtl bool
     * @return array
     */
    public function getInspectionSchedule($agreementId, $realtyobjectId, $dateFrom, $dateTill, $cacheTtl = false)
    {
        $result = [];

        if (!$agreementId) {
            return false;
        }
        if (!$realtyobjectId) {
            return false;
        }
        if (!$dateFrom) {
            return false;
        }
        if (!$dateTill) {
            return false;
        }

        $filter = serialize([
            'agreementId' => $agreementId,
            'realtyobjectId' => $realtyobjectId,
            'dateFrom' => $dateFrom,
            'dateTill' => $dateTill,
        ]);
        if (is_array($this->cache_inspection_schedule[$filter])) {
            return $this->cache_inspection_schedule[$filter];
        }

        $cacheTtl = is_numeric($cacheTtl) ? $cacheTtl : COMPONENTS_CACHE_5MIN_TTL;
        $obCache = new \CPHPCache();
        if ($obCache->InitCache($cacheTtl, __FUNCTION__ . $filter)) {
            $result = $obCache->GetVars();
        } elseif ($obCache->StartDataCache()) {

            $post_data = [
                'method' => 'getInspectionSchedule',
                'params' => [
                    'token' => $this->service_token,
                    'agreementId' => $agreementId,
                    'realtyobjectId' => $realtyobjectId,
                    'dateFrom' => $dateFrom,
                    'dateTill' => $dateTill,
                ],
            ];

            $ch = $this->prepareCurl($this->service_url, false, $post_data);
            $response = curl_exec($ch);
            $error = curl_errno($ch);
            $error_message = curl_error($ch);
            curl_close($ch);
            $this->writeLog('finish getInspectionSchedule');
            if ($error) {
                $this->errors[] = 'Execute error: ' . $error_message;
                $result = false;
            } else if ($response) {
                $result = json_decode($response, true);
                if (isErrors()) {
                    $this->errors[] = 'Execute error: ' . $result['errorMessage'];
                    $result = false;
                }
            }

            $obCache->EndDataCache($result);
        }

        $this->cache_inspection_schedule[$filter] = $result;

        return $result;
    }

    /**
     * @param $processstageId string
     * @param $realtyobjectId string
     * @param $dateFrom string
     * @param $dateTill string
     * @return array
     */
    public function addProcessstageInspection($processstageId, $realtyobjectId, $dateFrom, $dateTill)
    {
        $result = [];

        if (!$processstageId) {
            return false;
        }
        if (!$realtyobjectId) {
            return false;
        }
        if (!$dateFrom) {
            return false;
        }
        if (!$dateTill) {
            return false;
        }

        $post_data = [
            'method' => 'addProcessstageInspection',
            'params' => [
                'token' => $this->service_token,
                'processstageId' => $processstageId,
                'realtyobjectId' => $realtyobjectId,
                'dateFrom' => $dateFrom,
                'dateTill' => $dateTill,
            ],
        ];

        $ch = $this->prepareCurl($this->service_url, false, $post_data);
        $response = curl_exec($ch);

        $error = curl_errno($ch);
        $error_message = curl_error($ch);
        curl_close($ch);
        $this->writeLog('finish addProcessstageInspection');
        if ($error) {
            $this->errors[] = 'Execute error: ' . $error_message;
            $result = false;
        } else if ($response) {
            $result = json_decode($response, true);
            if (isErrors()) {
                $this->errors[] = 'Execute error: ' . $result['errorMessage'];
                $result = false;
            }
        }

        return $result;
    }

    /**
     * @return array
     */
    public function getQuestionnaireAttributesValues()
    {
        $result = [];

        if ($this->cache_questionnaire_attributes_values) {
            return $this->cache_questionnaire_attributes_values;
        }

        $post_data = [
            'method' => 'getQuestionnaireAttributesValues',
            'params' => [
                'token' => $this->service_token,
            ],
        ];

        $ch = $this->prepareCurl($this->service_url, false, $post_data);
        $response = curl_exec($ch);
        $error = curl_errno($ch);
        $error_message = curl_error($ch);
        curl_close($ch);
        $this->writeLog('finish getQuestionnaireAttributesValues');
        if ($error) {
            $this->errors[] = 'Execute error: ' . $error_message;
            return false;
        }

        if ($response) {
            $result = json_decode($response, true);
            if (isErrors()) {
                $this->errors[] = 'Execute error: ' . $result['errorMessage'];
                return false;
            }
        }

        $this->cache_questionnaire_attributes_values = $result;

        return $result;
    }

    /**
     * @param $value string
     * @return array
     */
    public function getMortgageAddressPrompt($value = false)
    {
        $values = [];
        if ($value) {
            $post_data = [
                'method' => 'getMortgageAddressPrompt',
                'params' => [
                    'token' => $this->service_token,
                    'value' => $value,
                ],
            ];
    
            $ch = $this->prepareCurl($this->service_url, $headers, $post_data);
            $response = curl_exec($ch);
            curl_close($ch);
            $result = json_decode($response, true);
            if ($result['result']['addresses']) {
                $values = $result['result']['addresses'];
            }
        }
        return $values;
    }

    /**
     * @param $value string
     * @return array
     */
    public function getMortgagePassportcodePrompt($value = false)
    {
        $values = [];
        if ($value) {
            $post_data = [
                'method' => 'getMortgagePassportcodePrompt',
                'params' => [
                    'token' => $this->service_token,
                    'value' => $value,
                ],
            ];
            $ch = $this->prepareCurl($this->service_url, false, $post_data);
            $response = curl_exec($ch);
            curl_close($ch);
            $result = json_decode($response, true);
            if ($result['result']['passportInfo']) {
                $values = $result['result']['passportInfo'];
            }
        }
        return $values;
    }

    /**
     * @param $value string
     * @return array
     */
    public function getMortgageOrganizationPrompt($value = false)
    {
        $values = [];
        if ($value) {
            $post_data = [
                'method' => 'getMortgageOrganizationPrompt',
                'params' => [
                    'token' => $this->service_token,
                    'value' => $value,
                ],
            ];
            $ch = $this->prepareCurl($this->service_url, false, $post_data);
            $response = curl_exec($ch);
            curl_close($ch);
            $result = json_decode($response, true);
            if ($result['result']['organizationInfo']) {
                $values = $result['result']['organizationInfo'];
            }
        }
        return $values;
    }

    public function clearCache()
    {
        $this->cache_projects_data = null;
        $this->cache_buildings_data = null;
        $this->cache_realty_data = null;
        $this->cache_realty_short_data = null;
        $this->cache_customer_id = null;
        $this->cache_check_customer_questionnaire = null;
        $this->cache_customer_profile = null;
        $this->cache_customer_salutation_notifications = null;
        $this->cache_customer_agreements = null;
        $this->cache_customer_agreements_full = null;
        $this->cache_agreement_realtyobjects = null;
        $this->cache_realtyobject = null;
        $this->cache_agreement_invoices = null;
        $this->cache_agreement_process = null;
        $this->cache_invoice = null;
        $this->cache_customer_booking_invoices = null;
        $this->cache_booked_realty_objects = null;
        $this->cache_customer_preliminary_invoices = null;
        $this->cache_customer_preliminary_invoices_full = null;
        $this->cache_agreement_paymentplans = null;
        $this->cache_paymentplan = null;
        $this->cache_inspection = null;
        $this->cache_inspection_schedule = null;
        $this->cache_questionnaire_attributes_values = null;
    }
}