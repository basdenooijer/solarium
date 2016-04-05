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

/**
 * @namespace
 */

namespace Solarium\QueryType\Select\QueryBuilder;

use Solarium\Core\Query\AbstractQueryBuilder;
use Solarium\Core\Client\Request;
use Solarium\Core\Query\QueryBuilderInterface;
use Solarium\Core\Query\QueryInterface;
use Solarium\QueryType\Select\Query\Query;
use Solarium\QueryType\Select\RequestBuilder\Component\Grouping;

/**
 * Build a select request.
 */
class QueryBuilder extends AbstractQueryBuilder implements QueryBuilderInterface
{
    /**
     * Build query object from a request.
     *
     * @param QueryInterface $query
     * @param Request $request
     *
     * @return void
     */
    public function build(QueryInterface $query, Request $request)
    {
        $query->setOptions($this->parseLocalParams($request->getParam('q'), 'query'));

        $query->setStart($request->getParam('start'));
        $query->setRows($request->getParam('rows'));

        $query->setFields($this->getParamAsArray($request, 'fl'));
        $query->setQueryDefaultOperator($request->getParam('q.op'));
        $query->setQueryDefaultField($request->getParam('df'));

        $this->parseSortParam($query, $request);
        $this->parseFilterQueries($query, $request);
        $this->parseComponents($query, $request);
    }

    /**
     * @param Query $query
     * @param Request $request
     */
    protected function parseSortParam(Query $query, Request $request)
    {
        $sort = $request->getParam('sort');

        $parts = explode(',', $sort);
        $parts = array_map('trim', $parts);
        foreach ($parts as $sort) {
            if (strtolower(substr($sort, -3)) == 'asc') {
                $mode = Query::SORT_ASC;
                $sort = substr($sort, 0, -3);
            } else if (strtolower(substr($sort, -4)) == 'desc') {
                $mode = Query::SORT_DESC;
                $sort = substr($sort, 0, -4);
            } else {
                $mode = Query::SORT_DESC;
            }

            $query->addSort(trim($sort), $mode);
        }
    }

    /**
     * @param Query $query
     * @param Request $request
     */
    protected function parseFilterQueries(Query $query, Request $request)
    {
        foreach ($this->getParamAsArray($request, 'fq') as $index => $filterQuery) {
            $filterQueryParams = $this->parseLocalParams($filterQuery, 'query');

            if (!array_key_exists('query', $filterQueryParams)) {
                continue;
            }

            if (!array_key_exists('key', $filterQueryParams)) {
                preg_match_all('/\b([\w]+):/', $filterQueryParams['query'], $matches);
                $filterQueryParams['key'] = implode('-', $matches[1]);
            }

            $query->createFilterQuery($filterQueryParams);
        }
    }

    /**
     * @param Query $query
     * @param Request $request
     */
    protected function parseComponents(Query $query, Request $request)
    {
        /**
         * @var ComponentQueryBuilderInterface[] $componentBuilders
         */
        $componentBuilders = array(
            new Grouping(),
            //@TODO implement more components
        );

        foreach ($componentBuilders as $componentBuilder) {
            $componentBuilder->buildQuery($query, $request);
        }
    }
}
