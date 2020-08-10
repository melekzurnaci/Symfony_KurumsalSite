<?php

namespace App\Controller\Admin;

use App\Entity\Image;
use App\Form\ImageType;
use App\Repository\ImageRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\File\Exception\FileException;

/**
 * @Route("/admin/image")
 */
class ImageController extends AbstractController
{
    /**
     * @Route("/", name="admin_image_index", methods={"GET"})
     */
    public function index(ImageRepository $imageRepository): Response
    {
        return $this->render('admin/image/index.html.twig', [
            //bütün veriler findall ile çekiliyor.
            'images' => $imageRepository->findAll(),
        ]);
    }

    /**
     * @Route("/new/{id}", name="admin_image_new", methods={"GET","POST"})
     */
    public function new(Request $request,$id,ImageRepository $imageRepository): Response
    {
        //Echo "Content id:" . $id;
        //die();
        $image = new Image();
        $form = $this->createForm(ImageType::class, $image);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();

// /////******** file upload ****** //////////
            /** @var file $file*/
            $file=$form['image']->getData();
            if($file){
                $fileName=$this->generateUniqueFileName() . '.' . $file->guessExtension();
                try{
                    $file->move(
                        $this->getParameter('images_directory'),
                        $fileName
                    );
                }catch (FileException $e){

                }
                $image->setImage($fileName);
            }
// ///******** file upload *******///
            $entityManager->persist($image);
            $entityManager->flush();

            return $this->redirectToRoute('admin_image_new',['id'=>$id]);
        }
        //findall yerine findby ile gönderilen id ye eşit olanları getiriyor.
        $images= $imageRepository->findBy(['content'=>$id]);

        return $this->render('admin/image/new.html.twig', [
            'image' => $image,
            'id'=> $id,
            'images'=>$images,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="admin_image_show", methods={"GET"})
     */
    public function show(Image $image): Response
    {
        return $this->render('admin/image/show.html.twig', [
            'image' => $image,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="admin_image_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, Image $image): Response
    {
        $form = $this->createForm(ImageType::class, $image);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('admin_image_index');
        }

        return $this->render('admin/image/edit.html.twig', [
            'image' => $image,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}/{cid}", name="admin_image_delete", methods={"DELETE"})
     */
    public function delete(Request $request,$cid, Image $image): Response
    {
        echo "content id:" .$cid;
        die();

        if ($this->isCsrfTokenValid('delete'.$image->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($image);
            $entityManager->flush();
        }

        return $this->redirectToRoute('admin_image_new',['id'=> $cid]);
    }

    private function generateUniqueFileName()
    {
        return md5(uniqid());
    }
}
