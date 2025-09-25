<?php

declare(strict_types=1);

add_action('transition_post_status', 'maybe_send_on_publish', 20, 3);
function maybe_send_on_publish($new_status, $old_status, $post)
{
    if (wp_is_post_revision($post->ID) || wp_is_post_autosave($post->ID)) {
        return;
    }

    if ($old_status !== 'publish' && $new_status === 'publish') {

        // Si un hook ACF existe, on laisse ce hook gérer l’envoi (fallback)
        if (has_action('acf/save_post')) {
            error_log("acf/save_post existe, on laisse le hook ACF faire l'envoi");
            return;
        }

        // Vérifier que la clé API est configuré pour l’envoi
        $raw_key = get_option('brevo_auto_campaign_APIKEY', '');
        if (trim((string)$raw_key) === '') {
            return;
        }

        handle_send_for_post($post->ID);
    }
}

add_action('acf/save_post', 'handle_acf_save_post_send', 20);
function handle_acf_save_post_send($post_id)
{
    // Vérifier qu’on n’a pas déjà envoyé pour ce post
    if (get_post_meta($post_id, '_brevo_sent', true)) {
        error_log("Publication déjà envoyée sur Brevo");
        return;
    }

    $post = get_post($post_id);
    if (!$post || $post->post_status !== 'publish') {
        // Si le post n’est pas en statut "publish", on ne fait rien
        return;
    }

    // Vérifier que la clé API est configuré pour l’envoi
    $raw_key = get_option('brevo_auto_campaign_APIKEY', '');
    if (trim((string)$raw_key) === '') {
        return;
    }

    handle_send_for_post($post_id);
}

