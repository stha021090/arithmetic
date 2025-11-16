<?php

declare(strict_types=1);

namespace App\Service;

use App\Enum\Operation;
use ErrorException;
use Throwable;

readonly class OperationService
{
    public function __construct(
        private string $resultFile,
        private string $logFile,
    ) {
    }

    /**
     * @throws ErrorException
     */
    public function run(Operation $action, string $file): void
    {
        $this->prepare($file);
        $resultHandler = $this->getResultHandler();
        $fileHandler = $this->getFileHandler($file);
        $logHandler = $this->getLogHandler();
        fwrite($logHandler, sprintf("Started %s operation\r\n", $action->value));

        try {
            while ($data = fgetcsv($fileHandler, 1000, ";")) {
                $a = (int)trim($data[0]);
                $b = (int)trim($data[1]);

                if (!$result = $this->validate($action, $a, $b)) {
                    fwrite($logHandler, sprintf("Numbers '%s' and '%s' are invalid\r\n", $a, $b));
                } else {
                    fwrite($resultHandler, sprintf("%s\r\n", implode(";", [$a, $b, $result])));
                }
            }
        } catch (Throwable) {
            fwrite($logHandler, sprintf("Failed %s operation\r\n", $action->value));
        }

        fclose($resultHandler);
        fclose($fileHandler);
        fwrite($logHandler, sprintf("Finished %s calculation\r\n", $action->value));
        fclose($logHandler);
    }

    private function validate(Operation $action, int $a, int $b): float|false|int
    {
        $result = false;

        switch ($action) {
            case Operation::PLUS:
                if (($result = $a + $b) < 0) {
                    $result = false;
                }

                break;
            case Operation::MINUS:
                if (($result = $a - $b) < 0) {
                    $result = false;
                }

                break;
            case Operation::MULTIPLY:
                if (($result = $a * $b) < 0) {
                    $result = false;
                }

                break;
            case Operation::DIVISION:
                if ($b === 0 || ($result = $a / $b) < 0) {
                    $result = false;
                }

                break;
        }

        return $result;
    }

    /**
     * @throws ErrorException
     */
    private function prepare(string $file): void
    {
        if (file_exists($this->logFile)) {
            unlink($this->logFile);
        }

        if (file_exists($this->resultFile)) {
            unlink($this->resultFile);
        }

        if (!file_exists($file) || !is_readable($file)) {
            throw new ErrorException(sprintf("Failed to open file '%s'", $file));
        }
    }

    /**
     * @return resource
     * @throws ErrorException
     */
    private function getLogHandler()
    {
        if (!$logHandler = fopen($this->logFile, "wb+")) {
            throw new ErrorException(sprintf("Failed to open file '%s'", $this->logFile));
        }

        return $logHandler;
    }

    /**
     * @return resource
     * @throws ErrorException
     */
    private function getResultHandler()
    {
        if (!$resultHandler = fopen($this->resultFile, "ab+")) {
            throw new ErrorException(sprintf("Failed to open file '%s'", $this->resultFile));
        }

        return $resultHandler;
    }

    /**
     * @return resource
     * @throws ErrorException
     */
    private function getFileHandler(string $file)
    {
        if (!$fileHandler = fopen($file, "rb")) {
            throw new ErrorException(sprintf("Failed to open file '%s'", $file));
        }

        return $fileHandler;
    }
}
