<?php

namespace Egzakt\DatabaseConfigBundle\Form;

use Symfony\Component\Config\Definition\ArrayNode;
use Symfony\Component\Config\Definition\BooleanNode;
use Symfony\Component\Config\Definition\EnumNode;
use Symfony\Component\Config\Definition\FloatNode;
use Symfony\Component\Config\Definition\IntegerNode;
use Symfony\Component\Config\Definition\NodeInterface;
use Symfony\Component\Config\Definition\PrototypedArrayNode;
use Symfony\Component\Config\Definition\ScalarNode;
use Symfony\Component\Config\Definition\VariableNode;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints\NotBlank;

use Egzakt\DatabaseConfigBundle\Form\DataTransformer\ArrayEntityTransformer;

/**
 * This is only a PARTIAL and EXPERIMENTAL implementation of all the features available in the Symfony configuration tree.
 *
 *  If you want a tree node to be handled by the configurator form, just set the "configurator" attribute to "true"
 *  in the tree builder on your bundle.
 *
 *  Example:
 *
 *      $rootNode
 *          ->children()
 *              ->integerNode('integer')
 *                  ->info('This is a configurable integer node')
 *                  ->defaultValue(21)
 *                  ->attribute('configurator', true)
 *              ->end()
 *          ->end();
 *
 *  Nodes type
 *   - BooleanNode           Supported.
 *   - IntegerNode           Supported.
 *   - FloatNode             Supported.
 *   - EnumNode              Supported.
 *   - ScalarNode            Supported.
 *   - ArrayNode             Partial support / Hacky support.
 *   - PrototypedArrayNode   No support. This would required lots of work. Maybe in future version.
 *
 *  Validation rules support (http://symfony.com/doc/current/components/config/definition.html#validation-rules)
 *   - Not implemented at the moment.
 *
 *  Default values
 *   - Supported for all types. Displayed below the label of the field.
 *
 *  Information attribute
 *   - Supported for all types. Displayed below the label of the field.
 */
class ConfiguratorType extends AbstractType
{
    /**
     * Build Form
     *
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addModelTransformer(new ArrayEntityTransformer());

        $this->processChildren($options['tree'], $builder, $options['request']);
    }

    /**
     * Takes a ArrayNode and build the form recursively
     *
     * @param ArrayNode             $arrayNode
     * @param FormBuilderInterface  $builder
     * @param Request               $request
     */
    protected function processChildren(ArrayNode $arrayNode, FormBuilderInterface $builder, Request $request)
    {
        foreach ($arrayNode->getChildren() as $node) {
            if (false == $node->getAttribute('configurator')) {
                // Nodes that are not explicitly configurable are skipped
                continue;
            } elseif ($node instanceof PrototypedArrayNode) {
                // PrototypedArrayNode are not currently supported
                continue;
            } elseif ($node instanceof ArrayNode) {
                $builder->add($node->getName() . '_activated', 'checkbox'); // used to trigger the validation of the childrens fields
                $builder->add($node->getName(), new ConfiguratorArrayType(), array('tree' => $node, 'request' => $request));
            } else {
                $this->nodeToField($node, $builder, $request);
            }
        }
    }

    /**
     * Conversion of a node element to a form field.
     * The field is automatically added to the builder.
     *
     * @param NodeInterface         $node
     * @param FormBuilderInterface  $builder
     * @param Request               $request
     */
    protected function nodeToField(NodeInterface $node, FormBuilderInterface $builder, Request $request)
    {
        $options = array(
            'required' => $node->isRequired(),
            'constraints' => array(),
            'attr' => array()
        );

        if ($node instanceof BooleanNode) {
            $type = 'checkbox';
        } elseif ($node instanceof IntegerNode) {
            $type = 'number';
        } elseif ($node instanceof FloatNode) {
            $type = 'number';
        } elseif ($node instanceof EnumNode) {
            $type = 'choice';
            $options['choices'] = array_combine($node->getValues(), $node->getValues()); // generate identical key/value
        } elseif ($node instanceof ScalarNode) {
            $type = 'text';
        } elseif ($node instanceof VariableNode) {
            $type = 'text';
        }

        if ($node->isRequired() && $this->parentFormIsActivated($node, $request)) {
            $options['constraints'][] = new NotBlank();
        }

        // infos
        $infos = '';
        if ($node->hasAttribute('info')) {
            $infos = $node->getAttribute('info') . '<br />';
        }

        // default value (using get instead of has to automatically filter empty strings)
        if ($node->getDefaultValue()) {
            $infos .= 'default value: ' . $node->getDefaultValue();
        }

        $options['attr']['alt'] = $infos;

        $builder->add($node->getName(), $type, $options);
    }

    /**
     * The getParent method is missing in the BaseNode class.
     * It is scheduled available in the 2.4 version (https://github.com/symfony/symfony/commit/d95c245d65af09fe55882204cb2b3a9cf2886f6d)
     *
     * Until this is available the only way to get the parent name of a node is by extracting the name from the path.
     *
     * @param NodeInterface $node
     *
     * @return string
     */
    protected function extractParentNameFromNode(NodeInterface $node)
    {
        $path = $node->getPath();
        $match = array();

        if (preg_match('/.*\.(.*)\.' . $node->getName() . '/', $path, $match)) {
            return $match[1];
        }

        return false;
    }

    /**
     * Ok, there should be a better way to have conditionnal validation based of the parent fields but
     * this is the simplest solution that I have found right now.
     *
     * @param NodeInterface $node
     * @param Request       $request
     *
     * @return bool
     */
    protected function parentFormIsActivated(NodeInterface $node, Request $request)
    {
        $parentName = $this->extractParentNameFromNode($node);

        // this node have to parent, default to true
        if (false === $parentName) {
            return true;
        }

        $expression = $this->getName() . '[' . $parentName . '_activated]';

        if ($request->get($expression, null, true)) {
            return true;
        }
    }

    /**
     * Get Name
     *
     * @return string
     */
    public function getName()
    {
        return 'configurator';
    }

    /**
     * Set default options
     *
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => null,
            'tree' => array(),
            'request' => null
        ));
    }
}
