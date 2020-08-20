<?php

namespace App\Controller;

use APP\Entity\Setting;
use App\Entity\Admin\Messages;
use App\Entity\Content;
use App\Form\Admin\MessagesType;
use App\Repository\ContentRepository;
use App\Repository\SettingRepository;
use phpDocumentor\Reflection\Type;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\ImageRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\Bridge\Google\Smtp;
use Symfony\Component\Mailer\Bridge\Google\Smtp\GmailTransport;
use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mailer\Mailer\Transport\Smtp\EsmtpTransport;
use Symfony\Component\Mime\Email;
use PhpParser\Node\Expr\BinaryOp\NotEqual;


class HomeController extends AbstractController
{
    /**
     * @Route("/", name="home")
     */

    public function index(SettingRepository $settingRepository, ContentRepository $contentRepository)
    {
        $setting=$settingRepository->findAll();
        $slider=$contentRepository->findBy([],['id'=>'DESC'],5);
        $announcements=$contentRepository->findBy(['type'=>'Haber'],['title'=>'DESC'],5);
        $newcontent=$contentRepository->findBy(['type'=>'Haber'],['id'=>'DESC'],3);
        $etkinlik=$contentRepository->findBy(['type'=>'Etkinlik'],['id'=>'DESC'],3);
        $duyurular=$contentRepository->findBy(['type'=>'Duyuru'],['id'=>'DESC'],3);

        #dump($slider);
        #die();

        return $this->render('home/index.html.twig', [
            'controller_name' => 'HomeController',
            'setting'=>$setting,
            'slider'=>$slider,
            'announcements'=>$announcements,
            'newcontent'=>$newcontent,
            'etkinlik'=>$etkinlik,
            'duyurular'=>$duyurular,
        ]);
    }

    /**
     * @Route("/content/{id}", name="content_show", methods={"GET"})
     */
    public function show(Content $content,$id,SettingRepository $settingRepository, ImageRepository $imageRepository): Response
    {
        $setting=$settingRepository->findAll();
       $images=$imageRepository->findBy(['content'=>$id]);
//        $comments=$commentRepository->findBy(['contentid'=>$id, 'status'=>'True']);
        return $this->render('home/contentshow.html.twig', [
            'content' => $content,
            'images' => $images,
            'setting'=>$setting,
//            'comments' => $comments,
        ]);
    }


    /**
     * @Route("/about", name="home_about", methods={"GET","POST"})
     */
    public function about(SettingRepository $settingRepository): Response
    {
        $setting=$settingRepository->findAll();
        return $this->render('home/aboutus.html.twig', [
            'setting' => $setting,
        ]);
    }
    /**
     * @Route("/haberler", name="home_haberler", methods={"GET","POST"})
     */
    public function haberler(SettingRepository $settingRepository,ContentRepository $contentRepository): Response
    {
        $haberler=$contentRepository->findBy(['type'=>'Haber'],['id'=>'DESC']);
        $setting=$settingRepository->findAll();
        return $this->render('home/haberler.html.twig', [
            'haberler' => $haberler,
            'setting' => $setting,
        ]);
    }
    /**
     * @Route("/duyurular", name="home_duyurular", methods={"GET","POST"})
     */
    public function duyurular(SettingRepository $settingRepository,ContentRepository $contentRepository): Response
    {
        $duyurular=$contentRepository->findBy(['type'=>'Duyuru'],['id'=>'DESC']);
        $setting=$settingRepository->findAll();
        return $this->render('home/duyurular.html.twig', [
            'duyurular' => $duyurular,
            'setting' => $setting,
        ]);
    }

    /**
     * @Route("/etkinlikler", name="home_etkinlikler", methods={"GET","POST"})
     */
    public function etkinlikler(SettingRepository $settingRepository,ContentRepository $contentRepository): Response
    {
        $etkinlikler=$contentRepository->findBy(['type'=>'Etkinlik'],['id'=>'DESC']);
        $setting=$settingRepository->findAll();
        return $this->render('home/Etkinlikler.html.twig', [
            'etkinlikler' => $etkinlikler,
            'setting' => $setting,
        ]);
    }

    /**
     * @Route("/contact", name="home_contact", methods={"GET","POST"})
     */
    public function contact(SettingRepository $settingRepository,Request $request): Response
    {
        $message = new Messages();
        $form = $this->createForm(MessagesType::class, $message);
        $form->handleRequest($request);
        $submittedToken = $request->get('token');//Submit token ile key tooken oluşturuyoruz

        $setting=$settingRepository->findAll(); //Get setting data

        if ($form->isSubmitted()) {
            if($this->isCsrfTokenValid('form-message',$submittedToken)){  //form message ile eşitse içeri girecek
                $entityManager = $this->getDoctrine()->getManager();
                $message->setStatus('New');
                $message->setIp($_SERVER['REMOTE_ADDR']);
                $entityManager->persist($message);
                $entityManager->flush();
                $this->addFlash('success', 'Your message sent successfully');

                //************SEND EMAİL************
                $email=(new Email())
                    ->from($setting[0]->getSmtpemail()) //veritabanından
                    ->to($form['email']->getData())  //mailden
                    ->subject('Announcement')
                    ->html("Dear ".$form['name']->getData()."<br>
                                   <p> We will evaluate your requests and contact you as soon as possible</p>
                                   Thank your for messages <br>
                                   =========================================================
                                   <br>".$setting[0]->getCompany()." <br>
                                   Address :".$setting[0]->getAddress()." <br>
                                   Phone   : ".$setting[0]->getPhone()."<br>"
                    );
                $transport= new GmailTransport($setting[0]->getSmtpemail(),$setting[0]->getSmtppassword());
                $mailer= new Mailer($transport);
                $mailer->send($email);

                //**************************SEND EMAİL**************
                return $this->redirectToRoute('home_contact');
            }
        }

        $setting=$settingRepository->findAll();
        return $this->render('home/contact.html.twig', [
            'setting' => $setting,



        ]);
    }

}
