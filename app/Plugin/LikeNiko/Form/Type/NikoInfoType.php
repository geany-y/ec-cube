<?php
namespace Plugin\LikeNiko\Form\Type;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

class NikoInfoType extends AbstractType
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
        $config = $this->app['config'];
        $builder
             ->add('id', 'hidden', array(
                'required' => false,
                'data' => 1,
            ))
            ->add('is_auth_flg', 'choice', array(
                'label' => 'コメント権限設定',
                'required' => true,
                'choices' => array(
                    '1' => '会員のみ',
                    '2' => 'サイト閲覧者全員',
                ),
                'expanded' => true,
                'multiple' => false,
                'empty_value' => false,
            ))
            ->add('form_insert_key', 'text', array(
                'required' => true,
                'data' => '#main_middle',
                'constraints' => array(
                    new Assert\Regex(array(
                        'pattern' => "/^[#a-zA-Z_\-]+$/u",
                        'message' => 'admin.nikoinfo.alphanumeric.error'
                    )),
                ),
            ))
            ->add('replace_block_key', 'text', array(
                'required' => true,
                'data' => '.main_visual',
                'constraints' => array(
                    new Assert\Regex(array(
                        'pattern' => "{^[#a-zA-Z_\-\.0-9]+$}u",
                        'message' => 'admin.nikoinfo.alphanumeric.error'
                    )),
                ),
            ))
            ->add('target_img_name', 'text', array(
                'required' => true,
                'data' => '/ec-cube/html/template/default/img/top/mv01.jpg',
                'constraints' => array(
                    new Assert\Regex(array(
                        'pattern' => "{^[a-zA-Z_\-\.\/0-9]+$}u",
                        'message' => 'admin.nikoinfo.alphanumeric.error'
                    )),
                ),
            ))
            ->add('target_img_height', 'text', array(
                'required' => true,
                'data' => 435,
                'constraints' => array(
                    new Assert\Regex(array(
                        'pattern' => "/^[0-9]+$/u",
                        'message' => 'admin.nikoinfo.numeric.error'
                    )),
                ),
            ))
            ->add('target_img_width', 'text', array(
                'required' => true,
                'data' => 1087,
                'constraints' => array(
                    new Assert\Regex(array(
                        'pattern' => "/^[0-9]+$/u",
                        'message' => 'admin.nikoinfo.numeric.error'
                    )),
                ),
            ))
            ->add('node_server_address', 'text', array(
                'required' => true,
                'constraints' => array(
                    new Assert\Length(array(
                        'max' => $config['stext_len'],
                    )) ,
                    new Assert\Regex(array(
                        'pattern' => "/^[a-zA-Z_\-\.0-9:\/]+$/u",
                        'message' => 'form.type.float.invalid'
                    )),
                ),
            ))
            ->addEventSubscriber(new \Eccube\Event\FormEventSubscriber());
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Plugin\LikeNiko\Entity\NikoInfo',
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'admin_nikoinfo';
    }
}
