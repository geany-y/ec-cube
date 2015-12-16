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
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

class ProductPointRateType extends AbstractType
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
             ->add('id', 'hidden', array(
                'required' => false,
            ))
             ->add('ProductClassId', 'entity', array(
                'label' => '商品規格ID',
                'class' => 'Eccube\Entity\ProductClass',
                'expanded' => false,
                'multiple' => false,
                'empty_value' => 0,
                'constraints' => array(
                    new Assert\NotBlank(),
                ),
            ))
            ->add('product_point_rate', 'text', array(
                'label' => '商品別ポイント付与率',
                'required' => false,
                'constraints' => array(
                    new Assert\Regex(array(
                        'pattern' => "/^\d+(\.\d+)?$/u",
                        'message' => 'form.type.float.invalid'
                    )),
                ),
            ))
            ->add('created', 'datetime', array(
                'required' => false,
                'format' => 'yyyy/MM/dd H:i:s',
                'empty_value' => date('yyyy/MM/dd H:i:s'),
            ))
            ->add('modified', 'datetime', array(
                'required' => false,
                'format' => 'yyyy/MM/dd H:i:s',
                'empty_value' => date('yyyy/MM/dd H:i:s'),
            ))
            ->addEventSubscriber(new \Eccube\Event\FormEventSubscriber());
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Plugin\Point\Entity\ProductPointRate',
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'admin_product_point_rate';
    }
}
