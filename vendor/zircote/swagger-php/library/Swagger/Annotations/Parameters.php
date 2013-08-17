<?php
namespace Swagger\Annotations;

/**
 * @license    http://www.apache.org/licenses/LICENSE-2.0
 *             Copyright [2012] [Robert Allen]
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *   http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 *
 * @package
 * @category
 * @subpackage
 */
use Swagger\Logger;
/**
 * @package
 * @category
 * @subpackage
 *
 * @Annotation
 */
class Parameters extends AbstractAnnotation
{
    /**
     * @var array|Parameter
     */
    public $parameters;

    protected function setNestedAnnotations($annotations)
    {
        foreach ($annotations as $annotation) {
            if ($annotation instanceof Parameter) {
                $this->parameters[] = $annotation;
            } else {
                Logger::notice('Unexpected '.get_class($annotation).' in a '.get_class($this).' in '.AbstractAnnotation::$context);
            }
        }
    }
}
