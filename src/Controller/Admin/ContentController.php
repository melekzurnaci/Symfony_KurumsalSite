<?php

namespace App\Controller\Admin;

use App\Entity\Content;
use App\Form\ContentType;
use App\Repository\ContentRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/admin/content")
 */
class ContentController extends AbstractController
{
    /**
     * @Route("/", name="admin_content_index", methods={"GET"})
     */
    public function index(ContentRepository $contentRepository): Response
    {
        return $this->render('admin/content/index.html.twig', [
            'contents' => $contentRepository->findAll(),
        ]);
    }
//    /**
//     * @Route("/", name="admin_content_index", methods={"GET"})
//     */
//    public function index(ContentRepository $contentRepository): Response
//    {
//        $contents=$contentRepository->getAllContents();
//        return $this->render('admin/content/index.html.twig', [
//            'contents' => $contents,
//        ]);
//    }

    /**
     * @Route("/new", name="admin_content_new", methods={"GET","POST"})
     */
    public function new(Request $request): Response
    {
        $content = new Content();
        $form = $this->createForm(ContentType::class, $content);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();

            //*****************file upload****************
            $file = $form['image']->getData(); //formdan gelen img get data olarak alıyor
            if($file) { //eğer bu dosya varsa bu alana bir dosya select edilsiyse buraya gir
                $fileName=$this->generateUniqueFileName().'.'. $file->guessExtension(); //bir tane rastgele dosya adı oluştur.4fffkjdf556f4fkfdldkf.jpeg gibi benzersiz bir isim olusturur
                //dosya adı bu olacak ve (.) uzantısını al birleştir
                try{
                    $file->move(  //dosyayı al move et
                        $this->getParameter('images_directory'), //image/direct kaydet demek. in servis.yaml dosyaında
                        $fileName
                    );
                } catch (FileException $e){

                }
                $content->setImage($fileName);
            }

            //**************file upload***************
            $entityManager->persist($content);
            $entityManager->flush();

            return $this->redirectToRoute('admin_content_index');
        }

        return $this->render('admin/content/new.html.twig', [
            'content' => $content,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="admin_content_show", methods={"GET"})
     */
    public function show(Content $content): Response
    {
        return $this->render('admin/content/show.html.twig', [
            'content' => $content,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="admin_content_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, Content $content): Response
    {
        $form = $this->createForm(ContentType::class, $content);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $file=$form['image']->getData();
            if($file){
                $fileName=$this->generateUniqueFileName().'.'. $file->guessExtension();
                try{
                    $file->move(
                        $this->getParameter('images_directory'),
                        $fileName
                    );
                } catch (FileException $e){

                }
                $content->setImage($fileName);
            }
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('admin_content_index');
        }

        return $this->render('admin/content/edit.html.twig', [
            'content' => $content,
            'form' => $form->createView(),
        ]);
    }

    private function generateUniqueFileName(){ //benzersiz jpeg resmi adı oluşturur
        return md5(uniqid());
    }

    /**
     * @Route("/{id}", name="admin_content_delete", methods={"DELETE"})
     */
    public function delete(Request $request, Content $content): Response
    {
        if ($this->isCsrfTokenValid('delete'.$content->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($content);
            $entityManager->flush();
        }

        return $this->redirectToRoute('admin_content_index');
    }
}
