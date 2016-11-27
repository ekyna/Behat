<?php

namespace Ekyna\Behat\Context;

use Behat\Behat\Context\Context;
use Behat\Symfony2Extension\Context\KernelAwareContext;
use Behat\Symfony2Extension\Context\KernelDictionary;
use Doctrine\DBAL\Driver\PDOConnection;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Process\Process;

/**
 * Class DatabaseContext
 * @package Ekyna\Behat\Context
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ResetContext implements Context, KernelAwareContext
{
    use KernelDictionary;

    /**
     * @var string
     */
    private $rootDir;

    /**
     * @var string
     */
    private $dataDir;

    /**
     * @var \Doctrine\DBAL\Connection
     */
    private $connection;


    /**
     * @BeforeScenario
     * @throws \RuntimeException
     */
    public function reset()
    {
        $this->resetDatabase();
        $this->resetSearch();
        $this->resetFilesystem();
    }

    /**
     * @throws \RuntimeException
     */
    private function resetDatabase()
    {
        $conn = $this->getConnection();

        // Reset database
        $sql = file_get_contents($this->getRootDir().'/tests/db.sql');
        if ($conn instanceof PDOConnection) {
            // PDO Drivers
            try {
                $lines = 0;
                $stmt = $conn->prepare($sql);
                $stmt->execute();
                do {
                    // Required due to "MySQL has gone away!" issue
                    $stmt->fetch();
                    $stmt->closeCursor();
                    $lines++;
                } while ($stmt->nextRowset());
            } catch (\PDOException $e) {
                throw new \RuntimeException($e->getMessage(), $e->getCode(), $e);
            }
        } else {
            // Non-PDO Drivers (ie. OCI8 driver)
            $stmt = $conn->prepare($sql);
            $rs = $stmt->execute();
            if (!$rs) {
                $error = $stmt->errorInfo();
                throw new \RuntimeException($error[2], $error[0]);
            }
            $stmt->closeCursor();
        }
    }

    /**
     * @throws \RuntimeException
     */
    private function resetSearch()
    {
        // TODO Reset elasticsearch
//        try {
//            $phar = new \PharData('monphar.tar');
//            $phar->extractTo($kernelDir);
//        } catch (\Exception $e) {
//            throw new \RuntimeException($e->getMessage(), $e->getCode(), $e);
//            throw $e;
//        }
    }

    /**
     * @throws \RuntimeException
     */
    private function resetFilesystem()
    {
        $this->extractTar(sprintf('tar -xf %s/tests/data.tar', $this->getRootDir()), $this->getDataDir());
    }

    /**
     * Extract the [source] .tar archive to the [destination] directory.
     *
     * @param string $source
     * @param string $destination
     */
    private function extractTar($source, $destination)
    {
        $fs = new Filesystem();
        $fs->remove($destination);
        $fs->mkdir($destination);

        $process = new Process($source, $destination);
        $process->run();
        if (!$process->isSuccessful()) {
            throw new \RuntimeException('Failed to extract data.');
        }
    }

    /**
     * Returns the project's root directory.
     *
     * @return string
     */
    private function getRootDir()
    {
        if (null !== $this->rootDir) {
            return $this->rootDir;
        }

        return $this->rootDir = realpath($this->getKernel()->getRootDir() . '/..');
    }

    /**
     * Returns the project's data directory.
     *
     * @return string
     */
    private function getDataDir()
    {
        if (null !== $this->dataDir) {
            return $this->dataDir;
        }

        /** @noinspection PhpUndefinedMethodInspection */
        return $this->dataDir = $this->getKernel()->getDataDir();
    }

    /**
     * Returns the database connection.
     *
     * @return \Doctrine\DBAL\Connection
     */
    private function getConnection()
    {
        if (null !== $this->connection) {
            return $this->connection;
        }

        return $this->connection = $this->getContainer()->get('doctrine.dbal.default_connection');
    }
}
