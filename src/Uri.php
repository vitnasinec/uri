<?php

namespace Vitnasinec\Uri;

use Illuminate\Contracts\Routing\UrlGenerator;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class Uri implements Htmlable
{
    protected $request;

    protected $urlGenerator;

    protected string $scheme;

    protected string $host;

    protected $path = '/';

    protected $query = [];

    protected $decode = [
        '%5B' => '[',
        '%5D' => ']',
        '%2C' => ',',
    ];

    /**
     * __construct
     *
     * @param  ?string  $fromString
     * @return void
     */
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

    /**
     * Initialize from string request
     *
     * @return void
     */
    protected function fromString(string $fromString)
    {
        $this->scheme = parse_url($fromString, PHP_URL_SCHEME) ?? $this->request->getScheme();
        $this->host = parse_url($fromString, PHP_URL_HOST) ?? $this->request->getHost();
        $this->path = parse_url($fromString, PHP_URL_PATH) ?? '/';
        parse_str(
            parse_url($fromString, PHP_URL_QUERY) ?? '',
            $this->query
        );
    }

    /**
     * Initialize from current request
     *
     * @return void
     */
    protected function fromRequest()
    {
        $this->scheme = $this->request->getScheme();
        $this->host = $this->request->getHost();
        $this->path = $this->request->path();
        $this->query = $this->request->query();
    }

    /**
     * route
     *
     * @param  string  $name
     * @param  mixed  $params
     * @return $this
     */
    public function route($name, $params = [])
    {
        $this->query = [];
        $this->path = route($name, $params, false);

        return $this;
    }

    /**
     * path
     *
     * @return $this
     */
    public function path(string $path)
    {
        $this->path = $path;

        return $this;
    }

    /**
     * Alias to mergeQuery
     *
     *
     * @return $this
     */
    public function query(array $query)
    {
        return $this->mergeQuery($query);
    }

    /**
     * Merge query
     *
     *
     * @return $this
     */
    public function mergeQuery(array $query, bool $replace = true)
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

    public function mergeMissingQuery(array $query): self
    {
        $this->mergeQuery($query, replace: false);

        return $this;
    }

    /**
     * Replace whole query
     *
     *
     * @return $this
     */
    public function replaceQuery(array $query)
    {
        $this->query = [];

        return $this->mergeQuery($query);
    }

    /**
     * Add or replace single query param
     *
     * @param  string  $key
     * @param  mixed  $value
     * @return $this
     */
    public function addQuery($key, $value)
    {
        Arr::set($this->query, $key, $value);

        return $this;
    }

    /**
     * Remove single query param
     *
     * @param  string  $key
     * @return $this
     */
    public function removeQuery($key)
    {
        Arr::forget($this->query, $key);

        return $this;
    }

    /**
     * Transform bool values to string
     *
     *
     * @return array
     */
    protected function stringifyBoolQueryValues(array $query)
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

    /**
     * Build the query string
     *
     * @return string
     */
    public function buildQuery()
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

    /**
     * Build full url
     *
     * @return string
     */
    public function build()
    {
        $queryString = count($this->query)
            ? "?{$this->buildQuery()}"
            : null;

        return "{$this->scheme}://{$this->host}"
            .Str::of($this->path)->start('/')
            .$queryString;
    }

    /**
     * Htmlable
     *
     * @return string
     */
    public function toHtml()
    {
        return $this->build();
    }

    /**
     * __toString
     *
     * @return string
     */
    public function __toString()
    {
        return $this->build();
    }
}
