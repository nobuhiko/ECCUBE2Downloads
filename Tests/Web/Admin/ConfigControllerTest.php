<?php

namespace Plugin\ECCUBE2Downloads\Tests\Web\Admin;

use Eccube\Tests\Web\Admin\AbstractAdminWebTestCase;
use Plugin\ECCUBE2Downloads\Entity\Config;

class ConfigControllerTest extends AbstractAdminWebTestCase
{
    public function testRouting()
    {
        $this->client->request('GET', $this->generateUrl('eccube2downloads_admin_config'));

        self::assertEquals(200, $this->client->getResponse()->getStatusCode());
    }

    public function testSubmit()
    {
        $crawler = $this->client->request('GET', $this->generateUrl('eccube2downloads_admin_config'));

        $form = $crawler->selectButton('登録')->form();
        $form['config[downloadable_days]'] = 60;

        $this->client->submit($form);
        self::assertTrue($this->client->getResponse()->isRedirection());

        $Config = $this->entityManager->getRepository(Config::class)->findOneBy([]);
        self::assertEquals(60, $Config->getDownloadableDays());
    }

    public function testSubmitUnlimited()
    {
        $crawler = $this->client->request('GET', $this->generateUrl('eccube2downloads_admin_config'));

        $form = $crawler->selectButton('登録')->form();
        $form['config[downloadable_days]'] = 30;
        $form['config[downloadable_days_unlimited]'] = 1;

        $this->client->submit($form);
        self::assertTrue($this->client->getResponse()->isRedirection());

        // re-read from DB
        $this->entityManager->clear();
        $Config = $this->entityManager->getRepository(Config::class)->findOneBy([]);
        self::assertTrue($Config->isDownloadableDaysUnlimited());
    }

    public function testSubmitValidationError()
    {
        $crawler = $this->client->request('GET', $this->generateUrl('eccube2downloads_admin_config'));

        $form = $crawler->selectButton('登録')->form();
        $form['config[downloadable_days]'] = '';

        $crawler = $this->client->submit($form);
        // フォームバリデーションエラーで200が返る
        self::assertEquals(200, $this->client->getResponse()->getStatusCode());
    }
}
