<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Autocomplete degli indirizzi di consegna per la webapp.
 *
 * Il servizio di geocoding viene contattato SOLO da qui: la webapp chiama
 * /api/geocode e non parla mai direttamente con il provider esterno. Questo
 * permette di (a) sostituire il provider senza aggiornare la webapp, (b)
 * mettere in cache le risposte, (c) non esporre dettagli/chiavi al client.
 *
 * Provider attuale: **Photon** (https://photon.komoot.io), geocoder open
 * source su dati OpenStreetMap: gratuito, senza API key e con supporto
 * nativo al "search-as-you-type" (a differenza di Nominatim, il cui policy
 * vieta esplicitamente l'autocomplete). Non è un servizio Google: i dati
 * sono OpenStreetMap (licenza ODbL → attribuzione "© OpenStreetMap").
 *
 * GET /api/geocode?q=via+roma+1+milano&limit=5
 */
class GeocodeController extends Controller
{
    /** Endpoint di ricerca del provider (server pubblico demo di Photon). */
    private const PROVIDER_URL = 'https://photon.komoot.io/api/';

    /** Sotto questa soglia la ricerca non parte (query troppo generica). */
    private const MIN_QUERY_LENGTH = 4;

    /** Numero massimo di suggerimenti restituibili. */
    private const MAX_RESULTS = 8;

    /**
     * Gli indirizzi cambiano raramente: la cache evita di ripetere le stesse
     * ricerche (policy dei servizi pubblici: "results must be cached").
     */
    private const CACHE_TTL_HOURS = 24;

    /**
     * Suggerimenti di indirizzo per il campo di ricerca della webapp.
     *
     * Risponde sempre 200 con un array `results` (eventualmente vuoto): se il
     * provider non è raggiungibile la webapp degrada in modo silenzioso,
     * lasciando l'inserimento manuale dell'indirizzo. Il flag `available`
     * distingue "nessun risultato" da "servizio non disponibile".
     *
     * GET /api/geocode
     */
    public function search(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'q'     => ['required', 'string', 'min:' . self::MIN_QUERY_LENGTH, 'max:150'],
            'limit' => ['nullable', 'integer', 'between:1,' . self::MAX_RESULTS],
        ], [
            'q.required' => 'Indica un indirizzo da cercare.',
            'q.min'      => 'Digita almeno :min caratteri per cercare l\'indirizzo.',
            'q.max'      => 'Indirizzo troppo lungo.',
            'limit.between' => 'Numero di suggerimenti non valido.',
        ]);

        $query = trim($validated['q']);
        $limit = (int) ($validated['limit'] ?? 5);
        $cacheKey = $this->cacheKey($query, $limit);

        // Ricerca identica già fatta di recente (es. mentre l'utente digita)
        $cached = Cache::get($cacheKey);
        if (is_array($cached)) {
            return $this->respond($cached, true);
        }

        $results = $this->fetchFromProvider($query, $limit);

        if ($results === null) {
            // Provider non disponibile: nessun risultato e nessuna cache
            return $this->respond([], false);
        }

        Cache::put($cacheKey, $results, now()->addHours(self::CACHE_TTL_HOURS));

        return $this->respond($results, true);
    }

    /**
     * Risposta JSON nel formato atteso dalla webapp.
     *
     * @param array<int, array<string, mixed>> $results
     */
    private function respond(array $results, bool $available): JsonResponse
    {
        return response()->json([
            'results'     => $results,
            'available'   => $available,
            'provider'    => 'photon',
            'attribution' => '© OpenStreetMap contributors',
        ]);
    }

    /**
     * Chiama il provider e normalizza i risultati.
     *
     * @return array<int, array<string, mixed>>|null null se il provider non è
     *                                              raggiungibile o risponde con errore
     */
    private function fetchFromProvider(string $query, int $limit): ?array
    {
        try {
            $response = Http::withHeaders([
                    // User-Agent identificativo: richiesto dalle policy dei
                    // servizi OSM (gli UA di default delle librerie non bastano).
                    'User-Agent' => sprintf('%s (webapp ordini)', config('app.name', 'Shara Light')),
                ])
                ->acceptJson()
                ->timeout(6)
                ->get(self::PROVIDER_URL, [
                    'q'     => $query,
                    'limit' => $limit,
                ]);
        } catch (ConnectionException $e) {
            Log::warning('Geocoding non raggiungibile', ['message' => $e->getMessage()]);

            return null;
        }

        if (! $response->successful()) {
            Log::warning('Geocoding ha risposto con errore', ['status' => $response->status()]);

            return null;
        }

        return collect($response->json('features', []))
            ->map(fn ($feature) => $this->mapFeature(is_array($feature) ? $feature : []))
            ->filter()
            ->values()
            ->all();
    }

    /**
     * Converte una feature GeoJSON di Photon nel formato compatto usato dalla
     * webapp: etichetta leggibile + coordinate decimali (lat/lng).
     *
     * @param array<string, mixed> $feature
     * @return array<string, mixed>|null null se mancano le coordinate
     */
    private function mapFeature(array $feature): ?array
    {
        $coordinates = $feature['geometry']['coordinates'] ?? [];
        if (! is_array($coordinates) || count($coordinates) < 2) {
            return null;
        }

        // GeoJSON: [longitudine, latitudine]
        $lng = (float) $coordinates[0];
        $lat = (float) $coordinates[1];

        if ($lat < -90 || $lat > 90 || $lng < -180 || $lng > 180) {
            return null;
        }

        $properties = is_array($feature['properties'] ?? null) ? $feature['properties'] : [];

        return [
            'label'     => $this->formatLabel($properties),
            'name'      => (string) ($properties['name'] ?? ''),
            'postcode'  => (string) ($properties['postcode'] ?? ''),
            'city'      => (string) ($properties['city'] ?? ($properties['district'] ?? ($properties['county'] ?? ''))),
            'country'   => (string) ($properties['country'] ?? ''),
            'lat'       => round($lat, 7),
            'lng'       => round($lng, 7),
        ];
    }

    /**
     * Etichetta leggibile del suggerimento: "Via Roma 1, 20121 Milano".
     * Per i punti di interesse senza via si usa il nome del luogo.
     *
     * @param array<string, mixed> $properties
     */
    private function formatLabel(array $properties): string
    {
        $street = trim(($properties['street'] ?? '') . ' ' . ($properties['housenumber'] ?? ''));
        $city = trim(($properties['postcode'] ?? '') . ' ' . ($properties['city'] ?? ''));

        $parts = array_filter([$street !== '' ? $street : (string) ($properties['name'] ?? ''), $city]);

        if ($parts === []) {
            $parts = array_filter([(string) ($properties['name'] ?? ''), (string) ($properties['country'] ?? '')]);
        }

        return implode(', ', $parts);
    }

    /**
     * Chiave di cache: normalizza la query (minuscole, spazi compattati) così
     * che "Via Roma 1  Milano" e "via roma 1 milano" condividano la voce.
     */
    private function cacheKey(string $query, int $limit): string
    {
        $normalized = mb_strtolower(preg_replace('/\s+/u', ' ', trim($query)) ?? $query);

        return 'geocode:' . $limit . ':' . md5($normalized);
    }
}
