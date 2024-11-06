<?php

namespace App\Command;

use App\Service\GmailClientService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class FetchLatestMessagesCommand extends Command
{
    private $gmail_service;

    public function __construct(GmailClientService $gmail_service)
    {
        parent::__construct();
        $this->gmail_service = $gmail_service;
    }

    protected function configure()
    {
        $this->setName('app:fetch-latest-messages')
            ->setDescription('Fetch the 10 latest messages for all users from a file');
    }

    /**
     * Ensure that the method signature matches the parent class
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln('Fetching latest messages for all users...');

        $token_file_path = '/home/rony78k/g_integration_php/src/Controller/token.json'; // Adjust the path accordingly
        if (!file_exists($token_file_path)) {
            $output->writeln('Token file does not exist.');
            return Command::FAILURE;
        }

        // Read the token data from the file
        $token_data = json_decode(file_get_contents($token_file_path), true);

        foreach ($token_data as $user_email => &$user_token) {  // Use reference to update tokens and fetch email
            $client = $this->gmail_service->getClient();
            $client->setAccessToken($user_token['access_token']);

            // Refresh the token if expired
            if ($client->isAccessTokenExpired()) {
                $client->fetchAccessTokenWithRefreshToken($user_token['refresh_token']);
                $new_access_token = $client->getAccessToken();
                $user_token['access_token'] = $new_access_token['access_token'];
                $user_token['token_expiry'] = time() + $new_access_token['expires_in'];

                // Save updated tokens back to file
                file_put_contents($token_file_path, json_encode($token_data, JSON_PRETTY_PRINT));
            }

            // Fetch and display user email
            $output->writeln("<info>Messages for user: $user_email</info>");

            // Fetch the latest messages for the user
            $gmail_service = $this->gmail_service->getGmailService();
            $messages = $gmail_service->users_messages->listUsersMessages('me', ['maxResults' => 10]);

            foreach ($messages->getMessages() as $message) {
                $message_id = $message->getId();
                $message_detail = $gmail_service->users_messages->get('me', $message_id);

                // Extract and display the subject
                $subject = $this->extractSubject($message_detail);
                $output->writeln("Subject: {$subject}");
            }
            $output->writeln("-------------------------------------------------------------");
        }

        return Command::SUCCESS;
    }


    private function extractSubject($message_detail)
    {
        foreach ($message_detail->getPayload()->getHeaders() as $header) {
            if ($header->getName() === 'Subject') {
                return $header->getValue();
            }
        }
        return 'No subject';
    }
}
