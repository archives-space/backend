<?php

namespace App\Utils;

use Doctrine\ODM\MongoDB\DocumentManager;
use Exception;
use RuntimeException;

class IdGenerator implements \Doctrine\ODM\MongoDB\Id\IdGenerator
{
    private string $alphabet = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789_-';

    /**
     * @param int $len
     * @return string
     * @throws Exception
     */
    public function generateStr(int $len = 6): string
    {
        $out = '';
        for ($i = 0; $i < $len; $i++) {
            $out .= $this->alphabet[random_int(0, strlen($this->alphabet) - 1)];
        }

        return $out;
    }

    /**
     * @param DocumentManager $dm
     * @param object $document
     * @return string
     * @throws Exception
     */
    public function generate(DocumentManager $dm, object $document): string
    {
        $len = 6;
        $tries = 0;
        $totalTries = 0;
        $idError = true;
        $documentClassName = get_class($document);
        $id = '';
        while ($idError) {
            $id = $this->generateStr($len);
            $idError = $dm->getRepository($documentClassName)->find($id) !== null;
            if ($tries > 2) {
                $len++;
                $tries = 0;
            }
            if ($totalTries > 10) {
                throw new RuntimeException("IdGenerator: Run out of IDs for " . $documentClassName);
            }
            $tries++;
            $totalTries++;
        }

        return $id;
    }

}
