<?php

namespace App\Controller;

use App\Service\GoogleClientService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;

class GoogleOAuthController extends AbstractController
{
    private GoogleClientService $gmail_client_service;

    public function __construct(GoogleClientService $gmail_client_service)
    {
        $this->gmail_client_service = $gmail_client_service;
    }

    /**
     * @Route("/google/oauth", name="google_oauth")
     */
    #[Route('/google/oauth', name: 'google_oauth')]
    public function auth(SessionInterface $session): RedirectResponse
    {
        $client = $this->gmail_client_service->get_client();
        $auth_url = $client->createAuthUrl();
        $session->set('oauth_token', $client->getAccessToken());

        return $this->redirect($auth_url);
    }

    /**
     * @Route("/google/callback", name="google_callback")
     */
    #[Route('/google/callback', name: 'google_callback')]
    public function callback(Request $request): RedirectResponse
    {
        $client = $this->gmail_client_service->get_client();

        if ($request->query->get('code')) {
            $access_token = $client->fetchAccessTokenWithAuthCode($request->query->get('code'));
            $refresh_token = $client->getRefreshToken();

            $client->setAccessToken($access_token);

            $gmail_service = $this->gmail_client_service->get_gmail_service();

            // Fetch the user's email
            $user_profile = $gmail_service->users->getProfile('me');
            $user_email = $user_profile->getEmailAddress();

            $token_data = [
                'access_token' => $access_token['access_token'],
                'refresh_token' => $refresh_token,
                'token_expiry' => time() + $access_token['expires_in'],
            ];

            $this->save_tokens_to_file($user_email, $token_data);

            // TODO: create a route to handle oauth success
            return $this->redirectToRoute('invalid_success_route');
        }

        // TODO: create a route to handle oauth error
        return $this->redirectToRoute('invalid_error_route');
    }

    private function save_tokens_to_file(string $user_email, array $token_data): void
    {
        // Use __DIR__ to get the current directory of the this file
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
