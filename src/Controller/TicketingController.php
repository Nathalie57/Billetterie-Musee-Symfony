<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpFoundation\Session\Session;
use App\Services\CalculatePrice;
use App\Services\AuthorizedDate;
use App\Entity\User;
use App\Entity\Ticket;
use App\Form\UserType;
use App\Form\TicketType;
use App\Form\ClientType;
use Ramsey\Uuid\Uuid;

class TicketingController extends AbstractController
{
    /**
     * @Route("/", name="home")
     */
    public function index()
    {
        return $this->render('ticketing/index.html.twig', [
            'title' => '',
        ]);
    }

    /**
     * @Route("/horaires", name="opening_hours")
     */
    public function showOpeningHours(AuthorizedDate $authorizedDate){
        return $this->render('ticketing/showOpeningHours.html.twig', [
            'title'     => 'Ouverture',
            'ouverture' => $authorizedDate->openingHour,
            'fermeture' => $authorizedDate->closingHour
        ]);
    }

    /**
     * @Route("/tarifs", name="price")
     */
    public function showPrice(CalculatePrice $calculatePrice){
        return $this->render('ticketing/showPrice.html.twig', [
            'title' => 'Tarifs',
            'fullPriceChild'     => $calculatePrice->fullPriceChild,
            'halfPriceChild'     => $calculatePrice->halfPriceChild,
            'fullPriceNormal'    => $calculatePrice->fullPriceNormal,
            'halfPriceNormal'    => $calculatePrice->halfPriceNormal,
            'fullPriceSenior'    => $calculatePrice->fullPriceSenior,
            'halfPriceSenior'    => $calculatePrice->halfPriceSenior,
            'fullPriceReduction' => $calculatePrice->fullPriceReduction,
            'halfPriceReduction' => $calculatePrice->halfPriceReduction
        ]);
    }

        /**
     * @Route("/billetterie", name="choice")
     */
    public function choice(Request $request, User $user = null, SessionInterface $session, EntityManagerInterface $em)

    {
       // $session = new Session();
        //$session->start();
        
        $user = new User();
        
        $userForm = $this->createForm(UserType::class, $user);
        $userForm->handleRequest($request);

        $user->setOrderCode(Uuid::uuid4());
        $user->setOrderDate(date_create(), 'Y-M-d');
    
        /*$totalTicketsMax = 1000;
        $rawSql = "SELECT `visitDate`, SUM(`ticketsNumber`) as totalTickets FROM `order` WHERE visitDate >= DATE_SUB(curdate(), INTERVAL 0 DAY)";
    
        $stmt = $em->getConnection()->prepare($rawSql);
        $stmt->execute([]);
    
        $totalTickets = $stmt->fetchAll();
        return $totalTickets;*/
    
       /* if($user->getVisitDate != null){
            $okDateOrder = new AuthorizedDate($authorizedOrderDate, $authorizedVisitDate, $totalTickets)*/
    
        
      // die(var_dump($form->isSubmitted()));

        if ($userForm->isSubmitted() && $userForm->isValid()) {
         
            
            $session->set('orderCode', $user->getOrderCode());
            
            $user = $userForm->getData();

            $em = $this->getDoctrine()->getManager();
            $em->persist($user);
            $em->flush();
            
            return $this->redirectToRoute('visitors_designation');
        }
    
        return $this->render('ticketing/choiceForm.html.twig', array('choiceForm' => $userForm->createView()));
    }

    /**
     * @Route("/billetterie/commande", name="visitors_designation")
     */
    public function visitorsDesignation(Request $request, Ticket $ticket = null, SessionInterface $session){
               
        $ticket = new Ticket();
        
        if($session->get('orderCode')){
            $ticket = $session->get('orderCode');
        }

        $ticketForm = $this->createForm(TicketType::class, $ticket);
        $ticketForm->handleRequest($request);
    
        if ($ticketForm->isSubmitted() && $ticketForm->isValid()) {
            $ticket = $ticketForm->getData();
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($ticket);
            $entityManager->flush();

            $session->set('id', $ticket);
            
            return $this->redirectToRoute('identification');
        }
    
        return $this->render('ticketing/ticketForm.html.twig', array('ticketForm' => $ticketForm->createView()));
    }

    /**
     * @Route("/billetterie/identification", name="identification")
     */ 
    public function clientDesignation(Request $request, User $user = null, SessionInterface $session){

        $user = new User();

        if($session->get('id')){
            $user = $session->get('id');
        }

        $form = $this->createForm(ClientType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user = $form->getData();
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($user);
            $entityManager->flush();

            $user = $session->get('id');
            $session->set('id', $user);
            
          //  return $this->redirectToRoute("/billetterie/paiement");
        }
    
        return $this->render('ticketing/clientForm.html.twig', array('form' => $form->createView()));
    }    
}
