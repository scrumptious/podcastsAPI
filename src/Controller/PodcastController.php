<?php
/**
 * Created by PhpStorm.
 * User: nerd
 * Date: 07/04/2018
 * Time: 14:20
 */

namespace App\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

/**
 * @Route("/podcasts", name="podcasts_list")
 */
class PodcastController extends Controller {

  /**
   * @Route("/", name="podcasts_list")
   * @Route("/{podcast_id}", defaults={"podcast_id" = null}, name="podcast")
   * @Method("GET")
   */
  public function show($podcast_id = null) {
    if($podcast_id === null) {
      return new Response("<h2>List of all podcasts</h2>");
    }
    else {
      return new Response("<h2>Podcast " . $podcast_id . " episodes: </h2>");
    }
  }
}