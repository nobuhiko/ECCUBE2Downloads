<?php

namespace Plugin\ECCUBE2Downloads\Entity;

use Doctrine\ORM\Mapping as ORM;
use Eccube\Annotation\EntityExtension;

/**
 * @EntityExtension("Eccube\Entity\ProductClass")
 */
trait ProductClassTrait
{
    /**
     * @var string|null
     *
     * @ORM\Column(name="down_filename", type="string", length=255, nullable=true, options={ "eccube_form_options": { "auto_render": true, "form_theme": "@ECCUBE2Downloads/admin/product_class_down_filename.twig" } })
     */
    public $down_filename;

    /**
     * @var string|null
     *
     * @ORM\Column(name="down_realfilename", type="string", length=255, nullable=true)
     */
    public $down_realfilename;
}
