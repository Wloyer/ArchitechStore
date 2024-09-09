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
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Validator\Constraints\File as ConstraintsFile;
use Symfony\Component\String\Slugger\SluggerInterface;

#[Route('/file')]
#[IsGranted('ROLE_USER')]
class FileController extends AbstractController
{
    #[Route('/', name: 'app_file_index', methods: ['GET', 'POST'])]
    public function index(Request $request, FileRepository $fileRepository): Response
    {
        // Récupérer les valeurs de filtre et de tri depuis la requête
        $name = $request->query->get('name');
        $format = $request->query->get('format');
        $orderBy = $request->query->get('orderBy');
        $direction = $request->query->get('direction', 'ASC'); // 'ASC' par défaut

        // Obtenir les fichiers filtrés/triés
        if ($this->isGranted('ROLE_ADMIN')) {
            // Les administrateurs peuvent voir tous les fichiers
            $files = $fileRepository->findByFilters($name, $format, $orderBy, $direction);
        } else {
            // Les utilisateurs normaux ne voient que leurs propres fichiers
            $files = $fileRepository->findByUserAndFilters($this->getUser(), $name, $format, $orderBy, $direction);
        }

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

                // Associer l'utilisateur connecté au fichier
                $file->setUser($this->getUser()); // Ajout de cette ligne

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
        // Vérifier que l'utilisateur connecté est bien le propriétaire du fichier ou un admin
        if ($file->getUser() !== $this->getUser() && !$this->isGranted('ROLE_ADMIN')) {
            throw $this->createAccessDeniedException('Vous ne pouvez pas voir ce fichier.');
        }

        return $this->render('file/show.html.twig', [
            'file' => $file,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_file_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, File $file, EntityManagerInterface $entityManager, SluggerInterface $slugger): Response
    {
        // Vérifier que l'utilisateur connecté est bien le propriétaire du fichier ou un admin
        if ($file->getUser() !== $this->getUser() && !$this->isGranted('ROLE_ADMIN')) {
            throw $this->createAccessDeniedException('Vous ne pouvez pas modifier ce fichier.');
        }

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

            // Mise à jour des autres informations du fichier
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
        // Vérifier que l'utilisateur connecté est bien le propriétaire du fichier ou un admin
        if ($file->getUser() !== $this->getUser() && !$this->isGranted('ROLE_ADMIN')) {
            throw $this->createAccessDeniedException('Vous ne pouvez pas supprimer ce fichier.');
        }

        if ($this->isCsrfTokenValid('delete'.$file->getId(), $request->get('_token'))) {
            // Supprimer le fichier du système de fichiers
            $filePath = $this->getParameter('upload_directory') . '/' . $file->getPath();
            if (file_exists($filePath)) {
                unlink($filePath);
            }

            // Supprimer l'entité de la base de données
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

    #[Route('/my-files', name: 'app_user_files', methods: ['GET'])]
    public function userFiles(FileRepository $fileRepository): Response
    {
        $user = $this->getUser(); // Récupérer l'utilisateur connecté

        // Obtenir uniquement les fichiers de l'utilisateur connecté
        $files = $fileRepository->findBy(['user' => $user]);

        return $this->render('file/user_files.html.twig', [
            'files' => $files,
        ]);
    }
}
