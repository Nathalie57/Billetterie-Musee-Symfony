<?php

namespace App\Services;

use Swift_Mailer;

class SendEmail{

public function sendConfirmation($name, \Swift_Mailer $mailer){
    $message = (new \Swift_Message('Hello Email'))
        ->setFrom('send@example.com')
        ->setTo('recipient@example.com')
        ->setBody(
            $this->renderView(
                'emails/registration.html.twig',
                ['name' => $name]
            ),
            'text/html'
        );

    $mailer->send($message);

    return $this->render();
    }
}