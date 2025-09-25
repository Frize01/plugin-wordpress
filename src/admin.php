<?php
include_once __DIR__ . '/../helpers/brevo-api.php';
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

function administration_page()
{
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

    $configs = get_option('brevo_auto_campaign_config', []);
    $raw_key = get_option('brevo_auto_campaign_APIKEY', '');
    $api_key = trim((string)$raw_key) !== '' ? '******' : '';

    // Récupère tous les post types publics
    $post_types = get_post_types(['public' => true], 'objects');

    // Instancie BrevoAPI UNE SEULE FOIS
    $brevo = new BrevoAPI();
    $lists = $brevo->getLists();
    $templates = $brevo->getTemplates();
?>
    <style>
        .brevo-box {
            background: #fff;
            border: 1px solid #ccd0d4;
            border-radius: 6px;
            padding: 24px 32px 16px 32px;
            margin-bottom: 32px;
            box-shadow: 0 1px 1px rgba(0, 0, 0, 0.03);
            max-width: 700px;
        }

        .brevo-danger {
            background: #fff0f0;
            border: 1px solid #dc3232;
            color: #dc3232;
            padding: 16px 24px;
            border-radius: 6px;
            margin-bottom: 32px;
            max-width: 700px;
        }

        .brevo-danger button {
            background: #dc3232 !important;
            color: #fff !important;
            border: none;
            margin-top: 1rem !important;
        }

        .brevo-form-table th {
            width: 180px;
            vertical-align: top;
            padding-top: 12px;
        }

        .brevo-form-table td {
            padding-bottom: 18px;
        }

        .brevo-label {
            font-weight: 600;
            margin-bottom: 4px;
            display: inline-block;
        }

        .brevo-select,
        .brevo-input {
            min-width: 220px;
            margin-bottom: 6px;
        }
    </style>
    <div class="wrap">
        <h1 style="margin-bottom:32px;">Configuration Brevo Auto Campaign</h1>

        <div class="brevo-box">
            <form method="post" action="" style="margin-bottom: 0;">
                <h2 style="margin-top:0;">Clé API Brevo</h2>
                <input class="brevo-input" type="text" name="brevo_auto_campaign_APIKEY" value="<?php echo $api_key; ?>" style="width:400px">
                <p style="color:#666;font-size:13px;">Elle sera stockée de manière sécurisée.</p>
                <input type="hidden" name="form_type" value="api_key">
                <?php submit_button('Enregistrer la clé API', 'primary', 'submit', false); ?>
            </form>
        </div>

        <?php if (!empty($api_key)) : ?>
            <div class="brevo-box">
                <form method="post" action="">
                    <h2 style="margin-top:0;">Configuration des Post Types</h2>
                    <table class="form-table brevo-form-table">
                        <?php foreach ($post_types as $pt):
                            $cfg = $configs[$pt->name] ?? ['listIds' => '', 'templateId' => '', 'enabled' => '']; ?>
                            <tr>
                                <th>
                                    <span class="brevo-label"><?php echo $pt->labels->name; ?></span>
                                    <br><span style="color:#888;font-size:12px;"><?php echo $pt->labels->singular_name; ?></span>
                                </th>
                                <td style="display: flex; flex-direction: column; gap:5px;">
                                    <label style="margin-bottom:8px;display:inline-block;">
                                        <input type="checkbox" name="brevo_auto_campaign_config[<?php echo $pt->name; ?>][enabled]" value="1"
                                            <?php checked(!empty($cfg['enabled'])); ?>>
                                        Activer l’envoi automatique d’une campagne lors de la publication
                                    </label>

                                    <label>
                                        <input type="checkbox" name="brevo_auto_campaign_config[<?php echo $pt->name; ?>][draft]" value="1"
                                            <?php checked(!empty($cfg['draft'])); ?>>
                                        Créer la campagne en mode brouillon (l’envoi sera à déclenché manuellement dans Brevo)
                                    </label>
                                    <div style="display: flex; flex-direction: column; gap:1px;">
                                        <label class="brevo-label" for="list_<?php echo $pt->name; ?>">Liste(s) Brevo :</label>
                                        <select class="brevo-select" name="brevo_auto_campaign_config[<?php echo $pt->name; ?>][listIds]" id="list_<?php echo $pt->name; ?>">
                                            <option value="">Sélectionner une liste</option>
                                            <?php
                                            if ($lists && isset($lists['lists'])) {
                                                foreach ($lists['lists'] as $list) {
                                                    $selected = in_array($list['id'], explode(',', $cfg['listIds'])) ? 'selected' : '';
                                                    echo "<option value=\"" . esc_attr($list['id']) . "\" $selected>" . esc_html($list['name']) . "</option>";
                                                }
                                            } else {
                                                echo '<option value="">Aucune liste trouvée</option>';
                                            }
                                            ?>
                                        </select>
                                    </div>
                                    <div style="display: flex; flex-direction: column; gap:1px;">
                                        <label class="brevo-label" for="tpl_<?php echo $pt->name; ?>">Template :</label>
                                        <select class="brevo-select" name="brevo_auto_campaign_config[<?php echo $pt->name; ?>][templateId]" id="tpl_<?php echo $pt->name; ?>">
                                            <option value="">Sélectionner un template</option>
                                            <?php
                                            if ($templates && isset($templates['templates'])) {
                                                foreach ($templates['templates'] as $template) {
                                                    $selected = ($cfg['templateId'] == $template['id']) ? 'selected' : '';
                                                    echo "<option value=\"" . esc_attr($template['id']) . "\" $selected>" . esc_html($template['name']) . "</option>";
                                                }
                                            } else {
                                                echo '<option value="">Aucun template trouvé</option>';
                                            }
                                            ?>
                                        </select>
                                    </div>
                                    <?php
                                    // Liste les champs ACF liés au post type, même sans contenu
                                    $acf_fields = [];
                                    if (function_exists('acf_get_field_groups')) {
                                        $field_groups = acf_get_field_groups(['post_type' => $pt->name]);
                                        if ($field_groups) {
                                            foreach ($field_groups as $group) {
                                                if (isset($group['key']) && function_exists('acf_get_fields')) {
                                                    $fields = acf_get_fields($group['key']);
                                                    if ($fields) {
                                                        foreach ($fields as $field) {
                                                            if (!empty($field['name'])) {
                                                                $acf_fields[$field['name']] = $field['label'];
                                                            }
                                                        }
                                                    }
                                                }
                                            }
                                        }
                                    }
                                    ?>

                                    <p> Titre de la publication :
                                        <code style="background:#f4f4f4;border:1px solid #ddd;padding:2px 6px;margin:2px;display:inline-block;">
                                            {{ params.post_title }}
                                        </code>
                                    </p>

                                    <?php if (post_type_supports($pt->name, 'editor')): ?>
                                        <p> Contenu de la publication (Gutenberg):
                                            <code style="background:#f4f4f4;border:1px solid #ddd;padding:2px 6px;margin:2px;display:inline-block;">
                                                {{ params.post_content }}
                                            </code>
                                        </p>
                                    <?php endif; ?>

                                    <?php if (post_type_supports($pt->name, 'excerpt')): ?>
                                        <p> Résumé de la publication :
                                            <code style="background:#f4f4f4;border:1px solid #ddd;padding:2px 6px;margin:2px;display:inline-block;">
                                                {{ params.post_excerpt }}
                                            </code>
                                        </p>
                                    <?php endif; ?>

                                    <p> URL de la publication :
                                        <code style="background:#f4f4f4;border:1px solid #ddd;padding:2px 6px;margin:2px;display:inline-block;">
                                            {{ params.post_url }}
                                        </code>
                                    </p>

                                    <?php if (post_type_supports($pt->name, 'thumbnail')): ?>
                                        <p> Miniature de la publication :
                                            <code style="background:#f4f4f4;border:1px solid #ddd;padding:2px 6px;margin:2px;display:inline-block;">
                                                {{ params.post_thumbnail }}
                                            </code>
                                        </p>
                                    <?php endif; ?>

                                    <?php

                                    if ($acf_fields) {
                                        echo '<span class="brevo-label">Champs ACF disponibles pour ce post type :</span>';
                                        foreach ($acf_fields as $name => $label) {
                                            echo '<p><code style="background:#f4f4f4;border:1px solid #ddd;padding:2px 6px;margin:2px;display:inline-block;">{{ params.' . esc_html($name) . ' }}</code></p>';
                                        }
                                    } else {
                                        echo '<span style="color:#888;font-size:13px;">Aucun champ ACF trouvé pour ce post type (vérifiez vos groupes de champs ACF).</span>';
                                    }
                                    ?>
                                </td>
                            <?php endforeach; ?>
                    </table>
                    <input type="hidden" name="form_type" value="post_types">
                    <?php submit_button('Enregistrer la configuration', 'primary', 'submit', false); ?>
                </form>
            </div>
        <?php endif; ?>

        <?php if (!empty($api_key)) : ?>
            <div class="brevo-danger">
                <strong>Attention&nbsp;:</strong> Supprimer la clé API désactivera l’envoi automatique.<br>
                <form method="post" action="" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer la clé API ? Cette action est irréversible.');" style="display:inline;">
                    <input type="hidden" name="delete_api_key" value="1">
                    <button type="submit" class="button">Supprimer la clé API</button>
                </form>
            </div>
        <?php endif; ?>
    </div>
<?php
}

add_action('admin_menu', 'administration_add_admin_page');
