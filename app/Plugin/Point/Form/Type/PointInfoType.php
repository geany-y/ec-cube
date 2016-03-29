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

use Plugin\Point\Entity\PointInfo;
use Plugin\Point\Entity\PointInfoAddStatus;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class PointInfoType
 * @package Plugin\Point\Form\Type
 * @param Eccube\Applicaion
 */
class PointInfoType extends AbstractType
{
    protected $app;
    protected $orderStatus;

    /**
     * PointInfoType constructor.
     * @param \Eccube\Application $app
     */
    public function __construct(\Eccube\Application $app)
    {
        $this->app = $app;
        // 全受注ステータス ID・名称 取得保持
        $this->orderStatus = array();
        $this->app['orm.em']->getFilters()->enable('incomplete_order_status_hidden');
        foreach ($this->app['eccube.repository.order_status']->findAllArray() as $id => $node) {
            /*
            if ($id == $this->app['config']['order_cancel']){
                continue;
            }
            if($id == $this->app['config']['order_processing']) {
                continue;
            }
            */
            $this->orderStatus[$id] = $node['name'];
        }

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
        // 初期化処理(子要素をセット)
        if ($this->isEmptyAddStatus($builder)) {
            // データーが一件もない
            $this->setNewAddStatusEntities($builder);
        } else {
            // 既に登録データがある
            $this->setEditAddStatusEntities($builder);
        }

        $builder
            ->add(
                'plg_point_info_id',
                'hidden',
                array(
                    'required' => false,
                    'mapped' => true,
                )
            )
            ->add(
                'plg_add_point_status',
                'choice',
                array(
                    'label' => 'ポイント付与タイミング',
                    'choices' => $this->orderStatus,
                    'mapped' => true,
                    'expanded' => false,
                    'multiple' => false,
                )
            )
            ->add(
                'plg_calculation_type',
                'choice',
                array(
                    'label' => 'ポイント計算方法',
                    'choices' => array(
                        \Plugin\Point\Entity\PointInfo::POINT_CALCULATE_SUBTRACTION => '利用ポイント減算',
                        \Plugin\Point\Entity\PointInfo::POINT_CALCULATE_NORMAL => '減算なし',
                    ),
                    'mapped' => true,
                    'expanded' => false,
                    'multiple' => false,
                )
            )
            ->add(
                'plg_basic_point_rate',
                'text',
                array(
                    'label' => '基本ポイント付与率',
                    'required' => false,
                    'mapped' => true,
                    'empty_data' => 0,
                    'attr' => array(
                        'placeholder' => 'ポイント付与計算に使用するサイト全体の付与率（ ％ ）例. 1',
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
            ->add(
                'plg_point_conversion_rate',
                'text',
                array(
                    'label' => 'ポイント換算率',
                    'required' => false,
                    'mapped' => true,
                    'empty_data' => 0,
                    'attr' => array(
                        'placeholder' => 'ポイント利用時に何円で計算するかの換算率（ 円 ）例. 1',
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
            /*
            ->add(
                'plg_calculation_type',
                'choice',
                array(
                    'label' => 'ポイント計算方法',
                    'choices' => array(
                        \Plugin\Point\Entity\PointInfo::POINT_CALCULATE_SUBTRACTION => '利用ポイント減算',
                        \Plugin\Point\Entity\PointInfo::POINT_CALCULATE_NORMAL => '減算なし',
                    ),
                    'mapped' => true,
                    'expanded' => false,
                    'multiple' => false,
                )
            )
            */
            ->add(
                'plg_round_type',
                'choice',
                array(
                    'label' => '端数計算方法',
                    'choices' => array(
                        \Plugin\Point\Entity\PointInfo::POINT_ROUND_CEIL => '切り上げ',
                        \Plugin\Point\Entity\PointInfo::POINT_ROUND_FLOOR => '切り捨て',
                        \Plugin\Point\Entity\PointInfo::POINT_ROUND_ROUND => '四捨五入',
                    ),
                    'mapped' => true,
                    'expanded' => false,
                    'multiple' => false,
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

        if(!$entity){
            return true;
        }

        if (count($entity->getPlgAddPointStatus()) < 1) {
            return true;
        }

        return false;
    }

    /**
     * ポイント付与受注ステータスエンティティを受注ステータス分セット
     * @remark 子要素がある場合
     * @param FormBuilderInterface $builder
     * @return bool
     */
    protected function setEditAddStatusEntities(FormBuilderInterface $builder)
    {
        // 受注ステータスが存在しない際
        if (count($this->orderStatus) < 1) {
            return false;
        }

        // PointInfoAddStatusのエンティティを取得
        $entity = $builder->getData();
        //$addStatus = $entity->getPlgAddPointStatus();

        // PointInfoにフォーム取得基本情報をセット
        $pointInfo = new PointInfo();
        $pointInfo->setPlgBasicPointRate($entity->getPlgBasicPointRate());
        $pointInfo->setPlgPointConversionRate($entity->getPlgPointConversionRate());
        $pointInfo->setPlgRoundType($entity->getPlgRoundType());
        $pointInfo->setPlgCalculationType($entity->getPlgCalculationType());
        $pointInfo->setPlgAddPointStatus($entity->getPlgAddPointStatus());
        //$pointInfo->setPointAddStatus($entity->addStatus());
        // PointInfoAddStatusに受注ステータスをセット
        /*
        foreach ($addStatus as $key => $val) {
            $pointInfo->setPointAddStatus($val->getPointAddStatus());
        }
        */

        // 編集値をフォームに再格納
        $builder->setData($pointInfo);

        return true;
    }

    /**
     * 新規ポイント付与受注ステータスエンティティを受注ステータス分セット
     * @remark 子要素がない場合
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
        /*
        $entity = $builder->getData();
        foreach ($this->orderStatus as $key => $val) {
            $pointInfoAddStatus = new PointInfoAddStatus();
            $pointInfoAddStatus->setPlgPointInfoAddStatus($key);
            $entity->setPointInfoAddStatus($pointInfoAddStatus);
            $pointInfoAddStatus->setPointInfo($entity);
        }
        $builder->setData($entity);
        */

        return true;
    }

    /**
     * @param OptionsResolver $resolver
     */
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
