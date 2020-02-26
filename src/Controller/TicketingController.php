<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use App\Services\CalculatePrice;
use App\Services\AuthorizedDate;

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
     * @Route("/billetterie", name="order")
     */
    public function choice(Request $request, User $user = null, SessionInterface $session, EntityManagerInterface $em)

    {
        $user = new User();
        
        $form = $this->createForm(OrderType::class, $order);
        $form->handleRequest($request);

       // $order->setOrderCode();
    
        /*$totalTicketsMax = 1000;
        $rawSql = "SELECT `visitDate`, SUM(`ticketsNumber`) as totalTickets FROM `order` WHERE visitDate >= DATE_SUB(curdate(), INTERVAL 0 DAY)";
    
        $stmt = $em->getConnection()->prepare($rawSql);
        $stmt->execute([]);
    
        $totalTickets = $stmt->fetchAll();
        return $totalTickets;*/
    
       /* if($order->getVisitDate != null){
            $okDateOrder = new AuthorizedDate($authorizedOrderDate, $authorizedVisitDate, $totalTickets)*/
    
        
      // die(var_dump($form->isSubmitted()));

        if ($form->isSubmitted() && $form->isValid()) {
         //  $entityManager = $this->getDoctrine()->getManager();
            $data = $form->getData();   
            die(var_dump($order));
         /*   $order->setVisitDate($data->visitDate);    
            $entityManager->persist($data);
            $entityManager->flush();

            $order = $session->get('orderCode');
            $session->set('orderCode', $order);*/
            
            return $this->redirectToRoute("/billetterie/commande");
        }
    
        return $this->render('order/orderForm.html.twig', array('form' => $form->createView()));
    }
}
