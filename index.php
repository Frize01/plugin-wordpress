<?php
/*
Plugin Name: Véhicule Newsletter Auto
Description: Envoie un mail automatique aux abonnés du plugin Newsletter quand un véhicule est publié.
Version: 1.0
Author: Kévin V.
*/

declare(strict_types=1);

// Sécurité : empêcher appel direct
if (! defined('ABSPATH')) exit;

// Hook : quand un CPT "voiture" est publié
add_action('publish_voiture', 'vn_envoyer_newsletter_v2', 10, 2);

function vn_envoyer_newsletter_v2($post_ID, $post)
{
    $title     = get_the_title($post_ID);
    $permalink = get_permalink($post_ID);
    $price     = null;
    $mileage   = null;
    $image_url = null;

    // Construire le contenu HTML
    $content = "<h2>$title</h2>";
    if ($image_url) $content .= "<img src='$image_url' style='width:100%;height:auto'>";
    if ($price) $content .= "<p>Prix : $price €</p>";
    if ($mileage) $content .= "<p>Kilométrage : $mileage km</p>";
    $content .= "<p><a href='$permalink'>Voir le véhicule</a></p>";



    // Endpoint REST API v2 avec query string
    $url = add_query_arg([
        'client_key'    => MY_API_KEY,
        'client_secret' => TON_CLIENT_SECRET,
    ], site_url('/wp-json/newsletter/v2/newsletters'));

    // Corps de la requête
    $body = [
        'subject' => "Nouveau véhicule : $title",
        'body'    => $content,
        'lists'   => [1], // ID de la liste (1 = liste principale)
        'status'  => 'sending', // "sending" = envoi immédiat
    ];

    // Requête API
    $response = wp_remote_post($url, [
        'headers' => [
            'Content-Type' => 'application/json',
        ],
        'body'       => wp_json_encode($body),
        'timeout'    => 20,
        'sslverify'  => false, // TODO : ⚠️ seulement en local
    ]);

    if (is_wp_error($response)) {
        error_log('[VN] Erreur API Newsletter: ' . $response->get_error_message());
    } else {
        error_log('[VN] Newsletter API V2: ' . wp_remote_retrieve_body($response));
    }
}