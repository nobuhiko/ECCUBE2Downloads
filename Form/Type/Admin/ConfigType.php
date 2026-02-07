<?php

namespace Plugin\ECCUBE2Downloads\Form\Type\Admin;

use Plugin\ECCUBE2Downloads\Entity\Config;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

class ConfigType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('downloadable_days', IntegerType::class, [
                'label' => 'ダウンロード可能日数',
                'required' => true,
                'constraints' => [
                    new Assert\NotBlank(),
                    new Assert\GreaterThanOrEqual(['value' => 0]),
                ],
                'attr' => [
                    'min' => 0,
                ],
            ])
            ->add('downloadable_days_unlimited', CheckboxType::class, [
                'label' => 'ダウンロード期限無制限',
                'required' => false,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Config::class,
        ]);
    }
}
