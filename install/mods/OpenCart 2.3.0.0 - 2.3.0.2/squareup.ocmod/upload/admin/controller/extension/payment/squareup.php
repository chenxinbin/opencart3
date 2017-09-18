<?php

class ControllerExtensionPaymentSquareup extends Controller {
    private $error = array();
    private $imodule = 'squareup';
    private $imodule_route;
    private $imodule_model;
    private $imodule_extension_route;
    private $imodule_extension_type;
    private $imodule_debug = false;
    private $imodule_debug_uri_append = '&XDEBUG_SESSION_START=sublime.xdebug';
    private $imodule_alerts = array();

    public function __construct($registry) {
        parent::__construct($registry);

        if (!$this->registry->has('currency')) {
            if (version_compare(VERSION, '2.2.0.0', '>=')) {
                $currency = new \Cart\Currency($this->registry);
            } else {
                $currency = new \Currency($this->registry);
            }

            $this->registry->set('currency', $currency);
        }

        $this->load->config('vendor/' . $this->imodule);

        $this->registry->set('squareService', new \vendor\squareup\Service($this->registry));
        $this->registry->set('squareupCurrency', new \vendor\squareup\Currency($this->registry));

        $this->imodule_route = $this->config->get($this->imodule . '_route');
        $this->imodule_extension_route = $this->config->get($this->imodule . '_extension_route');
        $this->imodule_extension_type = $this->config->get($this->imodule . '_extension_type');

        $this->load->model($this->imodule_route);
        $this->imodule_model = $this->{$this->config->get($this->imodule . '_model_property')};

        // $this->loadSettings();
        $this->pullAlerts();
    }

    public function recurringCancel() {
        $json = array();
        
        $this->load->language($this->imodule_route);
        
        if (!$this->user->hasPermission('modify', 'sale/recurring')) {
            $json['error'] = $this->language->get('error_permission_recurring');
        } else {
            //cancel an active recurring
            $this->load->model('sale/recurring');
            
            if (isset($this->request->get['order_recurring_id'])) {
                $order_recurring_id = $this->request->get['order_recurring_id'];
            } else {
                $order_recurring_id = 0;
            }
            
            $recurring_info = $this->model_sale_recurring->getRecurring($order_recurring_id);

            if ($recurring_info) {
                $cancelled_status = constant($this->config->get($this->imodule . '_model_class') . '::RECURRING_CANCELLED');

                $this->imodule_model->editOrderRecurringStatus($order_recurring_id, $cancelled_status);

                $json['success'] = $this->language->get('text_canceled_success');
                
            } else {
                $json['error'] = $this->language->get('error_not_found');
            }
        }

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }

    public function recurringButtons() {
        if (!$this->user->hasPermission('modify', 'sale/recurring')) {
            return;
        }
        
        $this->load->model('sale/recurring');

        $this->load->language($this->imodule_route);

        if (isset($this->request->get['order_recurring_id'])) {
            $order_recurring_id = $this->request->get['order_recurring_id'];
        } else {
            $order_recurring_id = 0;
        }

        $recurring_info = $this->model_sale_recurring->getRecurring($order_recurring_id);

        $data['button_text'] = $this->language->get('button_cancel_recurring');

        $data['text_confirm_cancel'] = $this->language->get('text_confirm_cancel');
        $data['text_loading'] = $this->language->get('text_loading');

        if ($recurring_info['status'] == constant($this->config->get($this->imodule . '_model_class') . '::RECURRING_ACTIVE')) {
            $data['order_recurring_id'] = $order_recurring_id;
        } else {
            $data['order_recurring_id'] = '';
        }

        $this->load->model('sale/order');

        $order_info = $this->model_sale_order->getOrder($recurring_info['order_id']);

        $data['order_id'] = $recurring_info['order_id'];
        $data['store_id'] = $order_info['store_id'];
        $data['order_status_id'] = $order_info['order_status_id'];
        $data['comment'] = $this->language->get('text_order_history_cancel');
        $data['notify'] = 1;

        // API login
        $data['button_ip_add'] = $this->language->get('button_ip_add');
        $data['catalog'] = $this->request->server['HTTPS'] ? HTTPS_CATALOG : HTTP_CATALOG;
        $data['token'] = $this->session->data['token'];

        $this->load->model('user/api');

        $api_info = $this->model_user_api->getApi($this->config->get('config_api_id'));

        if ($api_info) {
            $data['api_id'] = $api_info['api_id'];
            $data['api_key'] = $api_info['key'];
            $data['api_ip'] = $this->request->server['REMOTE_ADDR'];
        } else {
            $data['api_id'] = '';
            $data['api_key'] = '';
            $data['api_ip'] = '';
        }

        $data['cancel_url'] = html_entity_decode($this->url->link($this->imodule_route . '/recurringCancel', 'order_recurring_id=' . $order_recurring_id . '&token=' . $this->session->data['token'], true));

        return $this->load->view($this->imodule_route . '_recurring_buttons', $data);
    }

    public function acceptAppSettings() {
        $this->load->language($this->imodule_route);

        $json = array();

        if (!$this->user->hasPermission('modify', $this->imodule_route)) {
            $json['error'] = $this->language->get('error_permission');
        }

        if (empty($this->request->post['squareup_client_id']) || strlen($this->request->post['squareup_client_id']) > 32) {
            $json['error'] = $this->language->get('error_client_id');
        }

        if (empty($this->request->post['squareup_client_secret']) || strlen($this->request->post['squareup_client_secret']) > 50) {
            $json['error'] = $this->language->get('error_client_secret');
        }

        if (empty($json['error'])) {
            $this->session->data['square_connect']['squareup_client_id'] = $this->request->post['squareup_client_id'];
            $this->session->data['square_connect']['squareup_client_secret'] = $this->request->post['squareup_client_secret'];
            $this->session->data['square_connect']['squareup_redirect_uri'] = $this->config->get('squareup_redirect_uri');

            $json['redirect'] = $this->setupAuthLink($this->request->post['squareup_client_id']);
        }

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }

    public function install() {
        $this->load->language($this->imodule_route);
        $this->load->model($this->imodule_route);
        $this->imodule_model->createTables();
    }

    public function uninstall() {
        $this->load->language($this->imodule_route);
        $this->load->model($this->imodule_route);
        $this->imodule_model->dropTables();
    }

    // generates an auth link with a random state, which is saved to the session for verification later
    private function setupAuthLink($clientId, $locale='en-US', $session=true) {
        $authLink = 
            $this->config->get($this->imodule . '_base_url') . '/' .
            $this->config->get($this->imodule . '_endpoint_authorize');
        $state = '';
        if (isset($this->session->data['squareup_oauth_state'])) {
            $state = $this->session->data['squareup_oauth_state'];
        } else {
            $state = bin2hex(openssl_random_pseudo_bytes(32));
            $this->session->data['squareup_oauth_state'] = $state;
        }
        
        $redirectUri = $this->getRedirectUri();
        $this->session->data['squareup_oauth_redirect'] = $redirectUri;
        $params = array(
            'client_id' => $clientId,
            'response_type' => 'code',
            'scope' => implode(' ', $this->config->get($this->imodule . '_scopes')),
            'locale' => $locale,
            'session' => 'false',
            'state' => $state,
            'redirect_uri' => $redirectUri
        );

        return $authLink . '?' . http_build_query($params);
    }

    private function getRedirectUri($addSessionToken = true) {
        $uri = $this->url->link($this->config->get($this->imodule . '_redirect_uri'), ($addSessionToken)?'token='.$this->session->data['token']:null, true);
        if ($this->imodule_debug) {
            $uri .= $this->imodule_debug_uri_append;
        }
        return str_replace('&amp;','&',$uri);
    }

    private function getSessionAlerts() {
        if (isset($this->session->data['alerts']))
            return $this->session->data['alerts'];
        else
            return array();
    }

    private function pushAlert($alert) {
        $this->imodule_alerts[] = $alert;
        $this->session->data['squareup_alerts'] = $this->imodule_alerts;
    }

    private function pullAlerts() {
        $this->imodule_alerts = (isset($this->session->data['squareup_alerts']))?$this->session->data['squareup_alerts']:array();
    }

    private function clearAlerts() {
        unset($this->session->data['squareup_alerts']);
    }

    public function refresh_token() {
        $this->load->model('setting/setting');
        $endpoint = $this->config->get($this->imodule . '_endpoint_refresh_token');
        $endpoint = str_replace('%clientID%', $this->config->get('squareup_client_id'), $endpoint);
        $params = array(
            'access_token' => $this->config->get('squareup_access_token')
        );
        try {
            $response = $this->squareService->api(
                'POST',
                $endpoint,
                $params,
                null,
                \vendor\squareup\Service::API_CONTENT_JSON,
                true,
                $this->config->get('squareup_client_secret'),
                'Client',
                true
            );
            if (!isset($response['access_token']) || !isset($response['token_type']) || !isset($response['expires_at']) || !isset($response['merchant_id']) ||
                $response['merchant_id'] != $this->config->get('squareup_merchant_id')) {
                $this->pushAlert(array(
                    'type' => 'danger',
                    'icon' => 'exclamation-circle',
                    'text' => $this->language->get('error_refresh_access_token') 
                ));
            } else {
                $settings = $this->model_setting_setting->getSetting($this->imodule); 
                $settings['squareup_access_token'] = $response['access_token']; 
                $settings['squareup_access_token_expires'] = $response['expires_at']; 
                $this->model_setting_setting->editSetting($this->imodule, $settings); 
                $this->pushAlert(array(
                    'type' => 'success',
                    'icon' => 'exclamation-circle',
                    'text' => $this->language->get('text_refresh_access_token_success')
                ));
            }
        } catch (\vendor\squareup\Exception $e) {
            $this->pushAlert(array(
                'type' => 'danger',
                'icon' => 'exclamation-circle',
                'text' => $e->getMessage()
            ));
        }

        $this->response->redirect($this->url->link($this->imodule_route, 'token=' . $this->session->data['token'], true));
    }

    public function index() {
        if ($this->request->server['HTTPS']) {
            $server = HTTPS_SERVER;
        } else {
            $server = HTTP_SERVER;
        }

        $this->load->model('setting/setting');
        $language = $this->load->language($this->imodule_route);

        $this->document->setTitle($this->language->get('heading_title'));
        $this->document->addStyle($server . 'view/stylesheet/squareup.css');
        $this->document->addScript($server . 'view/javascript/squareup.js');

        $this->imodule_alerts = $this->getSessionAlerts();

        $valid = true;

        $cronEmail = $this->config->get('squareup_cron_email');

        $data = array( // user editable settings
            'squareup_status' => $this->config->get('squareup_status'),
            'squareup_status_authorized' => $this->config->get('squareup_status_authorized'),
            'squareup_status_captured' => $this->config->get('squareup_status_captured'),
            'squareup_status_voided' => $this->config->get('squareup_status_voided'),
            'squareup_status_failed' => $this->config->get('squareup_status_failed'),
            'squareup_status_partially_refunded' => $this->config->get('squareup_status_partially_refunded'),
            'squareup_status_fully_refunded' => $this->config->get('squareup_status_fully_refunded'),
            'squareup_display_name' => $this->config->get('squareup_display_name'),
            'squareup_enable_sandbox' => $this->config->get('squareup_enable_sandbox'),
            'squareup_debug' => $this->config->get('squareup_debug'),
            'squareup_sort_order' => $this->config->get('squareup_sort_order'),
            'squareup_total' => $this->config->get('squareup_total'),
            'squareup_geo_zone_id' => $this->config->get('squareup_geo_zone_id'),
            'squareup_sandbox_client_id' => $this->config->get('squareup_sandbox_client_id'),
            'squareup_sandbox_token' => $this->config->get('squareup_sandbox_token'),
            'squareup_location_id' => $this->config->get('squareup_location_id'),
            'squareup_sandbox_location_id' => $this->config->get('squareup_sandbox_location_id'),
            'squareup_delay_capture' => $this->config->get('squareup_delay_capture'),
            'squareup_recurring_status' => $this->config->get('squareup_recurring_status'),
            'squareup_cron_email_status' => $this->config->get('squareup_cron_email_status'),
            'squareup_cron_email' => ($cronEmail !== null)?$cronEmail:$this->config->get('config_email'), // fill in the OpenCart admin email only initially, afterwards accept a blank field
            'squareup_cron_token' => $this->config->get('squareup_cron_token'),
            'squareup_cron_acknowledge' => $this->config->get('squareup_cron_acknowledge'),
            'squareup_notify_recurring_success' => $this->config->get('squareup_notify_recurring_success'),
            'squareup_notify_recurring_fail' => $this->config->get('squareup_notify_recurring_fail')
        );

        $data['admin_url'] = $this->url->link($this->imodule_route . '/transaction_info', '&squareup_transaction_id=%s%s', true);

        // Token expiration message
        $data['access_token_expires_time'] = $this->setAccessTokenAlerts(true); // if the access token has been revoked, this will clear the merchant info, so this has to be before inserting the internal settings in the tpl data

        $storedSettings = $this->model_setting_setting->getSetting($this->imodule);

        $internalSettings = array( // internal settings maintained by the extension
            'squareup_client_id' => (isset($storedSettings['squareup_client_id']))?$storedSettings['squareup_client_id']:'',
            'squareup_locations' => (isset($storedSettings['squareup_locations']))?$storedSettings['squareup_locations']:array(),
            'squareup_sandbox_locations' => (isset($storedSettings['squareup_sandbox_locations']))?$storedSettings['squareup_sandbox_locations']:array(),
            'squareup_client_secret' => (isset($storedSettings['squareup_client_secret']))?$storedSettings['squareup_client_secret']:'',
            'squareup_access_token' => (isset($storedSettings['squareup_access_token']))?$storedSettings['squareup_access_token']:'',
            'squareup_access_token_expires' => (isset($storedSettings['squareup_access_token_expires']))?$storedSettings['squareup_access_token_expires']:'',
            'squareup_merchant_id' => (isset($storedSettings['squareup_merchant_id']))?$storedSettings['squareup_merchant_id']:'',
            'squareup_merchant_name' => (isset($storedSettings['squareup_merchant_name']))?$storedSettings['squareup_merchant_name']:''
        );

        $data = array_merge($data, $internalSettings, $language); // TODO: dump the language file

        if ($this->request->server['REQUEST_METHOD'] == 'POST') {
            $valid = $this->validate();

            if ($valid) {
                $newSettings = $this->request->post;
                $merchantIdSetting = $this->config->get('squareup_merchant_id');
                try {
                    // check if we can update the live locations
                    if (!empty($merchantIdSetting)) {
                        $locationIdKey = 'squareup_location_id';
                        $locationsKey = 'squareup_locations';
                        $token = $this->config->get('squareup_access_token');
                        if (!empty($token)) {
                            $locations = $this->squareService->api('GET', $this->config->get($this->imodule . '_endpoint_locations'), null, null, \vendor\squareup\Service::API_CONTENT_JSON, true, $token);
                            $locations = $this->filterCardProcessingLocations($locations['locations']);
                            $locationCount = count($locations);
                            $oldLocationId = $this->config->get($locationIdKey);
                            if ($locationCount == 0) {
                                // no valid locations
                                $internalSettings[$locationIdKey] = '';
                                $internalSettings[$locationsKey] = array();
                            } else if ($locationCount == 1 || empty($oldLocationId)) { // if there's only one location or (>1 location, but hadn't been set before)
                                // set it to the first available one
                                $internalSettings[$locationIdKey] = $locations[0]['id'];
                                $internalSettings[$locationsKey] = $locations;
                            }
                        }
                    }
                    // check if we can update the sandbox locations
                    if (!empty($newSettings['squareup_sandbox_token']) && !empty($newSettings['squareup_sandbox_client_id'])) {
                        $locationIdKey = 'squareup_sandbox_location_id';
                        $locationsKey = 'squareup_sandbox_locations';
                        $token = $newSettings['squareup_sandbox_token'];
                        if (!empty($token)) {
                            $locations = $this->squareService->api('GET', $this->config->get($this->imodule . '_endpoint_locations'), null, null, \vendor\squareup\Service::API_CONTENT_JSON, true, $token);
                            $locations = $this->filterCardProcessingLocations($locations['locations']);
                            $locationCount = count($locations);
                            $oldLocationId = $this->config->get($locationIdKey);
                            if ($locationCount == 0) {
                                // no valid locations
                                $internalSettings[$locationIdKey] = '';
                                $internalSettings[$locationsKey] = array();
                            } else if ($locationCount == 1 || empty($oldLocationId)) { // if there's only one location or (>1 location, but hadn't been set before)
                                // set it to the first available one
                                $internalSettings[$locationIdKey] = $locations[0]['id'];
                                $internalSettings[$locationsKey] = $locations;
                            }
                        }
                    }
                } catch (\vendor\squareup\Exception $e) {
                    $this->pushAlert(array(
                        'type' => 'danger',
                        'icon' => 'exclamation-circle',
                        'text' => $this->language->get('text_location_error')
                    ));
                }

                $this->model_setting_setting->editSetting($this->imodule, array_merge($newSettings, $internalSettings)); // perserve internal settings + update any changed in the above connect + toggled block

                $this->session->data['success'] = $this->language->get('text_success');

                if (isset($this->request->get['save_and_auth'])) {
                    $this->response->redirect($this->setupAuthLink($newSettings['squareup_client_id']));
                } else {
                    $this->response->redirect($this->url->link($this->imodule_extension_route, 'token=' . $this->session->data['token'] . $this->imodule_extension_type, true));
                }
            } else {
                // forward the edited but not saved values
                if (isset($this->request->post['squareup_status'])) { $data['squareup_status'] = $this->request->post['squareup_status']; }
                if (isset($this->request->post['squareup_status_authorized'])) { $data['squareup_status_authorized'] = $this->request->post['squareup_status_authorized']; }
                if (isset($this->request->post['squareup_status_captured'])) { $data['squareup_status_captured'] = $this->request->post['squareup_status_captured']; }
                if (isset($this->request->post['squareup_status_voided'])) { $data['squareup_status_voided'] = $this->request->post['squareup_status_voided']; }
                if (isset($this->request->post['squareup_status_failed'])) { $data['squareup_status_failed'] = $this->request->post['squareup_status_failed']; }
                if (isset($this->request->post['squareup_status_partially_refunded'])) { $data['squareup_status_partially_refunded'] = $this->request->post['squareup_status_partially_refunded']; }
                if (isset($this->request->post['squareup_status_fully_refunded'])) { $data['squareup_status_fully_refunded'] = $this->request->post['squareup_status_fully_refunded']; }
                if (isset($this->request->post['squareup_display_name'])) { $data['squareup_display_name'] = $this->request->post['squareup_display_name']; }
                if (isset($this->request->post['squareup_enable_sandbox'])) { $data['squareup_enable_sandbox'] = $this->request->post['squareup_enable_sandbox']; }
                if (isset($this->request->post['squareup_debug'])) { $data['squareup_debug'] = $this->request->post['squareup_debug']; }
                if (isset($this->request->post['squareup_sort_order'])) { $data['squareup_sort_order'] = $this->request->post['squareup_sort_order']; }
                if (isset($this->request->post['squareup_total'])) { $data['squareup_total'] = $this->request->post['squareup_total']; }
                if (isset($this->request->post['squareup_geo_zone_id'])) { $data['squareup_geo_zone_id'] = $this->request->post['squareup_geo_zone_id']; }
                if (isset($this->request->post['squareup_sandbox_client_id'])) { $data['squareup_sandbox_client_id'] = $this->request->post['squareup_sandbox_client_id']; }
                if (isset($this->request->post['squareup_sandbox_token'])) { $data['squareup_sandbox_token'] = $this->request->post['squareup_sandbox_token']; }
                if (isset($this->request->post['squareup_location_id'])) { $data['squareup_location_id'] = $this->request->post['squareup_location_id']; }
                if (isset($this->request->post['squareup_sandbox_location_id'])) { $data['squareup_sandbox_location_id'] = $this->request->post['squareup_sandbox_location_id']; }
                if (isset($this->request->post['squareup_delay_capture'])) { $data['squareup_delay_capture'] = $this->request->post['squareup_delay_capture']; }
                if (isset($this->request->post['squareup_recurring_status'])) { $data['squareup_recurring_status'] = $this->request->post['squareup_recurring_status']; }
                if (isset($this->request->post['squareup_cron_email_status'])) { $data['squareup_cron_email_status'] = $this->request->post['squareup_cron_email_status']; }
                if (isset($this->request->post['squareup_cron_email'])) { $data['squareup_cron_email'] = $this->request->post['squareup_cron_email']; }
                if (isset($this->request->post['squareup_cron_token'])) { $data['squareup_cron_token'] = $this->request->post['squareup_cron_token']; }
                if (isset($this->request->post['squareup_cron_acknowledge'])) { $data['squareup_cron_acknowledge'] = $this->request->post['squareup_cron_acknowledge']; } else { $data['squareup_cron_acknowledge'] = ''; }
                if (isset($this->request->post['squareup_notify_recurring_success'])) { $data['squareup_notify_recurring_success'] = $this->request->post['squareup_notify_recurring_success']; }
                if (isset($this->request->post['squareup_notify_recurring_fail'])) { $data['squareup_notify_recurring_fail'] = $this->request->post['squareup_notify_recurring_fail']; }
            }
        } else {
            if (empty($storedSettings['squareup_cron_acknowledge'])) {
                $this->pushAlert(array(
                    'type' => 'warning',
                    'icon' => 'exclamation-circle',
                    'text' => $this->language->get('text_warning_cron')
                ));
            }
        }

        $authLink = $this->setupAuthLink($data['squareup_client_id']);
        $data['squareup_auth_link'] = $authLink;
        $data['squareup_redirect_uri'] = $this->getRedirectUri(false);
        $data['squareup_refresh_link'] = $this->url->link($this->imodule_route . '/refresh_token', 'token=' . $this->session->data['token'], true);

        $data['scroll_to_connect'] = !empty($this->request->get['scroll_to_connect']);

        // input field error messages
        if (isset($this->error['status'])) {
            $data['err_status'] = $this->error['status'];
        } else {
            $data['err_status'] = '';
        }
        if (isset($this->error['display_name'])) {
            $data['err_display_name'] = $this->error['display_name'];
        } else {
            $data['err_display_name'] = '';
        }
        if (isset($this->error['client_id'])) {
            $data['err_client_id'] = $this->error['client_id'];
        } else {
            $data['err_client_id'] = '';
        }
        if (isset($this->error['client_secret'])) {
            $data['err_client_secret'] = $this->error['client_secret'];
        } else {
            $data['err_client_secret'] = '';
        }
        if (isset($this->error['delay_capture'])) {
            $data['err_delay_capture'] = $this->error['delay_capture'];
        } else {
            $data['err_delay_capture'] = '';
        }
        if (isset($this->error['enable_sandbox'])) {
            $data['err_enable_sandbox'] = $this->error['enable_sandbox'];
        } else {
            $data['err_enable_sandbox'] = '';
        }
        if (isset($this->error['sandbox_client_id'])) {
            $data['err_sandbox_client_id'] = $this->error['sandbox_client_id'];
        } else {
            $data['err_sandbox_client_id'] = '';
        }
        if (isset($this->error['sandbox_token'])) {
            $data['err_sandbox_token'] = $this->error['sandbox_token'];
        } else {
            $data['err_sandbox_token'] = '';
        }
        if (isset($this->error['location'])) {
            $data['err_location'] = $this->error['location'];
        } else {
            $data['err_location'] = '';
        }
        if (isset($this->error['cron_email'])) {
            $data['err_cron_email'] = $this->error['cron_email'];
        } else {
            $data['err_cron_email'] = '';
        }
        if (isset($this->error['cron_acknowledge'])) {
            $data['err_cron_acknowledge'] = $this->error['cron_acknowledge'];
        } else {
            $data['err_cron_acknowledge'] = '';
        }
        if (isset($this->error['status_authorized'])) {
            $data['err_status_authorized'] = $this->error['status_authorized'];
        } else {
            $data['err_status_authorized'] = '';
        }
        if (isset($this->error['status_captured'])) {
            $data['err_status_captured'] = $this->error['status_captured'];
        } else {
            $data['err_status_captured'] = '';
        }
        if (isset($this->error['status_voided'])) {
            $data['err_status_voided'] = $this->error['status_voided'];
        } else {
            $data['err_status_voided'] = '';
        }
        if (isset($this->error['status_failed'])) {
            $data['err_status_failed'] = $this->error['status_failed'];
        } else {
            $data['err_status_failed'] = '';
        }
        if (isset($this->error['status_partially_refunded'])) {
            $data['err_status_partially_refunded'] = $this->error['status_partially_refunded'];
        } else {
            $data['err_status_partially_refunded'] = '';
        }
        if (isset($this->error['status_fully_refunded'])) {
            $data['err_status_fully_refunded'] = $this->error['status_fully_refunded'];
        } else {
            $data['err_status_fully_refunded'] = '';
        }

        if (!$this->config->get('squareup_status')) {
            $this->pushAlert(array(
                'type' => 'warning',
                'icon' => 'exclamation-circle',
                'text' => $this->language->get('text_extension_disabled'),
                'non_dismissable' => true
            ));
        }

        if ($this->config->get('squareup_enable_sandbox')) {
            $this->pushAlert(array(
                'type' => 'warning',
                'icon' => 'exclamation-circle',
                'text' => $this->language->get('text_sandbox_enabled')
            ));
        }

        if (isset($this->error['warning'])) {
            $this->pushAlert(array(
                'type' => 'danger',
                'icon' => 'exclamation-circle',
                'text' => $this->error['warning']
            ));
        }

        // Insert success message from the session
        if (isset($this->session->data['success'])) {
            $this->pushAlert(array(
                'type' => 'success',
                'icon' => 'exclamation-circle',
                'text' => $this->session->data['success']
            ));
            unset($this->session->data['success']);
        }

        if ($this->request->server['HTTPS']) {
            // Push the SSL reminder alert
            $this->pushAlert(array(
                'type' => 'info',
                'icon' => 'lock',
                'text' => $this->language->get('text_notification_ssl')
            ));
        } else {
            // Push the SSL reminder alert
            $this->pushAlert(array(
                'type' => 'danger',
                'icon' => 'exclamation-circle',
                'text' => $this->language->get('error_no_ssl')
            ));
        }

        if ($this->config->get('squareup_access_token')) {
            $this->pushAlert(array(
                'type' => 'info',
                'icon' => 'exclamation-circle',
                'text' => $this->language->get('text_enable_payment')
            ));
        }

        if ($this->config->get('squareup_delay_capture')) {
            $this->pushAlert(array(
                'type' => 'warning',
                'icon' => 'exclamation-circle',
                'text' => $this->language->get('text_auth_voided_6_days')
            ));
        }

        // Location warning
        $merchantId = $this->config->get('squareup_merchant_id');
        $hasSelectedLocation = ($this->config->get('squareup_enable_sandbox')) ? ($this->config->get('squareup_sandbox_location_id') != '') : ($this->config->get('squareup_location_id') != '');
        $availableLocations = ($this->config->get('squareup_enable_sandbox')) ? (count($this->config->get('squareup_sandbox_locations')) != 0) : (count($this->config->get('squareup_locations')) != 0);
        if (!empty($merchantId)) {
            if ($availableLocations && !$hasSelectedLocation) {
                $this->pushAlert(array(
                    'type' => 'warning',
                    'icon' => 'exclamation-circle',
                    'text' => $this->language->get('text_no_location_selected_warning')
                ));
            }
            if (!$availableLocations) {
                $this->pushAlert(array(
                    'type' => 'warning',
                    'icon' => 'exclamation-circle',
                    'text' => $this->language->get('text_no_appropriate_locations_warning')
                ));
            }
        }

        $this->load->model($this->imodule_route);

        $tabs = array(
            'tab-transaction',
            'tab-setting',
            'tab-recurring',
            'tab-cron'
        );

        if (isset($this->request->get['tab']) && in_array($this->request->get['tab'], $tabs)) {
            $data['tab'] = $this->request->get['tab'];
        } else if ($this->error) {
            $data['tab'] = 'tab-setting';
        } else {
            $data['tab'] = $tabs[1];
        }

        $data['breadcrumbs'] = array();

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/dashboard', 'token=' . $this->session->data['token'], true)
        );

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_extension'),
            'href' => $this->url->link($this->imodule_extension_route, 'token=' . $this->session->data['token'] . $this->imodule_extension_type, true)
        );

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('heading_title'),
            'href' => $this->url->link($this->imodule_route, 'token=' . $this->session->data['token'], true)
        );

        $data['action'] = $this->url->link($this->imodule_route, 'token=' . $this->session->data['token'], true) . (($this->imodule_debug)?$this->imodule_debug_uri_append:'');
        $data['help'] = 'http://docs.isenselabs.com/square';
        $data['url_video_help'] = 'https://www.youtube.com/watch?v=YVJyBrNb-BU';
        $data['url_integration_settings_help'] = 'http://docs.isenselabs.com/square/integration_settings';
        $data['cancel'] = $this->url->link($this->imodule_extension_route, 'token=' . $this->session->data['token'] . $this->imodule_extension_type, true);
        $data['url_accept_credentials'] = html_entity_decode($this->url->link($this->imodule_route . '/acceptAppSettings', 'token=' . $this->session->data['token'], true));

        $data['url_list_transactions'] = html_entity_decode($this->url->link($this->imodule_route . '/transactions', 'token=' . $this->session->data['token'] . '&page={PAGE}', true));

        $data['text_loading'] = $this->language->get('text_loading');

        $data['default_display_name'] = $this->language->get('heading_title');

        $this->load->model('localisation/language');
        $data['languages'] = array();

        foreach ($this->model_localisation_language->getLanguages() as $language) {
            $data['languages'][] = array(
                'language_id' => $language['language_id'],
                'name' => $language['name'] . ($language['code'] == $this->config->get('config_language') ? $this->language->get('text_default') : ''),
                'image' => version_compare(VERSION, '2.2', '>=') ? 'language/' . $language['code'] . '/'. $language['code'] . '.png' : 'view/image/flags/' . $language['image']
            );
        }

        $this->load->model('localisation/order_status');
        $data['order_statuses'] = $this->model_localisation_order_status->getOrderStatuses();

        $this->load->model('localisation/geo_zone');
        $data['geo_zones'] = $this->model_localisation_geo_zone->getGeoZones();

        $data['squareup_alerts'] = $this->imodule_alerts;

        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');

        if (version_compare(VERSION, '2.2.0.0', '>=')) {
            $tpl_path = $this->imodule_route;
            $modals_path = $this->imodule_route . '_modals';
        } else {
            $tpl_path = $this->imodule_route . '.tpl';
            $modals_path = $this->imodule_route . '_modals.tpl';
        }

        $data['squareup_modals'] = $this->load->view($modals_path, $data);

        $data['squareup_cron_command'] = 'export CUSTOM_SERVER_NAME=' . parse_url($server, PHP_URL_HOST) . '; export CUSTOM_SERVER_PORT=443; export SQUARE_CRON=1; ' . PHP_BINDIR . '/php ' . DIR_SYSTEM . 'library/vendor/squareup/cron.php > /dev/null 2> /dev/null';
        
        if (!$this->config->get('squareup_cron_token')) {
            $data['squareup_cron_token'] = md5(mt_rand());
        }

        $data['squareup_cron_url'] = 'https://' . parse_url($server, PHP_URL_HOST) . dirname(parse_url($server, PHP_URL_PATH)) . '/index.php?route=' . $this->imodule_route . '/recurring&cron_token={CRON_TOKEN}';

        $this->clearAlerts();
        
        $this->response->setOutput($this->load->view($tpl_path, $data));
    }

    private function setAccessTokenAlerts($scroll_to_connect = false) {
        $this->load->model('setting/setting');
        $setting = $this->config->get('squareup_access_token');
        
        $tokenExpiration = $this->config->get('squareup_access_token_expires');
        $accessTokenExpires = date_create_from_format('Y-m-d\TH:i:s\Z', $tokenExpiration);
        
        if (!empty($setting)) {
            $now = date_create();
            $delta = $accessTokenExpires->getTimestamp() - $now->getTimestamp();
            if ($delta < 0) {
                $this->pushAlert(array(
                    'type' => 'danger',
                    'icon' => 'exclamation-circle',
                    'text' => sprintf($this->language->get('text_token_expired'), $this->url->link($this->imodule_route . '/refresh_token', 'token=' . $this->session->data['token'], true))
                ));
            } else {
                // check if token has been revoked
                if (!$this->squareService->liveTokenIsValid(true)) { // the token can be invalid because it's either expired or revoked, but $delta >= 0 means it's not expired
                    //$authLink = $this->setupAuthLink($this->config->get('squareup_client_id'));

                    if (!$scroll_to_connect) {
                        $href = $this->url->link($this->imodule_route, '&scroll_to_connect=1&token=' . $this->session->data['token'], true);
                    } else {
                        $href = 'javascript:void(0)';
                    }

                    $this->pushAlert(array(
                        'type' => 'danger',
                        'icon' => 'exclamation-circle',
                        'text' => sprintf($this->language->get('text_token_revoked'), $href)
                    ));
                } else if ($delta < (5 * 24 * 60 * 60)) { // token is valid, just about to expire
                    $this->pushAlert(array(
                        'type' => 'warning',
                        'icon' => 'exclamation-circle',
                        'text' => sprintf($this->language->get('text_token_expiry_warning'), $accessTokenExpires->format('l, F jS, Y h:i:s A, e'), $this->url->link($this->imodule_route . '/refresh_token', 'token=' . $this->session->data['token'], true))
                    ));
                }
            }
        }

        return !empty($accessTokenExpires) ? $accessTokenExpires->format('l, F jS, Y h:i:s A, e') : '';
    }

    public function oauth_callback() {
        $this->load->model('setting/setting');
        $this->load->language($this->imodule_route);
        // check for api errors
        if (isset($this->request->get['error']) || isset($this->request->get['error_description'])) {
            // auth error
            if ($this->request->get['error'] == 'access_denied' && $this->request->get['error_description'] == 'user_denied') {
                // user rejected giving auth permissions to his store
                $this->pushAlert(array(
                    'type' => 'warning',
                    'icon' => 'exclamation-circle',
                    'text' => $this->language->get('error_user_rejected_connect_attempt')
                ));
            }

            $this->response->redirect($this->url->link($this->imodule_route, 'token=' . $this->session->data['token'], true));
            return;
        }
        // verify parameters for the redirect from Square (against random url crawling)
        if (!isset($this->request->get['route']) || !isset($this->request->get['token']) || !isset($this->request->get['state']) || !isset($this->request->get['code']) || !isset($this->request->get['response_type']) 
            || $this->request->get['route'] != $this->session->data['square_connect'][$this->imodule. '_redirect_uri']) {
            // missing or wrong info
            $this->pushAlert(array(
                'type' => 'danger',
                'icon' => 'exclamation-circle',
                'text' => $this->language->get('error_possible_xss')
            ));

            $this->response->redirect($this->url->link($this->imodule_route, 'token=' . $this->session->data['token'], true));
            return;
        }
        // verify the state (against cross site requests)
        if (!isset($this->session->data['squareup_oauth_state']) || $this->session->data['squareup_oauth_state'] != $this->request->get['state']) {
            // state mismatch
            $this->pushAlert(array(
                'type' => 'danger',
                'icon' => 'exclamation-circle',
                'text' => $this->language->get('error_possible_xss')
            ));

            $this->response->redirect($this->url->link($this->imodule_route, 'token=' . $this->session->data['token'], true));
            return;
        }
        $token = $this->exchangeCodeForAccessToken($this->session->data['square_connect']['squareup_client_id'], $this->session->data['square_connect']['squareup_client_secret'], $this->request->get['code']);
        $this->session->data['squareup_oauth_access_token'] = $token['access_token'];
        $this->cleanUpSessionLoginState();
        
        try {
            $liveLocations = $this->squareService->api(
                'GET',
                $this->config->get($this->imodule.'_endpoint_locations'),
                null,
                null,
                \vendor\squareup\Service::API_CONTENT_JSON,
                true,
                $token['access_token']
            );
            $liveLocations = $this->filterCardProcessingLocations($liveLocations['locations']);

            $setting_client_id = $this->config->get('squareup_sandbox_client_id');
            $setting_sandbox_token = $this->config->get('squareup_sandbox_token');

            $settings = $this->model_setting_setting->getSetting($this->imodule); // we need to carry over existing settings because we can't modify individual ones with editSettingValue in older OpenCart versions

            if (!empty($setting_client_id) && !empty($setting_sandbox_token)) {
                $sandboxLocations = $this->squareService->api(
                    'GET',
                    $this->config->get($this->imodule.'_endpoint_locations'),
                    null,
                    null,
                    \vendor\squareup\Service::API_CONTENT_JSON,
                    true,
                    $this->config->get('squareup_sandbox_token')
                );
                $sandboxLocations = $this->filterCardProcessingLocations($sandboxLocations['locations']);
                $settings['squareup_sandbox_locations'] = $sandboxLocations;
                $settings['squareup_sandbox_location_id'] = (count($sandboxLocations) > 0)?($sandboxLocations[0]['id']):'';
            }

            $settings['squareup_client_id'] = $this->session->data['square_connect']['squareup_client_id'];
            $settings['squareup_client_secret'] = $this->session->data['square_connect']['squareup_client_secret'];

            unset($this->session->data['square_connect']);

            $settings['squareup_merchant_id'] = $token['merchant_id'];
            $settings['squareup_merchant_name'] = ''; // only available in v1 of the API, not populated for now
            $settings['squareup_access_token'] = $token['access_token'];
            $settings['squareup_access_token_expires'] = $token['expires_at'];
            $settings['squareup_locations'] = $liveLocations;
            $settings['squareup_location_id'] = (count($liveLocations) > 0)?$liveLocations[0]['id']:'';

            $this->model_setting_setting->editSetting($this->imodule, $settings);

            $this->session->data['success'] = $this->language->get('text_connection_success');
        } catch (\vendor\squareup\Exception $e) {
            $this->pushAlert(array(
                'type' => 'danger',
                'icon' => 'exclamation-circle',
                'text' => $e->getMessage()
            ));
        }

        $this->response->redirect($this->url->link($this->imodule_route, 'token=' . $this->session->data['token'], true));
    }

    private function cleanUpSessionLoginState() {
        unset($this->session->data['squareup_oauth_state']);
        unset($this->session->data['squareup_oauth_redirect']);
    }

    private function exchangeCodeForAccessToken($clientId, $clientSecret, $code) {
        $body = json_encode(array(
            'client_id' => $clientId,
            'client_secret' => $clientSecret,
            'redirect_uri' => $this->session->data['squareup_oauth_redirect'],
            'code' => $code
        ));
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $this->config->get($this->imodule.'_base_url') . '/' . $this->config->get($this->imodule.'_endpoint_obtain_token'),
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_POST => 1,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json'
            ],
            CURLOPT_POSTFIELDS => $body
        ]);
        $result = curl_exec($ch);
        if ($result) {
            return json_decode($result,true);
        }
        return null;
    }

    private function fetchMe() {
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $this->config->get($this->imodule.'_base_url') . '/v1/me',
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_POST => 0,
            CURLOPT_HTTPHEADER => [
                'Authorization: Bearer ' . $this->session->data['squareup_oauth_access_token']
            ]
        ]);
        $result = curl_exec($ch);
        if ($result) {
            return json_decode($result,true);
        } else {
            return curl_getinfo($ch);
        }
    }

    public function transaction_info() {
        $this->load->language($this->imodule_route);

        $this->load->model($this->imodule_route);

        if (isset($this->request->get['squareup_transaction_id'])) {
            $squareup_transaction_id = $this->request->get['squareup_transaction_id'];
        } else {
            $squareup_transaction_id = 0;
        }
        $transaction_info = $this->imodule_model->getTransaction($squareup_transaction_id);

        if (empty($transaction_info)) {
            $this->response->redirect($this->url->link($this->imodule_route, 'token=' . $this->session->data['token'], true));
        }

        $this->document->setTitle(sprintf($this->language->get('heading_title_transaction'), $transaction_info['transaction_id']));

        if ($this->request->server['HTTPS']) {
            $server = HTTPS_SERVER;
        } else {
            $server = HTTP_SERVER;
        }

        $this->document->addStyle($server . 'view/stylesheet/squareup.css');
        $this->document->addScript($server . 'view/javascript/squareup.js');

        $this->pullAlerts();

        $data['squareup_alerts'] = $this->imodule_alerts;
        
        $this->clearAlerts();

        $data['text_edit'] = sprintf($this->language->get('heading_title_transaction'), $transaction_info['transaction_id']);

        $data['heading_title'] = $this->language->get('heading_title');

        $data['button_cancel'] = $this->language->get('button_cancel');
        $data['button_void'] = $this->language->get('button_void');
        $data['button_refund'] = $this->language->get('button_refund');
        $data['button_capture'] = $this->language->get('button_capture');

        $amount = $this->currency->format($transaction_info['transaction_amount'], $transaction_info['transaction_currency']);

        $data['text_void'] = $this->language->get('text_void');
        $data['text_refund'] = $this->language->get('text_refund');
        $data['text_capture'] = $this->language->get('text_capture');
        $data['confirm_capture'] = sprintf($this->language->get('text_confirm_capture'), $amount);
        $data['confirm_void'] = sprintf($this->language->get('text_confirm_void'), $amount);
        $data['confirm_refund'] = $this->language->get('text_confirm_refund');
        $data['insert_amount'] = sprintf($this->language->get('text_insert_amount'), $amount, $transaction_info['transaction_currency']);
        $data['text_loading'] = $this->language->get('text_loading_short');
        $data['text_confirm_action'] = $this->language->get('text_confirm_action');
        $data['text_close'] = $this->language->get('text_close');
        $data['text_ok'] = $this->language->get('text_ok');
        $data['text_refund_details'] = $this->language->get('text_refund_details');

        $data['entry_transaction_id'] = $this->language->get('entry_transaction_id');
        $data['entry_merchant'] = $this->language->get('entry_merchant');
        $data['entry_order_id'] = $this->language->get('entry_order_id');
        $data['entry_partner_solution_id'] = $this->language->get('entry_partner_solution_id');
        $data['entry_transaction_status'] = $this->language->get('entry_transaction_status');
        $data['entry_amount'] = $this->language->get('entry_amount');
        $data['entry_currency'] = $this->language->get('entry_currency');
        $data['entry_browser'] = $this->language->get('entry_browser');
        $data['entry_ip'] = $this->language->get('entry_ip');
        $data['entry_date_created'] = $this->language->get('entry_date_created');

        $data['entry_billing_address_company'] = $this->language->get('entry_billing_address_company');
        $data['entry_billing_address_street'] = $this->language->get('entry_billing_address_street');
        $data['entry_billing_address_city'] = $this->language->get('entry_billing_address_city');
        $data['entry_billing_address_postcode'] = $this->language->get('entry_billing_address_postcode');
        $data['entry_billing_address_province'] = $this->language->get('entry_billing_address_province');
        $data['entry_billing_address_country'] = $this->language->get('entry_billing_address_country');

        $data['billing_address_company'] = $transaction_info['billing_address_company'];
        $data['billing_address_street'] = $transaction_info['billing_address_street'];
        $data['billing_address_city'] = $transaction_info['billing_address_city'];
        $data['billing_address_postcode'] = $transaction_info['billing_address_postcode'];
        $data['billing_address_province'] = $transaction_info['billing_address_province'];
        $data['billing_address_country'] = $transaction_info['billing_address_country'];

        $transaction_status = $this->getTransactionStatus($transaction_info);

        $data['transaction_id'] = $transaction_info['transaction_id'];
        $data['is_fully_refunded'] = $transaction_status['is_fully_refunded'];
        $data['merchant'] = $transaction_info['merchant_id'];
        $data['order_id'] = $transaction_info['order_id'];
        $data['status'] = $transaction_status['text'];
        $data['amount'] = $amount;
        $data['currency'] = $transaction_info['transaction_currency'];
        $data['browser'] = $transaction_info['device_browser'];
        $data['ip'] = $transaction_info['device_ip'];
        $data['date_created'] = date($this->language->get('datetime_format'), strtotime($transaction_info['created_at']));
        
        $data['cancel'] = $this->url->link($this->imodule_route, 'token=' . $this->session->data['token'] . '&tab=tab-transaction', true);

        $data['url_order'] = $this->url->link('sale/order/info', 'token=' . $this->session->data['token'] . '&order_id=' . $transaction_info['order_id'], true);
        $data['url_void'] = $this->url->link($this->imodule_route . '/void', 'token=' . $this->session->data['token'] . '&squareup_transaction_id=' . $transaction_info['squareup_transaction_id'], true);
        $data['url_capture'] = $this->url->link($this->imodule_route . '/capture', 'token=' . $this->session->data['token'] . '&squareup_transaction_id=' . $transaction_info['squareup_transaction_id'], true);
        $data['url_refund'] = $this->url->link($this->imodule_route . '/refund', 'token=' . $this->session->data['token'] . '&squareup_transaction_id=' . $transaction_info['squareup_transaction_id'], true);
        $data['url_transaction'] = sprintf(
            $this->config->get($this->imodule . '_transaction_link'),
            $transaction_info['transaction_id'],
            $transaction_info['location_id']
        );

        $data['is_authorized'] = in_array($transaction_info['transaction_type'], array('AUTHORIZED'));
        $data['is_captured'] = in_array($transaction_info['transaction_type'], array('CAPTURED'));

        $data['has_refunds'] = count($transaction_status['refunds']);

        if (count($transaction_status['refunds'])) {
            $data['refunds'] = array();

            $data['column_date_created'] = $this->language->get('column_date_created');
            $data['column_reason'] = $this->language->get('column_reason');
            $data['column_status'] = $this->language->get('column_status');
            $data['column_amount'] = $this->language->get('column_amount');
            $data['column_fee'] = $this->language->get('column_fee');

            $data['text_refunds'] = sprintf($this->language->get('text_refunds'), count($transaction_status['refunds']));

            foreach ($transaction_status['refunds'] as $refund) {
                $amount = $this->currency->format(
                    $this->squareupCurrency->standardDenomination(
                        $refund['amount_money']['amount'], 
                        $refund['amount_money']['currency']
                    ), 
                    $refund['amount_money']['currency']
                );

                $fee = $this->currency->format(
                    $this->squareupCurrency->standardDenomination(
                        $refund['processing_fee_money']['amount'], 
                        $refund['processing_fee_money']['currency']
                    ), 
                    $refund['processing_fee_money']['currency']
                );

                $data['refunds'][] = array(
                    'date_created' => date($this->language->get('datetime_format'), strtotime($refund['created_at'])),
                    'reason' => $refund['reason'],
                    'status' => $refund['status'],
                    'amount' => $amount,
                    'fee' => $fee
                );
            }
        }

        $data['breadcrumbs'] = array();

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/dashboard', 'token=' . $this->session->data['token'], true)
        );

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_extension'),
            'href' => $this->url->link($this->imodule_extension_route, 'token=' . $this->session->data['token'] . $this->imodule_extension_type, true)
        );

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('heading_title'),
            'href' => $this->url->link($this->imodule_route, 'token=' . $this->session->data['token'], true)
        );

        $data['breadcrumbs'][] = array(
            'text' => sprintf($this->language->get('heading_title_transaction'), $transaction_info['squareup_transaction_id']),
            'href' => $this->url->link($this->imodule_route . '/transaction_info', 'token=' . $this->session->data['token'] . '&squareup_transaction_id=' . $squareup_transaction_id, true)
        );

        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');

        if (version_compare(VERSION, '2.2.0.0', '>=')) {
            $tpl_path = $this->imodule_route . '_transaction_info';
            $modals_path = $this->imodule_route . '_modals';
        } else {
            $tpl_path = $this->imodule_route . '_transaction_info.tpl';
            $modals_path = $this->imodule_route . '_modals.tpl';
        }

        $data['squareup_modals'] = $this->load->view($modals_path, $data);

        $this->response->setOutput($this->load->view($tpl_path, $data));
    }

    public function transactions() {
        $this->load->language($this->imodule_route);

        $this->load->model($this->imodule_route);

        if (isset($this->request->get['page'])) {
            $page = (int)$this->request->get['page'];
        } else {
            $page = 1;
        }

        $result = array(
            'transactions' => array(),
            'order_status_id' => 0,
            'pagination' => ''
        );

        $filter_data = array(
            'start' => ($page - 1) * (int)$this->config->get('config_limit_admin'),
            'limit' => $this->config->get('config_limit_admin')
        );

        if (isset($this->request->get['order_id'])) {
            // We want to get all possible transactions, regardless of the selected page
            $filter_data = array(
                'order_id' => $this->request->get['order_id']
            );
        }

        $transactions_total = $this->imodule_model->getTotalTransactions($filter_data);
        $transactions = $this->imodule_model->getTransactions($filter_data);

        $this->load->model('sale/order');

        foreach ($transactions as $transaction) {
            $amount = $this->currency->format($transaction['transaction_amount'], $transaction['transaction_currency']);

            $order_info = $this->model_sale_order->getOrder($transaction['order_id']);
            $customer = $order_info['firstname'] . ' ' . $order_info['lastname'];

            $transaction_status = $this->getTransactionStatus($transaction);

            $result['transactions'][] = array(
                'squareup_transaction_id' => $transaction['squareup_transaction_id'],
                'transaction_id' => $transaction['transaction_id'],
                'url_order' => $this->url->link('sale/order/info', 'token=' . $this->session->data['token'] . '&order_id=' . $transaction['order_id'], true),
                'url_void' => $this->url->link($this->imodule_route . '/void', 'token=' . $this->session->data['token'] . '&squareup_transaction_id=' . $transaction['squareup_transaction_id'], true),
                'url_capture' => $this->url->link($this->imodule_route . '/capture', 'token=' . $this->session->data['token'] . '&squareup_transaction_id=' . $transaction['squareup_transaction_id'], true),
                'url_refund' => $this->url->link($this->imodule_route . '/refund', 'token=' . $this->session->data['token'] . '&squareup_transaction_id=' . $transaction['squareup_transaction_id'], true),
                'confirm_capture' => sprintf($this->language->get('text_confirm_capture'), $amount),
                'confirm_void' => sprintf($this->language->get('text_confirm_void'), $amount),
                'confirm_refund' => $this->language->get('text_confirm_refund'),
                'insert_amount' => sprintf($this->language->get('text_insert_amount'), $amount, $transaction['transaction_currency']),
                'order_id' => $transaction['order_id'],
                'type' => $transaction_status['type'],
                'status' => $transaction_status['text'],
                'amount_refunded' => $transaction_status['amount_refunded'],
                'is_fully_refunded' => $transaction_status['is_fully_refunded'],
                'amount' => $amount,
                'customer' => $customer,
                'ip' => $transaction['device_ip'],
                'date_created' => date($this->language->get('datetime_format'), strtotime($transaction['created_at'])),
                'url_info' => $this->url->link($this->imodule_route . '/transaction_info', 'token=' . $this->session->data['token'] . '&squareup_transaction_id=' . $transaction['squareup_transaction_id'], true)
            );
        }

        $pagination = new Pagination();
        $pagination->total = $transactions_total;
        $pagination->page = $page;
        $pagination->limit = $this->config->get('config_limit_admin');
        $pagination->url = '{page}';

        $result['pagination'] = $pagination->render();

        if (isset($this->request->get['order_id'])) {
            $order_info = $this->model_sale_order->getOrder($this->request->get['order_id']);

            $result['order_status_id'] = $order_info['order_status_id'];
        }

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($result));
    }

    private function getTransactionStatus($transaction) {
        $result['text'] = '';
        $result['amount_refunded'] = $this->language->get('text_na');
        $result['is_fully_refunded'] = false;
        $result['type'] = $transaction['transaction_type'];

        $refunds = @json_decode($transaction['refunds'], true);

        $result['refunds'] = $refunds;

        if (empty($refunds)) {
            // Check if transaction has been automatically voided
            if ($transaction['transaction_type'] == 'AUTHORIZED') {
                $this->imodule_model->refreshTransaction($transaction['squareup_transaction_id']);

                $transaction = $this->imodule_model->getTransaction($transaction['squareup_transaction_id']);

                $result['type'] = $transaction['transaction_type'];
            }

            $result['text'] = $this->language->get('text_status_' . strtolower($transaction['transaction_type']));
        } else {
            $amount = 0;
            $has_pending = false;
            $used_to_have_pending = false;

            // Fetch transaction again if it has a pending refund
            foreach ($refunds as $refund) {
                if ($refund['status'] == 'PENDING') {
                    $used_to_have_pending = true;

                    $this->imodule_model->refreshTransaction($transaction['squareup_transaction_id']);

                    $transaction = $this->imodule_model->getTransaction($transaction['squareup_transaction_id']);

                    $refunds = @json_decode($transaction['refunds'], true);

                    $result['refunds'] = $refunds;

                    break;
                }
            }

            foreach ($refunds as $refund) {
                if ($refund['status'] == 'REJECTED' || $refund['status'] == 'FAILED') {
                    continue;
                }

                if ($refund['status'] == 'PENDING') {
                    $has_pending = true;

                    if ($used_to_have_pending) {
                        // Set to false because it still has pending
                        $used_to_have_pending = false;
                    }
                }

                $amount += $refund['amount_money']['amount'];
            }

            $result['amount_refunded'] = $this->currency->format(
                $this->squareupCurrency->standardDenomination($amount, $transaction['transaction_currency']),
                $transaction['transaction_currency']
            );

            if ($amount == $this->squareupCurrency->lowestDenomination($transaction['transaction_amount'], $transaction['transaction_currency'])) {
                $result['text'] = $this->language->get('text_fully_refunded');
                $result['is_fully_refunded'] = true;
            } else {
                $result['text'] = $this->language->get('text_partially_refunded');
            }

            if ($has_pending) {
                $result['text'] = sprintf($this->language->get('text_refund_pending'), $result['text']);
            }

            if ($used_to_have_pending) {
                if ($result['is_fully_refunded']) {
                    $this->imodule_model->addOrderHistory($transaction['order_id'], 'fully_refunded', $this->language->get('text_fully_refunded_comment'));
                } else {
                    $this->imodule_model->addOrderHistory($transaction['order_id'], 'partially_refunded', $this->language->get('text_partially_refunded_comment'));
                }
            }
        }

        return $result;
    }

    private function transactionAction($callback) {
        $json = array();

        if (!$this->user->hasPermission('modify', $this->imodule_route)) {
            $json['error'] = $this->language->get('error_permission');
        }

        if (isset($this->request->get['squareup_transaction_id'])) {
            $squareup_transaction_id = $this->request->get['squareup_transaction_id'];
        } else {
            $squareup_transaction_id = 0;
        }

        $transaction_info = $this->imodule_model->getTransaction($squareup_transaction_id);

        if (empty($transaction_info)) {
            $json['error'] = $this->language->get('error_transaction_missing');
        } else if (empty($json['error'])) {
            try {
                $callback($squareup_transaction_id, $json);
            } catch (\vendor\squareup\Exception $e) {
                $json['error'] = $e->getMessage();
            }
        }

        if (!empty($this->request->server['HTTP_X_REQUESTED_WITH']) && $this->request->server['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest') {
            $this->response->addHeader('Content-Type: application/json');
            $this->response->setOutput(json_encode($json));
        } else {
            if (!empty($json['error'])) {
                $this->pushAlert(array(
                    'type' => 'danger',
                    'icon' => 'exclamation-circle',
                    'text' => $json['error']
                ));
            }

            if (!empty($json['success'])) {
                $this->pushAlert(array(
                    'type' => 'success',
                    'icon' => 'exclamation-circle',
                    'text' => $json['success']
                ));
            }

            $this->response->redirect($this->request->server['HTTP_REFERER']);
        }
    }

    public function capture() {
        $this->load->language($this->imodule_route);

        $this->transactionAction(function($squareup_transaction_id, &$json) {
            $this->imodule_model->captureTransaction($squareup_transaction_id);

            $result = $this->imodule_model->refreshTransaction($squareup_transaction_id);

            $comment = $this->language->get($this->imodule . '_status_comment_' . $result['transaction_status']);

            $this->imodule_model->addOrderHistory($result['order_id'], $result['transaction_status'], $comment);

            $json['success'] = $this->language->get('text_success_capture');
        });
    }

    public function void() {
        $this->load->language($this->imodule_route);

        $this->transactionAction(function($squareup_transaction_id, &$json) {
            $this->imodule_model->voidTransaction($squareup_transaction_id);

            $result = $this->imodule_model->refreshTransaction($squareup_transaction_id);

            $comment = $this->language->get($this->imodule . '_status_comment_' . $result['transaction_status']);

            $this->imodule_model->addOrderHistory($result['order_id'], $result['transaction_status'], $comment);

            $json['success'] = $this->language->get('text_success_void');
        });
    }

    public function refund() {
        $this->load->language($this->imodule_route);
        
        $this->transactionAction(function($squareup_transaction_id, &$json) {
            if (!empty($this->request->post['reason'])) {
                $reason = $this->request->post['reason'];
            } else {
                $reason = 'Reason not provided.';
            }

            if (!empty($this->request->post['amount'])) {
                $amount = preg_replace('~[^0-9\.\,]~', '', $this->request->post['amount']);

                if (strpos($amount, ',') !== FALSE && strpos($amount, '.') !== FALSE) {
                    $amount = (float)str_replace(',', '', $amount);
                } else if (strpos($amount, ',') !== FALSE && strpos($amount, '.') === FALSE) {
                    $amount = (float)str_replace(',', '.', $amount);
                } else {
                    $amount = (float)$amount;
                }
            } else {
                $amount = 0;
            }

            $new_refund = $this->imodule_model->refundTransaction($squareup_transaction_id, $reason, $amount);

            $result = $this->imodule_model->refreshTransaction($squareup_transaction_id, $new_refund['refund']);

            $refunded_amount = $this->currency->format(
                $this->squareupCurrency->standardDenomination(
                    $new_refund['refund']['amount_money']['amount'], 
                    $new_refund['refund']['amount_money']['currency']
                ), 
                $new_refund['refund']['amount_money']['currency']
            );

            $comment = sprintf($this->language->get('text_refunded_amount'), $refunded_amount, $new_refund['refund']['status'], $new_refund['refund']['reason']);

            $status = in_array($result['transaction_status'], array('partially_refunded', 'fully_refunded')) ? $result['transaction_status'] : null;

            $this->imodule_model->addOrderHistory($result['order_id'], $status, $comment);
            
            $json['success'] = $this->language->get('text_success_refund');
        });
    }

    public function access_token_alert() {
        $this->load->language($this->imodule_route);

        $this->setAccessTokenAlerts();

        $this->pullAlerts();

        $this->clearAlerts();

        $data['alerts'] = $this->imodule_alerts;

        if (version_compare(VERSION, '2.2.0.0', '>=')) {
            $tpl_path = $this->imodule_route . '_access_token_alert';
        } else {
            $tpl_path = $this->imodule_route . '_access_token_alert.tpl';
        }

        $this->response->setOutput($this->load->view($tpl_path, $data));
    }

    public function order() {
        $language = $this->load->language($this->imodule_route);

        $data = $language;

        $data['url_list_transactions'] = html_entity_decode($this->url->link($this->imodule_route . '/transactions', 'token=' . $this->session->data['token'] . '&order_id=' . $this->request->get['order_id'] . '&page={PAGE}', true));
        $data['token'] = $this->session->data['token'];
        $data['order_id'] = $this->request->get['order_id'];

        if (version_compare(VERSION, '2.2.0.0', '>=')) {
            $tpl_path = $this->imodule_route . '_order';
        } else {
            $tpl_path = $this->imodule_route . '_order.tpl';
        }

        return $this->load->view($tpl_path, $data);
    }

    private function filterCardProcessingLocations($locations) {
        $filtered = array();
        foreach ($locations as $key => $location) {
            if (!isset($location['capabilities']) || count($location['capabilities']) == 0) continue;
            if (in_array('CREDIT_CARD_PROCESSING', $location['capabilities'])) {
                $filtered[] = $location;
            }
        }
        return $filtered;
    }

    protected function validate() {
        $this->load->model('setting/setting');

        if (!$this->user->hasPermission('modify', $this->imodule_route)) {
            $this->error['warning'] = $this->language->get('error_permission');
        }

        if (empty($this->request->post['squareup_status'])) {
            return true;
        }

        if ((isset($this->request->post['squareup_enable_sandbox']) && $this->request->post['squareup_enable_sandbox'] == 1) &&
            (empty($this->request->post['squareup_sandbox_client_id']) || strlen($this->request->post['squareup_sandbox_client_id']) > 42) ) {
            $this->error['sandbox_client_id'] = $this->language->get('error_sandbox_client_id');
        }

        if ((isset($this->request->post['squareup_enable_sandbox']) && $this->request->post['squareup_enable_sandbox'] == 1) &&
            (empty($this->request->post['squareup_sandbox_token']) || strlen($this->request->post['squareup_sandbox_token']) > 42)) {
            $this->error['sandbox_token'] = $this->language->get('error_sandbox_token');
        }
        
        $merchantId = $this->config->get('squareup_merchant_id');
        if (!empty($merchantId)) {
            if (isset($this->request->post['squareup_enable_sandbox']) && $this->request->post['squareup_enable_sandbox'] == 1) {
                // sandbox mode selected
                /*$hasLocations = (count($this->config->get('squareup_sandbox_locations')) != 0);
                if ($hasLocations && empty($this->request->post['squareup_sandbox_location_id'])) {
                    $this->error['location'] = $this->language->get('error_no_location_selected');
                }*/
            } else {
                // live mode
                $hasLocations = (count($this->config->get('squareup_locations')) != 0);
                if ($hasLocations && empty($this->request->post['squareup_location_id'])) {
                    $this->error['location'] = $this->language->get('error_no_location_selected');
                }
            }
        }

        if (!empty($this->request->post['squareup_cron_email_status'])) {
            if (!filter_var($this->request->post['squareup_cron_email'], FILTER_VALIDATE_EMAIL)) {
                $this->error['cron_email'] = $this->language->get('error_invalid_email');
            }
        }

        if (empty($this->request->post['squareup_cron_acknowledge'])) {
            $this->error['cron_acknowledge'] = $this->language->get('error_cron_acknowledge');
        }

        if (empty($this->request->post['squareup_status_authorized'])) {
            $this->error['status_authorized'] = $this->language->get('error_status_not_set');
        }

        if (empty($this->request->post['squareup_status_captured'])) {
            $this->error['status_captured'] = $this->language->get('error_status_not_set');
        }

        if (empty($this->request->post['squareup_status_voided'])) {
            $this->error['status_voided'] = $this->language->get('error_status_not_set');
        }

        if (empty($this->request->post['squareup_status_failed'])) {
            $this->error['status_failed'] = $this->language->get('error_status_not_set');
        }

        if (empty($this->request->post['squareup_status_partially_refunded'])) {
            $this->error['status_partially_refunded'] = $this->language->get('error_status_not_set');
        }

        if (empty($this->request->post['squareup_status_fully_refunded'])) {
            $this->error['status_fully_refunded'] = $this->language->get('error_status_not_set');
        }

        if (empty($this->error['warning']) && !empty($this->error)) {
            $this->error['warning'] = $this->language->get('error_form');
        }

        return !$this->error;
    }
}
