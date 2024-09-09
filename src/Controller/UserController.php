<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use App\Repository\FileRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\Mime\Email;

#[Route('/user')]
class UserController extends AbstractController
{
    #[Route('/', name: 'app_user_index', methods: ['GET'])]
    #[IsGranted('ROLE_ADMIN')]
    public function index(UserRepository $userRepository): Response
    {
        return $this->render('user/index.html.twig', [
            'users' => $userRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_user_new', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $user = new User();
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($user);
            $entityManager->flush();

            return $this->redirectToRoute('app_user_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('user/new.html.twig', [
            'user' => $user,
            'form' => $form,
        ]);
    }

    #[Route('/{id<\d+>}', name: 'app_user_show', methods: ['GET'])]
    #[IsGranted('ROLE_ADMIN')]
    public function show(int $id, UserRepository $userRepository): Response
    {
        // Récupérer l'utilisateur par ID
        $user = $userRepository->find($id);

        // Si l'utilisateur n'est pas trouvé, lever une exception ou retourner une page 404
        if (!$user) {
            throw $this->createNotFoundException('Utilisateur non trouvé.');
        }

        return $this->render('user/show.html.twig', [
            'user' => $user,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_user_edit', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function edit(Request $request, User $user, EntityManagerInterface $entityManager): Response
    {
        // Passer l'option is_admin = true pour permettre à l'admin de modifier certains champs
        $form = $this->createForm(UserType::class, $user, ['is_admin' => $this->isGranted('ROLE_ADMIN')]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user->setUpdatedAt(new \DateTime());
            $entityManager->flush();

            $this->addFlash('success', 'Profil mis à jour avec succès.');

            return $this->redirectToRoute('app_user_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('user/edit.html.twig', [
            'user' => $user,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}', name: 'app_user_delete', methods: ['POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function delete(Request $request, User $user, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$user->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($user);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_user_index', [], Response::HTTP_SEE_OTHER);
    }
    #[Route('/my-files', name: 'app_user_files', methods: ['GET'])]
    #[IsGranted('ROLE_USER')] // Vérifier que l'utilisateur est connecté
    public function userFiles(FileRepository $fileRepository): Response
    {
        // Récupérer l'utilisateur connecté
        $user = $this->getUser();

        // Obtenir les fichiers appartenant à l'utilisateur connecté
        $files = $fileRepository->findBy(['user' => $user]);

        return $this->render('user/my_files.html.twig', [
            'files' => $files,
        ]);
    }

    #[Route('/profil', name: 'app_user_profile', methods: ['GET'])]
    #[IsGranted('ROLE_USER')]
    public function profile(): Response
    {
        $user = $this->getUser();

        if (!$user) {
            throw $this->createNotFoundException('Utilisateur non trouvé.');
        }

        return $this->render('user/profile.html.twig', [
            'user' => $user,
        ]);
    }

    #[Route('/user/profil/edit', name: 'app_user_profile_edit', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_USER')]
    public function editProfile(Request $request, EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordHasher): Response
    {
        /** @var \App\Entity\User $user */
        $user = $this->getUser();

        if (!$user) {
            throw $this->createAccessDeniedException('Vous devez être connecté pour modifier votre profil.');
        }

        // Créer le formulaire
        $form = $this->createForm(UserType::class, $user, ['is_admin' => false]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Récupérer le nouveau mot de passe soumis
            $newPassword = $form->get('password')->getData();
            if ($newPassword) {
                // Hacher le nouveau mot de passe et l'assigner à l'utilisateur
                $hashedPassword = $passwordHasher->hashPassword($user, $newPassword);
                $user->setPassword($hashedPassword);
            }

            // Mettre à jour l'utilisateur
            $user->setUpdatedAt(new \DateTime());
            $entityManager->flush();

            // Message de succès
            $this->addFlash('success', 'Votre profil a été mis à jour avec succès.');

            return $this->redirectToRoute('app_user_profile');
        }

        return $this->render('user/edit_profile.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/profil/delete', name: 'app_user_profile_delete', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function deleteProfile(Request $request, EntityManagerInterface $entityManager, FileRepository $fileRepository, UserRepository $userRepository, MailerInterface $mailer): Response
    {
        $user = $this->getUser();

        if (!$user) {
            throw $this->createNotFoundException('Utilisateur non trouvé.');
        }

        if ($this->isCsrfTokenValid('delete' . $user->getId(), $request->request->get('_token'))) {
            // Récupérer le nom et l'email avant suppression
            $userName = $user->getFirstName() . ' ' . $user->getLastName();
            $userEmail = $user->getEmail();

            // Récupérer et supprimer les fichiers de l'utilisateur
            $files = $fileRepository->findBy(['user' => $user]);
            $fileCount = count($files); // Nombre de fichiers supprimés

            foreach ($files as $file) {
                // Supprimer le fichier du système de fichiers
                $filePath = $this->getParameter('upload_directory') . '/' . $file->getPath();
                if (file_exists($filePath)) {
                    unlink($filePath); // Supprimer le fichier du disque
                }

                // Supprimer le fichier de la base de données
                $entityManager->remove($file);
            }

            // Supprimer l'utilisateur connecté
            $entityManager->remove($user);
            $entityManager->flush();

            // Envoyer un email de confirmation à l'utilisateur
            $this->sendConfirmationEmail($mailer, $userEmail, $userName);

            // Envoyer une notification par email aux administrateurs
            $this->notifyAdminsOfDeletion($mailer, $userName, $fileCount, $userRepository);

            // Invalider la session et déconnecter l'utilisateur
            $this->container->get('security.token_storage')->setToken(null);
            $request->getSession()->invalidate();

            // Rediriger vers la page de déconnexion
            return $this->redirectToRoute('app_logout');
        }

        return $this->redirectToRoute('app_user_profile');
    }
    private function sendConfirmationEmail(MailerInterface $mailer, string $userEmail, string $userName): void
    {
        $email = (new Email())
            ->from('noreply@votre-site.com')
            ->to($userEmail)
            ->subject('Confirmation de suppression de votre compte')
            ->html("
            <p>Bonjour $userName,</p>
            <p>Votre compte a été supprimé avec succès. Nous sommes désolés de vous voir partir.</p>
            <p>Si vous avez des questions ou souhaitez revenir, n'hésitez pas à nous contacter.</p>
            <p>Cordialement,<br>L'équipe de gestion des utilisateurs.</p>
        ");

        $mailer->send($email);
    }
    private function notifyAdminsOfDeletion(MailerInterface $mailer, string $userName, int $fileCount, UserRepository $userRepository): void
    {
        // Récupérer tous les administrateurs avec le rôle 'ROLE_ADMIN'
        $adminEmails = $userRepository->findByRole('ROLE_ADMIN');

        foreach ($adminEmails as $admin) {
            $adminEmail = $admin->getEmail();

            $email = (new Email())
                ->from('noreply@votre-site.com')
                ->to($adminEmail)
                ->subject('Un utilisateur a supprimé son compte')
                ->html("
                <p>Bonjour,</p>
                <p>Le client <strong>$userName</strong> a supprimé son compte.</p>
                <p>Nombre de fichiers supprimés : <strong>$fileCount</strong></p>
                <p>Cordialement,<br>L'équipe de gestion des utilisateurs.</p>
            ");

            $mailer->send($email);
        }
    }

}
