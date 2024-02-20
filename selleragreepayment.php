<?php
use PrestaShop\PrestaShop\Core\Payment\PaymentOption;

if (!defined('_PS_VERSION_')) {
    exit;
}

class Selleragreepayment extends PaymentModule
{
    protected $_html = '';
    protected $_postErrors = array();

    public $details;
    public $owner;
    public $address;
    public $extra_mail_vars;

    public function __construct()
    {
        $this->name = 'selleragreepayment';
        $this->tab = 'payments_gateways';
        $this->version = '1.0.1';
        $this->author = 'Franco Jara';
        $this->need_instance = 0;
        $this->ps_versions_compliancy = [
            'min' => '1.7.0.0',
            'max' => '8.99.99',
        ];

        $this->currencies = true;
        $this->currencies_mode = 'checkbox';

        $config = Configuration::getMultiple(array('SELLER_AGREE_DETAILS', 'SELLER_AGREE_OWNER', 'SELLER_AGREE_ADDRESS', 'SELLER_AGREE_RESERVATION_DAYS'));
        if (!empty($config['SELLER_AGREE_OWNER'])) {
            $this->owner = $config['SELLER_AGREE_OWNER'];
        }
        if (!empty($config['SELLER_AGREE_DETAILS'])) {
            $this->details = $config['SELLER_AGREE_DETAILS'];
        }
        if (!empty($config['SELLER_AGREE_ADDRESS'])) {
            $this->address = $config['SELLER_AGREE_ADDRESS'];
        }
        if (!empty($config['SELLER_AGREE_RESERVATION_DAYS'])) {
            $this->reservation_days = $config['SELLER_AGREE_RESERVATION_DAYS'];
        }

        $this->bootstrap = true;
        parent::__construct();

        $this->displayName = $this->trans('Seller Agree Payment');
        $this->description = $this->trans('Add the payment method agreement with the seller');

        $this->confirmUninstall = $this->trans('Are you sure you want to uninstall?');

        if (!count(Currency::checkPaymentCurrencies($this->id))) {
            $this->warning = $this->trans('No currency has been set for this module.', array(), 'Modules.Wirepayment.Admin');
        }

        if (!Configuration::get('MYMODULE_NAME')) {
            $this->warning = $this->trans('No name provided', [], 'Modules.Mymodule.Admin');
        }

        $this->extra_mail_vars = array(
            '{bankwire_owner}' => Configuration::get('SELLER_AGREE_OWNER'),
            '{bankwire_details}' => nl2br(Configuration::get('SELLER_AGREE_DETAILS')),
            '{bankwire_address}' => nl2br(Configuration::get('SELLER_AGREE_ADDRESS')),
            );
    }

    public function install()
    {
        $this->registerHook('PaymentOptions');
        $this->registerHook('PaymentReturn');

        return parent::install();
    }

    public function uninstall()
    {
        return parent::uninstall();
    }

    public function hookPaymentOptions()
    {
        $paymentOptions = new PaymentOption();
        $paymentOptions->setModuleName($this->name);
        $paymentOptions->setCallToActionText($this->trans('Custom payment SELLER'));

        $paymentOptions->setAction($this->context->link->getModuleLink($this->name, 'validation', ['option' => 'offline'], true));
        $paymentOptions->setAdditionalInformation($this->fetch('module:selleragreepayment/views/templates/hook/selleragreepayment_intro.tpl'));

        return [$paymentOptions];
    }

    public function hookPaymentReturn()
    {
        return $this->fetch('module:ps_wirepayment/views/templates/hook/payment_return.tpl');
    }
}
