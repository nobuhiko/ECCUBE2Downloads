<?php

namespace Plugin\ECCUBE2Downloads44\Controller\Admin;

use Eccube\Controller\AbstractController;
use Plugin\ECCUBE2Downloads44\Form\Type\Admin\ConfigType;
use Plugin\ECCUBE2Downloads44\Repository\ConfigRepository;
use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

class ConfigController extends AbstractController
{
    public function __construct(private readonly ConfigRepository $configRepository)
    {
    }

    #[Route(path: '/%eccube_admin_route%/eccube2downloads44/config', name: 'eccube2downloads44_admin_config', methods: ['GET', 'POST'])]
    #[Template(template: '@ECCUBE2Downloads44/admin/config.twig')]
    public function index(Request $request): array|RedirectResponse
    {
        $Config = $this->configRepository->get();
        $form = $this->createForm(ConfigType::class, $Config);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $Config = $form->getData();
            $this->entityManager->persist($Config);
            $this->entityManager->flush();

            $this->addSuccess('admin.common.save_complete', 'admin');

            return $this->redirectToRoute('eccube2downloads44_admin_config');
        }

        return [
            'form' => $form->createView(),
        ];
    }
}
