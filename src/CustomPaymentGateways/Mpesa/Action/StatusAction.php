<?php
namespace App\CustomPaymentGateways\Mpesa\Action;

use Payum\Core\Action\ActionInterface;
use Payum\Core\Request\GetHumanStatus;
use Payum\Core\Request\GetStatusInterface;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\RequestNotSupportedException;

class StatusAction implements ActionInterface
{
    /**
     * {@inheritDoc}
     *
     * @param GetStatusInterface $request
     */
    public function execute($request)
    {
        if (!$request instanceof GetStatusInterface) {
            throw RequestNotSupportedException::createActionNotSupported($this, $request);
        }

        $model = ArrayObject::ensureArrayObject($request->getModel());
        if($model["status"] == GetHumanStatus::STATUS_FAILED){
            $request->markFailed();
        }
        else if($model["status"] == GetHumanStatus::STATUS_CAPTURED){
            $request->markCaptured();
        }
    }

    /**
     * {@inheritDoc}
     */
    public function supports($request)
    {
        return
            $request instanceof GetStatusInterface &&
            $request->getModel() instanceof \ArrayAccess
        ;
    }
}
