<?php

namespace App\Controller;

use App\Repository\ContentRepository;
use App\Repository\SettingRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Content;
use App\Repository\ImageRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;


class HomeController extends AbstractController
{
    /**
     * @Route("/", name="home")
     */

    public function index(SettingRepository $settingRepository, ContentRepository $contentRepository)
    {
        $setting=$settingRepository->findAll();
        $slider=$contentRepository->findBy([],['title'=>'ASC'],5);
//        $slider=$contentRepository->findAll();

        #dump($slider);
        #die();

        return $this->render('home/index.html.twig', [
            'controller_name' => 'HomeController',
            'setting'=>$setting,
            'slider'=>$slider,
        ]);
    }

    /**
     * @Route("/content/{id}", name="content_show", methods={"GET"})
     */
    public function show(Content $content,$id, ImageRepository $imageRepository): Response
    {
//        $images=$imageRepository->findBy(['content'=>$id]);
//        $comments=$commentRepository->findBy(['contentid'=>$id, 'status'=>'True']);
        return $this->render('home/contentshow.html.twig', [
            'content' => $content,
//            'images' => $images,
//            'comments' => $comments,
        ]);
    }


    /**
     * @Route("/about", name="home_about", methods={"GET"})
     */
    public function about(SettingRepository $settingRepository): Response
    {
        $setting=$settingRepository->findAll();
        return $this->render('home/aboutus.html.twig', [
            'setting' => $setting,
        ]);
    }


}
