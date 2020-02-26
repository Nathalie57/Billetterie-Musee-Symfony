<?php

namespace App\Services;

class SendEmail{

public function sendConfirmation($name, \Swift_Mailer $mailer){
    $message = (new \Swift_Message('Hello Email'))
        ->setFrom('send@example.com')
        ->setTo('recipient@example.com')
        ->setBody(
            $this->renderView(
                // templates/emails/registration.html.twig
                'emails/registration.html.twig',
                ['name' => $name]
            ),
            'text/html'
        );

    $mailer->send($message);

    return $this->render();
    }
}