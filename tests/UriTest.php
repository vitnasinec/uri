<?php

namespace Vitnasinec\Uri\Tests;

use Illuminate\Http\Request;
use Orchestra\Testbench\TestCase;

class UriTest extends TestCase
{
    protected $request;

    protected function setUp(): void
    {
        parent::setUp();

        $this->request = Request::create('https://domain-name.com/category/product?filter[foo]=bar&sort=-baz');
        app()->instance('request', $this->request);
    }

    /** @test */
    public function it_builds_uri_from_request()
    {
        $this->assertEquals(
            'filter[foo]=bar&sort=-baz',
            uri()->buildQuery()
        );
    }

    /** @test */
    public function it_replaces_whole_query()
    {
        $this->assertEquals(
            'foo=bar',
            uri()->replaceQuery(['foo' => 'bar'])->buildQuery()
        );
    }

    /** @test */
    public function it_adds_query_param()
    {
        $this->assertEquals(
            'filter[foo]=bar&filter[other]=next&sort=-baz',
            uri()->addQuery('filter.other', 'next')->buildQuery()
        );
    }

    /** @test */
    public function it_merges_query_param()
    {
        $this->assertEquals(
            'filter[foo]=changed&filter[other]=next&sort=-baz',
            uri()->mergeQuery(['filter' => ['foo' => 'changed', 'other' => 'next']])->buildQuery()
        );
    }

    /** @test */
    public function it_merges_query_param_using_dot_notation()
    {
        $this->assertEquals(
            'filter[foo]=changed&filter[other]=next&sort=-baz',
            uri()->mergeQuery(['filter.foo' => 'changed', 'filter.other' => 'next'])->buildQuery()
        );
    }

    /** @test */
    public function it_replaces_single_query_param()
    {
        $this->assertEquals(
            'filter[foo]=other&sort=-baz',
            uri()->addQuery('filter.foo', 'other')->buildQuery()
        );
    }

    /** @test */
    public function it_removes_query_param()
    {
        $this->assertEquals(
            'sort=-baz',
            uri()->removeQuery('filter.foo')->buildQuery()
        );
    }
}
