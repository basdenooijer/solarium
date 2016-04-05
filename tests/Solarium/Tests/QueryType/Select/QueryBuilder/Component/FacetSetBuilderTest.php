<?php
/**
 * Copyright 2016 Bas de Nooijer. All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions are met:
 *
 * 1. Redistributions of source code must retain the above copyright notice,
 *    this list of conditions and the following disclaimer.
 *
 * 2. Redistributions in binary form must reproduce the above copyright notice,
 *    this listof conditions and the following disclaimer in the documentation
 *    and/or other materials provided with the distribution.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDER AND CONTRIBUTORS "AS IS"
 * AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE
 * IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE
 * ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT HOLDER OR CONTRIBUTORS BE
 * LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR
 * CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF
 * SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS
 * INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN
 * CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE)
 * ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
 * POSSIBILITY OF SUCH DAMAGE.
 *
 * The views and conclusions contained in the software and documentation are
 * those of the authors and should not be interpreted as representing official
 * policies, either expressed or implied, of the copyright holder.
 *
 * @copyright Copyright 2016 Bas de Nooijer <solarium@raspberry.nl>
 * @license http://github.com/basdenooijer/solarium/raw/master/COPYING
 *
 * @link http://www.solarium-project.org/
 */

namespace Solarium\Tests\QueryType\Select\QueryBuilder\Component;

use Solarium\Core\Client\Request;
use Solarium\QueryType\Select\Query\Component\Facet\Field;
use Solarium\QueryType\Select\Query\Query;
use Solarium\QueryType\Select\QueryBuilder\Component\FacetSet as FacetSetBuilder;

class FacetSetBuilderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Request
     */
    protected $request;

    /**
     * @var FacetSetBuilder
     */
    protected $builder;

    /**
     * @var Query
     */
    protected $query;

    public function setUp()
    {
        $this->query = new Query;
        $this->request = new Request();
        $this->builder = new FacetSetBuilder();
    }

    public function testGlobalFacetParams()
    {
        $this->request->addParam('facet', 'true');
        $this->request->addParam('facet.sort', 'count');
        $this->request->addParam('facet.contains', 'test');
        $this->request->addParam('facet.contains.ignoreCase', 'true');
        $this->request->addParam('facet.missing', 'false');
        $this->request->addParam('facet.mincount', 10);
        $this->request->addParam('facet.limit', 25);

        $this->builder->buildQuery($this->query, $this->request);

        $this->assertEquals('count', $this->query->getFacetSet()->getSort());
        $this->assertEquals('test', $this->query->getFacetSet()->getContains());
        $this->assertEquals(true, $this->query->getFacetSet()->getContainsIgnoreCase());
        $this->assertEquals(false, $this->query->getFacetSet()->getMissing());
        $this->assertEquals(10, $this->query->getFacetSet()->getMinCount());
        $this->assertEquals(25, $this->query->getFacetSet()->getLimit());
    }

    public function testFieldFacet()
    {
        $this->request->addParam('facet', 'true');
        $this->request->addParam('facet.field', 'brand');
        $this->request->addParam('facet.limit', 25);

        $this->builder->buildQuery($this->query, $this->request);

        $facets = $this->query->getFacetSet()->getFacets();
        $this->assertCount(1, $facets);
        $facet = current($facets);

        $this->assertInstanceOf('Solarium\QueryType\Select\Query\Component\Facet\Field', $facet);
        $this->assertEquals('brand', $facet->getField());
        $this->assertEquals(25, $this->query->getFacetSet()->getLimit());
    }

    public function testComplexFieldFacets()
    {
        $this->request->addParam('facet', 'true');
        $this->request->addParam('facet.field', 'brand');
        $this->request->addParam('facet.field', 'category');
        $this->request->addParam('facet.limit', 25);
        $this->request->addParam('f.category.facet.limit', 10);
        $this->request->addParam('f.category.facet.sort', 'index');
        $this->request->addParam('f.category.facet.prefix', 'a');
        $this->request->addParam('f.category.facet.contains', 'z');
        $this->request->addParam('f.category.facet.mincount', 10);

        $this->builder->buildQuery($this->query, $this->request);

        $facets = $this->query->getFacetSet()->getFacets();
        $this->assertEquals(25, $this->query->getFacetSet()->getLimit());
        $this->assertCount(2, $facets);

        $facet = current($facets);

        $this->assertInstanceOf('Solarium\QueryType\Select\Query\Component\Facet\Field', $facet);
        $this->assertEquals('brand', $facet->getField());

        $facet = next($facets);

        $this->assertInstanceOf('Solarium\QueryType\Select\Query\Component\Facet\Field', $facet);
        $this->assertEquals('category', $facet->getField());
        $this->assertEquals(10, $facet->getLimit());
        $this->assertEquals('index', $facet->getSort());
        $this->assertEquals('a', $facet->getPrefix());
        $this->assertEquals('z', $facet->getContains());
        $this->assertEquals(10, $facet->getMinCount());
    }

    public function testQueryFacets()
    {
        $this->request->addParam('facet', 'true');
        $this->request->addParam('facet.query', 'brand:acme');
        $this->request->addParam('facet.query', '{!key=cheap}price:10');
        $this->request->addParam('facet.query', '{!key=f_cat ex=user,cat}category:x');

        $this->builder->buildQuery($this->query, $this->request);

        $facets = $this->query->getFacetSet()->getFacets();
        $this->assertCount(3, $facets);

        $facet = current($facets);

        $this->assertInstanceOf('Solarium\QueryType\Select\Query\Component\Facet\Query', $facet);
        $this->assertEquals('brand:acme', $facet->getQuery());

        $facet = next($facets);

        $this->assertInstanceOf('Solarium\QueryType\Select\Query\Component\Facet\Query', $facet);
        $this->assertEquals('price:10', $facet->getQuery());
        $this->assertEquals('cheap', $facet->getKey());

        $facet = next($facets);

        $this->assertInstanceOf('Solarium\QueryType\Select\Query\Component\Facet\Query', $facet);
        $this->assertEquals('category:x', $facet->getQuery());
        $this->assertEquals('f_cat', $facet->getKey());
        $this->assertEquals(array('user', 'cat'), $facet->getExcludes());
    }

    public function testRangeFacets()
    {
        $this->request->addParam('facet', 'true');
        $this->request->addParam('facet.range', 'price');
        $this->request->addParam('facet.range', 'weight');
        $this->request->addParam('f.weight.facet.range.start', 10);
        $this->request->addParam('f.weight.facet.range.end', 100);
        $this->request->addParam('f.weight.facet.range.gap', 5);
        $this->request->addParam('f.weight.facet.range.hardend', 'true');
        $this->request->addParam('f.weight.facet.range.mincount', 10);
        $this->request->addParam('f.weight.facet.range.other', 'a,b,c');
        $this->request->addParam('f.weight.facet.range.include', 'x,y,z');

        $this->builder->buildQuery($this->query, $this->request);

        $facets = $this->query->getFacetSet()->getFacets();
        $this->assertCount(2, $facets);

        $facet = current($facets);

        $this->assertInstanceOf('Solarium\QueryType\Select\Query\Component\Facet\Range', $facet);
        $this->assertEquals('price', $facet->getField());

        $facet = next($facets);

        $this->assertInstanceOf('Solarium\QueryType\Select\Query\Component\Facet\Range', $facet);
        $this->assertEquals('weight', $facet->getField());
        $this->assertEquals(10, $facet->getStart());
        $this->assertEquals(100, $facet->getEnd());
        $this->assertEquals(true, $facet->getHardend());
        $this->assertEquals(10, $facet->getMinCount());
        $this->assertEquals(['a', 'b', 'c'], $facet->getOther());
        $this->assertEquals(['x', 'y', 'z'], $facet->getInclude());
    }
}