<?php

namespace App\Controller;

use App\Entity\Content;
use App\Form\Content1Type;
use App\Form\ContentType;
use App\Repository\ContentRepository;
use App\Repository\SettingRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use phpDocumentor\Reflection\File;
use Symfony\Component\HttpFoundation\File\Exception\FileException;

/**
 * @Route("/user/content")
 */
class ContentController extends AbstractController
{
    /**
     * @Route("/", name="user_content_index", methods={"GET"})
     */
    public function index(ContentRepository $contentRepository,SettingRepository $settingRepository): Response
    {
        $user = $this->getUser();
        $setting=$settingRepository->findAll();
        return $this->render('content/index.html.twig', [
            'setting'=>$setting,
            'contents' => $contentRepository->findBy(['userid'=>$user->getId()],['id'=>'DESC']),
        ]);
    }

    /**
     * @Route("/new", name="user_content_new", methods={"GET","POST"})
     */
    public function new(Request $request,SettingRepository $settingRepository): Response
    {
        $content = new Content();
        $form = $this->createForm(Content1Type::class, $content);
        $form->handleRequest($request);
        $setting=$settingRepository->findAll();

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            /** @var file $file */
            //*****************file upload****************
            $file = $form['image']->getData(); //formdan gelen img get data olarak alıyor
            if ($file) { //eğer bu dosya varsa bu alana bir dosya select edilsiyse buraya gir
                $fileName = $this->generateUniqueFileName() . '.' . $file->guessExtension(); //bir tane rastgele dosya adı oluştur.4fffkjdf556f4fkfdldkf.jpeg gibi benzersiz bir isim olusturur
                //dosya adı bu olacak ve (.) uzantısını al birleştir
                try {
                    $file->move(  //dosyayı al move et
                        $this->getParameter('images_directory'), //image/direct kaydet
                        $fileName
                    );
                } catch (FileException $e) {

                }
                $content->setImage($fileName);
            }
            //**************file upload***************
            $user= $this->getUser();
            $content->setUserid($user->getId());
            $content->setStatus("New");
            $entityManager->persist($content);
            $entityManager->flush();

            return $this->redirectToRoute('user_content_index');
        }

        return $this->render('content/new.html.twig', [
            'content' => $content,
            'setting'=>$setting,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="user_content_show", methods={"GET"})
     */
    public function show(Content $content,SettingRepository $settingRepository): Response
    {
        $setting=$settingRepository->findAll();
        return $this->render('content/show.html.twig', [
            'content' => $content,
            'setting'=>$setting,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="user_content_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, Content $content,SettingRepository $settingRepository): Response
    {
        $form = $this->createForm(Content1Type::class, $content);
        $form->handleRequest($request);
        $setting=$settingRepository->findAll();

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var file $file */
            //*****************file upload****************
            $file = $form['image']->getData(); //formdan gelen img get data olarak alıyor
            if ($file) { //eğer bu dosya varsa bu alana bir dosya select edilsiyse buraya gir
                $fileName = $this->generateUniqueFileName() . '.' . $file->guessExtension(); //bir tane rastgele dosya adı oluştur.4fffkjdf556f4fkfdldkf.jpeg gibi benzersiz bir isim olusturur
                //dosya adı bu olacak ve (.) uzantısını al birleştir
                try {
                    $file->move(  //dosyayı al move et
                        $this->getParameter('images_directory'), //image/direct kaydet
                        $fileName
                    );
                } catch (FileException $e) {

                }
                $content->setImage($fileName);
            }
            
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('user_content_index');
        }

        return $this->render('content/edit.html.twig', [
            'content' => $content,
            'form' => $form->createView(),
            'setting'=>$setting,
        ]);
    }

    private function generateUniqueFileName(){ //benzersiz jpeg resmi adı oluşturur
        return md5(uniqid());
    }

    /**
     * @Route("/{id}", name="user_content_delete", methods={"DELETE"})
     */
    public function delete(Request $request, Content $content,SettingRepository $settingRepository): Response
    {
        if ($this->isCsrfTokenValid('delete'.$content->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($content);
            $entityManager->flush();
        }

        return $this->redirectToRoute('user_content_index');
    }
}
