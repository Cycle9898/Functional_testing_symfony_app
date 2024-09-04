<?php

namespace App\Tests\Controller;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class DiaryControllerTest extends WebTestCase
{
    private KernelBrowser|null $client = null;
    private $userRepo = null;
    private USer|null $user = null;
    private $urlGenerator = null;

    public function setUp(): void
    {
        $this->client = static::createClient();

        $this->userRepo = $this->client->getContainer()->get('doctrine.orm.entity_manager')->getRepository(User::class);
        $this->user = $this->userRepo->findOneByEmail($_ENV['GITHUB_MAIL']);

        $this->urlGenerator = $this->client->getContainer()->get('router.default');

        // Authentication
        $this->client->loginUser($this->user);
    }

    /* Status code page tests */

    public function testHomepageIsUp()
    {
        $this->client->request(Request::METHOD_GET, $this->urlGenerator->generate('homepage'));

        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
    }

    public function testDiaryIsUp()
    {
        $this->client->request(Request::METHOD_GET, $this->urlGenerator->generate('diary'));

        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
    }

    public function testAddNewRecordIsUp()
    {
        $this->client->request(Request::METHOD_GET, $this->urlGenerator->generate('add-new-record'));

        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
    }

    public function testDeleteRecordIsUp()
    {
        $this->client->request(Request::METHOD_GET, $this->urlGenerator->generate('delete-record'));

        $this->assertResponseStatusCodeSame(Response::HTTP_FOUND);
    }

    public function testCaloriesStatusIsUp()
    {
        $this->client->request(Request::METHOD_GET, $this->urlGenerator->generate('calories-status'));

        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
    }

    public function testGithubConnectIsUp()
    {
        $this->client->request(Request::METHOD_GET, $this->urlGenerator->generate('github_connect'));

        $this->assertResponseStatusCodeSame(Response::HTTP_FOUND);
    }

    public function testGithubRedirectUrlIsUp()
    {
        $this->client->request(Request::METHOD_GET, $this->urlGenerator->generate('github_redirect_url'));

        $this->assertResponseStatusCodeSame(Response::HTTP_FOUND);
    }

    public function testAppLoginIsUp()
    {
        $this->client->request(Request::METHOD_GET, $this->urlGenerator->generate('app_login'));

        $this->assertResponseStatusCodeSame(Response::HTTP_FOUND);
    }

    public function testAppLogoutIsUp()
    {
        $this->client->request(Request::METHOD_GET, $this->urlGenerator->generate('app_logout'));

        $this->assertResponseStatusCodeSame(Response::HTTP_FOUND);
    }

    /* Page tests */

    public function testHomepageH1()
    {
        $crawler = $this->client->request(Request::METHOD_GET, $this->urlGenerator->generate('homepage'));

        $this->assertSame(1, $crawler->filter('h1')->count());
    }

    public function testList()
    {
        $crawler = $this->client->request(Request::METHOD_GET, $this->urlGenerator->generate('diary'));
        $link = $crawler->selectLink('Voir tous les rapports')->link();
        $crawler = $this->client->click($link);

        $info = $crawler->filter('h1')->text();
        // format text
        $info = trim(preg_replace('/\s\s+/', '', $info));

        $this->assertSame('Tous les rapports Tout ce qui a été mangé !', $info);
    }

    /* Form test */

    public function testAddRecord()
    {
        $crawler = $this->client->request(Request::METHOD_GET, $this->urlGenerator->generate('add-new-record'));

        $form = $crawler->selectButton('Enregistrer')->form();
        $form['food[entitled]'] = 'Plat de spaghetti';
        $form['food[calories]'] = 785;
        $this->client->submit($form);
        $this->client->followRedirect();

        $this->assertSelectorTextContains('div.alert.alert-success', 'Ajouter une nouvelle entrée.');
    }
}
