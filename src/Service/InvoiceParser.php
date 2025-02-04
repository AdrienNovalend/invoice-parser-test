<?php

declare(strict_types=1);


namespace App\Service;

use Doctrine\ORM\EntityManagerInterface;
use Exception;


class InvoiceParser
{
    private EntityManagerInterface $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    /**
     * @throws Exception
     */
    public function parse(string $filePath): void
    {
        //On peux ajouter des logs mais je vais pas y passer 1h non plus ¯\_(ツ)_/¯
        switch (true) {
            case str_contains($filePath, 'json'):
                $content = file_get_contents($filePath);

                if ($content === false) {
                    throw new Exception(sprintf("Could not read json file with path: %s", $filePath));
                }
                $data = json_decode($content , true);
                foreach ($data as $row) {
                    $query = $this->em->createQueryBuilder()
                        ->update('App\Entity\Invoice', 'i')
                        ->set('i.amount', ':amount')
                        ->where('i.name = :name')
                        ->getQuery();

                    $query->execute([
                        'amount' => $row['montant'],
                        'name' => $row['nom']
                    ]);

                }
                break;
            case str_contains($filePath, 'csv'):
                $content = file($filePath);

                if ($content === false) {
                    throw new Exception(sprintf("Could not read csv file with path: %s", $filePath));
                }
                $data = array_map(function($row) {
                    return str_getcsv($row, "\t");
                },$content);

                foreach ($data as $row) {
                    $query = $this->em->createQueryBuilder()
                        ->update('App\Entity\Invoice', 'i')
                        ->set('i.amount', ':amount')
                        ->where('i.name = :name')
                        ->getQuery();

                    $query->execute([
                        'amount' => $row[0],
                        'name' => $row[2]
                    ]);

                }
                break;
        }
    }
}
