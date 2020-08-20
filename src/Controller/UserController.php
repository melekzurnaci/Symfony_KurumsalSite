<?php

namespace App\Controller;

use App\Entity\Admin\Comment;
use App\Entity\User;
use App\Form\Admin\ChoiceType;
use App\Form\Admin\CommentType;
use App\Form\UserType;
use App\Repository\Admin\CommentRepository;
use App\Repository\SettingRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use App\Repository\ContentRepository;

/**
 * @Route("/user")
 */
class UserController extends AbstractController
{
    /**
     * @Route("/", name="user_index", methods={"GET"})
     */
    public function index(SettingRepository $settingRepository): Response
    {
        $setting = $settingRepository->findAll();
        return $this->render('user/show.html.twig', [
            'setting' => $setting,

        ]);
    }

    /**
     * @Route("/comments", name="user_comments", methods={"GET"})
     */
    public function comments(CommentRepository $commentRepository,SettingRepository $settingRepository): Response
    {
        $setting = $settingRepository->findAll();
        $user=$this->getUser();
        $comments=$commentRepository->getAllCommentsUser($user->getId());
        return $this->render('user/comments.html.twig',[
            'comments'=>$comments,
            'setting' => $setting,
        ]);
    }


    /**
     * @Route("/announcements", name="user_announcements", methods={"GET"})
     */
    public function announcements(): Response
    {
        return $this->render('user/announcements.html.twig');
    }

    /**
     * @Route("/news", name="user_news", methods={"GET"})
     */
    public function news(): Response
    {
        return $this->render('user/news.html.twig');
    }

    /**
     * @Route("/activities", name="user_activities", methods={"GET"})
     */
    public function activities(): Response
    {
        return $this->render('user/activities.html.twig');
    }


    /**
     * @Route("/new", name="user_new", methods={"GET","POST"})
     */
    public function new(Request $request, UserPasswordEncoderInterface $passwordEncoder): Response
    {
        $user = new User();
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();

            //*****************file upload****************
            $file = $form['image']->getData(); //formdan gelen img get data olarak alıyor
            if ($file) { //eğer bu dosya varsa bu alana bir dosya select edilsiyse buraya gir
                $fileName = $this->generateUniqueFileName() . '.' . $file->guessExtension(); //bir tane rastgele dosya adı oluştur.4fffkjdf556f4fkfdldkf.jpeg gibi benzersiz bir isim olusturur
                //dosya adı bu olacak ve (.) uzantısını al birleştir
                try {
                    $file->move(  //dosyayı al move et
                        $this->getParameter('images_directory'), //image/direct kaydet demek. in servis.yaml dosyaında
                        $fileName
                    );
                } catch (FileException $e) {

                }
                $user->setImage($fileName);
            }

            // encode the plain password
            $user->setPassword(
                $passwordEncoder->encodePassword(
                    $user,
                    $form->get('password')->getData()
                )
            );
            //**************file upload***************
            $entityManager->persist($user);
            $entityManager->flush();

            return $this->redirectToRoute('user_index');
        }

        return $this->render('user/new.html.twig', [
            'user' => $user,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="user_show", methods={"GET"})
     */
    public function show(User $user): Response
    {
        return $this->render('user/show.html.twig', [
            'user' => $user,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="user_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, $id, User $user, UserPasswordEncoderInterface $passwordEncoder): Response
    {   //edit işleminde  id numarasının tarayıcıdan değiştrilerek başka kullanıcı bilgilerine erişilmesini engellemiş olduk.
        $user = $this->getUser(); //GET LOGİN USER DATA
        if ($user->getId() != $id) {
            return $this->redirectToRoute('home');
        }

        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            //*****************file upload****************
            $file = $form['image']->getData(); //formdan gelen img get data olarak alıyor
            if ($file) { //eğer bu dosya varsa bu alana bir dosya select edilsiyse buraya gir
                $fileName = $this->generateUniqueFileName() . '.' . $file->guessExtension(); //bir tane rastgele dosya adı oluştur.4fffkjdf556f4fkfdldkf.jpeg gibi benzersiz bir isim olusturur
                //dosya adı bu olacak ve (.) uzantısını al birleştir
                try {
                    $file->move(  //dosyayı al move et
                        $this->getParameter('images_directory'), //image/direct kaydet demek. in servis.yaml dosyaında
                        $fileName
                    );
                } catch (FileException $e) {

                }
                $user->setImage($fileName);
            }

            // encode the plain password
            $user->setPassword(
                $passwordEncoder->encodePassword(
                    $user,
                    $form->get('password')->getData()
                )
            );
            //**************file upload***************

            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('user_index');
        }

        return $this->render('user/edit.html.twig', [
            'user' => $user,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="user_delete", methods={"DELETE"})
     */
    public function delete(Request $request, User $user): Response
    {
        if ($this->isCsrfTokenValid('delete' . $user->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($user);
            $entityManager->flush();
        }

        return $this->redirectToRoute('user_index');
    }

    private function generateUniqueFileName()
    {
        return md5(uniqid());
    }

    /**
     * @Route("/newcomment/{id}", name="user_new_comment", methods={"GET","POST"})
     */
    public function newcomment(Request $request,$id): Response
    {
        $comment = new Comment();
        $form = $this->createForm(UserType::class, $comment);
        $form->handleRequest($request);
        $submittedToken=$request->request->get('token'); //Submit token ile key tooken oluşturuyoruz

        if ($form->isSubmitted()) {
            if($this->isCsrfTokenValid('comment', $submittedToken)) {

                $entityManager = $this->getDoctrine()->getManager();
                $comment->setStatus('New');
                $comment->setIp($_SERVER['REMOTE_ADDR']);
                $comment->setContentid($id);
                $user=$this->getUser();
                $comment->setUserid($user->getId());

                $entityManager->persist($comment);
                $entityManager->flush();
                $this->addFlash('success', 'Your comment has been sent successfully');
                return $this->redirectToRoute('content_show', ['id' => $id]);
            }
        }
        return $this->redirectToRoute('content_show', ['id' => $id]);
    }
}