<?php
/**
 * Plugin Name: Joinvix Footer Brand & Background
 * Description: Adiciona a logo Joinvix no final da página com seletor de cor de fundo.
 * Version: 1.3
 * Author: Lucas Pedrozo
 * Text Domain: joinvix-footer
 */

if (!defined('ABSPATH')) exit;

class JoinvixFooterPlugin {

    public function __construct() {
        add_action('admin_menu', [$this, 'create_admin_menu']);
        add_action('admin_init', [$this, 'register_settings']);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_color_picker']);
        add_action('wp_footer', [$this, 'display_footer_image'], 100);
    }

    // Carrega o seletor de cores nativo e adiciona o JS inline de forma segura
    public function enqueue_color_picker($hook) {
        if ($hook !== 'settings_page_joinvix-footer-settings') return;
        
        wp_enqueue_style('wp-color-picker');
        wp_enqueue_script('joinvix-color-js', false, ['wp-color-picker'], false, true);
        
        // Melhoria: Usando wp_add_inline_script em vez de imprimir no admin_footer
        $custom_js = 'jQuery(document).ready(function($){ $(".joinvix-color-field").wpColorPicker(); });';
        wp_add_inline_script('joinvix-color-js', $custom_js);
    }

    public function create_admin_menu() {
        add_options_page(
            __('Configurações do Joinvix Footer', 'joinvix-footer'),
            __('Joinvix Brand', 'joinvix-footer'),
            'manage_options',
            'joinvix-footer-settings',
            [$this, 'settings_page_content']
        );
    }

    public function register_settings() {
        // Melhoria: Adicionando funções de sanitização para todos os inputs (Segurança)
        register_setting('joinvix_footer_group', 'joinvix_footer_enabled', 'absint');
        register_setting('joinvix_footer_group', 'joinvix_footer_mode', 'sanitize_text_field');
        register_setting('joinvix_footer_group', 'joinvix_footer_link', 'esc_url_raw');
        register_setting('joinvix_footer_group', 'joinvix_footer_bg_color', 'sanitize_hex_color');
        
        // Melhoria: Removendo hardcode do CDN e transformando em configurações
        register_setting('joinvix_footer_group', 'joinvix_footer_cdn_user', 'sanitize_text_field');
        register_setting('joinvix_footer_group', 'joinvix_footer_cdn_repo', 'sanitize_text_field');
    }

    public function settings_page_content() {
        // Melhoria: Verificação dupla de permissão (Segurança)
        if (!current_user_can('manage_options')) {
            wp_die(__('Você não tem permissão para acessar esta página.', 'joinvix-footer'));
        }
        ?>
        <div class="wrap">
            <h1><?php esc_html_e('Configurações de Marca Joinvix', 'joinvix-footer'); ?></h1>
            <form method="post" action="options.php">
                <?php
                settings_fields('joinvix_footer_group');
                
                $enabled  = get_option('joinvix_footer_enabled', '1');
                $mode     = get_option('joinvix_footer_mode', 'light');
                $link     = get_option('joinvix_footer_link', 'https://www.joinvix.com.br/');
                $bg_color = get_option('joinvix_footer_bg_color', '#ffffff');
                $cdn_user = get_option('joinvix_footer_cdn_user', 'seu-usuario');
                $cdn_repo = get_option('joinvix_footer_cdn_repo', 'seu-repo');
                ?>
                <table class="form-table">
                    <tr>
                        <th scope="row"><?php esc_html_e('Ativar Exibição', 'joinvix-footer'); ?></th>
                        <td>
                            <input type="hidden" name="joinvix_footer_enabled" value="0" />
                            <input type="checkbox" name="joinvix_footer_enabled" value="1" <?php checked('1', $enabled); ?> />
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php esc_html_e('Estilo da Logo', 'joinvix-footer'); ?></th>
                        <td>
                            <label><input type="radio" name="joinvix_footer_mode" value="light" <?php checked('light', $mode); ?> /> <?php esc_html_e('Light Mode', 'joinvix-footer'); ?></label><br>
                            <label><input type="radio" name="joinvix_footer_mode" value="dark" <?php checked('dark', $mode); ?> /> <?php esc_html_e('Dark Mode', 'joinvix-footer'); ?></label>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php esc_html_e('Cor de Fundo do Bloco', 'joinvix-footer'); ?></th>
                        <td>
                            <input type="text" name="joinvix_footer_bg_color" value="<?php echo esc_attr($bg_color); ?>" class="joinvix-color-field" />
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php esc_html_e('Link de Redirecionamento', 'joinvix-footer'); ?></th>
                        <td><input type="url" name="joinvix_footer_link" value="<?php echo esc_attr($link); ?>" class="regular-text" /></td>
                    </tr>
                    <tr>
                        <th scope="row"><?php esc_html_e('Usuário do GitHub (CDN)', 'joinvix-footer'); ?></th>
                        <td><input type="text" name="joinvix_footer_cdn_user" value="<?php echo esc_attr($cdn_user); ?>" class="regular-text" /></td>
                    </tr>
                    <tr>
                        <th scope="row"><?php esc_html_e('Repositório do GitHub (CDN)', 'joinvix-footer'); ?></th>
                        <td><input type="text" name="joinvix_footer_cdn_repo" value="<?php echo esc_attr($cdn_repo); ?>" class="regular-text" /></td>
                    </tr>
                </table>
                <?php submit_button(); ?>
            </form>
        </div>
        <?php
    }

    public function display_footer_image() {
        if (get_option('joinvix_footer_enabled', '1') !== '1') return;

        $mode     = get_option('joinvix_footer_mode', 'light');
        $bg_color = get_option('joinvix_footer_bg_color', '#ffffff');
        $link     = get_option('joinvix_footer_link', 'https://www.joinvix.com.br/');
        $cdn_user = get_option('joinvix_footer_cdn_user', 'seu-usuario');
        $cdn_repo = get_option('joinvix_footer_cdn_repo', 'seu-repo');

        // Impede a renderização se os dados do CDN não estiverem preenchidos corretamente
        if (empty($cdn_user) || empty($cdn_repo) || $cdn_user === 'seu-usuario') {
            return; 
        }

        $file_path = ($mode === 'dark') ? "logo-dark.png" : "logo-light.png";
        
        // urlencode garante que espaços ou caracteres especiais não quebrem a URL do CDN
        $img_url   = "https://cdn.jsdelivr.net/gh/" . urlencode($cdn_user) . "/" . urlencode($cdn_repo) . "@main/{$file_path}";

        ?>
        <div class="joinvix-brand-footer" style="width: 100%; text-align: center; padding: 40px 0; background-color: <?php echo esc_attr($bg_color); ?>; clear: both;">
            <a href="<?php echo esc_url($link); ?>" target="_blank" rel="noopener" style="display: inline-block; border: none;">
                <img src="<?php echo esc_url($img_url); ?>" alt="<?php esc_attr_e('Joinvix Logo', 'joinvix-footer'); ?>" style="max-width: 180px; height: auto; display: block; margin: 0 auto;">
            </a>
        </div>
        <?php
    }
}

new JoinvixFooterPlugin();