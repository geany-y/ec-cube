<?php
namespace Plugin\GiftWrapping\Form\Extension;

use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;

class ShoppingTypeExtension extends AbstractTypeExtension
{
    protected $app;

    public function __construct($app)
    {
        $this->app = $app;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('gift_wrapping', 'choice', array(
                'choices' => array(
                    '1' => 'のしのようなもの',
                    '2' => 'リボン',
                    '3' => '紙包み',
                ),
                'expanded' => false,
                'multiple' => false,
                'required'    => false,
                'empty_value' => 'ラッピングなし',
                'mapped' => false,
            ));
    }

    public function getExtendedType()
    {
        return 'shopping';
    }
}