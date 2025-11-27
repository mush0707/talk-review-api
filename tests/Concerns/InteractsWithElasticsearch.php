<?php

namespace Tests\Concerns;

use Illuminate\Support\Facades\Http;

trait InteractsWithElasticsearch
{
    protected function elasticHost(): string
    {
        return rtrim(env('ELASTICSEARCH_HOST', 'http://elasticsearch:9200'), '/');
    }

    protected function skipIfElasticDown(): void
    {
        try {
            $ok = Http::timeout(1)->get($this->elasticHost().'/_cluster/health')->ok();
        } catch (\Throwable $e) {
            $ok = false;
        }

        if (!$ok) {
            $this->markTestSkipped('Elasticsearch is not reachable. Start it (docker compose up -d elasticsearch).');
        }
    }

    protected function esDeleteAll(string $index): void
    {
        try {
            Http::timeout(2)->post($this->elasticHost()."/{$index}/_delete_by_query?refresh=true", [
                'query' => ['match_all' => (object)[]],
            ]);
        } catch (\Throwable $e) {
            // ignore
        }
    }

    protected function esRefresh(string $index): void
    {
        try {
            Http::timeout(2)->post($this->elasticHost()."/{$index}/_refresh");
        } catch (\Throwable $e) {
            // ignore
        }
    }
}
