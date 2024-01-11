<?php

declare(strict_types=1);

namespace Vitnasinec\Uri;

use Illuminate\Contracts\Routing\UrlGenerator;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class Uri implements Htmlable
{
    protected Request $request;

    protected UrlGenerator $urlGenerator;

    protected string $scheme;

    protected string $host;

    protected string $path = '/';

    /** @var array<string> */
    protected array $query = [];

    /** @var array<string> */
    protected array $decode = [
        '%5B' => '[',
        '%5D' => ']',
        '%2C' => ',',
    ];

    public function __construct(string $fromString = null)
    {
        $this->urlGenerator = app(UrlGenerator::class);
        $this->request = app(Request::class);

        if ($fromString) {
            $this->fromString($fromString);
        } else {
            $this->fromRequest();
        }
    }

    protected function fromString(string $fromString): void
    {
        $this->scheme = parse_url($fromString, PHP_URL_SCHEME) ?: $this->request->getScheme();
        $this->host = parse_url($fromString, PHP_URL_HOST) ?: $this->request->getHost();
        $this->path = parse_url($fromString, PHP_URL_PATH) ?: '/';
        parse_str(
            parse_url($fromString, PHP_URL_QUERY) ?: '',
            $this->query
        );
    }

    protected function fromRequest(): void
    {
        $this->scheme = $this->request->getScheme();
        $this->host = $this->request->getHost();
        $this->path = $this->request->path();
        $this->query = is_array($this->request->query()) ? $this->request->query() : [];
    }

    /**
     * @param  array<mixed>  $params
     */
    public function route(string $name, array $params = []): self
    {
        $this->query = [];
        $this->path = route($name, $params, false);

        return $this;
    }

    public function path(string $path): self
    {
        $this->path = $path;

        return $this;
    }

    /**
     * Alias to mergeQuery
     *
     * @param  array<string|numeric>  $query
     */
    public function query(array $query): self
    {
        return $this->mergeQuery($query);
    }

    /**
     * @param  array<string|numeric>  $query
     */
    public function mergeQuery(array $query, bool $replace = true): self
    {
        $query = Arr::dot($query);

        foreach ($query as $key => $value) {
            if (! $replace && Arr::exists(Arr::dot($this->query), $key)) {
                continue;
            }
            Arr::set($this->query, $key, $value);
        }

        return $this;
    }

    /**
     * @param  array<string|numeric>  $query
     */
    public function mergeMissingQuery(array $query): self
    {
        $this->mergeQuery($query, replace: false);

        return $this;
    }

    /**
     * @param  array<string|numeric>  $query
     */
    public function replaceQuery(array $query): self
    {
        $this->query = [];

        return $this->mergeQuery($query);
    }

    /**
     * @param  string|numeric|bool|null  $value
     */
    public function addQuery(string $key, $value): self
    {
        Arr::set($this->query, $key, $value);

        return $this;
    }

    public function removeQuery(string $key): self
    {
        Arr::forget($this->query, $key);

        return $this;
    }

    /**
     * @param  array<mixed>  $query
     * @return array<string, mixed>
     */
    protected function stringifyBoolQueryValues(array $query): array
    {
        return array_map(function ($value) {
            if (is_array($value)) {
                return $this->stringifyBoolQueryValues($value);
            }

            if ($value === true) {
                return 'true';
            }

            if ($value === false) {
                return 'false';
            }

            return $value;
        }, $query);
    }

    public function buildQuery(): string
    {
        $queryString = http_build_query(
            $this->stringifyBoolQueryValues($this->query)
        );

        $decodedQueryString = str_replace(
            array_keys($this->decode),
            $this->decode,
            $queryString
        );

        return $decodedQueryString;
    }

    public function build(): string
    {
        $queryString = count($this->query)
            ? "?{$this->buildQuery()}"
            : null;

        return "{$this->scheme}://{$this->host}"
            .Str::of($this->path)->start('/')
            .$queryString;
    }

    public function toHtml(): string
    {
        return $this->build();
    }

    public function __toString(): string
    {
        return $this->build();
    }
}
