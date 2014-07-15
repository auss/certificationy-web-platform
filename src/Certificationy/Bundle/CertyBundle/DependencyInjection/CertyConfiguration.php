<?php
/**
* This file is part of the PhpStorm.
* (c) johann (johann_27@hotmail.fr)
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
**/

namespace Certificationy\Bundle\CertyBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

class CertyConfiguration
{
    /**
     * @var \Symfony\Component\DependencyInjection\ContainerBuilder
     */
    protected $container;

    /**
     * @var array
     */
    protected $defaultClasses = array(
        'description' => 'Certificationy\Bundle\CertyBundle\Process\Certification\DescriptionStep',
        'quiz' => 'Certificationy\Bundle\CertyBundle\Process\Certification\QuizStep',
        'final' => 'Certificationy\Bundle\CertyBundle\Process\Certification\FinalStep'
    );

    /**
     * @var array
     */
    public static $acceptedDelegator = array(null, 'rabbitmq');

    /**
     * @param ContainerBuilder $container
     */
    public function __construct(ContainerBuilder $container)
    {
        $this->container = $container;
    }

    /**
     * @param array $providerConfig
     */
    public function buildProvider(Array $providerConfig)
    {
        if (isset($providerConfig['file'])) {
            $this->container->setParameter('certy_file_provider_root_dir', $providerConfig['file']['root_dir']);
        }
    }

    /**
     * @param array $scenarioConfig
     */
    public function buildScenario(Array $scenarioConfig)
    {
        $this->container->setParameter('certy_scenario_class', $scenarioConfig['class']);
    }

    /**
     * @param array $stepsConfig
     */
    public function buildSteps(Array $stepsConfig)
    {
        $parameters = array();

        foreach ($this->defaultClasses as $stepName => $stepClass) {
            $class = isset($stepsConfig[$stepName])
                ? $stepsConfig[$stepName]
                : $stepClass
            ;

            $parameters[$stepName] = $class;
        }

        $this->container->setParameter('certy_certification_step_classes', $parameters);
    }

    /**
     * @param array $calculatorConfig
     */
    public function buildCalculator(Array $calculatorConfig)
    {
        //Register calculator service
        $definition = new Definition($calculatorConfig['class']);
        $this->container->setDefinition('certy.certificationy.calculator', $definition);

        //Register CalculatorManager
        $definition = new Definition('Certificationy\Component\Certy\Calculator\CalculatorManager');
        $definition->addArgument(new Reference('certy.certificationy.calculator'));

        if (null !== $delegator = $calculatorConfig['delegator']) {
            $this->getDelegator($delegator);
            $definition->addArgument(new Reference('certy.certification.delegator'));
        }

        $this->container->setDefinition('certy.certification.calculator_manager', $definition);
    }

    /**
     * @param string $delegatorName
     */
    protected function getDelegator($delegatorName)
    {
        if ('rabbitmq' === $delegatorName) {
            if (!class_exists('Swarrot\SwarrotBundle\SwarrotBundle')) {
                throw new \Exception('Delegate with RabbitMQ need SwarrotBundle to work, make sure he is enabled in appKernel.php');
            }

            if (!class_exists('JMS\SerializerBundle\JMSSerializerBundle')) {
                throw new \Exception('Delegate with RabbitMQ need JMS Serializer to communicate with worker');
            }

            $delegatorDefinition = new Definition('Certificationy\Component\Certy\Calculator\Delegator\RabbitMQ');
            $delegatorDefinition->setArguments(array(
                new Reference('swarrot.publisher'),
                new Reference('jms_serializer')
            ));

            $processorDefinition = new Definition('Certificationy\Component\Certy\Calculator\Delegator\Processor\ResultComputeProcessor');
            $processorDefinition->setArguments(array(
                new Reference('jms_serializer'),
                new Reference('certy.certificationy.calculator')
            ));

            $this->container->setDefinition('certy.certification.result_processor', $processorDefinition);

            $this->container->setDefinition('certy.certification.delegator', $delegatorDefinition);
        }
    }
}
