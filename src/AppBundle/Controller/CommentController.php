<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\HttpFoundation\JsonResponse;
use BackendBundle\Entity\User;
use BackendBundle\Entity\Video;
use BackendBundle\Entity\Comment;

/**
 * Description of CommentController
 *
 * @author morpheo848
 */
class CommentController extends Controller{
    
    public function newAction(Request $request){
        
        $helpers = $this->get("app.helpers");
        
        $hash = $request->get("authoritation", null);
        $authCheck = $helpers->authCheck($hash);
        
        if($authCheck == true){
            $identity = $helpers->authCheck($hash, true);
            
            $json = $request->get("json", null);
            
            if( $json != null ){
                $params = json_decode($json);

                $created_at = new \Datetime('now');
                $updated_at = new \Datetime('now');
                
                $user_id = ($identity->sub != null) ? $identity->sub : null;
                $video_id = (isset($params->video_id)) ? $params->video_id : null;
                $body = (isset($params->body)) ? $params->body : null;
                
                if($user_id != null && $video_id != null){
                    $em = $this->getDoctrine()->getManager();
                    
                    $user = $em->getRepository('BackendBundle:User')->findOneBy(
                                array(
                                    "id" => $user_id
                                )
                            );
                    $video = $em->getRepository('BackendBundle:Video')->findOneBy(
                                array(
                                    "id" => $video_id
                                )
                            );
                    
                    $comment = new Comment();
                    $comment->setUser($user);
                    $comment->setVideo($video);
                    $comment->setBody($body);
                    $comment->setCreatedAt($created_at);
                    $comment->setUpdatedAt($updated_at);
                    
                    $em->persist($comment);
                    $em->flush();
                    
                    $comment = $em->getRepository("BackendBundle:Comment")->findOneBy(
                                array(
                                    "user"      => $user,
                                    "video"     => $video,
                                    "body"      => $body,
                                    "createdAt" => $created_at,
                                )
                            );
                    
                    $data = array(
                            "status" => "success",
                            "code"   => 200,
                            "data"    => $comment
                    );
                }else{
                    $data = array(
                                "status" => "error",
                                "code"   => 400,
                                "msg"    => "Comment not created"
                            );
                }
            }else{
                $data = array(
                                "status" => "error",
                                "code"   => 400,
                                "msg"    => "Video not created, params failed"
                        );
            }
            
        }else{
            $data = array(
                            "status" => "error",
                            "code"   => 400,
                            "msg"    => "Authoritation not valid"
                        );
        }
        
        return $helpers->json($data);
    }
    
    public function deleteAction(Request $request, $id = null){
        
        $helpers = $this->get("app.helpers");
        
        $hash = $request->get("authoritation", null);
        $authCheck = $helpers->authCheck($hash);
        
        if($authCheck == true){
            $identity = $helpers->authCheck($hash, true);
            
            $json = $request->get("json", null);
            
            $user_id = ($identity->sub != null) ? $identity->sub : null;
            
            $em = $this->getDoctrine()->getManager();
            $comment = $em->getRepository("BackendBundle:Comment")->findOneBy(
                    array(
                        "id" => $id
                    )
                    );
            
            if( is_object($comment) && $user_id != null ){
                if( isset($identity->sub) && 
                        ($identity->sub == $comment->getUser()->getId() ||
                        $identity->sub == $comment->getVideo()->getUser()->getId()) ){
                    
                    $em->remove($comment);
                    $em->flush();

                    $data = array(
                                "status"  => "success",
                                "code"    => 200,
                                "message" =>"Comment deleted"
                    );
                }else{
                    $data = array(
                               "status" => "error",
                               "code"   => 400,
                               "msg"    => "Comment not deleted"
                           );
                }
                
            }else{
                 $data = array(
                            "status" => "error",
                            "code"   => 400,
                            "msg"    => "Comment not deleted"
                        );
            }
             
        }else{
            $data = array(
                            "status" => "error",
                            "code"   => 400,
                            "msg"    => "Authoritation not valid"
                        );
        }
        
        return $helpers->json($data);
    }
    
    public function listAction(Request $request, $id = null){
        
        $helpers = $this->get("app.helpers");
        $em = $this->getDoctrine()->getManager();
        
        $video = $em->getRepository("BackendBundle:Video")->findOneBy(array(
                "id" => $id
            ));
        
        $comments = $em->getRepository("BackendBundle:Comment")->findBy(array(
                "video" => $video
            ), array("id"=>"DESC"));
        
        if( count($comments) >= 1 ){
            $data = array(
                "status" => "success",
                "status" => 200,
                "data"   => $comments,
            );
        }else{
            $data = array(
                "status" => "error",
                "status" => 400,
                "msg"   => "Dont exists comments in this video!!",
            );
        }
        
        return $helpers->json($data);  
    }
    
    
}
