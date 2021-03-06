<?php

namespace App\Controller;

use Aws\S3\Exception\S3Exception;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
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
    $this->request = Request::createFromGlobals();
    $this->path = substr($this->request->getPathInfo(), 1);
    $this->url = "https://s3.eu-west-2.amazonaws.com/" . $this->bucketName . $this->path;
  }


  /**
   * @Route("/{id}",
   *   requirements={"id" = "\d+"}, defaults={"id" = null})
   * @Method("POST")
   */
  public function create($podcast_id, $id) {
    if(!isset($id)) {
      return new Response('No episode id specified', 400);
    }
    //serve the case of wrong content-type of request, expecting json
    if($this->request->getContentType() === 'json') {
      $subDirectory = 'podcasts/' . $podcast_id . '/episodes/';
      //check if episode doesn't exist yet
      try {
        $objects = $this->S3Client->getIterator('ListObjects', array('Bucket' => $this->bucketName, 'Prefix' => $subDirectory));
      }
      catch(S3Exception $e) {
        return new Response("Could not list episodes", 400);
      }

      foreach($objects as $object) {
        if(strpos($id, substr($object['Key'], strlen($subDirectory))) !== false) {
          //if exists inform with appropriate message and http code
          return new Response("Episode already exist.<br>" . substr($object['Key'], strlen($subDirectory)), 409);
        }
      }
      //otherwise upload episode data
      $content = $this->request->getContent();
      $result = $this->S3Client->putObject([
        'Bucket' => $this->bucketName,
        'Key'    => $this->path,
        'Body'   => $content
      ]);
      if(isset($result)) {
        return new Response("Episode created.", 201);
      }
    }
    else {
      return new Response("Wrong content-type, expecting 'application/json'", 415);
    }
  }

  /**
   * @Route("/", name="episodes_list")
   * @Method("GET")
   */
  public function list($podcast_id) {
    if($this->request->getContentType() === "json") {
      $result = [];
      try {
        $objects = $this->S3Client->getIterator('ListObjects', array('Bucket' => $this->bucketName, 'Key' => $podcast_id));
      }
      catch(S3Exception $e) {
        return new Response("Could not list episodes", 400);
      }

      $subDirectory = 'podcasts/' . $podcast_id . '/episodes/';
      foreach($objects as $object) {
        if(substr($object['Key'], 0, strlen($subDirectory)) === $subDirectory) {
          array_push($result, substr($object['Key'], strlen($subDirectory)));
        }
      }
      json_encode($result);
      return new JsonResponse($result, 200);
    }
    else {
      return new Response("Wrong content-type, expected 'application/json", 415);
    }
  }


  /**
   * @Route("/{id}", name="episode", defaults={"id" = null})
   * @Method("GET")
   */
  public function show($podcast_id, $id = null) {
    if($this->request->getContentType() === 'json') {
      //load object or return error
      try {
        $result = $this->S3Client->getObject([
          'Bucket' => $this->bucketName,
          'Key' => $this->path
        ]);
      } catch (S3Exception $e) {
        if ($e->getAwsErrorMessage() === "The specified key does not exist.") {
          return new Response($e->getAwsErrorMessage(), 404);
        } else {
          return new Response('', 400);
        }
      }
      if ($result) {
        $jsonResult = $result['Body'];
        return new JsonResponse($jsonResult, 200, array(), true);
      } else {
        return new Response("Ups! Something went wrong", 404);
      }
    }
    else {
      return new Response("Wrong content-type of request", 415);
    }
  }


  /**
   * @Route("/{id}", name="delete_episode", defaults={"id" = null})
   * @Method("DELETE")
   */
  public function delete($podcast_id, $id) {
    $result = $this->S3Client->deleteObject([
      'Bucket' => $this->bucketName,
      'Key' => $this->path
    ]);
    if($result) {
      return new Response("result delete marker = " . $result->get('deleteMarker'), 204);
    }
    else {
      return new Response ("Could not delete anything", 400);
    }
  }

  /*  TO DO
    - ?? update episodes metadata ??
  */

}