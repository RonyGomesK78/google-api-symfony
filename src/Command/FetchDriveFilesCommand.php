<?php

namespace App\Command;

use App\Service\GoogleClientService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class FetchDriveFilesCommand extends Command
{
    private $drive_service;

    public function __construct(GoogleClientService $drive_service)
    {
        parent::__construct();
        $this->drive_service = $drive_service;
    }

    protected function configure()
    {
        $this->setName('app:fetch_drive_files')
            ->setDescription('Fetch a list of files from Google Drive for all users');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln('Fetching Google Drive files for all users...');

        $token_file_path = __DIR__ . '/../Controller/token.json';

        if (!file_exists($token_file_path)) {
            $output->writeln('Token file does not exist.');
            return Command::FAILURE;
        }

        $token_data = json_decode(file_get_contents($token_file_path), true);

        foreach ($token_data as $user_email => &$user_token) {
            $client = $this->drive_service->get_drive_service()->getClient();
            $client->setAccessToken($user_token['access_token']);

            // Refresh the token if it has expired
            if ($client->isAccessTokenExpired()) {
                $client->fetchAccessTokenWithRefreshToken($user_token['refresh_token']);
                $new_access_token = $client->getAccessToken();
                $user_token['access_token'] = $new_access_token['access_token'];
                $user_token['token_expiry'] = time() + $new_access_token['expires_in'];

                file_put_contents($token_file_path, json_encode($token_data, JSON_PRETTY_PRINT));
            }

            // Output user's files from Google Drive
            $output->writeln("<info>Google Drive files for user: $user_email</info>");

            $drive_service = $this->drive_service->get_drive_service();

            $files = $drive_service->files->listFiles(['pageSize' => 10, 'fields' => 'files(id, name)'])->getFiles();

            foreach ($files as $file) {
                $output->writeln("File Name: " . $file['name'] . " | ID: " . $file['id']);
            }

            $output->writeln("-------------------------------------------------------------");
        }

        return Command::SUCCESS;
    }
}
