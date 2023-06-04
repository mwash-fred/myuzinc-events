<?php
namespace App\CustomPaymentGateways\Mpesa\Action;

use App\CustomPaymentGateways\Mpesa\Action\Api\BaseApiAwareAction;
use Doctrine\ORM\Mapping\Entity;
use FOS\RestBundle\Controller\ControllerTrait;
use Payum\Core\Action\ActionInterface;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\GatewayAwareTrait;
use Payum\Core\Model\PaymentInterface;
use Payum\Core\Request\Authorize;
use Payum\Core\Request\Capture;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Request\GetHumanStatus;
use Payum\Core\Request\Notify;
use Payum\Core\Tests\Request\GetStatusInterfaceTest;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\RequestStack;

class CaptureAction extends BaseApiAwareAction implements ActionInterface
{
    use GatewayAwareTrait;
    use ControllerTrait;

    /**
     * {@inheritDoc}
     *
     * @param Capture $request
     */
    public function execute($request)
    {
        // RequestNotSupportedException::assertSupports($this, $request);
        if (!$request instanceof Capture) {
            throw RequestNotSupportedException::createActionNotSupported($this, $request);
        }

        $model = ArrayObject::ensureArrayObject($request->getModel());
        $payment = $request->getFirstModel();
        
        $auth = json_decode($this->api->getAuthToken());

        if(is_null($auth) || !array_key_exists("access_token", $auth)){
            $model["status"] = GetHumanStatus::STATUS_FAILED;
            $status = new GetHumanStatus($model);
            $status->markFailed();
        }
        else if( !is_null($auth) && array_key_exists("access_token", $auth) ){
            $paymentResponse = json_decode($this->api->stkPushCharge([
                "amount"=>$payment->getTotalAmount(),
                "MSISDN"=>"254".substr($payment->getPhonenumber(), -9),
                "ref" => "MYUZINC-EVENT-".$payment->getId(),
                "description" => $payment->getDescription()
            ], $auth));

            if(is_null($paymentResponse) || array_key_exists("errorCode", $paymentResponse)){
                $model["status"] = GetHumanStatus::STATUS_FAILED;
                $model["message"] = $paymentResponse["errorMessage"];
                $request->setModel($model);
                $status = new GetHumanStatus($model);
                $status->markFailed();
            }
            else{
                $model["status"] = GetHumanStatus::STATUS_PENDING;
                $model["message"] = "Please check your phone to authorize payment";
                $model["mpesaResponse"] = $paymentResponse;
                $request->setModel($model);
                $status = new GetHumanStatus($model);
                $status->markPending();
            }
        }        
    }

    /**
     * {@inheritDoc}
     */
    public function supports($request)
    {
        return
            $request instanceof Capture &&
            $request->getModel() instanceof \ArrayAccess
        ;
    }
}
