<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\MasterService\HelperService3;
use Symfony\Component\Finder\Finder;
use App\Repository\PdfFilesRepository;




class PdfUploadController extends AbstractController
{

    /**
     * @Route("/pdf/upload", name="upload")
     */
    public function upload(Request $request, HelperService3 $helperService, PdfFilesRepository $pdfFilesRepository): Response
    {
        $showAlert = false;

        // Handling File Upload
        $uploadedFile = $request->files->get('pdf_file');

        if ($uploadedFile) {
            //checking for pdf files only to show alert
            $fileExt = pathinfo($uploadedFile->getClientOriginalName(), PATHINFO_EXTENSION);
            if($fileExt == 'pdf'){

            $destination = $this->getParameter('kernel.project_dir') . '/public/uploads'; //saving in public folder for now, we can configure for it to upload to Amazon S3 bucket
            $filename = pathinfo($uploadedFile->getClientOriginalName(), PATHINFO_FILENAME);
            
            $newFilename = $filename . '-' . uniqid() . '.' . $uploadedFile->guessExtension();

            // Moving the file to specified path
                $uploadedFile->move(
                    $destination,
                    $newFilename
                );

            // storing the content of pdf in database
            $pdfFilesRepository->savePDFContent($destination."/".$newFilename, $newFilename);

            } else {
                $showAlert = true;
            }
        } 

        // Listing files in the "public/uploads" directory (we can use separate api and page but for simplicity doing here)
        $finder = new Finder();
        $uploadedFiles = $finder->in($this->getParameter('kernel.project_dir') . '/public/uploads');

        //search result logic - 1 using Symfony Parser bundle
        $searchTerm = $request->query->get('search_within_pdf'); 
        $searchResults = [];

        if ($searchTerm) {
            $finder = new Finder();
            $uploadedFiles = $finder->in($this->getParameter('kernel.project_dir') . '/public/uploads');
            foreach ($uploadedFiles as $file) {
                $pdfFilePath = $file->getPathname();
                $found = $helperService->searchInPdf($pdfFilePath, $searchTerm);
                if ($found) {
                    $searchResults[] = $file;
                }
            }
        } else {
            $searchResults = null;
        }

        //search result logic -2 using DB query
        // $searchTerm = $request->query->get('search_within_pdf'); 
        // $result = [];


        // if ($searchTerm) {
        //     $result = $pdfFilesRepository->searchWithinPdf($searchTerm);
        // } else {
        //     $result = null;
        // }

        //return to template
        return $this->render('file_upload/index.html.twig', [
            'uploadedFiles' => $uploadedFiles,
            'searchResults' => $searchResults,
            'showAlert' => $showAlert,
            // 'matchedPdfFiles' => $result,
        ]);
    }
    

}