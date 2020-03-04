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
    public function choice(Request $request, User $user = null, SessionInterface $session, EntityManagerInterface $em, AuthorizedDate $authorizedDate)

    {       
        $user = new User();
        
        $userForm = $this->createForm(UserType::class, $user);
        $userForm->handleRequest($request);

        $user->setOrderCode(Uuid::uuid4());
        $user->setOrderDate(date_create(), 'Y-M-d');     

        if ($userForm->isSubmitted() && $userForm->isValid()) {          
            
            $user = $userForm->getData();

            $em = $this->getDoctrine()->getManager();
            $em->persist($user);
            $em->flush();

            $session->set('currentUserId', $user->getId());

            //Création d'un tableau des jours où le nombre de billets>1000
            $rawSql = "SELECT `visit_date`, SUM(`number_tickets`) as totaltickets FROM `user` where visit_date > DATE_SUB(curdate(), INTERVAL 2 DAY) group by `visit_date` HAVING SUM(number_tickets) >= 1000";

            $stmt = $em->getConnection()->prepare($rawSql);
            $stmt->execute([]);

            $result = $stmt->fetchAll();
            $datesSoldOut = array();
            
            foreach ($result as $visit_date){
                array_push($datesSoldOut, $visit_date["visit_date"]);
            }

            //On récupère la date, le nombre de billets et la durée de la visite
            if($session->get('currentUserId')){  
                $sessionUserId = $session->get('currentUserId');
            }
            $repository = $em->getRepository(User::class);
            $currentUser = $repository->findOneBy(['id' => $sessionUserId]);
          
            $currentNumberTickets = $currentUser->getNumberTickets();
            $currentVisitDate = $currentUser->getVisitDate();
            $currentVisitDuration = $currentUser->getVisitDuration();

            $canBuyTickets = true;

            if($user->getVisitDate() != null)
            {
                //On regarde si on peut commander un billet pour ce jour et si le musée est ouvert
                $authorizedDate = new AuthorizedDate();
                $canBuyTickets = $authorizedDate->authorizedOrderDate($currentVisitDate, $currentVisitDuration);
            //    $canBuyTickets .= $authorizedDate->authorizedVisitDate($currentVisitDate);
            //    die(var_dump($canBuyTickets));
                if($canBuyTickets)
                {
                    //Requête pour obtenir la somme des billets à une date donnée
                    $rawSql = "SELECT `visit_date`, SUM(`number_tickets`) as totaltickets FROM `user` where date(visit_date) = date('".$user->getVisitDate()->format('Y-m-d')."') group by `visit_date`";
                            
                    $stmt = $em->getConnection()->prepare($rawSql);
                    $stmt->execute([]);
                
                    $result = $stmt->fetchAll();
                     //   die(var_dump($result));
                    foreach ($result as $visit_date){
                        if($user->getVisitDate()->format('Y-m-d') == $visit_date["visit_date"]) 
                        {
                            if(($visit_date["totaltickets"]+$user->getNumberTickets())>1000)
                            {
                                $canBuyTickets = false;
                                //Afficher combien on peut acheter de billets
                                $canBuyTicketsAmount = 1000 - $visit_date["totaltickets"];
                            } 
                        }
                    }
                }    
            }
            
            if(!$canBuyTickets){
                return $this->render('ticketing/orderError.html.twig', [ 
                    'visitDate' => $currentVisitDate,
                    ]);
            }

            else return $this->redirectToRoute('visitors_designation');
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
