# Qlacon Esports - Website Profissional

**Website oficial do time profissional Qlacon Esports de Brawl Stars.**

## Visão Geral

Site completo, responsivo e profissional desenvolvido em **PHP puro + HTML5 + CSS3 + JavaScript**.

### Features

- ✅ Design responsivo (mobile, tablet, desktop)
- ✅ Tema claro/escuro com alternância automática
- ✅ Sistema de admin com autenticação por senha
- ✅ Integração com Brawlify API (sincronização de dados de jogadores)
- ✅ Gestão de patrocínios (formulário + painel admin)
- ✅ Newsletter e contato
- ✅ Meta tags SEO + sitemap.xml + robots.txt
- ✅ Servidor MySQL local (banco de dados `novageracao_db`)
- ✅ Animações suaves e efeitos visuais (IntersectionObserver)
- ✅ Compartilhamento em redes sociais (WhatsApp, Twitter, Facebook, LinkedIn, YouTube)

## Estrutura

```
c:\xampp\htdocs\Novageracao\
├── index.php              # Arquivo principal (PHP + HTML + CSS + JS)
├── Qlaconlogo.jpg         # Logo da marca
├── robots.txt             # Arquivo para mecanismos de busca
├── sitemap.xml            # Mapa do site XML
├── .gitignore             # Arquivo para Git
├── README.md              # Este arquivo
└── db_backup_*.sql        # Backups da base de dados
```

## Configuração & Instalação

### Pré-requisitos

- **XAMPP** (Apache + PHP 7.4+ + MySQL)
- **Navegador moderno** (Chrome, Firefox, Safari, Edge)
- Conexão com a internet (para fonts, icons, APIs externas)

### Setup Local

1. **Clone/copie** a pasta para `C:\xampp\htdocs\Novageracao\`
2. **Inicie XAMPP**: Apache e MySQL (painel de controle do XAMPP)
3. **Acesse**: `http://localhost/Novageracao/`

### Configuração do Banco de Dados

O banco `novageracao_db` já existe no servidor local XAMPP. Se precisar restaurar:

```bash
# Terminal XAMPP (ou via phpMyAdmin)
mysql -u root < db_backup_YYYYMMDD_HHMM.sql
```

**Tabelas principais:**
- `jogadores` — Dados dos jogadores
- `patrocinadores_inscritos` — Inscrições de patrocínios
- `newsletter` — E-mails inscritos
- `videos` — Vídeos em destaque
- `sponsors` — Parceiros/patrocinadores
- `valores` — Valores da empresa
- `horarios_treino` — Schedule de treinos
- `estatisticas` — Estatísticas gerais

## Acesso Admin

- **URL**: `http://localhost/Novageracao/?admin=1`
- **Senha**: `0125Qlaconadministracao`

**Painel Admin permite:**
- Visualizar todas as inscrições de patrocínio
- Aprovar/rejeitar inscrições
- Excluir inscrições
- Visualizar estatísticas

## Contato & Redes Sociais

- **E-mail**: contato.QlaconEsports@outlook.com.br
- **Canal YouTube**: https://www.youtube.com/@QlaconEsports

## Deploy para Produção

### Checklist Pré-Lançamento

- [ ] Backup final do banco: `mysqldump -u root novageracao_db > backup_prod.sql`
- [ ] Testar em múltiplos navegadores e dispositivos
- [ ] Validar formulários e endpoints AJAX
- [ ] Verificar links e imagens
- [ ] Configurar domínio + SSL (Let's Encrypt)
- [ ] Atualizar `robots.txt` e `sitemap.xml` com URL real
- [ ] Revisar credenciais de BD (não commitar dados sensíveis)
- [ ] Ativar modo de produção (ajustar error_reporting no PHP)
- [ ] Verificar velocidade com Lighthouse

### Hospedagem Recomendada

- **Hostinger**, **Bluehost**, **SiteGround** (suporte PHP + MySQL)
- Fazer upload via FTP/SFTP
- SSL automático (geralmente incluído)
- Backup automático

### Deploy no InfinityFree (passos rápidos)

- No painel do InfinityFree, crie um banco de dados MySQL e anote: `hostname`, `database`, `username`, `password`.
- No seu projeto local, edite `config.php` (ou crie-o a partir de `config.php.example`) e coloque essas credenciais.
- Faça upload dos arquivos via FTP (use cliente FileZilla). Não suba backups SQL grandes pelo painel; prefira phpMyAdmin.
- Acesse `https://<seu-domínio>.epizy.com/diagnostics.php` para verificar conexão com o BD. O endpoint retorna JSON.
- Lembre que o InfinityFree costuma usar um host diferente de `localhost` e pode bloquear envio de e-mail SMTP — prefira usar uma API de e-mail (Mailgun/SendGrid) se precisar enviar mensagens.

Observações específicas:
- Certifique-se de que `display_errors` esteja desativado em produção (já desativado no topo de `index.php`).
- Se você receber HTML em vez de JSON nas chamadas AJAX, verifique `diagnostics.php` e os logs do painel.

### Variáveis de Ambiente (opcional)

Criar arquivo `.env` (não commitar):
```
DB_HOST=localhost
DB_USER=root
DB_PASS=seu_senha
DB_NAME=novageracao_db
ADMIN_PASS=0125Qlaconadministracao
```

## Segurança

⚠️ **Recomendações Importantes:**

- [ ] Alterar senha de admin após lançamento
- [ ] Usar HTTPS em produção
- [ ] Validar todos os inputs server-side (além do client)
- [ ] Usar prepared statements (ya está implementado)
- [ ] Implementar rate limiting em formulários
- [ ] Fazer backups regulares (diários/semanais)
- [ ] Monitorar logs de erro

## Problemas Comuns

### "Página não carrega"
- Verificar se XAMPP está rodando (Apache + MySQL)
- Testar: `http://localhost/phpmyadmin/`

### Imagens não aparecem
- Verificar caminho de `Qlaconlogo.jpg` (deve estar na mesma pasta de `index.php`)
- Ajustar permissões de arquivo se necessário

### Erro ao enviar formulário
- Conferir se a conexão MySQL está ativa
- Verificar `error_log` do PHP (`C:\xampp\apache\logs\error.log`)

## Performance

- Página carrega em ~1-2s (depende da internet)
- CSS/JS inlined (sem requisições externas para estilo)
- Imagens otimizadas (JPG comprimido)
- Lazy loading ativado para seções

## Licença & Créditos

**Qlacon Esports** © 2025. Todos os direitos reservados.

---

**Última atualização**: 14 de novembro de 2025

Para suporte ou dúvidas: **contato.QlaconEsports@outlook.com.br**
