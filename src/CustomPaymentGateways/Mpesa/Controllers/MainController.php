<?php

namespace App\CustomPaymentGateways\Mpesa\Controllers;

use App\CustomPaymentGateways\Mpesa\Action\StatusAction;
use App\Entity\Order;
use App\Entity\Payment;
use App\Service\AppServices;
use FOS\RestBundle\Controller\ControllerTrait;
use Payum\Core\Bridge\Symfony\Security\TokenFactory;
use Payum\Core\Request\GetHumanStatus;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use  Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;
use Payum\Core\Request\GetBinaryStatusRequest;

/**
 * Summary of MainController
 */
class MainController extends Controller{
    use ControllerTrait;

    /**
     */
    public function __construct() {
    }

    /**
     * @Route("/mpesa", name="mpesa_index")
     */
     public function index():Response{
        return $this->render('CustomPaymentGateways/Mpesa/index.html.twig');
     }

     /**
      * @Route("/mpesa/pay", name="mpesa_pay")
      */
      public function pay(Request $request, AppServices $services, TranslatorInterface $translator):Response{
        $em = $this->getDoctrine()->getManager();
        
        print_r("Testing");
        return $this->render('CustomPaymentGateways/Mpesa/index.html.twig');
      }

    /**
     * @Route("/mpesa/ipn", name="mpesa_ipn")
     */
    public function ipn(Request $requests, AppServices $services, TranslatorInterface $translator):Response{

        $requestBody = json_decode($requests->getContent(), true);
        if( array_key_exists("Body", $requestBody) && array_key_exists("stkCallback", $requestBody["Body"])){
            $stkCallbackData = $requestBody["Body"]["stkCallback"];            
            $em = $this->getDoctrine()->getManager();
            
            $payments = $em->getRepository(Payment::class)->createQueryBuilder('p')
            // ->where("p.id = :id")
            // ->setParameter("id", 240)
            ->where("p.details LIKE :detail")
            ->setParameter("detail", '%'.$stkCallbackData["CheckoutRequestID"].'%')
            ->getQuery()
            ->getResult();

            if(!empty($payments)){
                $payment = $payments[0];

                $paymentDetails = $payment->getDetails();
                $paymentDetails["mpesaPaymentStatus"] = $requestBody;
                $payment->setDetails($paymentDetails);
                
                $order = $payment->getOrder();

#check if payment was successfull and update
                if($stkCallbackData["ResultCode"] == 0){
                    #Payment complete
                    $order->setStatus(1);
                }
                else{
                    #Payment failed
                    $order->setStatus(-2);
                }
                $order->setNote($translator->trans($stkCallbackData["ResultDesc"]));
                $em->flush();
                
                return new Response("OK");
            }

            return new JsonResponse(["error"=>"Invalid Transaction"]);
        }

        return new JsonResponse([
            "status"=>"failure"
        ]);
    }

    /**
     * @Route("/mpesa/done", name="mpesa_done")
     */
    public function done(Request $requests):Response{
        $this->addFlash("success", "The payment has benn done");
        return new JsonResponse([
            "done"=>"It's Done"
        ]);
    }

    /**
     * @Route("/mpesa/failure", name="mpesa_failure")
     */
    public function failure(Request $request):Response{
        $this->addFlash("error", "Mpesa payment failed");
        $this->redirect();
    }
}

?>