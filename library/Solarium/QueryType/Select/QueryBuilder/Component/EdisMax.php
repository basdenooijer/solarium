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
use Solarium\QueryType\Select\Query\Component\BoostQuery;
use Solarium\QueryType\Select\Query\Query;
use Solarium\QueryType\Select\Query\Component\EdisMax as EdisMaxComponent;

/**
 * Add select component Grouping to the query.
 */
class EdisMax extends AbstractQueryBuilder implements ComponentQueryBuilderInterface
{
    /**
     * @param Query $query
     * @param Request $request
     * @return void
     */
    public function buildQuery(Query $query, Request $request)
    {
        if ($request->getParam('defType') !== 'edismax') {
            return;
        }

        $edisMax = $query->getEDisMax();

        $edisMax->setQueryAlternative($request->getParam('q.alt'));
        $edisMax->setQueryFields($request->getParam('qf'));
        $edisMax->setMinimumMatch($request->getParam('mm'));
        $edisMax->setPhraseFields($request->getParam('pf'));
        $edisMax->setPhraseSlop($request->getParam('ps'));
        $edisMax->setPhraseBigramFields($request->getParam('pf2'));
        $edisMax->setPhraseBigramSlop($request->getParam('ps2'));
        $edisMax->setPhraseTrigramFields($request->getParam('pf3'));
        $edisMax->setPhraseTrigramSlop($request->getParam('ps3'));
        $edisMax->setQueryPhraseSlop($request->getParam('qs'));
        $edisMax->setTie($request->getParam('tie'));
        $edisMax->setBoostFunctions($request->getParam('bf'));
        $edisMax->setBoostFunctionsMult($request->getParam('boost'));
        $edisMax->setUserFields($request->getParam('uf'));

        $this->parseBoostQueries($edisMax, $request);
    }

    /**
     * @param EdisMaxComponent $edisMax
     * @param Request $request
     */
    protected function parseBoostQueries(EdisMaxComponent $edisMax, Request $request)
    {
        $boostQueries = $this->getParamAsArray($request, 'bq');
        foreach ($boostQueries as $i => $query) {
            $bq = new BoostQuery();
            $bq->setKey('bq_' . $i);
            $bq->setQuery($query);
            $edisMax->addBoostQuery($bq);
        }
    }
}
