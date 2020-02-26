<?php

namespace App\Services;

use Symfony\Component\Yaml\Yaml;
use Symfony\Component\Yaml\Exception\ParseException;
use \DateTime;

class CalculatePrice{
    
    public function __construct() {
    
        try {
            $value = Yaml::parseFile(__DIR__.'/price.yaml');
        } catch (ParseException $exception) {
            printf('Unable to parse the YAML string: %s', $exception->getMessage());
        }

        $this->ageBaby            = $value['bebe']['age_max'];
        $this->priceBaby          = $value['bebe']['prix'];

        $this->ageChild           = $value['enfant']['age_max'];
        $this->fullPriceChild     = $value['enfant']['prix'];
        $this->halfPriceChild     = $value['enfant']['prix_demi_journee'];

        $this->ageNormal          = $value['normal']['age_max'];
        $this->fullPriceNormal    = $value['normal']['prix'];
        $this->halfPriceNormal    = $value['normal']['prix_demi_journee'];

        $this->fullPriceSenior    = $value['senior']['prix'];
        $this->halfPriceSenior    = $value['senior']['prix_demi_journee'];

        $this->fullPriceReduction = $value['tarif_reduit']['prix'];   
        $this->halfPriceReduction = $value['tarif_reduit']['prix_demi_journee'];      
    }

    public function calculatePrice($birthdayVisitor, $reduction, $visitDuration){

        $dateDay = new \Datetime('today'); 
        if($birthdayVisitor < $dateDay) $age = $birthdayVisitor->diff($dateDay)->y;
        else return 'false';
        
        if($reduction && $visitDuration === 1) return $this->fullPriceReduction;
        if($reduction && $visitDuration === .5) return $this->halfPriceReduction;
         
        if($age <= $this->ageBaby)                             return $this->priceBaby;
        if($age <= $this->ageChild && $visitDuration === 1)    return $this->fullPriceChild;
        if($age <= $this->ageChild && $visitDuration === .5)   return $this->halfPriceChild;
        if($age <= $this->ageNormal && $visitDuration === 1)   return $this->fullPriceNormal;
        if($age <= $this->ageNormal && $visitDuration === 0.5) return $this->halfPriceNormal;
        if($visitDuration === .5)                               return $this->halfPriceSenior;
        if($visitDuration === 1)                             return $this->fullPriceSenior;
    }
}