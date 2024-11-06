<?php

namespace App\Service;

use Google\Client;
use Google\Service\Gmail;
use Google\Service\Drive;

class GoogleClientService
{
    private Client $client;

    public function __construct()
    {
        $this->client = new Client();
        $this->client->setAuthConfig(__DIR__.'/../../config/google/credentials.json');

        //TODO: put this as an env variable
        $this->client->setRedirectUri('http://127.0.0.1:8000/google/callback'); // Ensure this matches the URI registered in Google Cloud

        $this->client->addScope(Gmail::MAIL_GOOGLE_COM);
        $this->client->addScope(Drive::DRIVE);
        $this->client->setAccessType('offline');
        $this->client->setPrompt('select_account consent');
    }

    public function get_client(): Client
    {
        return $this->client;
    }

    public function get_gmail_service(): Gmail
    {
        return new Gmail($this->client);
    }

    public function get_drive_service(): Drive
    {
        return new Drive($this->client);
    }
}
