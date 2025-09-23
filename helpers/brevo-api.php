<?php
class BrevoAPI
{
    private string $api_key;

    public function __construct()
    {
        include_once __DIR__ . '/secure-storage.php';
        $crypt = new Secure_Storage();
        $this->api_key = $crypt->decrypt(get_option('brevo_auto_campaign_APIKEY', ''));
    }

    /**
     * Récupère les templates d'email depuis l'API Brevo
     * @return void
     */
    public function getTemplate(): null | array
    {
        $response = wp_remote_get(
            'https://api.brevo.com/v3/smtp/templates',
            [
                'headers' => [
                    'accept'  => 'application/json',
                    'api-key' => $this->api_key,
                ],
                'timeout' => 15,
            ]
        );

        if (is_wp_error($response)) {
            return null;
        }

        $body = wp_remote_retrieve_body($response);
        return json_decode($body, true);
    }

    /**
     * Récupère les listes de contacts depuis l'API Brevo
     * @return void
     */
    public function getLists()
    {
        // Appel API pour récupérer les listes
    }

    /**
     * Crée une campagne email
     * @return void
     */
    public function createCampaign(array $data)
    {
        // Appel API pour créer une campagne
    }

    /**
     * Envoie une campagne email
     * @return void
     */
    public function sendCampaign(int $campaignId)
    {
        // Appel API pour envoyer une campagne
    }
}
