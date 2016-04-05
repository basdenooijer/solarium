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

namespace Solarium\Tests\QueryType\Select\QueryBuilder;

use Solarium\Core\Client\Request;
use Solarium\QueryType\Select\Query\Query;
use Solarium\QueryType\Select\QueryBuilder\QueryBuilder;

class QueryBuilderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Request
     */
    protected $request;

    /**
     * @var QueryBuilder
     */
    protected $queryBuilder;

    /**
     * @var Query
     */
    protected $query;

    public function setUp()
    {
        $this->request = new Request();
        $this->request->setOptions(array(
            'handler' => 'select'
        ));
        $this->request->setParams(array(
            'omitHeader' => true,
            'wt' => 'json',
            'json.nl' => 'flat',
        ));

        $this->query = new Query();
        $this->queryBuilder = new QueryBuilder();
    }

    public function testSimpleQuery()
    {
        $this->request->addParam('q', 'test query string');
        $this->queryBuilder->build($this->query, $this->request);
        $this->assertEquals('test query string', $this->query->getQuery());
    }

    public function testQueryWithTags()
    {
        $this->request->addParam('q', '{!tag=a,b}test query string');

        $this->queryBuilder->build($this->query, $this->request);
        $this->assertEquals(array('a', 'b'), $this->query->getTags());
        $this->assertEquals('test query string', $this->query->getQuery());
    }

    public function testQueryWithStartAndRowsParameters()
    {
        $this->request->addParam('start', 10);
        $this->request->addParam('rows', 15);

        $this->queryBuilder->build($this->query, $this->request);

        $this->assertEquals(10, $this->query->getStart());
        $this->assertEquals(15, $this->query->getRows());
    }

    public function testQueryZeroRows()
    {
        $this->request->addParam('rows', 0);
        $this->queryBuilder->build($this->query, $this->request);
        $this->assertEquals(0, $this->query->getRows());
    }

    public function testQuerySingleField()
    {
        $this->request->addParam('fl', 'a');
        $this->queryBuilder->build($this->query, $this->request);
        $this->assertEquals(array('a'), $this->query->getFields());
    }

    public function testQueryMultipleFields()
    {
        $this->request->addParam('fl', 'a,b,long_name');
        $this->queryBuilder->build($this->query, $this->request);
        $this->assertEquals(array('a', 'b', 'long_name'), $this->query->getFields());
    }

    public function testQueryDefaultOperator()
    {
        $this->request->addParam('q.op', 'AND');
        $this->queryBuilder->build($this->query, $this->request);
        $this->assertEquals('AND', $this->query->getQueryDefaultOperator());
    }

    public function testQueryDefaultField()
    {
        $this->request->addParam('df', 'content');
        $this->queryBuilder->build($this->query, $this->request);
        $this->assertEquals('content', $this->query->getQueryDefaultField());
    }

    public function testQuerySort()
    {
        $this->request->addParam('sort', 'price ASC');
        $this->queryBuilder->build($this->query, $this->request);
        $this->assertEquals(array('price' => Query::SORT_ASC), $this->query->getSorts());
    }

    public function testQueryMultipleSortsWithWhitespace()
    {
        $this->request->addParam('sort', ' price ASC, rating DESC ');
        $this->queryBuilder->build($this->query, $this->request);
        $this->assertEquals(array('price' => Query::SORT_ASC, 'rating' => Query::SORT_DESC), $this->query->getSorts());
    }

    public function testQueryWithFilter()
    {
        $this->request->addParam('fq', 'price:[10 TO 20]');
        $this->queryBuilder->build($this->query, $this->request);

        $this->assertCount(1, $this->query->getFilterQueries());
        $filterQuery = current($this->query->getFilterQueries());
        $this->assertEquals('price:[10 TO 20]', $filterQuery->getQuery());
        $this->assertEquals('price', $filterQuery->getKey());
    }

    public function testQueryWithMultiFieldFilter()
    {
        $this->request->addParam('fq', 'price:[10 TO 20] AND stock:true');
        $this->queryBuilder->build($this->query, $this->request);

        $this->assertCount(1, $this->query->getFilterQueries());
        $filterQuery = current($this->query->getFilterQueries());
        $this->assertEquals('price:[10 TO 20] AND stock:true', $filterQuery->getQuery());
        $this->assertEquals('price-stock', $filterQuery->getKey());
    }

    public function testQueryWithMultipleFilters()
    {
        $this->request->addParam('fq', 'locale:nl_NL');
        $this->request->addParam('fq', 'price:[10 TO 20] AND stock:true');
        $this->queryBuilder->build($this->query, $this->request);
        $filterQueries = $this->query->getFilterQueries();

        $this->assertCount(2, $filterQueries);

        $filterQuery = current($filterQueries);
        $this->assertEquals('locale', $filterQuery->getKey());
        $this->assertEquals('locale:nl_NL', $filterQuery->getQuery());

        $filterQuery = next($filterQueries);
        $this->assertEquals('price-stock', $filterQuery->getKey());
        $this->assertEquals('price:[10 TO 20] AND stock:true', $filterQuery->getQuery());
    }

    public function testQueryWithEmptyFilter()
    {
        $this->request->addParam('fq', '');
        $this->queryBuilder->build($this->query, $this->request);

        $this->assertCount(0, $this->query->getFilterQueries());
    }

    public function testQueryWithComponents()
    {
        $this->markTestIncomplete('not yet implemented');
    }
}