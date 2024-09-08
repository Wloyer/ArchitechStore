<?php

namespace App\Controller;

use App\Entity\File;
use App\Form\FileType;
use App\Repository\FileRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Validator\Constraints\File as ConstraintsFile;
use Symfony\Component\String\Slugger\SluggerInterface;

#[Route('/file')]
#[IsGranted('ROLE_USER')]
class FileController extends AbstractController
{
    #[Route('/', name: 'app_file_index', methods: ['GET', 'POST'])]
    
    /* public function index(FileRepository $fileRepository): Response
    {
        return $this->render('file/index.html.twig', [
            'files' => $fileRepository->findAll(),
        ]);
    } */
    public function index(Request $request, FileRepository $fileRepository): Response
    {
        // Récupérer les valeurs de filtre et de tri depuis la requête
        $name = $request->query->get('name');
        $format = $request->query->get('format');
        $orderBy = $request->query->get('orderBy');
        $direction = $request->query->get('direction', 'ASC'); // 'ASC' par défaut
    
        // Obtenir les fichiers filtrés/triés
        $files = $fileRepository->findByFilters($name, $format, $orderBy, $direction);
    
        // Rendre la vue avec les fichiers et les paramètres de filtre/tri
        return $this->render('file/index.html.twig', [
            'files' => $files,
            'name' => $name,
            'format' => $format,
            'orderBy' => $orderBy,
            'direction' => $direction
        ]);
    }

    #[Route('/new', name: 'app_file_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, SluggerInterface $slugger): Response
    {
        $file = new File();
        $form = $this->createForm(FileType::class, $file);
    
        $form->handleRequest($request);
    
        if ($form->isSubmitted() && $form->isValid()) {
            /** @var UploadedFile $uploadedFile */
            $uploadedFile = $form->get('attachment')->getData();
    
            if ($uploadedFile) {
                // Vérifier si l'objet UploadedFile est valide
                if (!$uploadedFile->isValid()) {
                    throw new \Exception('Le fichier n\'a pas été uploadé correctement.');
                }
            
                // Récupérer le nom du fichier original
                $originalFilename = pathinfo($uploadedFile->getClientOriginalName(), PATHINFO_FILENAME);
            
                // Obtenir la taille et le type MIME avant de déplacer le fichier
                $fileSize = $uploadedFile->getSize();
                $mimeType = $uploadedFile->getMimeType();
            
                if ($fileSize === false) {
                    throw new \Exception('Impossible de récupérer la taille du fichier.');
                }
            
                // Générer un nom de fichier sécurisé
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename . '-' . uniqid() . '.' . $uploadedFile->guessExtension();
            
                try {
                    // Déplacer le fichier vers le répertoire de destination
                    $uploadedFile->move(
                        $this->getParameter('upload_directory'),
                        $newFilename
                    );
                } catch (FileException $e) {
                    throw new \Exception('Le téléchargement du fichier a échoué : ' . $e->getMessage());
                }
            
                // Définir les attributs de l'entité File
                $file->setFileName($originalFilename);
                $file->setSize($fileSize);  // Utilise la taille obtenue avant le déplacement
                $file->setUploadDate(new \DateTime());
                $file->setType($mimeType);  // Utilise le type MIME obtenu avant le déplacement
                $file->setPath($newFilename);
            
                // Sauvegarder l'entité dans la base de données
                $entityManager->persist($file);
                $entityManager->flush();
            
                return $this->redirectToRoute('app_file_index', [], Response::HTTP_SEE_OTHER);
            }
        }

        return $this->render('file/new.html.twig', [
            'file' => $file,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_file_show', methods: ['GET'])]
    public function show(File $file): Response
    {
        return $this->render('file/show.html.twig', [
            'file' => $file,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_file_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, File $file, EntityManagerInterface $entityManager, SluggerInterface $slugger): Response
{
    $form = $this->createForm(FileType::class, $file);
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
        /** @var UploadedFile $uploadedFile */
        $uploadedFile = $form->get('attachment')->getData();

        if ($uploadedFile) {
            // Vérifier si l'objet UploadedFile est valide
            if (!$uploadedFile->isValid()) {
                throw new \Exception('Le fichier n\'a pas été uploadé correctement.');
            }

            // Récupérer le nom du fichier original
            $originalFilename = pathinfo($uploadedFile->getClientOriginalName(), PATHINFO_FILENAME);

            // Obtenir la taille et le type MIME avant de déplacer le fichier
            $fileSize = $uploadedFile->getSize();
            $mimeType = $uploadedFile->getMimeType();

            if ($fileSize === false) {
                throw new \Exception('Impossible de récupérer la taille du fichier.');
            }

            // Générer un nom de fichier sécurisé
            $safeFilename = $slugger->slug($originalFilename);
            $newFilename = $safeFilename . '-' . uniqid() . '.' . $uploadedFile->guessExtension();

            try {
                // Déplacer le fichier vers le répertoire de destination
                $uploadedFile->move(
                    $this->getParameter('upload_directory'),
                    $newFilename
                );

                // Supprimer l'ancien fichier si nécessaire
                $oldFilePath = $this->getParameter('upload_directory') . '/' . $file->getPath();
                if (file_exists($oldFilePath)) {
                    unlink($oldFilePath);
                }

                // Mettre à jour les attributs du fichier dans l'entité
                $file->setFileName($originalFilename);
                $file->setSize($fileSize);  // Utilise la taille obtenue avant le déplacement
                $file->setType($mimeType);  // Utilise le type MIME obtenu avant le déplacement
                $file->setPath($newFilename);

            } catch (FileException $e) {
                throw new \Exception('Le téléchargement du fichier a échoué : ' . $e->getMessage());
            }
        }

        $entityManager->flush();

        return $this->redirectToRoute('app_file_index', [], Response::HTTP_SEE_OTHER);
    }

    return $this->render('file/edit.html.twig', [
        'file' => $file,
        'form' => $form,
    ]);
}

    #[Route('/{id}', name: 'app_file_delete', methods: ['POST'])]
    public function delete(Request $request, File $file, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$file->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($file);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_file_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/file/stats', name: 'app_file_stats', methods: ['GET'])]
    public function stats(FileRepository $fileRepository): Response
    {
        $totalFiles = $fileRepository->countTotalFiles();
        $filesToday = $fileRepository->countFilesUploadedToday();

        return $this->render('file/stats.html.twig', [
            'totalFiles' => $totalFiles,
            'filesToday' => $filesToday,
        ]);
    }



}
