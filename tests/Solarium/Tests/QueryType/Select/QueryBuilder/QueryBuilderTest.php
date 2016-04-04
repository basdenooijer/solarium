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
 */

namespace Solarium\Tests\QueryType\Select\QueryBuilder;

use Solarium\Core\Client\Request;
use Solarium\QueryType\Select\QueryBuilder\QueryBuilder;
use Solarium\QueryType\Select\RequestBuilder\RequestBuilder as RequestBuilder;

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
     * @var RequestBuilder
     */
    protected $requestBuilder;

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

        $this->queryBuilder = new QueryBuilder();
        $this->requestBuilder = new RequestBuilder();
    }

    public function testSimpleQuery()
    {
        $this->request->addParam('q', 'test query string');

        $query = $this->queryBuilder->build($this->request);

        $this->assertEquals(
            $this->request,
            $this->requestBuilder->build($query)
        );
    }

    public function testQueryWithTags()
    {

    }
}