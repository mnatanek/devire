# Devire — System wyników badań

Aplikacja umożliwiająca import wyników badań laboratoryjnych z pliku CSV oraz przeglądanie wyników przez pacjentów po zalogowaniu.

## Struktura projektu

```
devire/
├── backend/    Laravel 13 — REST API
├── frontend/   Vue 3 — interfejs użytkownika
└── docker-compose.yml
```

## Wymagania

- Docker + Docker Compose
- PHP 8.4 + Composer (do lokalnego dev bez Dockera)
- Node.js 20+ (do lokalnego dev frontendu)

---

> **Uwaga:** oba tryby uruchomienia używają portów 80 i 3306 — nie można ich uruchamiać jednocześnie.

## Tryb 1 — Docker (pełny stack)

```bash
cp backend/.env.example backend/.env
# uzupełnij APP_KEY w backend/.env

docker compose up --build

# Uruchom migracje
docker compose exec backend php artisan migrate
```

Aplikacja dostępna pod `http://localhost`. MySQL dostępny na porcie `3306`.

---

## Tryb 2 — Lokalny development (Sail + Vite)

**Backend (Laravel Sail):**
```bash
cd backend
sail up -d
sail artisan migrate
```

**Frontend (Vite dev server):**
```bash
cd frontend
npm install
npm run dev
```

Frontend dostępny pod `http://localhost:5173`, API proxy do `http://localhost`.

---

## Import danych

**Przez Sail:**
```bash
sail artisan app:import-results storage/app/results.csv
```

**Przez Docker:**
```bash
docker compose exec backend php artisan app:import-results storage/app/results.csv
```

Format pliku CSV (separator `;`):

| patientId | patientName | patientSurname | patientSex | patientBirthDate | orderId | testName | testValue | testReference |
|-----------|-------------|----------------|------------|------------------|---------|----------|-----------|---------------|

Logi importu zapisywane do `storage/logs/import.log`.

---

## API

### `POST /api/login`

```json
{
    "login": "PiotrKowalski",
    "password": "1983-04-12"
}
```

Zwraca token JWT:
```json
{ "token": "eyJ..." }
```

### `GET /api/results`

Nagłówek: `Authorization: Bearer {token}`

Zwraca dane pacjenta i wyniki badań:
```json
{
    "patient": {
        "id": 1,
        "name": "Piotr",
        "surname": "Kowalski",
        "sex": "male",
        "birthDate": "1983-04-12"
    },
    "orders": [
        {
            "orderId": "1",
            "results": [
                { "name": "Glukoza", "value": "5.5", "reference": "4.0-6.0" }
            ]
        }
    ]
}
```

---

## Testy

```bash
cd backend
php artisan test
```

---

## Konfiguracja JWT

Czas wygaśnięcia tokenu kontroluje zmienna `JWT_TTL` w `backend/.env` (wartość w minutach, domyślnie 60):

```env
JWT_TTL=60
```

Po zmianie wyczyść cache konfiguracji:

```bash
php artisan config:clear
```

---

## Git hooks

Repozytorium zawiera hook `pre-commit` który przed każdym commitem uruchamia PHPStan, testy i build frontendu.

Po sklonowaniu repo aktywuj jednorazowo:

```bash
git config core.hooksPath .githooks
```

---

## CI/CD (GitLab)

Pipeline (`.gitlab-ci.yml`) uruchamia:
1. **test** — PHPStan + testy jednostkowe i integracyjne backendu
2. **build** — budowanie frontendu Vue
3. **docker** — budowanie i push obrazów Docker do GitLab Container Registry (tylko na gałęzi `main`)
