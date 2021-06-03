<?php

namespace App\PassportCheckService\Form\Type;

use App\PassportCheckService\Model\CurlResponseData;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class CurlResponseDataType.
 */
class CurlResponseDataType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('code', null, [
                'required' => true,
            ])
            ->add('inn', null, [
                'required' => false,
            ])
            ->add('captchaRequired', CheckboxType::class, [
                'required' => true,
            ])
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class'         => CurlResponseData::class,
            'csrf_protection'    => false,
            'allow_extra_fields' => true,
        ]);
    }
}
