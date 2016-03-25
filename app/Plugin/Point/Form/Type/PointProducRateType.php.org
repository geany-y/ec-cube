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

use Eccube\Entity\Product;
use Plugin\Point\Entity\PointInfo;
use Plugin\Point\Entity\PointInfoAddStatus;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class PointProductRateType
 * @package Plugin\Point\Form\Type
 * @param Eccube\Applicaion
 */
class PointProductRateType extends AbstractType
{
    protected $app;

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
                'Product',
                'hidden',
                array(
                    'type' => new Product(),
                    'prototype' => true,
                )
            )
            ->add(
                'plg_point_product_rate',
                'text',
                array(
                    'label' => '商品毎ポイント付与率',
                    'required' => false,
                    'mapped' => true,
                    'empty_data' => 0,
                    'attr' => array(
                        'placeholder' => 'ポイント計算時に使用する付与率（ 商品毎の設定値で計算 （％））例. 1',
                    ),
                    'constraints' => array(
                        new Assert\Regex(
                            array(
                                'pattern' => "/^\d+$/u",
                                'message' => 'form.type.numeric.invalid',
                            )
                        ),
                    ),
                )
            )
            ->addEventSubscriber(new \Eccube\Event\FormEventSubscriber());
    }

    /**
     * 子要素が空かどうかを判定
     * @param FormBuilderInterface $builder
     * @return bool
     */
    protected function isEmptyAddStatus(FormBuilderInterface $builder)
    {
        $entity = $builder->getData();

        if (count($entity->getPointInfoAddStatus()) < 1) {
            return true;
        }

        return false;
    }

    /**
     * ポイント付与受注ステータスエンティティを受注ステータス分セット
     * @param FormBuilderInterface $builder
     * @return bool
     */
    protected function setEditAddStatusEntities(FormBuilderInterface $builder)
    {
        // 受注ステータスが存在しない際
        if (count($this->orderStatus) < 1) {
            return false;
        }

        $entity = $builder->getData();
        $addStatus = $entity->getPointInfoAddStatus();
        // PointInfoにフォーム取得値をセット
        $pointInfo = new PointInfo();
        $pointInfo->setPlgBasicPointRate($entity->getPlgBasicPointRate());
        $pointInfo->setPlgPointConversionRate($entity->getPlgPointConversionRate());
        $pointInfo->setPlgRoundType($entity->getPlgRoundType());
        $pointInfo->setPlgCalculationType($entity->getPlgCalculationType());
        // PointInfoAddStatusに受注ステータスをセット
        foreach ($addStatus as $key => $val) {
            $pointInfoAddStatus = new PointInfoAddStatus();
            $pointInfoAddStatus->setPlgPointInfoAddStatus($val->getPlgPointInfoAddStatus());
            $pointInfoAddStatus->setPlgPointInfoAddTriggerType($val->getPlgPointInfoAddTriggerType());
            $pointInfo->setPointInfoAddStatus($pointInfoAddStatus);
            $pointInfoAddStatus->setPointInfo($pointInfo);
        }

        $builder->setData($pointInfo);

        return true;
    }

    /**
     * 新規ポイント付与受注ステータスエンティティを受注ステータス分セット
     * @param FormBuilderInterface $builder
     * @return bool
     */
    protected function setNewAddStatusEntities(FormBuilderInterface $builder)
    {
        // 受注ステータスが存在しない際
        if (count($this->orderStatus) < 1) {
            return false;
        }

        // PointInfoAddStatusに受注ステータスをセット
        $entity = $builder->getData();
        foreach ($this->orderStatus as $key => $val) {
            $pointInfoAddStatus = new PointInfoAddStatus();
            $pointInfoAddStatus->setPlgPointInfoAddStatus($key);
            $entity->setPointInfoAddStatus($pointInfoAddStatus);
            $pointInfoAddStatus->setPointInfo($entity);
        }
        $builder->setData($entity);

        return true;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            array(
                'data_class' => 'Plugin\Point\Entity\PointInfo',
            )
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'admin_point_info';
    }
}
