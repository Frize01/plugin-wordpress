<?php

declare(strict_types=1);
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
    public function getTemplates(): null | array
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
    public function getLists(): null | array
    {
        $response = wp_remote_get(
            'https://api.brevo.com/v3/contacts/lists',
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
     * Crée une campagne email
     * @return mixed (array|null) La réponse de l'API ou null en cas d'erreur
     */
    public function createCampaign(int $templateId, array $listIds, WP_Post $post, array | null $params, $sendNow): mixed
    {

        $tpl = $this->TemplateInformation($templateId);
        if ($tpl) {
            if (isset($tpl['sender']['id'])) {
                $senderId = $tpl['sender']['id'];
            }
            if (isset($tpl['name'])) {
                $name = $tpl['name'];
            }
            if (isset($tpl['subject'])) {
                $subject = $tpl['subject'];
            }
        }

        // Vérification si le tableau params est vide
        if (empty($params)) {
            error_log('Aucun paramètre ACF valide trouvé.');
        } else {
            error_log('Paramètres ACF envoyés : ' . print_r($params, true));
        }


        $body = [
            'sender'     => ['id' => $senderId],
            'recipients' => ['listIds' => $listIds],
            'params'     => $params,
            'templateId' => $templateId,
            'name'       => $name,
            'subject'    => $subject,
        ];

        $response = wp_remote_post(
            'https://api.brevo.com/v3/emailCampaigns',
            [
                'headers' => [
                    'accept'       => 'application/json',
                    'api-key'      => $this->api_key,
                    'content-type' => 'application/json',
                ],
                'body'    => wp_json_encode($body),
                'timeout' => 15,
            ]
        );

        if (is_wp_error($response)) {
            error_log('Brevo API error: ' . $response->get_error_message());
            return null;
        }

        $body = wp_remote_retrieve_body($response);
        error_log('Brevo API response: ' . $body);

        $resp = json_decode($body, true);

        if ($sendNow && isset($resp['id'])) {
            $this->sendCampaign($resp['id']);
        }

        return $resp;
    }

    /**
     * Envoie une campagne email
     * @return void
     */
    public function sendCampaign(int $campaignId)
    {
        // Appel API pour envoyer une campagne
        $response = wp_remote_post(
            'https://api.brevo.com/v3/emailCampaigns/' . intval($campaignId) . '/sendNow',
            [
                'headers' => [
                    'accept'  => 'application/json',
                    'api-key' => $this->api_key,
                ],
                'timeout' => 15,
            ]
        );
    }

    // Méthode pour récupérer les infos du template
    public function TemplateInformation(int $templateId)
    {
        $response = wp_remote_get(
            'https://api.brevo.com/v3/smtp/templates/' . intval($templateId),
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
}
