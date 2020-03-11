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
use App\Form\TicketCollectionType;
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
            'title' => 'Billetterie du Louvre',
            'paragraph' => 'Achetez vos billets en toute simplicité !'
        ]);
    }

    /**
     * @Route("/horaires", name="opening_hours")
     */
    public function showOpeningHours(AuthorizedDate $authorizedDate){
        return $this->render('ticketing/showOpeningHours.html.twig', [
            'title'     => 'Horaires et jours d\'ouverture',
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

            //Création d'un tableau des jours où le nombre de billets>1000
            $rawSql = "SELECT `visit_date`, SUM(`number_tickets`) as totaltickets FROM `user` where visit_date > DATE_SUB(curdate(), INTERVAL 2 DAY) group by `visit_date` HAVING SUM(number_tickets) >= 1000";

            $stmt = $em->getConnection()->prepare($rawSql);
            $stmt->execute([]);
 
            $result = $stmt->fetchAll();
            $datesSoldOut = array();
             
            foreach ($result as $visit_date){
                array_push($datesSoldOut, $visit_date["visit_date"]);
            }

            $canBuyTickets = true;

            if($user->getVisitDate() != null)
            {
                //On regarde si on peut commander un billet pour ce jour et si le musée est ouvert
                $authorizedDate = new AuthorizedDate();
             //   $canBuyTickets = $authorizedDate->authorizedOrderDate($user->getVisitDate(), $user->getVisitDuration());
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

            $em = $this->getDoctrine()->getManager();
            $em->persist($user);
            $em->flush();

            $session->set('currentUserId', $user->getId());

           

            //On récupère la date, le nombre de billets et la durée de la visite
            if($session->get('currentUserId')){  
                $sessionUserId = $session->get('currentUserId');
            }
            $repository = $em->getRepository(User::class);
            $currentUser = $repository->findOneBy(['id' => $sessionUserId]);
          
        //    $currentNumberTickets = $currentUser->getNumberTickets();
            $currentVisitDate = $currentUser->getVisitDate();
        //    $currentVisitDuration = $currentUser->getVisitDuration();

           
            
            if(!$canBuyTickets){
                return $this->render('ticketing/orderError.html.twig', [ 
                    'visitDate' => $currentVisitDate,
                    ]);
            }

            else return $this->redirectToRoute('visitors_designation');
        }
    
        return $this->render('ticketing/choiceForm.html.twig', [ 
            'choiceForm' => $userForm->createView(),
            'title' => 'Choix des billets'
            ]);
    
    }
    /**
     * @Route("/billetterie/commande", name="visitors_designation")
     */
    public function visitorsDesignation(EntityManagerInterface $em, Request $request, User $user = null, Ticket $ticket = null, CalculatePrice $calculatePrice, SessionInterface $session){
        
        if($session->get('currentUserId')){  
            $sessionUserId = $session->get('currentUserId');
        }

        $repository = $em->getRepository(User::class);
        $currentUser = $repository->findOneBy(['id' => $sessionUserId]);
       
        if (!$currentUser) {
            throw $this->createNotFoundException(sprintf('No Tickets for id "%s"', $sessionUserId));
        }

        $currentNumberTickets = $currentUser->getNumberTickets();
        

        for ($i = 1; $i <= $currentNumberTickets; $i++) {
            $ticket = new Ticket();
         
            $ticketForm = $this->createForm(UserType::class, $user);
            $ticketForm->handleRequest($request);

            $ticket->getId(); 
            $ticket->setIdOrder($currentUser);

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($ticket);
            $entityManager->flush();
        }
        
        $currentIdOrder = $currentUser->getId();
    
        $ticketArray= $this->getDoctrine()->getRepository(Ticket::class)->findBy(['idOrder'=>$currentIdOrder]);
		$formCollection = $this->createForm(TicketCollectionType ::class, ['TicketCollection' => $ticketArray]);
 
        $formCollection->handleRequest($request);
       
		if ($formCollection->isSubmitted() && $formCollection->isValid()) {
            $submittedTicketArray= $formCollection->getData();
          //  die(var_dump($submittedTicketArray));
         /*   $data = array(
                //"birthday" => $submittedTicketArray['visitorBirthday'],
                "reduction" => $submittedTicketArray->getReduction(),
                "visitDuration" => $submittedTicketArray->getVisitDuration()
            );*/

         /* foreach ($submittedTicketArray as $currentTicket) {
                               
                $calculatePrice = New CalculatePrice();
                $this->price = $calculatePrice->calculatePrice($this->visitorBirthday, $this->reduction, $this->visitDuration);
            }*/

            $em = $this->getDoctrine()->getManager();
        
            foreach($em as $tickets){
                $em->persist($tickets);
            }

            $em->flush();

            $session->set('currentIdOrder', $ticket->getIdOrder());           
die(var_dump($ticket->getIdOrder()));
            //On récupère l'idOrder des tickets
            if($session->get('currentIdOrder')){  
                $sessionIdOrder = $session->get('currentIdOrder');
            }
            $repository = $em->getRepository(Ticket::class);
            $currentOrder = $repository->findOneBy(['idOrder' => $sessionIdOrder]);

            return $this->redirectToRoute('paiement');
		}
 
		return $this->render('ticketing/ticketForm.html.twig', [
            'form_collection' => $formCollection->createView(),
            'title' => 'Détail de chaque visiteur',
            'numberTickets' => $currentUser->getNumberTickets()
		]);    
    }

    /**
     * @Route("/billetterie/paiement", name="paiement")
     */ 
    public function paiement(EntityManagerInterface $em, Request $request, SessionInterface $session){
        
        if($session->get('currentUserId')){  
            $currentUserId = $session->get('currentUserId');
        }

      //  $repository = $em->getRepository(User::class);
      //  $currentUser = $repository->findOneBy(['id' => $currentUserId]);
        
        if($session->get('currentIdOrder')){  
            $currentIdOrder = $session->get('currentIdOrder');
        }

        $repository = $em->getRepository(Ticket::class);
        $currentOrder = $repository->findOneBy(['idOrder' => $currentIdOrder]);
  
        if (!$currentOrder) {
            throw $this->createNotFoundException(sprintf('No Tickets for id "%s"', $currentIdOrder));
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
        $this->name = $currentTicket->getVisitorName();
        $this->birthday = $currentTicket->getVisitorBirthday();
        $this->reduction = $currentTicket->getReduction();
        $this->country = $currentTicket->getCountry();
        die(var_dump($this->country));
       // if($this->numberTickets==0){
       //     for ($i = 1; $i <= $this->numberTickets; $i++) {
        //$calculatePrice = New CalculatePrice();
        //$this->price = $calculatePrice->calculatePrice($this->birthday, $this->reduction, $this->visitDuration);
       // $ticket->setPrice();
        // die(var_dump($this->price));
         //   }
        //}

        $this->totalPrice = $currentUser->getTotalPrice();

        return $this->render('ticketing/summary.html.twig', [
            'orderCode' => $this->orderCode,
            'orderDate' => $this->orderDate,
            'visitDate' => $this->visitDate,
            'visitDuration' => $this->visitDuration,
            'totalPrice' => $this->totalPrice,
            'numberTickets' => $this->numberTickets,
            'clientName' => $this->clientName,
            
            'name'      => $this->name,
            'birthday'  => $this->birthday,
            'reduction' => $this->reduction,
            'country'   => $this->country,
          //  'price'     => $this->price
            ]);
    }
}
