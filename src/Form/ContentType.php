<?php

namespace App\Form;
use App\Entity\Category;
use App\Entity\Content;
use FOS\CKEditorBundle\Form\Type\CKEditorType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;


class ContentType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {

             $builder

                 ->add('title')
                 ->add('type', ChoiceType::class,[
                     'choices' => [
                         'Haber' => 'Haber',
                         'Duyuru' => 'Duyuru',
                         'Etkinlik' => 'Etkinlik',
                     ],
                 ])
                 ->add('category', EntityType::class,[
                     'class' => Category::class,
                     'choice_label' => 'title',
                 ])
                 ->add('keywords')
                 ->add('description')
                 ->add('detail', CKEditorType::class, array(
                     'config' =>array(
                         'uiColor' =>'#ffffff',
                         //...
                     ),
                 ))
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
             ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Content::class,
        ]);
    }
}
