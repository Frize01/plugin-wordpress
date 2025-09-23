<?php
/*
Plugin Name: Véhicule Newsletter Auto
Description: Envoie un mail automatique aux abonnés du plugin Newsletter quand un véhicule est publié.
Version: 1.0
Author: Kévin V.
*/

declare(strict_types=1);

use Random\Engine\Secure;

if (! defined('ABSPATH')) exit;
function administration_add_admin_page()
{
    add_submenu_page(
        'options-general.php',
        'Brevo Auto Campaign',
        'Brevo Auto Campaign',
        'manage_options',
        'brevo_auto_campaign',
        'administration_page',
    );
}

include_once __DIR__ . '/helpers/secure-storage.php';
$crypt = new Secure_Storage();

if (isset($_POST['delete_api_key'])) {
    update_option('brevo_auto_campaign_APIKEY', '');
}

if (isset($_POST['submit'])) {
    if (isset($_POST['brevo_auto_campaign_APIKEY']) && $_POST['brevo_auto_campaign_APIKEY'] !== '******') {
        update_option('brevo_auto_campaign_APIKEY', $crypt->encrypt($_POST['brevo_auto_campaign_APIKEY']));
    }
    if (isset($_POST['brevo_auto_campaign_config'])) {
        update_option('brevo_auto_campaign_config', $_POST['brevo_auto_campaign_config']);
    }
}

function administration_page()
{
    $configs = get_option('brevo_auto_campaign_config', []);
    $raw_key = get_option('brevo_auto_campaign_APIKEY', '');
    $api_key = trim((string)$raw_key) !== '' ? '******' : '';


    // Récupère tous les post types publics
    $post_types = get_post_types(['public' => true], 'objects');
?>
    <div class="wrap">
        <h1>Configuration Brevo Auto Campaign</h1>

        <!-- Formulaire Clé API -->
        <form method="post" action="" style="margin-bottom: 1em;">
            <h2>Clé API Brevo</h2>
            <input type="text" name="brevo_auto_campaign_APIKEY" value="<?php echo $api_key;?>" style="width:400px">
            <p>Elle sera stockée de manière sécurisée.</p>
            <input type="hidden" name="form_type" value="api_key">
            <?php submit_button('Enregistrer la clé API'); ?>
        </form>

        <?php if (!empty($api_key)) : ?>
            <!-- Bouton danger pour supprimer la clé -->
            <form method="post" action="" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer la clé API ? Cette action est irréversible.');" style="margin-bottom:2em;">
                <input type="hidden" name="delete_api_key" value="1">
                <button type="submit" class="button button-danger" style="background:#dc3232;color:#fff;border:none;">
                    Supprimer la clé API
                </button>
            </form>
        <?php endif; ?>

        <?php if (!empty($api_key)) : ?>
            <!-- Formulaire Post Types -->
            <form method="post" action="">
                <h2>Configuration des Post Types</h2>
                <table class="form-table">
                    <?php foreach ($post_types as $pt):
                        $cfg = $configs[$pt->name] ?? ['listIds' => '', 'templateId' => '', 'enabled' => '']; ?>
                        <tr>
                            <th><?php echo $pt->labels->name; ?> (<?php echo $pt->labels->singular_name; ?>)</th>
                            <td>
                                <label>
                                    <input type="checkbox" name="brevo_auto_campaign_config[<?php echo $pt->name; ?>][enabled]" value="1"
                                        <?php checked(!empty($cfg['enabled'])); ?>>
                                    Activer
                                </label>
                                <br>
                                Liste(s) Brevo ID :
                                <input type="text" name="brevo_auto_campaign_config[<?php echo $pt->name; ?>][listIds]" value="<?php echo esc_attr($cfg['listIds']); ?>" placeholder="ex: 123,456">
                                <br>
                                Template ID :
                                <input type="text" name="brevo_auto_campaign_config[<?php echo $pt->name; ?>][templateId]" value="<?php echo esc_attr($cfg['templateId']); ?>">
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </table>
                <input type="hidden" name="form_type" value="post_types">
                <?php submit_button('Enregistrer la configuration'); ?>
            </form>
        <?php endif; ?>
    </div>
<?php
}

add_action('admin_menu', 'administration_add_admin_page');
