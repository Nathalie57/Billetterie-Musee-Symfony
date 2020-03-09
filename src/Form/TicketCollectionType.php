<?php

namespace App\Form;

use App\Form\TicketType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;


class TicketCollectionType extends AbstractType {

	public function buildForm(FormBuilderInterface $builder, array $options) {
		$builder
			->add('TicketCollection', CollectionType::class, [
				'entry_type' => TicketType ::class,
				'entry_options' => ['label' => false],
			])
			->add('Submit', SubmitType::class);
	}
 
	public function configureOptions(OptionsResolver $resolver) {
		$resolver->setDefaults([
			'data_class' => null,
		]);
	}
}
