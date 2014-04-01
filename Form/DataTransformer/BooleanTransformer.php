<?php
namespace Unifik\DatabaseConfigBundle\Form\DataTransformer;
use Symfony\Component\Form\DataTransformerInterface;

/**
 * Transforms the value representation of a boolean between the doctrine entity ('0'/'1' string) and the form (boolean)
 *
 * @package DatabaseConfigBundle
 *
 * @author  Guillaume Petit <guillaume.petit@sword-group.com>
 *
 */
class BooleanTransformer implements DataTransformerInterface
{

    /** (non-PHPdoc)
     *
     * @param string $value the value coming from the model
     *
     * @see \Symfony\Component\Form\DataTransformerInterface::transform()
     * @return the value to be used in the view
     *
     */
    public function transform($value)
    {
        return (bool)$value;
    }

    /** (non-PHPdoc)
     *
     * @param string $value the value coming from the view
     *
     * @see \Symfony\Component\Form\DataTransformerInterface::reverseTransform()
     * @return the value to store in the model
     */
    public function reverseTransform($value)
    {
        return $value ? '1' : '0';
    }

}
