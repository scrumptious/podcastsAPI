<?php

namespace App\Controller;

use Aws\S3\Exception\S3Exception;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\HttpFoundation\Response;
//use Aws\S3\S3Client;
use Aws\Sdk;


/**
 * @Route("/podcasts/{podcast_id}/episodes")
 */
class EpisodeController extends Controller {

  public function __construct() {

    $sharedConfig = [
      'version' => 'latest',
      'region'  => 'eu-west-2'
    ];
    $sdk = new Sdk($sharedConfig);
    $this->S3Client = $sdk->createS3();
    $this->bucketName = "jasiurski.cba.pl.test";

  }

  /**
   * @Route("/{id}",
   *   requirements={"id" = "\d+"}, defaults={"id" = 1})
   * @Method("POST")
   */
  public function create($podcast_id, $id) {
    //if not exist create an episode with {id}
    //otherwise return error http code
    $this->keyFromParams = 'podcasts/' . $podcast_id . '/episodes/' . $id;
    $this->url = "https://s3.eu-west-2.amazonaws.com/" . $this->bucketName . '/' . $this->keyFromParams;
    $json = [
      "Id" => 1,
      "DownloadUrl" => $this->url,
      "Title" => "podcast API task",
      "Description" => "Following task is required to be completed in order to prove your top quality coding skills",
      "EpisodeNumber" => $id,
      "Created date" => "07-04-2018"
    ];
    $testData = json_encode($json);
    $result = $this->S3Client->putObject([
      'Bucket' => $this->bucketName,
      'Key'    => $this->keyFromParams,
      'Body'   => $testData,
      'Content-Type' => "text/plain"
    ]);
    return new Response("result = " . $result);
  }

  /**
   * @Route("/", name="episodes_list")
   * @Route("/{id}", name="episode", defaults={"id" = null})
   * @Method("GET")
   */
  public function show($podcast_id, $id = null) {
    $this->keyFromParams = 'podcasts/' . $podcast_id . '/episodes/' . $id;

    //if no id param passed, then list all episodes,
    //otherwise show details about episode of id = {$id}
    try {
      $result = $this->S3Client->getObject([
        'Bucket' => $this->bucketName,
        'Key' => $this->keyFromParams,
        'response-content-type' => "application/json"
      ]);
    } catch(S3Exception $e) {
      echo 'Exception caught ' . $e->getMessage();
    }
    if(isset($result)) {
      $jsonResult = json_decode($result['Body']);
      return $this->render('podcast/show.html.twig', array(
        'fromParams' => $this->keyFromParams,
        'podcast_id' => $podcast_id,
        'id' => $id,
        'result' => $result['Body'],
        'Id' => $jsonResult->Id,
        'DownloadUrl' => $jsonResult->DownloadUrl,
        'Title' => $jsonResult->Title,
        'Description' => $jsonResult->Description,
        'EpisodeNumber' => $jsonResult->EpisodeNumber
      ));
    }
    else {
      return new Response("Ups! Something went wrong");
    }
  }

  /**
   * @Route("/{id}", name="delete_episode", defaults={"id" = null})
   * @Method({"OPTIONS", "DELETE"})
   */
  public function delete($podcast_id, $id) {
    $this->keyFromParams = 'podcasts/' . $podcast_id . '/episodes/' . $id;
    $this->id = $id;
    $this->podcast_id = $podcast_id;
    try {
      $result = $this->S3Client->deleteObject([
        'Bucket' => $this->bucketName,
        'Key' => $this->keyFromParams
      ]);
    } catch (S3Exception $e) {
      echo "Error, couldn't complete delete action";
    }
    if(isset($result)) {
      $result = $result['Body'];
      return new Response("<p>Episode ". $this->id . "of " . $this->podcast_id . " podcast has been deleted.</p>");
    }
    else {
      return new Response ("Could not delete anything", HTTP_, );
    }
  }

  /*  TO DO
    - set http response codes, as for now when there's nothing to show with get request, it still responds with 200 code etc.
    - list all episodes within a particular podcast
    - force JSON requests/responses
    - ?? update episodes metadata ??
  */








}