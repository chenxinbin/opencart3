<?php
class ControllerExtensionRecurringSquareup extends Controller {
    private $imodule = 'squareup';
    private $imodule_route;
    private $imodule_recurring_route;
    private $imodule_model;
    private $imodule_extension_route;
    private $imodule_extension_type;

    public function __construct($registry) {
        parent::__construct($registry);
        
        $this->registry->set('squareService', new \vendor\squareup\Service($this->registry));
        $this->registry->set('squareupCurrency', new \vendor\squareup\Currency($this->registry));

        $this->load->config('vendor/' . $this->imodule);

        $this->imodule_route = $this->config->get($this->imodule . '_route');
        $this->imodule_recurring_route = $this->config->get($this->imodule . '_recurring_route');
        $this->imodule_extension_route = $this->config->get($this->imodule . '_extension_route');
        $this->imodule_extension_type = $this->config->get($this->imodule . '_extension_type');

        $this->load->model($this->imodule_route);
        $this->imodule_model = $this->{$this->config->get($this->imodule . '_model_property')};
    }

    public function index() {
        $this->load->language($this->imodule_recurring_route);
        
        if (isset($this->request->get['order_recurring_id'])) {
            $order_recurring_id = $this->request->get['order_recurring_id'];
        } else {
            $order_recurring_id = 0;
        }
        
        $this->load->model('account/recurring');

        $recurring_info = $this->model_account_recurring->getOrderRecurring($order_recurring_id);
        
        if ($recurring_info) {
            $data['cancel_url'] = html_entity_decode($this->url->link($this->imodule_recurring_route . '/cancel', 'order_recurring_id=' . $order_recurring_id, 'SSL'));

            $data['text_loading'] = $this->language->get('text_loading');
            $data['text_confirm_cancel'] = $this->language->get('text_confirm_cancel');

            $data['button_continue'] = $this->language->get('button_continue');
            $data['button_cancel'] = $this->language->get('button_cancel');
            
            $data['continue'] = $this->url->link('account/recurring', '', true);    
            
            if ($recurring_info['status'] == constant($this->config->get($this->imodule . '_model_class') . '::RECURRING_ACTIVE')) {
                $data['order_recurring_id'] = $order_recurring_id;
            } else {
                $data['order_recurring_id'] = '';
            }

            return $this->load->view($this->imodule_recurring_route, $data);
        }
    }
    
    public function cancel() {
        $json = array();
        
        $this->load->language($this->imodule_recurring_route);
        
        //cancel an active recurring
        $this->load->model('account/recurring');
        
        if (isset($this->request->get['order_recurring_id'])) {
            $order_recurring_id = $this->request->get['order_recurring_id'];
        } else {
            $order_recurring_id = 0;
        }
        
        $recurring_info = $this->model_account_recurring->getOrderRecurring($order_recurring_id);

        if ($recurring_info) {
            $cancelled_status = constant($this->config->get($this->imodule . '_model_class') . '::RECURRING_CANCELLED');

            $this->model_account_recurring->editOrderRecurringStatus($order_recurring_id, $cancelled_status);

            $this->load->model('checkout/order');

            $order_info = $this->model_checkout_order->getOrder($recurring_info['order_id']);

            $this->model_checkout_order->addOrderHistory($recurring_info['order_id'], $order_info['order_status_id'], $this->language->get('text_order_history_cancel'), true);

            $json['success'] = $this->language->get('text_canceled');
        } else {
            $json['error'] = $this->language->get('error_not_found');
        }

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }    
}