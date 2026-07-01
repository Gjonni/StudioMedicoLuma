# Piano: Homelab Dashboard su Raspberry Pi 5 (1GB RAM, Raspberry Pi OS Lite)
## Architettura: servizi nativi sull'host + dashboard Laravel in Podman

## Obiettivo
Dashboard web Laravel + SQLite, con login unico, che esegue **azioni
dirette reali** (non solo link) su:
- **Stampa** (CUPS — Canon MG2500 via USB)
- **Scansione** (SANE — stessa MG2500, MFP) → salvataggio automatico
  nello share di rete
- **Samba** (share SMB su disco USB esterno)
- **AdGuard Home** (DNS filtering)

Nessuna interfaccia di amministrazione generica (Webmin escluso): tutta
la gestione quotidiana passa dalla dashboard stessa.

## Sistema operativo: Raspberry Pi OS Lite
- **Nessun ambiente desktop** — headless, gestione solo via SSH
- Footprint di base più basso di Raspberry Pi OS Standard (niente X11,
  niente pacchetti desktop) — è il motivo per cui il budget RAM di base
  è 80-120MB e non più alto
- Tutta l'installazione (servizi nativi + Podman) avviene da riga di
  comando via SSH, nessun passaggio richiede GUI
- Verificare che `raspi-config` non abbia abilitato nulla legato al
  desktop per errore (`sudo raspi-config` → System Options → Boot / Auto
  Login → assicurarsi sia impostato su console, non desktop)

## Perché Podman invece di Docker
Podman è **daemonless** (nessun demone persistente come `dockerd`) — i
container sono processi figli gestiti da `conmon` (uno leggero per
container attivo), invece di un demone sempre in background. Su un
dispositivo con 1GB di RAM è un risparmio concreto:

| Runtime | RAM overhead |
|---|---|
| Docker (dockerd + containerd sempre attivi) | 50-80 MB |
| Podman (nessun demone persistente, solo conmon quando serve) | 5-15 MB |

Installazione su Raspberry Pi OS Lite (Debian-based):
```bash
sudo apt install podman
```

## Decisione architetturale: perché servizi nativi + solo dashboard in container
- **Nessun passthrough USB complesso**: CUPS e SANE hanno accesso
  diretto al device USB della stampante/scanner
- **Meno processi/immagini base**: un solo container invece di 4-5
- CUPS e AdGuard Home comunicano nativamente via rete (IPP / HTTP),
  nessuna differenza se il chiamante è containerizzato o no
- Samba è l'unica eccezione che richiede un bind-mount per lo stato

## RAM Budget stimato (Pi 5, 1GB totale, Raspberry Pi OS Lite + Podman)

| Componente | RAM |
|---|---|
| Raspberry Pi OS Lite (base, headless) | 80-120 MB |
| CUPS + driver Gutenprint (nativo) | 15-25 MB |
| SANE (nativo, on-demand, non è un demone persistente) | ~0 MB idle, 30-60 MB durante scansione |
| Samba — smbd+nmbd (nativo) | 20-40 MB |
| AdGuard Home (nativo) | 60-100 MB |
| Avahi (mDNS/Bonjour, nativo) | 5-10 MB |
| Podman (nessun demone, solo conmon per container attivo) | 5-15 MB |
| Container dashboard (Laravel: PHP-FPM 2 worker + Caddy) | 70-120 MB |
| **Totale idle** | **~255-390 MB** |
| **Totale sotto carico misto** | **~400-580 MB** |

Margine molto ampio su 1GB, ulteriormente migliorato rispetto alla
versione con Docker (risparmio di 40-65MB dal runtime container).

## Setup dei servizi nativi sull'host (via SSH, Raspberry Pi OS Lite)

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

# SANE (scanner)
sudo apt install sane sane-utils
scanimage -L   # verifica rilevamento MG2500

# Samba
sudo apt install samba
sudo smbpasswd -a pi
# configurare /etc/samba/smb.conf con la share sul disco USB montato

# AdGuard Home
curl -s -S -L https://raw.githubusercontent.com/AdguardTeam/AdGuardHome/master/scripts/install.sh | sh -s -- -v

# Podman (solo per la dashboard)
sudo apt install podman
# opzionale, per sintassi docker-compose-like:
sudo apt install podman-compose
```

Verificare `lpinfo -m | grep -i canon` per il driver corretto prima di
procedere con la dashboard.

## Setup del progetto Laravel (in locale, con Composer — non sul Pi)

```bash
composer create-project laravel/laravel homelab-dashboard
cd homelab-dashboard
composer require laravel/breeze --dev
php artisan breeze:install blade
```

`.env`:
```
DB_CONNECTION=sqlite
DB_DATABASE=/percorso/assoluto/database.sqlite
```

## Moduli applicativi Laravel

### 1. Autenticazione
- Laravel Breeze (Blade stack), un solo utente admin, no registrazione
  pubblica in produzione (disabilitare la rotta `/register`)

### 2. Modulo Stampa
`app/Services/CupsService.php`
- Il container Laravel deve avere installato il pacchetto client
  `cups-client` (leggero, solo i binari)
- `getPrinterStatus()`: `lpstat -h <IP_o_localhost_se_host_network> -p <nome_stampante>`
- `getJobs()`: `lpstat -h ... -o`
- `cancelJob($id)`: `cancel -h ... <id>` — azione diretta reale

### 3. Modulo Scansione
`app/Services/ScanService.php`
- SANE non è un demone di rete di default — se il container deve
  invocare la scansione, opzioni:
  1. **Consigliata**: rete host per il container (vedi sezione
     networking sotto) + `saned` attivo sull'host
     (`apt install sane-utils`, abilitare `saned.socket`)
  2. Alternativa: script wrapper sull'host richiamato via piccola
     chiamata HTTP interna
- Output salvato **direttamente nella cartella condivisa da Samba**
  (stesso path host, es. `/mnt/usbdisk/Scansioni`)
- Azione diretta: bottone "Scansiona e salva nello share"

### 4. Modulo Samba
`app/Services/SambaService.php`
- `getDiskUsage()`: `df -h /mnt/usbdisk`
- `getConnectedUsers()`: `smbstatus -b` — richiede bind-mount di
  `/var/lib/samba` in lettura nel container, e il pacchetto
  `samba-common-bin` installato nel container per avere il binario

### 5. Modulo AdGuard Home
`app/Services/AdguardService.php`
- HTTP verso `http://<host_o_localhost>:3000/control/...`
- Login, stats, query log, toggle protezione

### 6. Dashboard principale
- Card riassuntive per i 4 moduli con azioni rapide

## Networking del container dashboard (Podman)
Podman supporta `--network=host` esattamente come Docker
(`network_mode: host` in Compose/podman-compose):

```yaml
# podman-compose.yml
services:
  dashboard:
    build: .
    network_mode: host
    volumes:
      - ./database.sqlite:/var/www/html/database/database.sqlite
      - /mnt/usbdisk:/mnt/usbdisk:ro
      - /var/lib/samba:/var/lib/samba:ro
```

Con `network_mode: host`, dal container `localhost`/`127.0.0.1` punta
correttamente all'host stesso (dove girano CUPS, SANE, Samba, AdGuard),
evitando la complessità di mapping IP/porte tra container e host.

### Nota Podman-specifica: rootless vs root
Podman può girare **rootless** (container eseguiti come utente normale,
non root) — più sicuro, ma con `network_mode: host` in modalità
rootless alcune limitazioni di rete/porte privilegiate (<1024) possono
comportarsi diversamente rispetto a Docker. Se la dashboard deve
esporsi su porta 80 (privilegiata), valutare:
- Eseguire la dashboard su una porta non privilegiata (es. 8080) e
  reindirizzare con un semplice redirect, oppure
- Usare Podman in modalità rootful per questo container specifico
  (`sudo podman-compose up`), accettabile per un homelab personale
  dato che è l'unico container in esecuzione

## Sicurezza
- Middleware `auth` su tutte le rotte tranne login
- CSRF su tutte le azioni POST (default Breeze)
- Policy CUPS (`cupsd.conf`) che permetta operazioni remote (cancel,
  lpstat) dall'IP del container/host locale
- `.env` mai in git, permessi restrittivi
- Nessun Webmin o altra interfaccia di amministrazione generica esposta
  in permanenza — se serve debug occasionale, installare Webmin solo
  temporaneamente e rimuoverlo dopo

## Ordine di sviluppo consigliato per l'agent
1. Setup servizi nativi sul Pi (CUPS, SANE, Samba, AdGuard Home, Avahi)
   — validare ognuno singolarmente PRIMA di scrivere la dashboard
   (`lpstat`, `scanimage -L`, `smbstatus`, curl verso AdGuard API)
2. Installare Podman, verificare `podman run hello-world` funzioni
3. Setup Laravel + Breeze + SQLite in locale, verificare login
4. Modulo AdGuard (il più semplice, solo HTTP)
5. Modulo Stampa: `CupsService` con `lpstat -h`/`cancel -h`
6. Modulo Scansione: decidere `saned` di rete vs script wrapper,
   testare salvataggio diretto nel path condiviso da Samba
7. Modulo Samba: `getDiskUsage()` prima, `getConnectedUsers()` dopo
8. Dashboard aggregata con le 4 card
9. Dockerfile/Containerfile per Laravel (PHP-FPM + Caddy, immagine
   Alpine), `podman-compose.yml` con `network_mode: host`
10. Deploy sul Pi, verificare `podman stats` sotto carico misto
    (stampa+scan+query DNS insieme) prima di considerarlo stabile
