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

namespace Solarium\QueryType\Select\QueryBuilder\Component;

use Solarium\Core\Client\Request;
use Solarium\Core\Query\AbstractQueryBuilder;
use Solarium\QueryType\Select\Query\Component\FacetSet as FacetSetComponent;
use Solarium\QueryType\Select\Query\Query;

/**
 * Add select component FacetSet to the request.
 */
class FacetSet extends AbstractQueryBuilder implements ComponentQueryBuilderInterface
{
    /**
     * @param Query $query
     * @param Request $request
     * @return void
     */
    public function buildQuery(Query $query, Request $request)
    {
        if ($request->getParam('facet') !== 'true') {
            return;
        }

        $facetSet = $query->getFacetSet();
        $facetSet->setSort($request->getParam('facet.sort'));
        $facetSet->setPrefix($request->getParam('facet.sort'));
        $facetSet->setContains($request->getParam('facet.contains'));
        $facetSet->setContainsIgnoreCase($this->getParameterAsBoolean($request, 'facet.contains.ignoreCase'));
        $facetSet->setMissing($this->getParameterAsBoolean($request, 'facet.missing'));
        $facetSet->setMinCount($request->getParam('facet.mincount'));
        $facetSet->setLimit($request->getParam('facet.limit'));

        $this->parseFieldFacets($facetSet, $request);
        $this->parseQueryFacets($facetSet, $request);
        $this->parseRangeFacets($facetSet, $request);
        $this->parseIntervalFacets($facetSet, $request);
    }

    /**
     * @param FacetSetComponent $facetSet
     * @param Request $request
     */
    protected function parseFieldFacets(FacetSetComponent $facetSet, Request $request)
    {
        foreach ($this->getParamAsArray($request, 'facet.field') as $field) {

            $params = $this->parseLocalParams($field, 'field', array('ex' => 'exclude'));

            if (!array_key_exists('field', $params) || empty($params['field'])) {
                continue;
            }
            $field = $params['field'];

            if (!array_key_exists('key', $params) || empty($params['key'])) {
                $params['key'] = 'field_' . $field;
            }

            $prefix = sprintf('f.%s.facet.', $field);

            $facet = $facetSet->createFacetField($params);
            $facet->setLimit($request->getParam($prefix . 'limit'));
            $facet->setSort($request->getParam($prefix . 'sort'));
            $facet->setPrefix($request->getParam($prefix . 'prefix'));
            $facet->setContains($request->getParam($prefix . 'contains'));
            $facet->setContainsIgnoreCase($request->getParam($prefix . 'contains.ignoreCase'));
            $facet->setOffset($request->getParam($prefix . 'offset'));
            $facet->setMinCount($request->getParam($prefix . 'mincount'));
            $facet->setMissing($request->getParam($prefix . 'missing'));
            $facet->setMethod($request->getParam($prefix . 'method'));
        }
    }

    /**
     * @param FacetSetComponent $facetSet
     * @param Request $request
     */
    protected function parseQueryFacets(FacetSetComponent $facetSet, Request $request)
    {
        foreach ($this->getParamAsArray($request, 'facet.query') as $index => $field) {
            $params = $this->parseLocalParams($field, 'query', array('ex' => 'exclude'));

            if (!array_key_exists('query', $params) || empty($params['query'])) {
                continue;
            }

            if (!array_key_exists('key', $params) || empty($params['key'])) {
                $params['key'] = 'fq_' . $index;
            }

            $facetSet->createFacetQuery($params);
        }
    }

    /**
     * @param FacetSetComponent $facetSet
     * @param Request $request
     */
    protected function parseRangeFacets(FacetSetComponent $facetSet, Request $request)
    {
        foreach ($this->getParamAsArray($request, 'facet.range') as $field) {

            $params = $this->parseLocalParams($field, 'field', array('ex' => 'exclude'));

            if (!array_key_exists('field', $params) || empty($params['field'])) {
                continue;
            }
            $field = $params['field'];

            if (!array_key_exists('key', $params) || empty($params['key'])) {
                $params['key'] = 'field_' . $field;
            }

            $prefix = sprintf('f.%s.facet.range.', $field);

            $facet = $facetSet->createFacetRange($params);
            $facet->setStart($request->getParam($prefix . 'start'));
            $facet->setEnd($request->getParam($prefix . 'end'));
            $facet->setGap($request->getParam($prefix . 'gap'));
            $facet->setHardend($this->getParameterAsBoolean($request, $prefix . 'hardend'));
            $facet->setMinCount($request->getParam($prefix . 'mincount'));

            $facet->setOther($this->getParamAsArray($request, $prefix . 'other'));
            $facet->setInclude($this->getParamAsArray($request, $prefix . 'include'));
        }
    }

    /**
     * @param FacetSetComponent $facetSet
     * @param Request $request
     */
    protected function parseIntervalFacets(FacetSetComponent $facetSet, Request $request)
    {
        //@todo
    }
}
