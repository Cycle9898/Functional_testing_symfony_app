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

    public function testHomepageIsUp()
    {
        $this->client->request(Request::METHOD_GET, $this->urlGenerator->generate('homepage'));

        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
    }
}
