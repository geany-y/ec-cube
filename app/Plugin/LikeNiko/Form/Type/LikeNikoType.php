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
namespace Plugin\LikeNiko\Form\Type;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints as Assert;
class LikeNikoType extends AbstractType
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
            ->add('nikomessage', 'text', array(
                'label' => 'メッセージを入力してください',
                'required' => false,
                /*
                'constraints' => array(
                    new Assert\NotBlank(array('message' => '※ メーカー名が入力されていません。')),
                ),
                */
            ))
            ->add('save', 'submit', array('label' => 'ニコる'))
            ->addEventSubscriber(new \Eccube\Event\FormEventSubscriber());
    }
    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'likeniko';
    }
}
