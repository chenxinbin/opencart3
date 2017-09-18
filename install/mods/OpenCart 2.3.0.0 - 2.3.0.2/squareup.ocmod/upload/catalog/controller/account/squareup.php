<?php

class Controlleraccountsquareup extends Controller {
    private $imodule = 'squareup';
    private $imodule_route;
    private $imodule_model;
    private $imodule_extension_route;
    private $imodule_extension_type;

    public function __construct($registry) {
        parent::__construct($registry);
        
        $this->registry->set('squareService', new \vendor\squareup\Service($this->registry));

        $this->load->config('vendor/' . $this->imodule);

        $this->imodule_route = $this->config->get($this->imodule . '_route');
        $this->imodule_extension_route = $this->config->get($this->imodule . '_extension_route');
        $this->imodule_extension_type = $this->config->get($this->imodule . '_extension_type');

        $this->load->model($this->imodule_route);
        $this->imodule_model = $this->{$this->config->get($this->imodule . '_model_property')};
        //$this->document->addScript($this->url->link($this->imodule_route . '/js', '', true));
    }

    public function index() {
        if (!$this->customer->isLogged()) {
            $this->session->data['redirect'] = $this->url->link('account/account', '', true);

            $this->response->redirect($this->url->link('account/login', '', true));
        }

        $this->load->language('account/squareup');

        $this->document->setTitle($this->language->get('text_manage_cards'));

        $data['breadcrumbs'] = array();

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/home')
        );

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_account'),
            'href' => $this->url->link('account/account', '', true)
        );

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_manage_cards'),
            'href' => $this->url->link('account/squareup', '', true)
        );

        if (isset($this->session->data['success'])) {
            $data['success'] = $this->session->data['success'];

            unset($this->session->data['success']);
        } else {
            $data['success'] = '';
        } 

        if (isset($this->session->data['error'])) {
            $data['error'] = $this->session->data['error'];

            unset($this->session->data['error']);
        } else {
            $data['error'] = '';
        } 

        $data['text_manage_cards'] = $this->language->get('text_manage_cards');
        $data['text_no_cards'] = $this->language->get('text_no_cards');
        $data['text_back'] = $this->language->get('text_back');
        $data['text_delete'] = $this->language->get('text_delete');
        $data['text_warning_card'] = $this->language->get('text_warning_card');

        $data['back'] = $this->url->link('account/account', '', true);

        $data['cards'] = array();
        
        foreach ($this->imodule_model->getCards($this->customer->getId(), $this->config->get($this->imodule . '_enable_sandbox')) as $card) {
            $data['cards'][] = array(
                'text' => sprintf($this->language->get('text_card_ends_in'), $card['brand'], $card['ends_in']),
                'delete' => $this->url->link('account/squareup/forget', 'card_id=' . $card['squareup_token_id'], true)
            );
        }
        
        if (version_compare(VERSION, '2.2.0.0', '>=')) {
            $tpl_path = 'account/squareup';
        } else {
            $tpl_path = $this->config->get('config_template') . '/template/account/squareup.tpl';
            
            if (!file_exists(DIR_TEMPLATE . $tpl_path)) {
                $tpl_path = 'default/template/account/squareup.tpl';
            }
        }

        $data['column_left'] = $this->load->controller('common/column_left');
        $data['column_right'] = $this->load->controller('common/column_right');
        $data['content_top'] = $this->load->controller('common/content_top');
        $data['content_bottom'] = $this->load->controller('common/content_bottom');
        $data['footer'] = $this->load->controller('common/footer');
        $data['header'] = $this->load->controller('common/header');
        
        $this->response->setOutput($this->load->view($tpl_path, $data));
    }

    public function forget() {
        if (!$this->customer->isLogged()) {
            $this->session->data['redirect'] = $this->url->link('account/account', '', true);

            $this->response->redirect($this->url->link('account/login', '', true));
        }

        $card_id = !empty($this->request->get['card_id']) ?
            $this->request->get['card_id'] : 0;

        if ($this->imodule_model->verifyCardCustomer($card_id, $this->customer->getId())) {
            $this->load->language('account/squareup');
            $this->load->model('setting/setting');

            $this->imodule_model->deleteCard($card_id);
            $sandboxIsEnabled = $this->config->get('squareup_enable_sandbox');
            $endpoint = $this->config->get($this->imodule . '_endpoint_delete_card');
            $endpoint = str_replace('%location%', ($sandboxIsEnabled)?$this->config->get('squareup_sandbox_location_id'):$this->config->get('squareup_location_id'), $endpoint);
            $endpoint = str_replace('%customerID%', ($sandboxIsEnabled)?$this->config->get('squareup_sandbox_location_id'):$this->config->get('squareup_location_id'), $endpoint);
            try {
                $this->squareService->api('DELETE', $endpoint);
                
                // This card has been deleted. Set the default card to the first available one
                $first_squareup_token_id = $this->imodule_model->getFirstTokenId($this->customer->getId(), $this->config->get('squareup_enable_sandbox'));
                $this->imodule_model->updateDefaultCustomerToken($this->customer->getId(), $this->config->get('squareup_enable_sandbox'), $first_squareup_token_id);

                $this->session->data['success'] = $this->language->get('text_success_card_delete');
            } catch (\vendor\squareup\Exception $e) {
                $this->session->data['error'] = $e->getMessage();
            }
        }

        $this->response->redirect($this->url->link('account/squareup', '', true));
    }
}