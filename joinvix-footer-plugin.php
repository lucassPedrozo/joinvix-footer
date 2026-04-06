<?php
/**
 * Plugin Name: Joinvix Footer
 * Description: Adiciona a logo Joinvix no final da página.
 * Version: 2.1 (UI/UX Refined)
 * Author: Lucas Pedrozo
 * Text Domain: joinvix-footer
 */

if (!defined('ABSPATH')) exit;

class JoinvixFooterPlugin {

    const ALLOWED_MODES = ['light', 'dark'];

    public function __construct() {
        add_action('admin_menu', [$this, 'create_admin_menu']);
        add_action('admin_init', [$this, 'register_settings']);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_color_picker']);
        add_action('wp_footer', [$this, 'display_footer_image'], 100);

        // 🔒 Proteção contra alteração indevida do CDN
        add_filter('pre_update_option_joinvix_footer_cdn_user', [$this, 'protect_cdn_update'], 10, 2);
        add_filter('pre_update_option_joinvix_footer_cdn_repo', [$this, 'protect_cdn_update'], 10, 2);
    }

    /* =========================
       🔐 SEGURANÇA
    ========================= */

    private function is_joinvix_email($email) {
        return (bool) preg_match('/@joinvix\.com\.br$/i', $email);
    }

    private function is_allowed_cdn($user, $repo) {
        $whitelist = [
            ['lucassPedrozo', 'joinvix-footer'],
            // Adicione mais se necessário
        ];

        foreach ($whitelist as $allowed) {
            if ($allowed[0] === $user && $allowed[1] === $repo) {
                return true;
            }
        }

        return false;
    }

    public function protect_cdn_update($new_value, $old_value) {

        if (!current_user_can('manage_options')) {
            return $old_value;
        }

        $user = wp_get_current_user();
        $email = $user->user_email;

        if (!$this->is_joinvix_email($email)) {
            error_log('Joinvix: tentativa bloqueada de alteração de CDN por ' . $email);
            return $old_value;
        }

        return $new_value;
    }

    public function sanitize_mode($value) {
        return in_array($value, self::ALLOWED_MODES, true) ? $value : 'light';
    }

    public function sanitize_github($value) {
        $value = sanitize_text_field($value);

        if (!preg_match('/^[a-zA-Z0-9._-]+$/', $value)) {
            return '';
        }

        return $value;
    }

    /* =========================
       ⚙️ ADMIN
    ========================= */

    public function enqueue_color_picker($hook) {
        if ($hook !== 'settings_page_joinvix-footer-settings') return;

        wp_enqueue_style('wp-color-picker');

        wp_register_script('joinvix-admin', '', ['wp-color-picker'], false, true);
        wp_enqueue_script('joinvix-admin');

        wp_add_inline_script('joinvix-admin',
            'jQuery(document).ready(function($){ $(".joinvix-color-field").wpColorPicker(); });'
        );
    }

    public function create_admin_menu() {
        add_options_page(
            __('Configurações Joinvix', 'joinvix-footer'),
            __('Joinvix Footer', 'joinvix-footer'),
            'manage_options',
            'joinvix-footer-settings',
            [$this, 'settings_page_content']
        );
    }

    public function register_settings() {
        register_setting('joinvix_footer_group', 'joinvix_footer_enabled', [
            'sanitize_callback' => 'absint',
            'default' => 1
        ]);

        register_setting('joinvix_footer_group', 'joinvix_footer_mode', [
            'sanitize_callback' => [$this, 'sanitize_mode'],
            'default' => 'light'
        ]);

        register_setting('joinvix_footer_group', 'joinvix_footer_link', [
            'sanitize_callback' => 'esc_url_raw',
            'default' => 'https://www.joinvix.com.br/'
        ]);

        register_setting('joinvix_footer_group', 'joinvix_footer_bg_color', [
            'sanitize_callback' => 'sanitize_hex_color',
            'default' => '#ffffff'
        ]);

        register_setting('joinvix_footer_group', 'joinvix_footer_cdn_user', [
            'sanitize_callback' => [$this, 'sanitize_github'],
            'default' => 'lucassPedrozo'
        ]);

        register_setting('joinvix_footer_group', 'joinvix_footer_cdn_repo', [
            'sanitize_callback' => [$this, 'sanitize_github'],
            'default' => 'joinvix-footer'
        ]);
    }

    public function settings_page_content() {

        if (!current_user_can('manage_options')) {
            wp_die(__('Você não tem permissão.', 'joinvix-footer'));
        }

        $enabled  = get_option('joinvix_footer_enabled', 1);
        $mode     = get_option('joinvix_footer_mode', 'light');
        $link     = get_option('joinvix_footer_link', 'https://www.joinvix.com.br/');
        $bg_color = get_option('joinvix_footer_bg_color', '#ffffff');
        $cdn_user = get_option('joinvix_footer_cdn_user', 'lucassPedrozo');
        $cdn_repo = get_option('joinvix_footer_cdn_repo', 'joinvix-footer');

        ?>

        <div class="wrap">
            <h1>Configurações da Marca Joinvix</h1>
            <p>Personalize a exibição da assinatura no rodapé do seu site.</p>

            <form method="post" action="options.php">
                <?php settings_fields('joinvix_footer_group'); ?>

                <div class="metabox-holder">
                    
                    <div class="postbox" style="max-width: 800px;">
                        <h2 class="hndle" style="padding: 15px; margin: 0; border-bottom: 1px solid #ccd0d4;">
                            <span>Opções de Exibição</span>
                        </h2>
                        <div class="inside" style="padding: 0 15px 15px;">
                            <table class="form-table">
                                <tr>
                                    <th>Ativar Rodapé</th>
                                    <td>
                                        <input type="hidden" name="joinvix_footer_enabled" value="0">
                                        <label>
                                            <input type="checkbox" name="joinvix_footer_enabled" value="1" <?php checked(1, $enabled); ?>>
                                            Habilitar assinatura Joinvix no site
                                        </label>
                                    </td>
                                </tr>

                                <tr>
                                    <th>Modo da Logo</th>
                                    <td>
                                        <label style="margin-right: 15px;">
                                            <input type="radio" name="joinvix_footer_mode" value="light" <?php checked('light', $mode); ?>> Light
                                        </label>
                                        <label>
                                            <input type="radio" name="joinvix_footer_mode" value="dark" <?php checked('dark', $mode); ?>> Dark
                                        </label>
                                    </td>
                                </tr>

                                <tr>
                                    <th>Cor de fundo</th>
                                    <td>
                                        <input type="text" name="joinvix_footer_bg_color" value="<?php echo esc_attr($bg_color); ?>" class="joinvix-color-field">
                                    </td>
                                </tr>

                                <tr>
                                    <th>Link de Destino</th>
                                    <td>
                                        <input type="url" name="joinvix_footer_link" value="<?php echo esc_attr($link); ?>" class="regular-text">
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    <div class="postbox" style="max-width: 800px;">
                        <h2 class="hndle" style="padding: 15px; margin: 0; border-bottom: 1px solid #ccd0d4;">
                            <span>Configurações Avançadas (CDN)</span>
                        </h2>
                        <div class="inside" style="padding: 0 15px 15px;">
                            
                            <div class="notice notice-warning inline" style="margin: 15px 0;">
                                <p><strong>Atenção:</strong> Somente usuários logados com e-mail <code>@joinvix.com.br</code> podem alterar estas opções com sucesso.</p>
                            </div>

                            <table class="form-table">
                                <tr>
                                    <th>Usuário GitHub</th>
                                    <td><input type="text" name="joinvix_footer_cdn_user" value="<?php echo esc_attr($cdn_user); ?>" class="regular-text"></td>
                                </tr>

                                <tr>
                                    <th>Repositório</th>
                                    <td><input type="text" name="joinvix_footer_cdn_repo" value="<?php echo esc_attr($cdn_repo); ?>" class="regular-text"></td>
                                </tr>
                            </table>
                        </div>
                    </div>

                </div> <?php submit_button('Salvar Alterações', 'primary', 'submit', true, ['style' => 'font-size: 14px; padding: 5px 30px;']); ?>
            </form>
        </div>

        <?php
    }

    /* =========================
       🌐 FRONTEND
    ========================= */

    public function display_footer_image() {

        if ((int) get_option('joinvix_footer_enabled', 1) !== 1) return;

        $mode     = $this->sanitize_mode(get_option('joinvix_footer_mode', 'light'));
        $bg_color = sanitize_hex_color(get_option('joinvix_footer_bg_color', '#ffffff'));
        $link     = esc_url(get_option('joinvix_footer_link', 'https://www.joinvix.com.br/'));

        $cdn_user = $this->sanitize_github(get_option('joinvix_footer_cdn_user', 'lucassPedrozo'));
        $cdn_repo = $this->sanitize_github(get_option('joinvix_footer_cdn_repo', 'joinvix-footer'));

        // 🔒 WHITELIST + FALLBACK
        if (!$this->is_allowed_cdn($cdn_user, $cdn_repo)) {
            $cdn_user = 'lucassPedrozo';
            $cdn_repo = 'joinvix-footer';
        }

        $file = ($mode === 'dark') ? 'assets/logo-dark.png' : 'assets/logo-light.png';

        $img_url = sprintf(
            'https://cdn.jsdelivr.net/gh/%s/%s@main/%s',
            rawurlencode($cdn_user),
            rawurlencode($cdn_repo),
            $file
        );
        ?>

        <div style="width:100%; text-align:center; padding: 5px 2.5px; background-color:<?php echo esc_attr($bg_color); ?>; box-sizing: border-box; border-top: 1px solid rgba(0,0,0,0.05);">
            <a href="<?php echo $link; ?>" target="_blank" rel="noopener noreferrer" style="display: inline-block; border: none; text-decoration: none; opacity: 0.85; transition: opacity 0.3s ease-in-out;" onmouseover="this.style.opacity='1'" onmouseout="this.style.opacity='0.85'">
                <img src="<?php echo esc_url($img_url); ?>" alt="Desenvolvido por Joinvix" style="max-width: 250px; width: 100%; height: auto; display: block; margin: 0 auto; object-fit: contain;">
            </a>
        </div>

        <?php
    }
}

new JoinvixFooterPlugin();