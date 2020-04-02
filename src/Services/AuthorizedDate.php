<?php

namespace App\Services;

use Symfony\Component\Yaml\Yaml;
use \DateTime;

class AuthorizedDate{
    
    private $holidays;

    public function __construct() {
    
        try {
            $value = Yaml::parseFile(__DIR__.'/openingHours.yaml');
        } catch (ParseException $exception) {
            printf('Unable to parse the YAML string: %s', $exception->getMessage());
        }

        $this->closedDays = $value['jours_fermes'];
        $this->offOrderHolidays = $value['jours_feries'];
                
        $this->openingHour = $value['horaires']['ouverture'];
        $this->afternoon   = $value['horaires']['apres_midi'];
        $this->closingHour = $value['horaires']['fermeture'];
    }

    private function calculateOffDays($currentDate)
    {
        $currentDate = new \DateTime('today');
        
        $currentYear  = $currentDate->format("Y");   
        $currentDay   = $currentDate->format("j");  
        $currentMonth = $currentDate->format("m");  
        
        $easterDate  = easter_date($currentYear);
        $easterDay   = date('j', $easterDate);
        $easterMonth = date('n', $easterDate);
        $easterYear  = date('Y', $easterDate);

        $calculatedOffDays = array(
            \DateTime::createFromFormat('m-j-Y', $easterMonth.'-'.($easterDay + 1+1).'-'.$currentYear),
            \DateTime::createFromFormat('m-j-Y', $easterMonth.'-'.($easterDay + 39+1).'-'.$currentYear),
            \DateTime::createFromFormat('m-j-Y', $easterMonth.'-'.($easterDay + 50+1).'-'.$currentYear),
        );

        $this->offDays = $calculatedOffDays;       
    }
    
    public function authorizedOrderDate($visitDate, $visitDuration){

        $orderDate = new \DateTime('today');
        $orderDate =  \DateTime::createFromFormat('Y-m-d', date_format($orderDate, 'Y-m-d'));

        //on regarde si le jour de visite est autorisé à la réservation
        $day = $visitDate->format("w");

        if ($this->closedDays[$day]) return false;
        $refDate = $visitDate->format("j")."/".$visitDate->format("m");
        
        foreach ($this->offOrderHolidays as $value){
            if($value === $refDate) return false;
        }

        $this->calculateOffDays($visitDate);
        foreach ($this->offDays as $value){
            if($value->format("j") == $visitDate->format("j") && $value->format("m") == $visitDate->format("m")) return false;
        }
       
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
}
