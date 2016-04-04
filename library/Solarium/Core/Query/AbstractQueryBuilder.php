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
 * @copyright Copyright 2011 Bas de Nooijer <solarium@raspberry.nl>
 * @license http://github.com/basdenooijer/solarium/raw/master/COPYING
 *
 * @link http://www.solarium-project.org/
 */

/**
 * @namespace
 */

namespace Solarium\Core\Query;
use Solarium\Core\Client\Request;

/**
 * Class for building Solarium query objects from requests.
 */
abstract class AbstractQueryBuilder implements QueryBuilderInterface
{
    /**
     * @param Request $request
     * @param string $paramName
     * @return array
     */
    protected function getParamAsArray(Request $request, $paramName)
    {
        $param = $request->getParam($paramName);

        if ($param === null) {
            return array();
        }

        if (!is_array($param)) {
            $param = array($param);
        }

        return $param;
    }

    /**
     * @param $paramString
     * @param string $mainValueKey
     * @param array $mapping
     * @return array
     */
    protected function parseLocalParams($paramString, $mainValueKey, $mapping = array())
    {
        /**
         * TODO
         * Parse $paramString into mainValue and localparams array,
         * for example, the input string: {!type=dismax qf='myfield yourfield'}solr rocks
         * should return:
         * $mainValue = 'solr rocks';
         * $localParams = array('type' => 'dismax', 'qf' => 'myfield yourfield');
         */
        $mainValue = '';
        $localParams = array();

        $result = array($mainValueKey => $mainValue);

        foreach ($localParams as $key => $value) {
            if (array_key_exists($key, $mapping)) {
                $result[$mapping[$key]] = $value;
            } else {
                $result[$key] = $value;
            }
        }

        return $result;
    }
}
