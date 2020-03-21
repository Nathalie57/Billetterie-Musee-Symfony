<?php

namespace App\Services;

//use Symfony\Component\Yaml\Yaml;
//use Symfony\Component\Yaml\Exception\ParseException;
use App\Entity\User;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManagerInterface;

class PaiementManager{
    
    private $stripeSecretKey;

    public function __construct()
    {
        $this->stripeSecretKey = 'sk_test_OVSeLOoumUW63SWENG6C0BtL00V3KiTZ1C';
    }

    public function checkoutAction(User $user, Request $request)
    {
        $totalPrice = $user->getTotalPrice();
        $token = $user->getStripeToken();
       // die(var_dump($token));
        \Stripe\Stripe::setApiKey('sk_test_OVSeLOoumUW63SWENG6C0BtL00V3KiTZ1C');

        try {
            \Stripe\Charge::create([
                'amount'      => $totalPrice * 100,
                'currency'    => 'eur',
                'source'      => $token,
                'description' => $user->getOrderCode(), ]);

            return true;
        } catch (\Stripe\Error\Card $e) {
            return false;
        }
    }
}