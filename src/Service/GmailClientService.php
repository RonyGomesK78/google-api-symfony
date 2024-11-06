<?php

namespace App\Service;

use Google\Client;
use Google\Service\Gmail;

class GmailClientService
{
    private Client $client;
    // create a cron job to look for new messages
    public function __construct()
    {
        $this->client = new Client();
        $this->client->setAuthConfig(__DIR__.'/../../config/google/credentials.json');
        $this->client->setRedirectUri('http://127.0.0.1:8000/gmail/callback'); // Ensure this matches the URI registered in Google Cloud
        $this->client->addScope(Gmail::MAIL_GOOGLE_COM);
        $this->client->setAccessType('offline');
        $this->client->setPrompt('select_account consent');
    }

    public function getClient(): Client
    {
        return $this->client;
    }

    public function getGmailService(): Gmail
    {
        return new Gmail($this->client);
    }
}
