<?php

namespace App\Controller;

use App\Service\GmailClientService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;

class GmailController extends AbstractController
{
    private GmailClientService $gmail_client_service;

    public function __construct(GmailClientService $gmail_client_service)
    {
        $this->gmail_client_service = $gmail_client_service;
    }

    /**
     * @Route("/gmail/auth", name="gmail_auth")
     */
    #[Route('/gmail/auth', name: 'gmail_auth')]
    public function auth(Request $request, SessionInterface $session): RedirectResponse
    {
        $client = $this->gmail_client_service->getClient();
        $auth_url = $client->createAuthUrl();
        $session->set('oauth_token', $client->getAccessToken());

        return $this->redirect($auth_url);
    }

    /**
     * @Route("/gmail/callback", name="gmail_callback")
     */
    #[Route('/gmail/callback', name: 'gmail_callback')]
    public function callback(Request $request, SessionInterface $session): RedirectResponse
    {
        $client = $this->gmail_client_service->getClient();

        if ($request->query->get('code')) {
            $access_token = $client->fetchAccessTokenWithAuthCode($request->query->get('code'));
            $refresh_token = $client->getRefreshToken();

            // Set the access token for the Gmail API service
            $client->setAccessToken($access_token);

            // Get the Gmail service
            $gmail_service = $this->gmail_client_service->getGmailService();

            // Fetch the user's email
            $userProfile = $gmail_service->users->getProfile('me');
            $user_email = $userProfile->getEmailAddress();

            $token_data = [
                'access_token' => $access_token['access_token'],
                'refresh_token' => $refresh_token,
                'token_expiry' => time() + $access_token['expires_in'],
            ];

            $this->save_tokens_to_file($user_email, $token_data);

            // Redirect to another route after saving the tokens
            return $this->redirectToRoute('some_route_name');
        }
        // return $this->redirectToRoute('some_route_name'); // Redirect to your desired route after auth
    }

    private function save_tokens_to_file(string $user_email, array $token_data): void
    {
        // Use __DIR__ to get the current directory of the script
        $file_path = __DIR__ . '/token.json';

        $dir = dirname($file_path);

        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }

        $existing_data = file_exists($file_path) ? json_decode(file_get_contents($file_path), true) : [];

        // Update the user's token data using the email as the key
        $existing_data[$user_email] = $token_data;

        file_put_contents($file_path, json_encode($existing_data, JSON_PRETTY_PRINT));
    }
}
