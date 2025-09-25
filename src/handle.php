<?php

declare(strict_types=1);

function handle_send_for_post($post_id)
{
    $post = get_post($post_id);

    $configs = get_option('brevo_auto_campaign_config', []);

    $cfg = $configs[$post->post_type] ?? null;

    if (!$cfg || empty($cfg['enabled']) || empty($cfg['listIds']) || empty($cfg['templateId'])) {
        return;
    }

    $fields = get_field_objects($post_id);
    if (!$fields || !is_array($fields)) {
        $fields = [];
    }

    $params = [];
    foreach ($fields as $key => $field) {
        $params[$key] = $field['value'];
    }

    $params['post_title'] = get_the_title(post: $post_id) ?: '';
    $params['post_content'] = get_the_content(post: $post_id) ?: '';
    $params['post_excerpt'] = get_the_excerpt(post: $post_id) ?: '';
    $params['post_url'] = get_permalink(post: $post_id) ?: '';
    $params['post_thumbnail'] = get_the_post_thumbnail_url(post: $post_id) ?: '';

    $brevo = new BrevoAPI();

    $brevo->createCampaign(
        templateId: (int)$cfg['templateId'],
        listIds: array_map('intval', explode(',', $cfg['listIds'])),
        post: $post,
        params: $params,
        sendNow: empty($cfg['draft'])
    );

    error_log("Envoi Brevo pour post {$post_id}, params : " . print_r($params, true));

    // Marquer que l’on a envoyé pour ne pas répéter
    update_post_meta($post_id, '_brevo_sent', 1);
}
