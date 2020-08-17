<?php

namespace App\Controller;

use App\Entity\Image;
use App\Form\Image1Type;
use App\Form\ImageType;
use App\Repository\ImageRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/user/image")
 */
class ImageController extends AbstractController
{
    /**
     * @Route("/", name="user_image_index", methods={"GET"})
     */
    public function index(ImageRepository $imageRepository): Response
    {
        return $this->render('image/index.html.twig', [
            'images' => $imageRepository->findAll(),
        ]);

    }

    /**
     * @Route("/new/{id}", name="user_image_new", methods={"GET","POST"})
     */
    public function new(Request $request,$id,ImageRepository $imageRepository): Response
    {
        $image = new Image();
        $form = $this->createForm(ImageType::class, $image);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();

            $file=$form['image']->getData();
            if($file){
                $fileName=$this->generateUniqueFileName().'.'.$file->guessExtension();
                try{
                    $file->move(
                        $this->getParameter('images_directory'),
                        $fileName
                    );
                }catch (FileException $e){

                }
                $image->setImage($fileName);
            }


            $entityManager->persist($image);
            $entityManager->flush();

            return $this->redirectToRoute('user_image_new',['id'=>$id]);
        }
        $images= $imageRepository->findBy(['content'=>$id]);

        return $this->render('image/new.html.twig', [
            'image' => $image,
            'id'=> $id,
            'images' => $images,
            'form' => $form->createView(),
        ]);
    }
    /**
     * @Route("/{id}", name="admin_image_show", methods={"GET"})
     */

    private function generateUniqueFileName(){
        return md5(uniqid());
    }


    /**
     * @Route("/{id}", name="user_image_show", methods={"GET"})
     */
    public function show(Image $image): Response
    {
        return $this->render('image/show.html.twig', [
            'image' => $image,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="user_image_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, Image $image): Response
    {
        $form = $this->createForm(Image1Type::class, $image);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('user_image_index');
        }

        return $this->render('image/edit.html.twig', [
            'image' => $image,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="user_image_delete", methods={"DELETE"})
     */
    public function delete(Request $request, Image $image): Response
    {
        if ($this->isCsrfTokenValid('delete'.$image->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($image);
            $entityManager->flush();
        }

        return $this->redirectToRoute('user_image_index');
    }
}
