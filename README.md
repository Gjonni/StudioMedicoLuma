# Homelab Dashboard — Studio Medico Luma

Dashboard Laravel con login unico e permessi per ruolo, che esegue **azioni dirette reali** su servizi nativi di un Raspberry Pi 5 (Raspberry Pi OS Lite):

- **Stampa** (CUPS)
- **Scansione** (SANE) → salvataggio automatico nello share Samba
- **Samba** (share SMB su disco USB esterno)
- **AdGuard Home** (DNS filtering)
- **Calendario** con sincronizzazione opzionale verso Google Calendar

Nessuna interfaccia di amministrazione generica: tutta la gestione quotidiana passa dalla dashboard stessa. Il piano architetturale completo è in [piano-homelab-dashboard.md](piano-homelab-dashboard.md).

## Setup dei servizi nativi sul Raspberry Pi (via SSH)

Questi passi vanno eseguiti manualmente sul Pi, prima del deploy della dashboard:

```bash
sudo apt update && sudo apt full-upgrade

# CUPS + driver
sudo apt install cups printer-driver-gutenprint
sudo usermod -aG lpadmin $USER
sudo cupsctl --remote-any
sudo systemctl restart cups

# Avahi (discovery Bonjour per macOS)
sudo apt install avahi-daemon
sudo systemctl restart avahi-daemon

# SANE (scanner) + saned di rete per il container
sudo apt install sane sane-utils
sudo systemctl enable --now saned.socket
scanimage -L   # verifica rilevamento stampante/MFP

# Samba
sudo apt install samba
sudo smbpasswd -a pi
# configura /etc/samba/smb.conf con la share sul disco USB montato

# AdGuard Home
curl -s -S -L https://raw.githubusercontent.com/AdguardTeam/AdGuardHome/master/scripts/install.sh | sh -s -- -v

# Podman (solo per la dashboard)
sudo apt install podman podman-compose
```

Verifica `lpinfo -m | grep -i canon` per il driver corretto prima di procedere.

## Deploy della dashboard

```bash
git clone <repo> homelab-dashboard
cd homelab-dashboard
cp .env.example .env
# modifica .env: APP_KEY, ADMIN_NAME/ADMIN_EMAIL/ADMIN_PASSWORD, GOOGLE_CLIENT_ID/SECRET (opzionali)

podman-compose up -d --build
```

Il container gira in `network_mode: host` insieme ai servizi nativi (stesso motivo per cui `localhost` funziona sia per CUPS che per AdGuard). Al primo avvio, l'entrypoint esegue automaticamente le migrazioni e il seeder, creando l'utente amministratore definito in `.env`.

Verifica sotto carico misto (stampa + scansione + query DNS insieme) con `podman stats` prima di considerare il deploy stabile.

## Sviluppo locale

```bash
composer install
npm install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
npm run build   # o `npm run dev` per hot-reload
php artisan serve
```

Credenziali admin di default (sovrascrivibili in `.env` prima del seed): `ADMIN_EMAIL` / `ADMIN_PASSWORD`.

## Utenti, ruoli e permessi

Nessuna registrazione pubblica: gli utenti si creano solo da **Impostazioni → Utenti** (visibile al ruolo `admin`). Ogni modulo ha un permesso dedicato (`modulo-stampa`, `modulo-scansione`, `modulo-samba`, `modulo-adguard`, `modulo-calendario`); i ruoli in **Impostazioni → Ruoli e permessi** raggruppano questi permessi. Le sezioni non autorizzate spariscono da dashboard e navigazione.

## Configurazione moduli

I parametri di CUPS, SANE, Samba e AdGuard sono editabili da **Impostazioni → Configurazione moduli** (basati su [spatie/laravel-settings](https://github.com/spatie/laravel-settings)), senza dover modificare `.env` via SSH dopo il primo deploy.

## Google Calendar

In **Impostazioni** non richiesto: ogni utente collega il proprio account da **Calendario → Collega Google Calendar**. Richiede `GOOGLE_CLIENT_ID`/`GOOGLE_CLIENT_SECRET` in `.env` (credenziali OAuth "Web application" da [Google Cloud Console](https://console.cloud.google.com/apis/credentials), redirect URI `https://<host>/google-calendar/callback`, scope `https://www.googleapis.com/auth/calendar`).

## Attività

**Impostazioni → Attività** mostra il log delle azioni dirette (scansioni, annullamenti di stampa, toggle DNS) tramite [spatie/laravel-activitylog](https://github.com/spatie/laravel-activitylog).

## CI/CD

Il workflow [.github/workflows/build-push.yml](.github/workflows/build-push.yml) costruisce l'immagine multi-arch (amd64 + arm64) dal `Containerfile` e la pubblica su `registry.filice.eu/StudioMedicoLuma/homelab-dashboard`, firmata con Cosign keyless su `main`. Richiede i secrets `HARBOR_USERNAME`/`HARBOR_PASSWORD` nel repository GitHub.
