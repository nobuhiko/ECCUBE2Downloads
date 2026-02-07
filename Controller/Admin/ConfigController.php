<?php

namespace Plugin\ECCUBE2Downloads\Controller\Admin;

use Eccube\Controller\AbstractController;
use Plugin\ECCUBE2Downloads\Form\Type\Admin\ConfigType;
use Plugin\ECCUBE2Downloads\Repository\ConfigRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class ConfigController extends AbstractController
{
    /**
     * @var ConfigRepository
     */
    protected $configRepository;

    public function __construct(ConfigRepository $configRepository)
    {
        $this->configRepository = $configRepository;
    }

    /**
     * @Route("/%eccube_admin_route%/eccube2downloads/config", name="eccube2downloads_admin_config")
     *
     * @Template("@ECCUBE2Downloads/admin/config.twig")
     */
    public function index(Request $request)
    {
        $Config = $this->configRepository->get();
        $form = $this->createForm(ConfigType::class, $Config);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $Config = $form->getData();
            $this->entityManager->persist($Config);
            $this->entityManager->flush();

            $this->addSuccess('admin.common.save_complete', 'admin');

            return $this->redirectToRoute('eccube2downloads_admin_config');
        }

        return [
            'form' => $form->createView(),
        ];
    }
}
