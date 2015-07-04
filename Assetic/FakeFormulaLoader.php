<?php
namespace Ap\Bundle\YoBundle\Assetic;

use Assetic\Factory\Loader\FormulaLoaderInterface;
use Assetic\Factory\Resource\ResourceInterface;

/**
 * Does absolutely nothing.
 */
class FakeFormulaLoader implements FormulaLoaderInterface
{
    /**
     * Does absolutely nothing.
     *
     * @param ResourceInterface $resource A resource
     *
     * @return array Just an empty array
     */
    public function load(ResourceInterface $resource)
    {
        return array();
    }
}
