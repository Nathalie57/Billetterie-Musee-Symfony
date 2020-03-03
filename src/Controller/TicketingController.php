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
            
            $user = $userForm->getData();

            $em = $this->getDoctrine()->getManager();
            $em->persist($user);
            $em->flush();

            $session->set('currentUserId', $user->getId());

            return $this->redirectToRoute('visitors_designation');
        }
    
        return $this->render('ticketing/choiceForm.html.twig', [ 
            'choiceForm' => $userForm->createView(),
            ]);
    }

    /**
     * @Route("/billetterie/commande", name="visitors_designation")
     */
    public function visitorsDesignation(EntityManagerInterface $em, Request $request, User $user = null, Ticket $ticket = null, SessionInterface $session){
        
        if($session->get('currentUserId')){  
            $sessionUserId = $session->get('currentUserId');
        }

        $repository = $em->getRepository(User::class);
        $currentUser = $repository->findOneBy(['id' => $sessionUserId]);
       
        if (!$currentUser) {
            throw $this->createNotFoundException(sprintf('No Tickets for id "%s"', $sessionUserId));
        }

        $currentNumberTickets = $currentUser->getNumberTickets();

       /* if($currentNumberTickets==0){
            for ($i = 1; $i <= $currentNumberTickets; $i++) {*/
                $ticket = new Ticket(); 

                $ticketForm = $this->createForm(TicketType::class, $ticket);
                $ticketForm->handleRequest($request);
                
                $ticket->setIdOrder($currentUser);
    
                if ($ticketForm->isSubmitted() && $ticketForm->isValid()) {
                    $ticket = $ticketForm->getData();
                    $entityManager = $this->getDoctrine()->getManager();
                   
                    $entityManager->persist($ticket);
                    $entityManager->flush();      
    
                    $session->set('currentTicketId', $ticket->getId());

            return $this->redirectToRoute('paiement');
            }
       
    return $this->render('ticketing/ticketForm.html.twig', [
        'ticketForm' => $ticketForm->createView(),
        'numberTickets' => $currentNumberTickets
        ]);
    
    }

    /**
     * @Route("/billetterie/identification", name="identification")
     */ 
    /*public function clientDesignation(Request $request, User $user = null, SessionInterface $session){

      //  $user = new User();

        if($session->get('id')){
            $user = $session->get('id');
        }

        $clientForm = $this->createForm(ClientType::class, $user);
        $clientForm->handleRequest($request);

        if ($clientForm->isSubmitted() && $clientForm->isValid()) {
            $user = $clientForm->getData();
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($user);
            $entityManager->flush();

            $user = $session->get('id');
            $session->set('id', $user);
            
            return $this->redirectToRoute("/billetterie/paiement");
        }
    
        return $this->render('ticketing/clientForm.html.twig', [
            'clientForm' => $clientForm->createView()
        ]);
    }    */

    /**
     * @Route("/billetterie/paiement", name="paiement")
     */ 
    public function paiement(EntityManagerInterface $em, User $user=null, Request $request, SessionInterface $session){
        
        if($session->get('currentUserId')){  
            $currentUserId = $session->get('currentUserId');
        }

        $repository = $em->getRepository(User::class);
        $currentUser = $repository->findOneBy(['id' => $currentUserId]);

        if($session->get('currentTicketId')){  
            $currentTicketId = $session->get('currentTicketId');
        }

        $repository = $em->getRepository(Ticket::class);
        $currentTicket = $repository->findOneBy(['id' => $currentTicketId]);
  
        if (!$currentUser) {
            throw $this->createNotFoundException(sprintf('No Tickets for id "%s"', $currentUserId));
        }

        $this->orderCode = $currentUser->getOrderCode();
        $this->orderDate = $currentUser->getOrderDate();
        $this->numberTickets = $currentUser->getNumberTickets();
        $this->visitDuration = $currentUser->getVisitDuration();
        $this->clientName = $currentUser->getClientName();
        $this->address = $currentUser->getClientAddress();
        $this->clientCountry = $currentUser->getClientCountry();
        $this->clientEmail = $currentUser->getClientEmail();
        $this->visitDate = $currentUser->getVisitDate();
        $this->totalPrice = $currentUser->getTotalPrice();
        $this->name = $currentTicket->getVisitorName();
        $this->birthday = $currentTicket->getVisitorBirthday();
        $this->reduction = $currentTicket->getReduction();
        $this->country = $currentTicket->getCountry();
        
        $calculatePrice = New CalculatePrice();
        $this->price = $calculatePrice->calculatePrice($this->birthday, $this->reduction, $this->visitDuration);
       // $ticket->setPrice();
        // die(var_dump($this->price));
     

        return $this->render('ticketing/summary.html.twig', [
            'orderCode' => $this->orderCode,
            'orderDate' => $this->orderDate,
            'visitDuration' => $this->visitDuration,
            'totalPrice' => $this->totalPrice,
            'numberTickets' => $this->numberTickets,
            'clientName' => $this->clientName,
            
            'name'      => $this->name,
            'birthday'  => $this->birthday,
            'reduction' => $this->reduction,
            'country'   => $this->country,
            'price'     => $this->price
            ]);
    }
}
