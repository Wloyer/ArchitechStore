<?php

namespace App\Form;

use App\Entity\File;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\FileType as SymfonyFileType;
use Symfony\Component\Validator\Constraints\File as ConstraintsFile;


class FileType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('attachment', SymfonyFileType::class, [
                'label' => 'File (PDF, DOCX, etc.)',
                'mapped' => false,
                'required' => true,
                'constraints' => [
                    new ConstraintsFile([
                        'maxSize' => '20G',  
                        'mimeTypes' => [
                            'text/csv', 
                            'application/msword',
                            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                            'image/jpeg',
                            'image/png',
                            'application/pdf',
                            'application/zip',
                            'application/vnd.ms-excel',
                            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
                        ],
                        'mimeTypesMessage' => 'Veuillez soumettre un format valide',
                    ])
                ],
            ]);
    }


    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => File::class,
        ]);
    }
}
