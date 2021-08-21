<?php
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use TM\ErrorLogParser\Parser;

class ConvertLogCommand extends Command
{
    protected $commandName = 'convert:log';
    protected $commandDescription = "Convert log file";

    protected $commandArgumentFile = "file";

    protected $commandOptionType = "type";
    protected $commandOptionOutput = "output";
    protected $commandOptionHelp = "help";

    protected function configure()
    {
        $this
            ->setName($this->commandName)
            ->setDescription($this->commandDescription)
            ->addArgument(
                $this->commandArgumentFile,
                InputArgument::REQUIRED
            )
            ->addOption(
                $this->commandOptionType,
                't',
                InputOption::VALUE_OPTIONAL,
                'Type of convert file category',
                'plain_text'
            )
            ->addOption(
                $this->commandOptionOutput,
                'o',
                InputOption::VALUE_OPTIONAL,
                'Output of convert file path'
            )
            ->addOption(
                $this->commandOptionHelp,
                'h',
                InputOption::VALUE_NONE
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $file = $input->getArgument($this->commandArgumentFile);

        if ($input->getOption($this->commandOptionHelp)) {
            $output->writeln('Help');
            exit();
        }

        if (!realpath($file)) {
            $output->writeln('File doesn\'t exists');
            exit();
        }

        $getFileNameWithoutExtension = basename($file, ".log");
        $getDefaultOutputPath = dirname($file, 1);

        $fileContent = file_get_contents($file);

        switch ($input->getOption($this->commandOptionType)) {
            case 'json':
                try {
                    $parser = new Parser(Parser::TYPE_NGINX);
                    $contents = [];
                    foreach ($this->getLines($fileContent) as $line) {
                        array_push($contents, $parser->parse($line));
                    }

                    if ($input->getOption($this->commandOptionOutput)) {
                        file_put_contents($input->getOption($this->commandOptionOutput) . '/' . $getFileNameWithoutExtension . ".json", json_encode((array)$contents));
                    } else {
                        file_put_contents($getDefaultOutputPath . '/' . $getFileNameWithoutExtension . ".json", json_encode((array)$contents));
                    }

                } catch(Exception $ex) {
                    return $output->writeln($ex->getMessage());
                }

                $output->writeln('file converted to json');
                break;

            case 'text';
                try {
                    if ($input->getOption($this->commandOptionOutput)) {
                        file_put_contents($input->getOption($this->commandOptionOutput) . '/' . $getFileNameWithoutExtension . '.txt', $fileContent);

                    } else {
                        file_put_contents($getDefaultOutputPath . '/' . $getFileNameWithoutExtension . '.txt', $fileContent);
                    }

                } catch(Exception $ex) {
                    return $output->writeln($ex->getMessage());
                }

                $output->writeln('file converted to text');
                break;

            case 'plain_text':
            default:
                try {
                    if ($input->getOption($this->commandOptionOutput)) {
                        file_put_contents($input->getOption($this->commandOptionOutput) . '/' . $getFileNameWithoutExtension, $fileContent);

                    } else {
                        file_put_contents($getDefaultOutputPath . '/' . $getFileNameWithoutExtension, $fileContent);
                    }

                } catch(Exception $ex) {
                    return $output->writeln($ex->getMessage());
                }

                $output->writeln('file converted to plain text');
                break;
        }
    }

    private function getLines($fileContent)
    {
        return explode(PHP_EOL, $fileContent);;
    }

}