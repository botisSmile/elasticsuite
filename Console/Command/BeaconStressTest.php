<?php
/**
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Smile ElasticSuite to newer
 * versions in the future.
 *
 * @category  Smile
 * @package   Smile\ElasticSuiteBeacon
 * @author    Richard Bayet <richard.bayet@smile.fr>
 * @copyright 2021 Smile
 * @license   Open Software License ("OSL") v. 3.0
 */

namespace Smile\ElasticsuiteBeacon\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Smile\ElasticsuiteBeacon\Test\Load\Generator;

/**
 * Class BeepGeneratorTest
 *
 * @category Smile
 * @package  Smile\ElasticSuiteBeacon
 */
class BeaconStressTest extends Command
{
    /**
     * @var Generator
     */
    private $generatorModels = [];

    /**
     * BeaconStressTest constructor.
     *
     * @param Generator[] $generatorModels Generator models.
     * @param string|null $name            Command name.
     */
    public function __construct($generatorModels = [], string $name = null)
    {
        parent::__construct($name);
        $this->generatorModels = $generatorModels;
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('elasticsuite:premium:beacon:stresstest');
        $this->setDescription('Perform DB stress tests.');
    }

    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.StaticAccess)
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $beepAttempts = 10000;

        $progress = new \Symfony\Component\Console\Helper\ProgressBar($output, $beepAttempts);
        $progress->setFormat('<comment>%message%</comment> %current%/%max% [%bar%] %percent:3s%% %elapsed%');
        foreach ($this->generatorModels as $generatorModel) {
            $progress->setMessage($generatorModel->getName());
            $progress->start();
            $startTimer = microtime(true);
            for ($i = 0; $i < $beepAttempts; $i++) {
                $generatorModel->generate([]);
                $progress->advance();
            }
            $endTimer = microtime(true);
            $progress->finish();

            $timelapse = $endTimer - $startTimer;
            $msTimelapse = substr(strstr($timelapse, '.'), 1, 3);
            $output->write(
                sprintf(
                    " | Total: %s %d ms",
                    \DateTime::createFromFormat('U', floor($endTimer))
                        ->diff(\DateTime::createFromFormat('U', floor($startTimer)))
                        ->format('%hh %im %Ss'),
                    $msTimelapse
                )
            );
            $output->write(
                sprintf(
                    " | Average: %f ms per beep",
                    ((((floor($endTimer) - floor($startTimer)) * 1000) + (float) $msTimelapse) / $beepAttempts)
                )
            );
            $output->writeln(' ');
        }
    }
}
