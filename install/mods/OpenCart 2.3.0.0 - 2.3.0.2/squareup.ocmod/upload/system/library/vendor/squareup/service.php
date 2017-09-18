<?php

namespace vendor\squareup;

class Service extends Registry {
    private $imodule = 'squareup';

    const API_CONTENT_JSON = 'application/json';
    const API_CONTENT_URLENC = 'application/x-www-form-urlencoded';
    const API_CONTENT_MULTIPART = 'multipart/form-data';

    const ERR_CODE_ACCESS_TOKEN_REVOKED = 'ACCESS_TOKEN_REVOKED';
    const ERR_CODE_ACCESS_TOKEN_EXPIRED = 'ACCESS_TOKEN_EXPIRED';

    private function loadSettings() {
        $this->load->model('setting/setting');
        $storedSettings = $this->model_setting_setting->getSetting($this->imodule);
        /*if ($storedSettings != null && is_array($storedSettings) && isset($storedSettings['squareup_ext_settings'])) {
            $storedSettings = $storedSettings['squareup_ext_settings'];
        }*/
        return $storedSettings;
    }

    private function modSettings($settings) {
        $group_key = version_compare(VERSION, '2.0.0.0', '>') ? 'code' : 'group';

        foreach ($settings as $key => $value) {
            $this->db->query("DELETE FROM `" . DB_PREFIX . "setting` WHERE `" . $group_key . "`='" . $this->imodule . "' AND `key`='" . $key . "'");

            $this->db->query("INSERT INTO `" . DB_PREFIX . "setting` SET `" . $group_key . "`='" . $this->imodule . "', `key`='" . $key . "', `value`='" . $this->db->escape($value) . "', serialized=0, store_id=0");
        }
    }

    private function debug($text) {
        if ($this->config->get('squareup_debug')) {
            $this->log->write($text);
        }
    }

    /* Generic remote API call method; uses the squareup config.
     * $auth: false - no auth headers set, true - uses the token stored in the extension settings, string - use as token
     */
    public function api($method, $endpoint, $parameters=null, $headers=null, $contentType='application/json', $auth=true, $customToken=null, $authType='Bearer', $noVersion=false) {
        $apiUri = $this->config->get($this->imodule.'_base_url') 
                  . ((!$noVersion)?('/' . $this->config->get($this->imodule.'_api_version')):'')
                  . '/' . $endpoint;
        $curlOptions = array(
            CURLOPT_URL => $apiUri,
            CURLOPT_RETURNTRANSFER => true
        );

        // handle method and parameters
        $encodedParameters = null;
        if ($parameters != null && is_array($parameters) && count($parameters) != 0) {
            $encodedParameters = $this->encodeAPIParameters($parameters, $contentType);
        }
        switch ($method) {
            case 'GET':
                $curlOptions[CURLOPT_POST] = 0;
                if ($encodedParameters != null) $curlOptions[CURLOPT_URL] .= ((strpos($apiUri, '?') === false)? '?' : '&') . $encodedParameters;
                break;
            case 'POST':
                $curlOptions[CURLOPT_POST] = 1;
                if ($encodedParameters != null) $curlOptions[CURLOPT_POSTFIELDS] = $encodedParameters;
                break;
            default:
                $curlOptions[CURLOPT_CUSTOMREQUEST] = $method;
                if ($encodedParameters != null) $curlOptions[CURLOPT_POSTFIELDS] = $encodedParameters;
                break;
        }

        $this->debug("SQUAREUP ENDPOINT: " . $curlOptions[CURLOPT_URL]);
        $this->debug("SQUAREUP PARAMS: " . $encodedParameters);

        // handle headers
        $addedHeaders = array();
        if ($auth !== false) {
            $bearerToken = null;
            if ($customToken == null) {
                if ($this->config->get('squareup_sandbox_token') && $this->config->get('squareup_enable_sandbox')) { // sandbox token trumps regular access token
                    $bearerToken = $this->config->get('squareup_sandbox_token');
                } else {
                    $bearerToken = $this->config->get('squareup_access_token');
                }
            } else {
                $bearerToken = $customToken; // custom token trumps sandbox/regular one
            }
            $addedHeaders[] = 'Authorization: ' . $authType . ' ' . $bearerToken;
        }
        if (!is_array($encodedParameters)) { // curl automatically adds Content-Type: multipart/form-data when we provide an array
            $addedHeaders[] = 'Content-Type: ' . $contentType;
        }
        if ($headers != null && is_array($headers) && count($headers) != 0) {
            $curlOptions[CURLOPT_HTTPHEADER] = array_merge($addedHeaders, $headers);
        } else {
            $curlOptions[CURLOPT_HTTPHEADER] = $addedHeaders;
        }

        // Fire off the request
        $ch = curl_init();
        curl_setopt_array($ch, $curlOptions);
        $result = curl_exec($ch);

        if ($result) {
            $this->debug("SQUAREUP RESULT: " . $result);

            curl_close($ch);

            $return = json_decode($result, true);

            if (!empty($return['errors'])) {
                throw new Exception($return['errors'], $this->registry);

                return null;
            } else {
                return $return;
            }

        } else {
            $info = curl_getinfo($ch);

            curl_close($ch);

            throw new Exception("CURL error. Info: " . print_r($info, true), $this->registry, $info);

            return null;
        }
    }

    /* Tests whether the current live (not sandbox) token is still valid, or has expired/been revoked.
     * Performs an authenticated GET request (/locations) and checks for ACCESS_TOKEN_REVOKED and ACCESS_TOKEN_EXPIRED error codes in the response
     */
    public function liveTokenIsValid($disconnectOnInvalid = false) {
        try {
            // fire test authenticated request
            $endpoint = $this->config->get('squareup_endpoint_locations');
            $liveToken = $this->config->get('squareup_access_token');
            $result = $this->api('GET', $endpoint, null, null, true, $liveToken);
            // if $this->api(...) doesn't throw the token is still valid
            return true;
        } catch (Exception $e) {
            $errors = $e->getErrors();
            foreach ($errors as $err) {
                if ($err['code'] == Service::ERR_CODE_ACCESS_TOKEN_REVOKED || $err['code'] == Service::ERR_CODE_ACCESS_TOKEN_EXPIRED) {
                    if ($disconnectOnInvalid) {
                        // keep the access token
                        $this->modSettings(array(
                            'squareup_status' => 0,
                            'squareup_merchant_id' => '',
                            'squareup_merchant_name' => ''
                        ));
                    }
                    return false;
                }
            }
            // the test authed call threw an error unrelated to token validity
            return true;
        }
    }

    // Prepares a key=>value parameter array for curl depending on the content type
    private function encodeAPIParameters($params, $contentType) {
        switch ($contentType) {
            case 'application/json': return json_encode($params); break;
            case 'application/x-www-form-urlencoded': return http_build_query($params); break;
            default:
            case 'multipart/form-data': return $params; break; // curl will handle the params as multipart form data if we just leave it as an array
        }
    }

}