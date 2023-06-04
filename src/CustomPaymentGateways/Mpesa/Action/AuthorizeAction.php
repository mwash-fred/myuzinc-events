<?php
namespace App\CustomPaymentGateways\Mpesa\Action;

use Payum\Core\Action\ActionInterface;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\GatewayAwareTrait;
use Payum\Core\Request\Authorize;
use Payum\Core\Exception\RequestNotSupportedException;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Loader\Configurator\Traits\RouteTrait;

class AuthorizeAction implements ActionInterface
{
    use GatewayAwareTrait;
    use RouteTrait;

    protected $api;

    /**
     * {@inheritDoc}
     *
     * @param Authorize $request
     */
    public function execute($request)
    {
        if (!$request instanceof Authorize) {
            throw RequestNotSupportedException::createActionNotSupported($this, $request);
        }

        $model = ArrayObject::ensureArrayObject($request->getModel());
        $auth = json_decode($this->api->getAuthToken());

        if ( !is_null($auth) && array_key_exists("access_token", $auth)) {
            $model["auth"] = $auth;
        }
        else{
            $model["status"] = "failure";
            $model["message"] = "Unauthorized";
        }
    }

    /**
     * {@inheritDoc}
     */
    public function supports($request)
    {
        return
            $request instanceof Authorize &&
            $request->getModel() instanceof \ArrayAccess
        ;
    }

    public function setApi($api)
    {
        $this->api = $api;
    }
}