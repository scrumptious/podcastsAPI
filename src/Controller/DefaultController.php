<?php
/**
 * Created by PhpStorm.
 * User: nerd
 * Date: 07/04/2018
 * Time: 14:34
 */

namespace App\Controller;
use Symfony\Component\HttpFoundation\Response;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller {
  /**
   * @Route("/")
   */
  public function welcome() {
    return new Response("<p>Welcome to podcasts API</p>");
  }
}