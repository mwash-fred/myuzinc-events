<?php

namespace App\CustomPaymentGateways\Mpesa\Forms;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MpesaCheckoutConfigType extends AbstractType{

    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder
                ->add('phonenumber', TextType::class, [
            'required'=>true,
            'purify_html'=>true,
            'label'=>'Mpesa Phone Number',
            'attr' => ['class'=>'mpesa_checkout_fields']
        ]);
    }

    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setDefaults([
                // Configure your form options here
        ]);
    }

}

?>