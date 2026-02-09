<?php

namespace Plugin\ECCUBE2Downloads\Entity;

use Doctrine\ORM\Mapping as ORM;
use Eccube\Annotation\EntityExtension;
use Eccube\Annotation\FormAppend;

/**
 * @EntityExtension("Eccube\Entity\ProductClass")
 */
trait ProductClassTrait
{
    /**
     * @var string|null
     *
     * @ORM\Column(name="down_filename", type="string", length=255, nullable=true)
     * @FormAppend(auto_render=true, options={"required": false, "label": "ダウンロードファイル名"})
     */
    public $down_filename;

    /**
     * @var string|null
     *
     * @ORM\Column(name="down_realfilename", type="string", length=255, nullable=true)
     * @FormAppend(auto_render=true, options={"required": false, "label": "ダウンロードファイル"})
     */
    public $down_realfilename;
}
