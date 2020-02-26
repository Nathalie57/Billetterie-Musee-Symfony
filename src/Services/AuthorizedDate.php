<?php

namespace App\Services;

use Symfony\Component\Yaml\Yaml;
use App\Repository\UserRepository;
use \DateTime;

class AuthorizedDate{
    
    private $holidays;
    private $totalTickets;

    public function __construct() {
    
        try {
            $value = Yaml::parseFile(__DIR__.'/openingHours.yaml');
        } catch (ParseException $exception) {
            printf('Unable to parse the YAML string: %s', $exception->getMessage());
        }

        $this->authorizedOrderDate = $value['jours_reservables'];

        $this->openedDays = $value['jours_ouverts'];
        /*$this->monday = $value['jours_ouverts']['lundi'];
        $this->tuesday = $value['jours_ouverts']['mardi'];
        $this->wednesday = $value['jours_ouverts']['mercredi'];
        $this->thursday = $value['jours_ouverts']['jeudi'];
        $this->friday = $value['jours_ouverts']['vendredi'];
        $this->saturday = $value['jours_ouverts']['samedi'];
        $this->sunday = $value['jours_ouverts']['dimanche'];*/
        
        $this->closedDays = $value['jours_fermes'];

        $this->setHolidays = $value['vacances'];

        $this->openingHour = $value['horaires']['ouverture'];
        $this->afternoon = $value['horaires']['apres_midi'];
        $this->closingHour = $value['horaires']['fermeture'];

        $this->totalTicketsMax = $value['total_tickets_max'];
    }

    public function calculateHolidays($date){
        if ($date === null)
	  	{
	    	$date = time();
	  	}

	 	$date = strtotime(date('m/d/Y',$date));

	 	$year = date('Y',$date);

		$easterDate  = easter_date($year);
		$easterDay   = date('j', $easterDate);
		$easterMonth = date('n', $easterDate);
		$easterYear   = date('Y', $easterDate);

		$holidays = array(
	    
	    mktime(0, 0, 0, $easterMonth, $easterDay + 1,  $easterYear), //lundi de Pâques
	    mktime(0, 0, 0, $easterMonth, $easterDay + 39, $easterYear), //ascension
	    mktime(0, 0, 0, $easterMonth, $easterDay + 50, $easterYear), //lundi de Pentecôte
		);
        
        return $holidays;
	} 
    
    public function authorizedOrderDate($visitDate, $orderDate, $visitDuration){

        $orderDate = new Datetime();
        $visitDate = new Datetime();

        //on regarde si le jour de visite est autorisé à la réservation
        if($visitDate = $this->holidays || $visitDate = $this->setHolidays || $visitDate = $this->authorizedOrderDate = false) return false;
            
        if($visitDate>$orderDate) return true;

        if($visitDate===$orderDate){
            //on récupère l'heure
            date_default_timezone_set('Europe/Paris');
            $hour = date('h:i:s');

            if($hour>=$value['horaires']['fermeture']) return 'false';
       
            if($hour>=$value['horaires']['apres_midi'] && $this->visitDuration = 1) return 'false';
                                               
            else return 'true';
        }            
    }  

    public function authorizedVisitDate($visitDate){

        $visitDate = new Datetime();

        if($visitDate = $this->openedDays = true || $visitDate != $this->closedDays) return true;
        else return false;
    }
}
