# Joinvix Footer

Plugin WordPress para adicionar uma assinatura visual da Joinvix no rodape do site. A exibicao e configurada pelo painel administrativo do WordPress e permite controlar o status do rodape, o modo da logo, a cor de fundo, o link de destino e a origem das imagens via CDN.

## Funcionalidades

- Exibe a logo da Joinvix no final das paginas do site.
- Permite ativar ou desativar a assinatura pelo painel do WordPress.
- Oferece modos de logo `light` e `dark`.
- Permite definir a cor de fundo do bloco do rodape.
- Permite configurar o link de destino da assinatura.
- Carrega as imagens pelo jsDelivr a partir de um repositorio GitHub autorizado.
- Bloqueia alteracoes indevidas das configuracoes de CDN para usuarios fora do dominio `@joinvix.com.br`.
- Aplica fallback automatico para o repositorio padrao caso a origem configurada nao esteja na lista permitida.

## Estrutura

```text
joinvix-footer/
+-- assets/
|   +-- logo-dark.png
|   +-- logo-light.png
+-- joinvix-footer-plugin.php
+-- README.md
```

## Instalacao

1. Copie a pasta do plugin para o diretorio `wp-content/plugins/` da instalacao WordPress.
2. No painel administrativo, acesse `Plugins`.
3. Ative o plugin `Joinvix Footer`.
4. Acesse `Configuracoes > Joinvix Footer` para ajustar as opcoes de exibicao.

## Configuracoes Disponiveis

### Ativar Rodape

Controla se a assinatura da Joinvix sera exibida no frontend do site.

### Modo da Logo

Define qual variacao da logo sera carregada:

- `light`: usa `assets/logo-light.png`.
- `dark`: usa `assets/logo-dark.png`.

### Cor de Fundo

Permite escolher a cor de fundo do bloco do rodape usando o seletor de cores nativo do WordPress.

### Link de Destino

Define a URL acessada quando o usuario clica na logo. Por padrao, o plugin aponta para:

```text
https://www.joinvix.com.br/
```

### Configuracoes Avancadas de CDN

O plugin permite configurar usuario e repositorio do GitHub para carregar os assets pelo jsDelivr. Por seguranca, essa alteracao so e aceita para administradores com e-mail do dominio `@joinvix.com.br`.

Repositorio padrao:

```text
lucassPedrozo/joinvix-footer
```

## Seguranca

O plugin valida e sanitiza os principais campos configuraveis:

- Modo da logo restrito aos valores `light` e `dark`.
- Link tratado com `esc_url_raw`.
- Cor de fundo tratada com `sanitize_hex_color`.
- Usuario e repositorio do GitHub restritos a caracteres seguros.
- Protecao contra alteracao de CDN por usuarios sem e-mail corporativo autorizado.
- Whitelist interna para garantir que os assets sejam carregados apenas de origens permitidas.

## Requisitos

- WordPress com suporte a plugins personalizados.
- Usuario administrador para ativacao e configuracao.
- Acesso ao painel administrativo para alterar as opcoes do plugin.

## Desenvolvimento

O plugin foi implementado em PHP puro, usando APIs nativas do WordPress:

- `add_action`
- `add_filter`
- `register_setting`
- `add_options_page`
- `wp_footer`
- `wp-color-picker`

Nao ha etapa de build ou dependencia externa obrigatoria para desenvolvimento local.

## Hook para portfolio

**Categoria do projeto:** Plugin WordPress

**Breve descricao:** Plugin WordPress que adiciona uma assinatura visual da Joinvix no rodape do site, com painel administrativo para controlar exibicao, tema da logo, cor de fundo, link de destino e origem dos assets via CDN.

**Contexto:** O projeto foi desenvolvido para padronizar a presenca da marca Joinvix em sites WordPress, oferecendo uma forma simples de inserir a assinatura no frontend sem editar temas manualmente. A implementacao tambem inclui validacoes e restricoes para proteger as configuracoes avancadas de CDN.

**Resultado:** Um plugin funcional para WordPress com pagina de configuracao no painel administrativo, exibicao automatica da logo no `wp_footer`, suporte a variacoes `light` e `dark`, personalizacao visual basica e protecao contra alteracoes nao autorizadas na origem dos assets.

**Destaques:**

- Integracao com hooks nativos do WordPress.
- Tela de configuracoes propria dentro do painel administrativo.
- Ativacao e desativacao da assinatura sem alterar codigo.
- Alternancia entre logo clara e escura.
- Personalizacao da cor de fundo com o color picker do WordPress.
- Configuracao do link de destino da assinatura.
- Carregamento de imagens via jsDelivr a partir de repositorio GitHub autorizado.
- Sanitizacao dos campos configuraveis.
- Restricao de alteracao do CDN para usuarios com e-mail `@joinvix.com.br`.
- Estrutura leve, sem frameworks e sem etapa de build.

**Stacks:**

- PHP
- WordPress Plugin API
- WordPress Settings API
- WordPress Hooks
- HTML
- CSS inline
- JavaScript
- jQuery
- wp-color-picker
- jsDelivr CDN

**Imagens:**

- `assets/[project_images]/img1.jpg`
- `assets/[project_images]/img2.jpg`
