<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('email')
           // ->add('roles')
           ->add('roles', ChoiceType::class,[
               'choices' => [
                   'ADMIN' => 'ROLE_ADMIN',
                   'USER' => 'ROLE_USER'],
           ])
            ->add('password')
            ->add('name')
            ->add('surname')
            ->add('image', FileType::class,[
                'label' => 'Category Image',

                'mapped' => false,

                'required'=> false,

                'constraints' => [
                    new \Symfony\Component\Validator\Constraints\File([
                        'maxSize' => '1024k',
                        'mimeTypes' => [
                            'image/*',
                        ],
                        'mimeTypesMessage' => 'Please upload a valid image file',
                    ])
                ],
            ])
            ->add('status', ChoiceType::class,[
                'choices' => [
                    'True' => 'True',
                    'False' => 'False'],
            ])
//            ->add('created_at')
//            ->add('updated_at')
        ;

//      Rolleri düzenlerken ve eklerken yaşanan formların getiirlmesindeki hataları engelliyor.
        $builder->get('roles')
            ->addModelTransformer(new CallbackTransformer(
                function ($rolesArray) {
                    // transform the array to a string
                    return count($rolesArray)? $rolesArray[0]: null;
                },
                function ($rolesString) {
                    // transform the string back to an array
                    return [$rolesString];
                }
            ));
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
