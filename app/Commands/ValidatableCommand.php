<?php
namespace App\Commands;

use App\Validation\ValidationLog;
use LaravelZero\Framework\Commands\Command;
use Override;
use RuntimeException;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

abstract class ValidatableCommand extends Command
{
    /**
     * Initialization logic should be written here, such as:
     * - Reading configuration files.
     * - Initializing values.
     * - Check for errors in user's input.
     *
     * This method shouldn't throw exceptions, instead, it should log all errors
     * in a {@link ValidationLog} object which should be returned.
     *
     * @return ValidationLog Object which contains all error logs. If no errors have been
     * caught, a {@link ValidationLog} should still be returned, even if empty.
     */
    abstract protected function initializeCommand(): ValidationLog;

    #[Override]
    protected final function initialize(InputInterface $in, OutputInterface $out)
    {
        $validationLog = $this->initializeCommand();

        if (!$validationLog->isEmpty()) {
            $this->line('<bg=red;fg=white;options=bold> INITIALIZATION ERROR </>');
            foreach ($validationLog->getSectionNames() as $sectionName) {
                $this->line("==== $sectionName ====");
                foreach ($validationLog->getSectionErrors($sectionName) as $error) {
                    $this->line("\t- <error>$error</error>");
                }
                $this->line('');
            }

            throw new RuntimeException("Errors have occurred during initialization.");
        }
    }
}
