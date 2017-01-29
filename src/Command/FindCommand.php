<?php

namespace Partnermarketing\FileSystemBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Partnermarketing\FileSystemBundle\FileSystem\FileSystem;

/**
 * This command checks if a specific file exists in a file system, depending on the adapter passed.
 *
 * Usage: `php app/console partnermarketing:file_system:exists amazon_s3 dir/filename.png`
 */
class FindCommand extends ContainerAwareCommand
{
    protected $availableAdapters = [
        'amazon_s3',
        'local_storage'
    ];

    protected function configure()
    {
        $this->setName('partnermarketing:file_system:exists')
            ->setDescription('Check if a file exists')
            ->addArgument('adapter', InputArgument::REQUIRED, 'amazon_s3 | local_storage - adapter to use')
            ->addArgument('filename', InputArgument::REQUIRED, 'Image filename to search');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $adapterName = $input->getArgument('adapter');
        if (!in_array($adapterName, $this->availableAdapters)) {
            $output->writeln(
                'Invalid adapter name supplied! Adapters available: ' . implode(', ', $this->availableAdapters)
            );

            return;
        }

        $adapter = $this->getContainer()->get('partnermarketing_file_system.factory')->build($adapterName);
        $fileSystem = new FileSystem($adapter);

        $filename = $input->getArgument('filename');

        if ($fileSystem->exists($filename)) {
            $output->writeln('File was found using adapter (' . $adapterName . ')');
        } else {
            $output->writeln('File not found using adapter (' . $adapterName . ')');
        }
    }
}
