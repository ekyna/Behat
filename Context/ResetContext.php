<?php

namespace Ekyna\Behat\Context;

use Behat\Behat\Context\Context;
use Behat\Symfony2Extension\Context\KernelAwareContext;
use Behat\Symfony2Extension\Context\KernelDictionary;
use Doctrine\DBAL\Driver\PDOConnection;
use Symfony\Component\Filesystem\Filesystem;

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
        $directory = $this->getRootDir().'/var/test';

        $fs = new Filesystem();
        $fs->remove($directory);
        $fs->mkdir($directory);

        try {
            $phar = new \PharData($this->getRootDir().'/tests/data.tar');
            $phar->extractTo($this->getRootDir());
        } catch (\Exception $e) {
            throw new \RuntimeException($e->getMessage(), $e->getCode(), $e);
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

        return $this->rootDir = realpath($this->getContainer()->getParameter('kernel.root_dir') . '/..');
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
