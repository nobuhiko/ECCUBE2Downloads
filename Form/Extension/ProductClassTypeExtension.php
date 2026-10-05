<?php

namespace Plugin\ECCUBE2Downloads44\Form\Extension;

use Eccube\Form\Type\Admin\ProductClassEditType;
use Eccube\Form\Type\Admin\ProductClassType;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * 商品規格フォーム (規格なし / 規格あり) にダウンロードファイルの項目を追加する.
 *
 * eccube_form_options.auto_render を有効にすると、管理画面の商品登録・商品規格登録の
 * テンプレートが自動で項目を描画する.
 */
class ProductClassTypeExtension extends AbstractTypeExtension
{
    #[\Override]
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('down_filename', TextType::class, [
                'label' => 'ダウンロードファイル名',
                'required' => false,
                'constraints' => [
                    new Assert\Length(max: 255),
                ],
                'eccube_form_options' => [
                    'auto_render' => true,
                ],
            ])
            ->add('down_realfilename', TextType::class, [
                'label' => 'ダウンロードファイル',
                'required' => false,
                'constraints' => [
                    new Assert\Length(max: 255),
                ],
                'eccube_form_options' => [
                    'auto_render' => true,
                ],
            ]);
    }

    #[\Override]
    public static function getExtendedTypes(): iterable
    {
        return [ProductClassType::class, ProductClassEditType::class];
    }
}
