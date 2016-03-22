<?php
/*
* This file is part of EC-CUBE
*
* Copyright(c) 2000-2015 LOCKON CO.,LTD. All Rights Reserved.
* http://www.lockon.co.jp/
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/
namespace Plugin\Point\Form\Type;

use Plugin\Point\Entity\PointInfoAddStatus;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class PointInfoAddStatusType
 * @package Plugin\Point\Form\Type
 * @param Eccube\Applicaion
 */
class PointInfoAddStatusType extends AbstractType
{
    private $app;

    public function __construct(\Eccube\Application $app)
    {
        $this->app = $app;
    }

    /**
     * Build config type form
     *
     * @param FormBuilderInterface $builder
     * @param array $options
     * @return type
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'plg_point_info_add_status_id',
                'hidden',
                array(
                    'required' => false,
                )
            )
            ->add(
                'plg_point_info_add_status',
                'hidden',
                array(
                    'mapped' => false,
                )
            )
            ->add(
                'plg_point_info_add_trigger_type',
                'choice',
                array(
                    'label' => false,
                    'choices' => array(
                        PointInfoAddStatus::ADD_STATUS_FIX => '付与',
                        PointInfoAddStatus::ADD_STATUS_NON_FIX => '付与しない',
                    ),
                    'expanded' => false,
                    'multiple' => false,
                )
            )
            ->addEventSubscriber(new \Eccube\Event\FormEventSubscriber());
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            array(
                'data_class' => 'Plugin\Point\Entity\PointInfoAddStatus',
            )
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'admin_point_info_add_status';
    }
}
