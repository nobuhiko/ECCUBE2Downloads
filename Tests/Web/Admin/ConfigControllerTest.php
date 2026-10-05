<?php

declare(strict_types=1);

namespace Plugin\ECCUBE2Downloads44\Tests\Web\Admin;

use Symfony\Component\HttpFoundation\Response;
use Eccube\Tests\Web\Admin\AbstractAdminWebTestCase;
use Plugin\ECCUBE2Downloads44\Entity\Config;

final class ConfigControllerTest extends AbstractAdminWebTestCase
{
    public function testRouting(): void
    {
        $this->client->request('GET', $this->generateUrl('eccube2downloads44_admin_config'));

        $this->assertEquals(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
    }

    public function testSubmit(): void
    {
        $crawler = $this->client->request('GET', $this->generateUrl('eccube2downloads44_admin_config'));

        $form = $crawler->selectButton('登録')->form();
        $form['config[downloadable_days]'] = '60';

        $this->client->submit($form);
        $this->assertTrue($this->client->getResponse()->isRedirection());

        $Config = $this->entityManager->getRepository(Config::class)->findOneBy([]);
        $this->assertInstanceOf(Config::class, $Config);
        $this->assertSame(60, $Config->getDownloadableDays());
    }

    public function testSubmitUnlimited(): void
    {
        $crawler = $this->client->request('GET', $this->generateUrl('eccube2downloads44_admin_config'));

        $form = $crawler->selectButton('登録')->form();
        $form['config[downloadable_days]'] = '30';
        $form['config[downloadable_days_unlimited]']->tick();

        $this->client->submit($form);
        $this->assertTrue($this->client->getResponse()->isRedirection());

        // re-read from DB
        $this->entityManager->clear();
        $Config = $this->entityManager->getRepository(Config::class)->findOneBy([]);
        $this->assertInstanceOf(Config::class, $Config);
        $this->assertTrue($Config->isDownloadableDaysUnlimited());
    }

    public function testSubmitValidationError(): void
    {
        $crawler = $this->client->request('GET', $this->generateUrl('eccube2downloads44_admin_config'));

        $form = $crawler->selectButton('登録')->form();
        $form['config[downloadable_days]'] = '';

        $this->client->submit($form);
        // フォームバリデーションエラーで200が返る
        $this->assertEquals(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
    }
}
