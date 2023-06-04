<?php
namespace App\CustomPaymentGateways\Mpesa\Action;

use Payum\Core\Action\ActionInterface;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\GatewayAwareTrait;
use Payum\Core\Request\Notify;

class NotifyAction implements ActionInterface
{
    use GatewayAwareTrait;

    /**
     * {@inheritDoc}
     *
     * @param Notify $request
     */
    public function execute($request)
    {
        // RequestNotSupportedException::assertSupports($this, $request);
        if (!$request instanceof Notify) {
            throw RequestNotSupportedException::createActionNotSupported($this, $request);
        }

        $model = ArrayObject::ensureArrayObject($request->getModel());
        $model["notified"] = true;
        // $this
        // print_r($model);

        // throw new \LogicException('Not implemented');
    }

    /**
     * {@inheritDoc}
     */
    public function supports($request)
    {
        return
            $request instanceof Notify &&
            $request->getModel() instanceof \ArrayAccess
        ;
    }
}
