<?php
  namespace App\Controller;
use Symfony\Component\HttpFoundation\Response;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

  class ArticleController {
    /**
     * @Route("/")
     */
    public function homepage() {
      return new Response('haha first page doone!');
      // return new Response('my first page already!');
    }
    /**
     * @Route("/news/{slug}")
     */
    public function show($slug) {
      return new Response('This ' . $slug . ' in future space to show article');
    }
  }