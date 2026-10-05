<?php

namespace Plugin\ECCUBE2Downloads44\Entity;

use Eccube\Entity\ProductClass;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Eccube\Attribute\EntityExtension;

/**
 * ダウンロード商品用のカラムを ProductClass に追加する.
 *
 * フォーム項目は Form\Extension\ProductClassTypeExtension で追加する
 * (4.4 の FormAppend アトリビュートは引数付きで利用できないため).
 */
#[EntityExtension(ProductClass::class)]
trait ProductClassTrait
{
    /**
     * @var string|null
     */
    #[ORM\Column(name: 'down_filename', type: Types::STRING, length: 255, nullable: true)]
    public $down_filename;

    /**
     * @var string|null
     */
    #[ORM\Column(name: 'down_realfilename', type: Types::STRING, length: 255, nullable: true)]
    public $down_realfilename;
}
