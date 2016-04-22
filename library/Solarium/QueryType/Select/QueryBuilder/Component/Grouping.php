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
 *
 * @copyright Copyright 2011 Bas de Nooijer <solarium@raspberry.nl>
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
use Solarium\QueryType\Select\Query\Query;

/**
 * Add select component Grouping to the query.
 */
class Grouping extends AbstractQueryBuilder implements ComponentQueryBuilderInterface
{
    /**
     * @param Query $query
     * @param Request $request
     * @return void
     */
    public function buildQuery(Query $query, Request $request)
    {
        if ($request->getParam('group') !== 'true') {
            return;
        }

        $component = $query->getGrouping();
        $component->setFields($this->getParamAsArray($request, 'group.field'));
        $component->setQueries($this->getParamAsArray($request, 'group.query'));
        $component->setLimit($request->getParam('group.limit'));
        $component->setOffset($request->getParam('group.offset'));
        $component->setSort($request->getParam('group.sort'));
        $component->setMainResult($request->getParam('group.main') === 'true');
        $component->setNumberOfGroups($request->getParam('group.ngroups') === 'true');
        $component->setCachePercentage($request->getParam('group.cache.percent'));
        $component->setTruncate($request->getParam('group.truncate') === 'true');
        $component->setFunction($request->getParam('group.func'));
        $component->setFacet($request->getParam('group.facet') === 'true');
        $component->setFormat($request->getParam('group.format'));
    }
}
