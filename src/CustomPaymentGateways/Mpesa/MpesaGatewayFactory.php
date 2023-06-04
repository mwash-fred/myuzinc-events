<?php
namespace App\CustomPaymentGateways\Mpesa;

use App\CustomPaymentGateways\Mpesa\Action\AuthorizeAction;
use App\CustomPaymentGateways\Mpesa\Action\CancelAction;
use App\CustomPaymentGateways\Mpesa\Action\CaptureAction;
use App\CustomPaymentGateways\Mpesa\Action\ConvertPaymentAction;
use App\CustomPaymentGateways\Mpesa\Action\IpnAction;
use App\CustomPaymentGateways\Mpesa\Action\NotifyAction;
use App\CustomPaymentGateways\Mpesa\Action\RefundAction;
use App\CustomPaymentGateways\Mpesa\Action\StatusAction;
use App\CustomPaymentGateways\Mpesa\Api;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\GatewayFactory;

class MpesaGatewayFactory extends GatewayFactory
{
    /**
     * {@inheritDoc}
     */
    protected function populateConfig(ArrayObject $config)
    {
        $config->defaults([
            'payum.factory_name' => 'mpesa',
            'payum.factory_title' => 'mpesa',
            'payum.action.capture' => new CaptureAction(),
            'payum.action.authorize' => new AuthorizeAction(),
            'payum.action.refund' => new RefundAction(),
            'payum.action.cancel' => new CancelAction(),
            'payum.action.notify' => new NotifyAction(),
            'payum.action.status' => new StatusAction(),
            'payum.action.convert_payment' => new ConvertPaymentAction(),
            'payum.action.ipn' => new IpnAction()
        ]);

        if (false == $config['payum.api']) {
            $config['payum.default_options'] = array(
                'sandbox' => true,
            );
            $config->defaults($config['payum.default_options']);
            $config['payum.required_options'] = [];

            $config['payum.api'] = function (ArrayObject $config) {
                $config->validateNotEmpty($config['payum.required_options']);

                return new Api((array) $config, $config['payum.http_client'], $config['httplug.message_factory']);
            };
        }
    }

    
}
