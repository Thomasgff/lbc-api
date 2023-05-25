<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class AnnoncesControllerTest extends WebTestCase
{
    public function testGetListeAnnonces()
    {
        $client = static::createClient();
        $client->request('GET', '/api/annonces');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h2', 'Give your feedback');
    }
}