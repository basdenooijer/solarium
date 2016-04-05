<?php
/**
 * Copyright 2016 Bram Gerritsen. All rights reserved.
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
 */

namespace Solarium\Tests\QueryType\Select\QueryBuilder\Component;

use Solarium\QueryType\Select\Query\Query;
use Solarium\QueryType\Select\QueryBuilder\Component\EdisMax as EdisMaxBuilder;
use Solarium\Core\Client\Request;

class EdisMaxTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Request
     */
    protected $request;

    /**
     * @var EdisMaxBuilder
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
        $this->builder = new EdisMaxBuilder();
    }

    public function testBoostQueries()
    {
        $this->request->addParam('defType', 'edismax');
        $this->request->addParam('bq', 'cat:electronics^5.0');
        $this->request->addParam('bq', 'foo:bar^10.0');

        $this->builder->buildQuery($this->query, $this->request);

        $component = $this->query->getEDisMax();

        $boostQueries = $component->getBoostQueries();

        $this->assertCount(2, $boostQueries);
        $this->assertEquals('cat:electronics^5.0', $boostQueries['bq_0']->getQuery());
        $this->assertEquals('foo:bar^10.0', $boostQueries['bq_1']->getQuery());
    }

    public function testSimpleParams()
    {
        $this->request->addParam('defType', 'edismax');
        $this->request->addParam('q.alt', 'test');
        $this->request->addParam('qf', 'content,name');
        $this->request->addParam('mm', '75%');
        $this->request->addParam('pf', 'content,description,category');
        $this->request->addParam('ps', 1);
        $this->request->addParam('pf2', 'content,description,category');
        $this->request->addParam('ps2', 4);
        $this->request->addParam('pf3', 'content2,date,subcategory');
        $this->request->addParam('ps3', 3);
        $this->request->addParam('qs', 2);
        $this->request->addParam('tie', 0.5);
        $this->request->addParam('bf', 'functionX(price)');
        $this->request->addParam('boost', 'functionX(date)');
        $this->request->addParam('uf', 'title *_s');

        $this->builder->buildQuery($this->query, $this->request);

        $component = $this->query->getEDisMax();

        $this->assertEquals('test', $component->getQueryAlternative());
        $this->assertEquals('content,name', $component->getQueryFields());
        $this->assertEquals('75%', $component->getMinimumMatch());
        $this->assertEquals('content,description,category', $component->getPhraseFields());
        $this->assertEquals(1, $component->getPhraseSlop());
        $this->assertEquals('content,description,category', $component->getPhraseBigramFields());
        $this->assertEquals(4, $component->getPhraseBigramSlop());
        $this->assertEquals('content2,date,subcategory', $component->getPhraseTrigramFields());
        $this->assertEquals(3, $component->getPhraseTrigramSlop());
        $this->assertEquals(2, $component->getQueryPhraseSlop());
        $this->assertEquals(0.5, $component->getTie());
        $this->assertEquals('functionX(price)', $component->getBoostFunctions());
        $this->assertEquals('functionX(date)', $component->getBoostFunctionsMult());
        $this->assertEquals('title *_s', $component->getUserFields());
    }
}
