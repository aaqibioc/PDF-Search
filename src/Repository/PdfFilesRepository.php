<?php

namespace App\Repository;

use App\Entity\PdfFiles;
use App\MasterService\HelperService3;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManager;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<PdfFiles>
 *
 * @method PdfFiles|null find($id, $lockMode = null, $lockVersion = null)
 * @method PdfFiles|null findOneBy(array $criteria, array $orderBy = null)
 * @method PdfFiles[]    findAll()
 * @method PdfFiles[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PdfFilesRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry, HelperService3 $helperService)
    {
        parent::__construct($registry, PdfFiles::class);
        $this->em =$this->getEntityManager();
        $this->helper = $helperService;

    }

    //Saves Pdf content in database
    public function savePDFContent($fullPath, $fileName): array
    {
        $content = $this->helper->parseContent($fullPath);

        $newEntry = new PdfFiles;
        $newEntry->setTitle($fileName);
        $newEntry->setContent($content);

        $this->em->persist($newEntry);
        $this->em->flush();
        return ['id' => $newEntry->getId()];
    }

    //performs a db query search for searched terms
    public function searchWithinPdf($searchTerm): array
    {
       if(!empty($searchTerm)){
        $query = $this->em->createQueryBuilder();
        $query->select("P.title")
                ->from(PdfFiles::class, 'P')
                ->where('P.content LIKE :content')
                ->setParameter('content', '%'. $searchTerm .'%');
            $response = $query->getQuery()->getArrayResult();
            foreach ($response as $key => $value) {
                $newResponse[] =  $value['title'];
            }
            return $newResponse;
       }
        return [];
    }
}
