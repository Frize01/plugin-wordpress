<?php
/*
Plugin Name: Véhicule Newsletter Auto
Description: Envoie un mail automatique aux abonnés du plugin Newsletter quand un véhicule est publié.
Version: 1.0
Author: Kévin V.
*/

declare(strict_types=1);
if (! defined('ABSPATH')) exit;


// Hook sur publication d’un CPT “voiture”
add_action('publish_voiture', 'vn_envoyer_newsletter_local', 10, 2);

function vn_envoyer_newsletter_local($post_ID, $post) {



}

function administration_add_admin_page() {
 add_submenu_page(
    'options-general.php',
    'Mes options',
    'Mes réglages',
    'manage_options',
    'administration',
    'administration_page'
 );
}

function administration_page() {
 $couleurs_disponibles = array(
    'ffffff' => 'Blanc',
    '000000' => 'Noir',
    'ff0000' => 'Rouge',
    '00ff00' => 'Vert',
    '0000ff' => 'Bleu'
 );

if (isset($_POST['submit'])) {
    update_option('couleur_fond_site', $_POST['fond_couleur']);
 }

 $couleur_actuelle = get_option('couleur_fond_site');
 ?>
 <div class="wrap">
    <h1>Mes options</h1>
    <form method="post" action="">
        <label for="fond_couleur"> : </label>
            <select id="fond_couleur" name="fond_couleur">
             <?php foreach ($couleurs_disponibles as $valeur => $libelle) { ?>
              <option value="<?php echo $valeur; ?>" <?php selected($couleur_actuelle, $valeur); ?>><?php echo $libelle; ?></option>
             <?php } ?>
            </select>
            <input type="submit" name="submit" class="button button-primary" value="Enregistrer" />
    </form>
 </div>
 <?php
}

add_action('admin_menu', 'administration_add_admin_page');

