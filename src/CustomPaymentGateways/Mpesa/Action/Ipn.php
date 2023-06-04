<?php
namespace App\CustomPaymentGateways\Mpesa\Action;

use App\CustomPaymentGateways\Mpesa\Action\Api\BaseApiAwareAction;
use Payum\Core\Action\ActionInterface;
use Payum\Core\Exception\RequestNotSupportedException;

class IpnAction extends BaseApiAwareAction implements ActionInterface{

    public function execute($request){
        // RequestNotSupportedException::assertSupports($this, $request);
        if (!$request instanceof Capture) {
            throw RequestNotSupportedException::createActionNotSupported($this, $request);
        }
    }

    public function supports($request){
        return 
        $request instanceof Capture &&
        $request->getModel() instanceof \ArrayAccess;
    }

}


?>